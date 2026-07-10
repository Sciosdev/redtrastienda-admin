<?php

namespace App\Http\Controllers\RestAPI\v1\auth;

use App\Contracts\Repositories\AffiliateProfileRepositoryInterface;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\LoginSetupRepositoryInterface;
use App\Contracts\Repositories\NumeroAnpRepositoryInterface;
use App\Contracts\Repositories\PhoneOrEmailVerificationRepositoryInterface;
use App\Services\ActivacionCuentaService;
use App\Services\AffiliateProfileService;
use App\Events\CustomerRegisteredViaReferralEvent;
use App\Events\EmailVerificationEvent;
use App\Events\PasswordResetEvent;
use App\Http\Controllers\Controller;
use App\Services\ReferByEarnCustomerService;
use App\Services\Web\CustomerAuthService;
use App\Traits\CustomerTrait;
use App\Utils\Helpers;
use App\Utils\SMSModule;
use Carbon\CarbonInterval;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;

class CustomerAPIAuthController extends Controller
{
    use CustomerTrait;

    /** Patrón del número ANP como identidad de login (ANP + dígitos + letra final opcional). */
    private const PATRON_NUMERO_ANP = '/^anp\d+a?$/i';

    public function __construct(
        private readonly CustomerRepositoryInterface                 $customerRepo,
        private readonly BusinessSettingRepositoryInterface          $businessSettingRepo,
        private readonly PhoneOrEmailVerificationRepositoryInterface $phoneOrEmailVerificationRepo,
        private readonly LoginSetupRepositoryInterface               $loginSetupRepo,
        private readonly CustomerAuthService                         $customerAuthService,
        private readonly ReferByEarnCustomerService                  $referByEarnCustomerService,
        private readonly NumeroAnpRepositoryInterface                $numeroAnpRepo,
        private readonly AffiliateProfileService                     $affiliateProfileService,
        private readonly AffiliateProfileRepositoryInterface         $affiliateProfileRepo,
        private readonly ActivacionCuentaService                     $activacionCuentaService,
    )
    {
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|unique:users',
            'phone' => 'required|min:6|max:20|unique:users',
            'password' => 'required|min:6',
        ], [
            'f_name.required' => translate('The first name field is required.'),
            'l_name.required' => translate('The last name field is required.'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        // R-Lead: interesado SIN número ANP. Crea cuenta pendiente que no puede
        // iniciar sesión; el registro con ANP y el legacy siguen intactos.
        $esLead = filter_var($request['es_lead'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $numeroAnp = null;
        if (!$esLead) {
            $anpResolution = $this->resolveNumeroAnpForRegistration($request);
            if ($anpResolution['error']) {
                return $anpResolution['error'];
            }
            $numeroAnp = $anpResolution['numero'];
        }

        $referUser = $request['referral_code'] ? $this->customerRepo->getFirstWhere(params: ['referral_code' => $request['referral_code']]) : null;

        $temporaryToken = Str::random(40);

        $user = DB::transaction(function () use ($request, $referUser, $temporaryToken, $numeroAnp, $esLead) {
            $user = $this->customerRepo->add([
                'name' => $request['f_name'] . ' ' . $request['l_name'],
                'f_name' => $request['f_name'],
                'l_name' => $request['l_name'],
                'email' => $request['email'],
                'phone' => $request['phone'],
                'password' => bcrypt($request['password']),
                'temporary_token' => $temporaryToken,
                'referral_code' => Helpers::generate_referer_code(),
                'referred_by' => $referUser?->id ?? null,
            ]);

            if ($numeroAnp) {
                $this->affiliateProfileService->createProfileAndConsumeAnp(request: $request, user: $user, numeroAnp: $numeroAnp);
            } elseif ($esLead) {
                $this->affiliateProfileService->createLeadProfile(request: $request, user: $user);
            }

            return $user;
        });

        if ($esLead) {
            $this->notificarNuevoLead(user: $user, nombreNegocio: $request['nombre_negocio'] ?? null);
            return response()->json([
                'lead' => true,
                'message' => translate('tu_solicitud_quedo_registrada_ANPEC_te_contactara'),
            ], 200);
        }

        $referralData = getWebConfig(name: 'ref_earning_customer');
        $referralEarningRate = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'ref_earning_exchange_rate']);
        if (!empty($referUser) && isset($referralData['ref_earning_discount_status']) && $referralData['ref_earning_discount_status'] == 1) {
            $referralCustomer = $this->referByEarnCustomerService->addReferralCustomerData(
                referralData: $referralData,
                referralEarningRate: $referralEarningRate,
                referUser: $referUser,
                userId: $user['id']
            );
            event(new CustomerRegisteredViaReferralEvent($referralCustomer, $referUser));
        }

        $emailVerification = getLoginConfig(key: 'email_verification') ?? 0;
        $phoneVerification = getLoginConfig(key: 'phone_verification') ?? 0;

        if ($phoneVerification && !$user->is_phone_verified) {
            return response()->json(['temporary_token' => $temporaryToken], 200);
        }
        if ($emailVerification && $user->email_verified_at == null) {
            return response()->json(['temporary_token' => $temporaryToken], 200);
        }

        $token = $user->createToken('LaravelAuthApp')->accessToken;
        return response()->json(['token' => $token], 200);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email_or_phone' => 'required',
            'password' => 'required|min:6',
            'type' => 'required|in:phone,email'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        // R-Afiliación: el número ANP también es identidad de login. Se resuelve
        // al email interno del afiliado y el flujo sigue idéntico.
        $identidad = trim((string)$request['email_or_phone']);
        if (preg_match(self::PATRON_NUMERO_ANP, $identidad)) {
            $perfilAnp = $this->affiliateProfileRepo->getFirstWhere(params: ['numero_anp' => $identidad], relations: ['customer']);
            if ($perfilAnp && !$perfilAnp->reclamada) {
                return response()->json(['errors' => [
                    ['code' => 'cuenta_sin_activar', 'message' => translate('tu_cuenta_aun_no_esta_activada_es_tu_primera_vez_activala')]
                ]], 403);
            }
            if ($perfilAnp?->customer) {
                $request->merge(['email_or_phone' => $perfilAnp->customer->email]);
            }
        }

        $type = $request['type'];
        $user = $this->customerRepo->getByIdentity(filters: ['identity' => $request['email_or_phone']]);
        $maxLoginHit = getWebConfig(name: 'maximum_login_hit') ?? 5;
        $tempBlockTime = getWebConfig(name: 'temporary_login_block_time') ?? 600; // seconds

        if (isset($user)) {
            if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $tempBlockTime) {
                $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();

                $errors = [];
                $errors[] = ['code' => 'login_block_time',
                    'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()
                ];
                return response()->json(['errors' => $errors], 403);
            }

            $data = [
                'email' => $user['email'],
                'password' => $request['password'],
            ];

            if (auth()->attempt($data)) {
                $temporaryToken = Str::random(40);
                $phoneVerification = getLoginConfig(key: 'phone_verification') ?? 0;
                $emailVerification = getLoginConfig(key: 'email_verification') ?? 0;
                $emailVerification = !$phoneVerification ? $emailVerification : 0;

                if (
                    ($phoneVerification && !$user['is_phone_verified']) ||
                    ($emailVerification && !$user['is_email_verified'])
                ) {
                    return response()->json([
                        'temporary_token' => $temporaryToken,
                        'status' => false,
                        'phone' => $user['phone'],
                        'email' => $user['email'],
                        'is_phone_verified' => $user['is_phone_verified'],
                        'is_email_verified' => $user['is_email_verified'],
                    ], 200);
                }

                if ($user['is_active'] != 1) {
                    return response()->json(['errors' => [
                        ['code' => 'active', 'message' => translate('This_user_is_not_active!')]
                    ]], 403);
                }

                if ($leadError = $this->getLeadPendienteError(user: $user)) {
                    return $leadError;
                }

                $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;

                $this->customerRepo->updateWhere(params: ['id' => $user['id']], data: [
                    'login_hit_count' => 0,
                    'is_temp_blocked' => 0,
                    'temp_block_time' => null,
                    'updated_at' => now()
                ]);

                return response()->json(['token' => $token, 'status' => true], 200);
            } else {
                $code = 'invalid_credentials';
                $errorMsg = translate('credentials_doesnt_match');

                if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->diffInSeconds() <= $tempBlockTime) {
                    $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->diffInSeconds();
                    $code = 'login_block_time';
                    $errorMsg = translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();
                } elseif ($user['is_temp_blocked'] == 1 && Carbon::parse($user['temp_block_time'])->diffInSeconds() >= $tempBlockTime) {
                    $this->customerRepo->updateWhere(params: ['id' => $user['id']], data: $this->customerAuthService->getCustomerLoginDataReset());
                    $errorMsg = translate('credentials_doesnt_match');
                } elseif ($user['login_hit_count'] >= $maxLoginHit && $user['is_temp_blocked'] == 0) {
                    $this->customerRepo->updateWhere(params: ['id' => $user['id']], data: [
                        'is_temp_blocked' => 1,
                        'temp_block_time' => now(),
                        'updated_at' => now()
                    ]);
                    $time = $tempBlockTime - Carbon::parse($user['temp_block_time'])->diffInSeconds();
                    $code = 'login_temp_blocked';
                    $errorMsg = translate('too_many_attempts._please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();
                }
                $user = $this->customerRepo->getByIdentity(filters: ['identity' => $request['email_or_phone']]);
                $this->customerRepo->updateWhere(params: ['id' => $user['id']], data: [
                    'login_hit_count' => ($user['login_hit_count'] + 1),
                    'updated_at' => now()
                ]);

                $errors = [];
                $errors[] = [
                    'code' => $code,
                    'message' => $errorMsg
                ];
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
        }

        $errors = [];
        $errors[] = ['code' => 'auth-001', 'message' => translate('Invalid_credentials')];
        return response()->json(['errors' => $errors], 401);
    }


    public function checkPhone(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|min:6|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $OTPIntervalTime = getWebConfig(name: 'otp_resend_time') ?? 60;// seconds
        $OTPVerificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $request['phone']]);

        if (isset($OTPVerificationData) && Carbon::parse($OTPVerificationData['created_at'])->DiffInSeconds() < $OTPIntervalTime) {
            $time = $OTPIntervalTime - Carbon::parse($OTPVerificationData['created_at'])->DiffInSeconds();
            $errors = [];
            $errors[] = [
                'code' => 'otp',
                'message' => translate('please_try_again_after_') . $time . ' ' . translate('seconds')
            ];
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $token = (env('APP_MODE') == 'live') ? rand(100000, 999999) : 123456;
        $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $request['phone']], value: [
            'phone_or_email' => $request['phone'],
            'token' => $token,
        ]);

        $response = SMSModule::sendCentralizedSMS($request['phone'], $token);
        if (env('APP_MODE') == 'dev') {
            $response = 'success';
        }

        if ($response == 'success') {
            return response()->json([
                'message' => $response,
                'token' => 'active'
            ], 200);
        }
        return response()->json([
            'message' => translate('OTP_sending_failed'),
            'token' => 'inactive'
        ], 401);
    }


    public function checkEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $emailVerification = $this->loginSetupRepo->getFirstWhere(params: ['key' => 'email_verification'])?->value ?? 0;
        if ($emailVerification == 1) {
            $OTPIntervalTime = getWebConfig(name: 'otp_resend_time') ?? 60;// seconds
            $OTPVerificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $request['email']]);

            if (isset($OTPVerificationData) && Carbon::parse($OTPVerificationData['created_at'])->DiffInSeconds() < $OTPIntervalTime) {
                $time = $OTPIntervalTime - Carbon::parse($OTPVerificationData['created_at'])->DiffInSeconds();

                $errors = [];
                $errors[] = [
                    'code' => 'otp',
                    'message' => translate('please_try_again_after_') . $time . ' ' . translate('seconds')
                ];
                return response()->json([
                    'errors' => $errors
                ], 403);
            }

            $token = (env('APP_MODE') == 'live') ? rand(100000, 999999) : 123456;

            $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $request['email']], value: [
                'phone_or_email' => $request['email'],
                'token' => $token,
            ]);

            try {
                $emailServices = getWebConfig(name: 'mail_config');
                if ($emailServices['status'] == 0) {
                    $emailServices = getWebConfig(name: 'mail_config_sendgrid');
                }

                if (isset($emailServices['status']) && $emailServices['status'] == 1) {
                    $data = [
                        'userName' => $request['email'],
                        'subject' => translate('registration_Verification_Code'),
                        'title' => translate('registration_Verification_Code'),
                        'verificationCode' => $token,
                        'userType' => 'customer',
                        'templateName' => 'registration-verification',
                    ];
                    event(new EmailVerificationEvent(email: $request['email'], data: $data));
                }
            } catch (\Exception $exception) {
                return response()->json([
                    'errors' => [
                        ['code' => 'otp', 'message' => translate('Token_sent_failed')]
                    ]
                ], 403);
            }

            return response()->json([
                'message' => translate('Email is ready to register'),
                'token' => 'active'
            ], 200);

        } else {
            return response()->json([
                'message' => translate('Email is ready to register'),
                'token' => 'inactive'
            ], 200);
        }
    }


    public function verifyPhone(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $verify = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $request['phone'], 'token' => $request['token']]);
        $verificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $request['phone']]);

        $verifyStatus = $this->checkCustomerOTPBlockTimeOrInvalid(verificationData: $verificationData, identity: $request['phone']);
        if ($verifyStatus['status'] == 1) {
            return response()->json([
                'errors' => [
                    ['code' => $verifyStatus['code'], 'message' => $verifyStatus['message']]
                ]
            ], 403);
        }

        if (isset($verify)) {
            $this->customerRepo->updateWhere(params: ['phone' => $request['phone']], data: [
                'is_phone_verified' => 1
            ]);

            $user = $this->customerRepo->getFirstWhere(params: ['phone' => $request['phone']]);
            $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => $request['phone']]);
            if ($user['is_active'] != 1) {
                return response()->json(['errors' => [
                    ['code' => 'active', 'message' => translate('This_user_is_not_active!')]
                ]], 403);
            }

            if ($leadError = $this->getLeadPendienteError(user: $user)) {
                return $leadError;
            }

            $token = $user->createToken('LaravelAuthApp')->accessToken;
            return response()->json(['message' => translate('OTP verified!'), 'token' => $token, 'status' => true], 200);
        }

        return response()->json(['errors' => [
            ['code' => 'token', 'message' => translate('OTP_is_not_matched')]
        ]], 403);
    }


    public function verifyEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $maxOTPHit = getWebConfig(name: 'maximum_otp_hit') ?? 5;
        $maxOTPHitTime = getWebConfig(name: 'otp_resend_time') ?? 60;// seconds
        $tempBlockTime = getWebConfig(name: 'temporary_block_time') ?? 600; // seconds

        $verify = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $request['email'], 'token' => $request['token']]);
        $verificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $request['email']]);

        $verifyStatus = $this->checkCustomerOTPBlockTimeOrInvalid(verificationData: $verificationData, identity: $request['email']);
        if ($verifyStatus['status'] == 1) {
            return response()->json([
                'errors' => [
                    ['code' => $verifyStatus['code'], 'message' => $verifyStatus['message']]
                ]
            ], 403);
        }

        if (isset($verify)) {
            $this->customerRepo->updateWhere(params: ['email' => $request['email']], data: [
                'email_verified_at' => now(),
                'is_email_verified' => 1
            ]);
            $user = $this->customerRepo->getFirstWhere(params: ['email' => $request['email']]);
            $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => $request['email']]);

            if ($user['is_active'] != 1) {
                return response()->json(['errors' => [
                    ['code' => 'active', 'message' => translate('This_user_is_not_active!')]
                ]], 403);
            }

            if ($leadError = $this->getLeadPendienteError(user: $user)) {
                return $leadError;
            }

            $token = $user->createToken('LaravelAuthApp')->accessToken;
            return response()->json(['message' => translate('OTP_verified'), 'token' => $token, 'status' => true], 200);
        }

        return response()->json(['errors' => [
            ['code' => 'otp', 'message' => translate('OTP_is_not_matched!')]
        ]], 403);
    }


    public function registration(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|unique:users',
            'phone' => 'required|min:6|max:20|unique:users',
            'password' => 'required|min:6',
        ], [
            'f_name.required' => translate('The first name field is required.'),
            'l_name.required' => translate('The last name field is required.'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $anpResolution = $this->resolveNumeroAnpForRegistration($request);
        if ($anpResolution['error']) {
            return $anpResolution['error'];
        }
        $numeroAnp = $anpResolution['numero'];

        $refer_user = $request['referral_code'] ? $this->customerRepo->getFirstWhere(params: ['referral_code' => $request['referral_code']]) : null;

        $temporaryToken = Str::random(40);

        $user = DB::transaction(function () use ($request, $refer_user, $temporaryToken, $numeroAnp) {
            $user = $this->customerRepo->add([
                'f_name' => $request['f_name'],
                'l_name' => $request['l_name'],
                'email' => $request['email'],
                'phone' => $request['phone'],
                'password' => bcrypt($request['password']),
                'temporary_token' => $temporaryToken,
                'referral_code' => Helpers::generate_referer_code(),
                'referred_by' => $refer_user->id ?? null,
            ]);

            if ($numeroAnp) {
                $this->affiliateProfileService->createProfileAndConsumeAnp(request: $request, user: $user, numeroAnp: $numeroAnp);
            }

            return $user;
        });

        $emailVerification = getLoginConfig(key: 'email_verification') ?? 0;
        $phoneVerification = getLoginConfig(key: 'phone_verification') ?? 0;

        if ($phoneVerification && !$user->is_phone_verified) {
            return response()->json(['temporary_token' => $temporaryToken], 200);
        }
        if ($emailVerification && $user->email_verified_at == null) {
            return response()->json(['temporary_token' => $temporaryToken], 200);
        }

        $token = $user->createToken('LaravelAuthApp')->accessToken;
        return response()->json(['token' => $token], 200);
    }


    public function remove_account(Request $request): JsonResponse
    {
        $customer = $this->customerRepo->getFirstWhere(params: ['id' => $request->user()->id]);
        if (isset($customer)) {
            Helpers::file_remover('customer/', $customer->image);
            $customer->delete();
        } else {
            return response()->json(['status_code' => 404, 'message' => translate('Not found')], 200);
        }
        return response()->json(['status_code' => 200, 'message' => translate('Successfully deleted')], 200);
    }

    public function firebaseAuthVerify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sessionInfo' => 'required',
            'phoneNumber' => 'required',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $verificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $request['phoneNumber']]);
        $verifyStatus = $this->checkCustomerOTPBlockTimeOrInvalid(verificationData: $verificationData, identity: $request['phoneNumber']);
        if ($verifyStatus['status'] == 1) {
            return response()->json([
                'errors' => [
                    ['code' => $verifyStatus['code'], 'message' => $verifyStatus['message']]
                ]
            ], 403);
        }

        $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification');
        $webApiKey = $firebaseOTPVerification ? $firebaseOTPVerification['web_api_key'] : '';

        $response = Http::post('https://identitytoolkit.googleapis.com/v1/accounts:signInWithPhoneNumber?key=' . $webApiKey, [
            'sessionInfo' => $request['sessionInfo'],
            'phoneNumber' => $request['phoneNumber'],
            'code' => $request['code'],
        ]);

        $responseData = $response->json();

        if (isset($responseData['error'])) {
            $errors = [];
            $errors[] = ['code' => "403", 'message' => translate(strtolower($responseData['error']['message']))];
            return response()->json(['errors' => $errors], 403);
        }

        $user = $this->customerRepo->getByIdentity(filters: ['identity' => $responseData['phoneNumber']]);

        if (isset($user)) {
            if ($request['is_reset_token'] == 1) {
                DB::table('password_resets')
                    ->where('user_type', 'customer')
                    ->updateOrInsert(['identity' => $request['phoneNumber']], [
                        'identity' => $request['phoneNumber'],
                        'token' => $request['code'],
                        'created_at' => now(),
                    ]);
            } else {
                if ($leadError = $this->getLeadPendienteError(user: $user)) {
                    return $leadError;
                }

                $token = $user->createToken('LaravelAuthApp')->accessToken;
                $user['is_phone_verified'] = 1;
                $user->save();
                return response()->json(['errors' => null, 'token' => $token], 200);
            }
        }

        $tempToken = Str::random(120);
        return response()->json(['errors' => null, 'temp_token' => $tempToken], 200);
    }

    public function verifyOTP(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $verify = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $request['phone'], 'token' => $request['token']]);
        $verificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $request['phone']]);

        $verifyStatus = $this->checkCustomerOTPBlockTimeOrInvalid(verificationData: $verificationData, identity: $request['phone']);
        if ($verifyStatus['status'] == 1) {
            return response()->json([
                'errors' => [
                    ['code' => $verifyStatus['code'], 'message' => $verifyStatus['message']]
                ]
            ], 403);
        }

        if (isset($verify)) {
            $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => $request['phone']]);
            $temporaryToken = Str::random(40);

            $isUserExist = $this->customerRepo->getFirstWhere(params: ['phone' => $request['phone']]);
            if (!$isUserExist) {
                return response()->json(['temporary_token' => $temporaryToken, 'status' => false], 200);
            }

            $this->customerRepo->updateWhere(params: ['phone' => $request['phone']], data: [
                'is_phone_verified' => 1
            ]);

            if ($isUserExist['is_active'] != 1) {
                return response()->json(['errors' => [
                    ['code' => 'active', 'message' => translate('This_user_is_not_active!')]
                ]], 403);
            }

            if ($leadError = $this->getLeadPendienteError(user: $isUserExist)) {
                return $leadError;
            }

            $token = $isUserExist->createToken('LaravelAuthApp')->accessToken;
            return response()->json(['token' => $token, 'status' => true], 200);
        }

        return response()->json(['errors' => [
            ['code' => 'token', 'message' => translate('OTP is not matched!')]
        ]], 403);
    }

    public function registrationWithOTP(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|max:255',
            'phone' => 'required|string|min:6|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        if ($request['email']) {
            $isEmailExist = $this->customerRepo->getFirstWhere(params: ['email' => $request['email']]);

            if ($isEmailExist) {
                return response()->json(['errors' => [
                    ['code' => 'email', 'message' => translate('this_email_has_already_been_used_in_another_account!')]
                ]], 403);
            }
        }

        $anpResolution = $this->resolveNumeroAnpForRegistration($request);
        if ($anpResolution['error']) {
            return $anpResolution['error'];
        }
        $numeroAnp = $anpResolution['numero'];

        $temporaryToken = Str::random(40);

        $user = DB::transaction(function () use ($request, $temporaryToken, $numeroAnp) {
            $user = $this->customerRepo->add([
                'name' => $request['name'],
                'f_name' => $request['name'],
                'email' => $request['email'],
                'phone' => $request['phone'],
                'password' => bcrypt(rand(11111111, 99999999)),
                'temporary_token' => $temporaryToken,
                'app_language' => 'en',
                'is_phone_verified' => 1,
                'referral_code' => Helpers::generate_referer_code(),
                'login_medium' => 'OTP',
            ]);

            if ($numeroAnp) {
                $this->affiliateProfileService->createProfileAndConsumeAnp(request: $request, user: $user, numeroAnp: $numeroAnp);
            }

            return $user;
        });

        $token = $user->createToken('LaravelAuthApp')->accessToken;
        return response()->json(['token' => $token], 200);
    }

    public function customerSocialLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'unique_id' => 'required',
            'email' => 'required_if:medium,google,facebook',
            'medium' => 'required|in:google,facebook,apple',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $client = new Client();
        $token = $request['token'];
        $email = $request['email'];
        $uniqueId = $request['unique_id'];

        try {
            if ($request['medium'] == 'google') {
                $res = $client->request('GET', 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=' . $token);
                $data = json_decode($res->getBody()->getContents(), true);
            } elseif ($request['medium'] == 'facebook') {
                $res = $client->request('GET', 'https://graph.facebook.com/' . $uniqueId . '?access_token=' . $token . '&&fields=name,email');
                $data = json_decode($res->getBody()->getContents(), true);
            } elseif ($request['medium'] == 'apple') {
                $apple_login = getWebConfig(name: 'apple_login');
                $teamId = $apple_login['team_id'];
                $keyId = $apple_login['key_id'];
                $sub = $apple_login['client_id'];
                $aud = 'https://appleid.apple.com';
                $iat = strtotime('now');
                $exp = strtotime('+60days');
                $keyContent = file_get_contents('storage/app/public/apple-login/' . $apple_login['service_file']);
                $token = JWT::encode([
                    'iss' => $teamId,
                    'iat' => $iat,
                    'exp' => $exp,
                    'aud' => $aud,
                    'sub' => $sub,
                ], $keyContent, 'ES256', $keyId);

                $redirect_uri = $apple_login['redirect_url'] ?? 'www.example.com/apple-callback';

                $res = Http::asForm()->post('https://appleid.apple.com/auth/token', [
                    'grant_type' => 'authorization_code',
                    'code' => $uniqueId,
                    'redirect_uri' => $redirect_uri,
                    'client_id' => $sub,
                    'client_secret' => $token,
                ]);

                $claims = explode('.', $res['id_token'])[1];
                $data = json_decode(base64_decode($claims), true);
            }
        } catch (\Exception $exception) {
            $errors = [];
            $errors[] = ['code' => 'auth-001', 'message' => 'Invalid Token'];
            return response()->json([
                'errors' => $errors
            ], 401);
        }

        if (!isset($claims) && isset($data)) {
            if (strcmp($email, $data['email']) != 0) {
                return response()->json(['error' => translate('email_does_not_match')], 403);
            }
        }

        $existingUser = $this->customerRepo->getFirstWhere(params: ['email' => $data['email']]);
        $temporaryToken = Str::random(40);

        if (!$existingUser) {
            return response()->json(['temp_token' => $temporaryToken, 'status' => false], 200);
        }

        if ($existingUser['is_active'] != 1) {
            return response()->json(['errors' => [
                ['code' => 'active', 'message' => translate('This_user_is_not_active!')]
            ]], 403);
        }

        if ($existingUser->email_verified_at != null) {
            if ($leadError = $this->getLeadPendienteError(user: $existingUser)) {
                return $leadError;
            }

            $token = $existingUser->createToken('LaravelAuthApp')->accessToken;
            return response()->json(['token' => $token, 'status' => true], 200);
        } else {
            return response()->json(['user' => $existingUser, 'status' => false], 200);
        }
    }

    public function existingAccountCheck(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'user_response' => 'required|in:0,1',
            'medium' => 'required|in:google,facebook,apple',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $user = $this->customerRepo->getFirstWhere(params: ['email' => $request['email']]);

        $temporaryToken = Str::random(40);
        if (!$user) {
            return response()->json(['temp_token' => $temporaryToken, 'status' => false], 200);
        }

        if ($user['is_active'] != 1) {
            return response()->json(['errors' => [
                ['code' => 'active', 'message' => translate('This_user_is_not_active!')]
            ]], 403);
        }

        if ($request['user_response'] == 1) {
            if ($leadError = $this->getLeadPendienteError(user: $user)) {
                return $leadError;
            }

            $user->email_verified_at = now();
            $user->login_medium = $request['medium'];
            $user->save();

            $token = $user->createToken('LaravelAuthApp')->accessToken;
            return response()->json(['token' => $token, 'status' => true], 200);
        }

        $user->email = null;
        $user->email_verified_at = null;
        $user->save();

        return response()->json(['temp_token' => $temporaryToken, 'status' => false], 200);
    }

    public function registrationWithSocialMedia(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'phone' => 'required|min:6|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $isPhoneExist = $this->customerRepo->getFirstWhere(params: ['phone' => $request['phone']]);

        if ($isPhoneExist) {
            return response()->json(['errors' => [
                ['code' => 'email', 'message' => translate('This phone has already been used in another account!')]
            ]], 403);
        }

        $temporaryToken = Str::random(40);
        $user = $this->customerRepo->add([
            'name' => $request['name'],
            'f_name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'password' => bcrypt(rand(11111111, 99999999)),
            'temporary_token' => $temporaryToken,
            'app_language' => 'en',
            'email_verified_at' => now(),
            'referral_code' => Helpers::generate_referer_code(),
            'login_medium' => 'social',
        ]);

        $phoneVerificationStatus = getLoginConfig(key: 'phone_verification') ?? 0;
        if ($phoneVerificationStatus) {
            return response()->json(['temp_token' => $temporaryToken, 'status' => false]);
        }

        $token = $user->createToken('LaravelAuthApp')->accessToken;
        return response()->json(['token' => $token]);
    }

    public function passwordResetRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email_or_phone' => 'required',
            'type' => 'required|in:phone,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        if ($request['type'] == 'phone') {
            $customer = $this->customerRepo->getFirstWhere(params: ['phone' => $request['email_or_phone']]);
        } else {
            $customer = $this->customerRepo->getFirstWhere(params: ['email' => $request['email_or_phone']]);
        }

        if (isset($customer)) {
            $OTPIntervalTime = getWebConfig(name: 'otp_resend_time') ?? 60; // seconds
            $passwordVerificationData = DB::table('password_resets')->where('identity', $request['email_or_phone'])->first();

            if (isset($passwordVerificationData) && Carbon::parse($passwordVerificationData?->created_at)->DiffInSeconds() < $OTPIntervalTime) {
                $time = $OTPIntervalTime - Carbon::parse($passwordVerificationData?->created_at)->DiffInSeconds();

                $errors = [];
                $errors[] = [
                    'code' => 'otp',
                    'message' => translate('please_try_again_after_') . $time . ' ' . translate('seconds')
                ];
                return response()->json(['errors' => $errors], 403);
            }

            $token = (env('APP_MODE') == 'live') ? rand(100000, 999999) : 123456;

            DB::table('password_resets')->updateOrInsert(['identity' => $request['email_or_phone']], [
                'token' => $token,
                'created_at' => now(),
            ]);

            DB::table('phone_or_email_verifications')->insert([
                'phone_or_email' => $request['email_or_phone'],
                'token' => $token,
                'created_at' => now(),
            ]);

            if ($request['type'] == 'phone') {
                $response = SMSModule::sendCentralizedSMS($customer['phone'], $token);

                if ($response != 'success') {
                    return response()->json(['errors' => [
                        ['code' => 'config-missing', 'message' => translate('Unable_to_send_the_verification_code.')]
                    ]], 400);
                }
                return response()->json([
                    'message' => translate('OTP_sent_successfully'),
                    'type' => 'sent_to_phone'
                ], 200);
            } else if ($request['type'] == 'email') {
                try {
                    $emailServices = getWebConfig(name: 'mail_config');
                    if ($emailServices['status'] == 0) {
                        $emailServices = getWebConfig(name: 'mail_config_sendgrid');
                    }

                    $resetUrl = route('customer.auth.reset-password', ['identity' => base64_encode($customer['email']), 'token' => $token]);
                    $data = [
                        'userType' => 'customer',
                        'templateName' => 'forgot-password',
                        'userName' => $customer['f_name'],
                        'subject' => translate('password_reset'),
                        'title' => translate('password_reset'),
                        'passwordResetURL' => $resetUrl,
                    ];

                    if (isset($emailServices['status']) && $emailServices['status'] == 1) {
                        event(new PasswordResetEvent(email: $customer['email'], data: $data));
                    }

                } catch (\Exception $exception) {
                    return response()->json(['errors' => [
                        ['code' => 'config-missing', 'message' => translate('Email_configuration_issue.')]
                    ]], 400);
                }
            }

            return response()->json([
                'message' => translate('Email_sent_successfully.'),
                'type' => 'sent_to_mail'
            ], 200);
        }

        return response()->json(['errors' => [
            ['code' => 'not-found', 'message' => translate('Customer_not_found!')]
        ]], 401);
    }

    public function verifyProfileInfo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:phone,email',
            'email_or_phone' => 'required',
            'token' => 'required'
        ]);

        $user = $this->customerRepo->getByIdentity(filters: ['identity' => $request['email_or_phone']]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $verificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $request['email_or_phone']]);
        $verifyStatus = $this->checkCustomerOTPBlockTimeOrInvalid(verificationData: $verificationData, identity: $request['email_or_phone']);
        if ($verifyStatus['status'] == 1) {
            return response()->json([
                'errors' => [
                    ['code' => $verifyStatus['code'], 'message' => $verifyStatus['message']]
                ]
            ], 403);
        }

        $verify = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $request['email_or_phone'], 'token' => $request['token']]);
        if (!$verify) {
            return response()->json(['errors' => [
                ['code' => 'token', 'message' => translate('OTP_is_not_matched')]
            ]], 403);
        }
        $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => $request['email_or_phone']]);

        if ($request['type'] == 'phone') {
            $this->customerRepo->updateWhere(['id' => $user?->id], data: [
                'phone' => $request['email_or_phone'],
                'is_phone_verified' => 1,
            ]);
            return response()->json(['message' => translate('Phone_number_is_successfully_verified')], 200);
        } else if ($request['type'] == 'email') {
            $this->customerRepo->updateWhere(['id' => $user?->id], data: [
                'email' => $request['email_or_phone'],
                'is_email_verified' => 1,
                'email_verified_at' => now(),
            ]);
            return response()->json(['message' => translate('Email_is_successfully_verified')], 200);
        }

        return response()->json(['errors' => [
            ['code' => 'token', 'message' => translate('Type_missing')]
        ]], 403);
    }

    public function firebaseAuthTokenStore(Request $request): JsonResponse
    {
        $this->phoneOrEmailVerificationRepo->updateOrCreate(params: ['phone_or_email' => $request['identity']], value: [
            'phone_or_email' => $request['identity'],
            'token' => $request['token'],
        ]);
        return response()->json(['message' => translate('Token_is_successfully_Saved')], 200);
    }

    public function checkNumeroAnp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'numero_anp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $numero = $this->numeroAnpRepo->getFirstWhere(params: ['numero_anp' => trim($request['numero_anp'])]);
        $existe = (bool)$numero;
        $disponible = $existe && $numero->estatus === 'disponible';

        // R-Acceso (aditivo): la app decide con esto qué pedir en la activación.
        // Solo el TIPO de factor; nunca datos personales.
        $perfil = $this->affiliateProfileRepo->getFirstWhere(params: ['numero_anp' => trim($request['numero_anp'])], relations: ['customer']);

        return response()->json([
            'existe' => $existe,
            'disponible' => $disponible,
            'precargado' => (bool)$perfil,
            'reclamada' => (bool)($perfil->reclamada ?? false),
            'factor' => $this->activacionCuentaService->determinarFactor(perfil: $perfil),
            'message' => !$existe
                ? translate('numero_anp_invalido')
                : ($disponible ? translate('numero_anp_disponible') : translate('numero_anp_no_disponible')),
        ], 200);
    }

    /**
     * R-Acceso paso 2: valida el segundo dato de identidad (teléfono o nombre)
     * y entrega un claim_token temporal. Con factor 'ninguno' recibe el correo
     * de contacto y deja la solicitud para verificación manual del admin.
     */
    public function verificarIdentidadAnp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'numero_anp' => 'required|string|max:50',
            'telefono' => 'nullable|string|max:30',
            'nombre' => 'nullable|string|max:150',
            'correo_contacto' => 'nullable|email|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $resultado = $this->activacionCuentaService->verificarIdentidad(
            numeroAnp: $request['numero_anp'],
            telefono: $request['telefono'],
            nombre: $request['nombre'],
            correoContacto: $request['correo_contacto'],
        );

        if (!$resultado['ok']) {
            return response()->json(['errors' => [
                ['code' => $resultado['code'], 'message' => $resultado['message']]
            ]], 403);
        }

        if (!empty($resultado['requiere_verificacion_manual'])) {
            return response()->json([
                'requiere_verificacion_manual' => true,
                'message' => $resultado['message'],
            ], 200);
        }

        return response()->json([
            'claim_token' => $resultado['claim_token'],
            'expira_en_minutos' => $resultado['expira_en_minutos'],
            'message' => $resultado['message'],
        ], 200);
    }

    /**
     * R-Acceso paso 3: crea las credenciales reales (correo + contraseña) de la
     * cuenta verificada y devuelve el token de sesión para entrar directo.
     */
    public function activarCuentaAnp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'claim_token' => 'required|string',
            'correo_real' => 'required|email|max:150',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $resultado = $this->activacionCuentaService->activarCuenta(
            claimToken: $request['claim_token'],
            correoReal: $request['correo_real'],
            password: $request['password'],
        );

        if (!$resultado['ok']) {
            return response()->json(['errors' => [
                ['code' => $resultado['code'], 'message' => $resultado['message']]
            ]], 403);
        }

        $token = $resultado['user']->createToken('LaravelAuthApp')->accessToken;

        return response()->json(['token' => $token, 'message' => $resultado['message']], 200);
    }

    /**
     * R-Lead: un lead pendiente sin número ANP solo navega como invitado. Se
     * evalúa DESPUÉS de validar credenciales para no regalar información.
     */
    private function getLeadPendienteError(object $user): ?JsonResponse
    {
        $perfil = $this->affiliateProfileRepo->getFirstWhere(params: ['customer_id' => $user->id]);
        if ($perfil && $perfil->numero_anp === null && $perfil->estatus === 'pendiente') {
            return response()->json(['errors' => [
                ['code' => 'lead_pendiente', 'message' => translate('tu_afiliacion_esta_en_proceso_ANPEC_te_contactara')]
            ]], 403);
        }
        return null;
    }

    /**
     * Aviso best-effort al correo de la empresa cuando entra un lead: si el
     * mail no está configurado, el registro no debe fallar.
     */
    private function notificarNuevoLead(object $user, ?string $nombreNegocio): void
    {
        try {
            $companyEmail = getWebConfig(name: 'company_email');
            if (empty($companyEmail)) {
                return;
            }

            $mensaje = translate('nuevo_interesado_en_afiliarse_a_ANPEC') . "\n\n"
                . translate('nombre') . ': ' . trim($user->f_name . ' ' . $user->l_name) . "\n"
                . translate('email') . ': ' . $user->email . "\n"
                . translate('phone') . ': ' . $user->phone . "\n"
                . ($nombreNegocio ? translate('nombre_negocio') . ': ' . $nombreNegocio . "\n" : '')
                . "\n" . translate('revisalo_en_el_panel_afiliados_filtro_leads_sin_numero');

            Mail::raw($mensaje, function ($message) use ($companyEmail) {
                $message->to($companyEmail)->subject(translate('nueva_solicitud_de_afiliacion_ANPEC'));
            });
        } catch (\Throwable $exception) {
            // Notificación no crítica: nunca debe tumbar el registro del lead.
        }
    }

    /**
     * Validate the ANP number for a registration request.
     * Returns ['error' => JsonResponse|null, 'numero' => NumeroAnp|null].
     * - No ANP + toggle off  => proceed as legacy (numero null).
     * - No ANP + toggle on    => validation error.
     * - ANP present           => must exist and be "disponible".
     */
    private function resolveNumeroAnpForRegistration(Request $request): array
    {
        $numeroAnp = trim((string)($request['numero_anp'] ?? ''));
        $obligatorio = (int)($this->businessSettingRepo->getFirstWhere(params: ['type' => 'numero_anp_obligatorio'])?->value ?? 0) === 1;

        if ($numeroAnp === '') {
            if ($obligatorio) {
                return [
                    'error' => response()->json(['errors' => [
                        ['code' => 'numero_anp', 'message' => translate('el_numero_anp_es_obligatorio')]
                    ]], 403),
                    'numero' => null,
                ];
            }
            return ['error' => null, 'numero' => null];
        }

        $numero = $this->numeroAnpRepo->getFirstWhere(params: ['numero_anp' => $numeroAnp]);
        if (!$numero) {
            return [
                'error' => response()->json(['errors' => [
                    ['code' => 'numero_anp', 'message' => translate('numero_anp_invalido')]
                ]], 403),
                'numero' => null,
            ];
        }
        if ($numero->estatus !== 'disponible') {
            return [
                'error' => response()->json(['errors' => [
                    ['code' => 'numero_anp', 'message' => translate('numero_anp_no_disponible')]
                ]], 403),
                'numero' => null,
            ];
        }

        return ['error' => null, 'numero' => $numero];
    }

}
