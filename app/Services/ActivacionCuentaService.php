<?php

namespace App\Services;

use App\Contracts\Repositories\AffiliateProfileRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\NumeroAnpRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * R-Acceso: activación (claim) de cuentas precargadas. El afiliado demuestra
 * identidad con su número ANP + un segundo dato (teléfono o nombre) y hasta
 * entonces CREA su contraseña y registra su correo real. Los claim_tokens son
 * opacos, viven en cache 15 minutos y nunca se loguean.
 */
class ActivacionCuentaService
{
    private const CLAIM_TTL_MINUTOS = 15;
    private const MAX_FALLOS_24H = 10;
    private const CACHE_CLAIM_PREFIX = 'anp_claim:';
    private const CACHE_FALLOS_PREFIX = 'anp_claim_fails:';

    public function __construct(
        private readonly AffiliateProfileRepositoryInterface $affiliateProfileRepo,
        private readonly NumeroAnpRepositoryInterface        $numeroAnpRepo,
        private readonly CustomerRepositoryInterface         $customerRepo,
    )
    {
    }

    /**
     * Second identity factor available for a preloaded profile:
     * - 'telefono' solo si telefono_contacto contiene al menos una secuencia de
     *   10 dígitos (hay celdas con teléfonos de 5 dígitos en el Excel real);
     *   si no, se degrada a 'nombre', y sin nombre queda 'ninguno'.
     */
    public function determinarFactor(?object $perfil): ?string
    {
        if (!$perfil) {
            return null;
        }
        $digitos = preg_replace('/\D+/', '', (string)$perfil->telefono_contacto);
        if (strlen($digitos) >= 10) {
            return 'telefono';
        }
        $nombre = trim((string)($perfil->customer?->f_name ?? ''));
        if ($nombre !== '' && $nombre !== '-') {
            return 'nombre';
        }
        return 'ninguno';
    }

    /**
     * Verify the affiliate's identity and hand out a claim token.
     *
     * @return array{ok:bool, code?:string, message:string, claim_token?:string, expira_en_minutos?:int, requiere_verificacion_manual?:bool}
     */
    public function verificarIdentidad(string $numeroAnp, ?string $telefono, ?string $nombre, ?string $correoContacto): array
    {
        $numero = strtoupper(trim($numeroAnp));
        $perfil = $this->affiliateProfileRepo->getFirstWhere(params: ['numero_anp' => $numero], relations: ['customer']);

        if (!$perfil || !$perfil->customer) {
            return ['ok' => false, 'code' => 'numero_anp', 'message' => translate('este_numero_ANP_no_tiene_cuenta_para_activar')];
        }
        if ($perfil->reclamada) {
            return ['ok' => false, 'code' => 'cuenta_ya_activada', 'message' => translate('esta_cuenta_ya_fue_activada_inicia_sesion')];
        }
        if (Cache::get(self::CACHE_FALLOS_PREFIX . $numero, 0) >= self::MAX_FALLOS_24H) {
            return ['ok' => false, 'code' => 'intentos_bloqueados', 'message' => translate('demasiados_intentos_contacta_a_ANPEC')];
        }

        $factor = $this->determinarFactor(perfil: $perfil);

        if ($factor === 'ninguno') {
            if (blank($correoContacto)) {
                return ['ok' => false, 'code' => 'verificacion_manual', 'message' => translate('esta_cuenta_requiere_verificacion_manual_dejanos_tu_correo')];
            }
            $this->registrarSolicitudManual(numero: $numero, correo: strtolower(trim($correoContacto)));
            return [
                'ok' => true,
                'requiere_verificacion_manual' => true,
                'message' => translate('recibimos_tu_solicitud_ANPEC_validara_tu_identidad_y_te_contactara'),
            ];
        }

        $coincide = $factor === 'telefono'
            ? $this->coincideTelefono(capturado: (string)$telefono, telefonoContacto: (string)$perfil->telefono_contacto)
            : $this->coincideNombre(capturado: (string)$nombre, nombreExcel: (string)$perfil->customer->f_name);

        if (!$coincide) {
            $this->registrarFallo(numero: $numero);
            return ['ok' => false, 'code' => 'identidad_no_coincide', 'message' => translate('los_datos_no_coinciden_verifica_e_intenta_de_nuevo')];
        }

        $claimToken = Str::random(48);
        Cache::put(self::CACHE_CLAIM_PREFIX . $claimToken, $perfil->id, now()->addMinutes(self::CLAIM_TTL_MINUTOS));
        Cache::forget(self::CACHE_FALLOS_PREFIX . $numero);

        return [
            'ok' => true,
            'claim_token' => $claimToken,
            'expira_en_minutos' => self::CLAIM_TTL_MINUTOS,
            'message' => translate('identidad_verificada_crea_tu_contrasena'),
        ];
    }

    /**
     * Create the real credentials for a verified claim and mark the profile.
     *
     * @return array{ok:bool, code?:string, message:string, user?:object}
     */
    public function activarCuenta(string $claimToken, string $correoReal, string $password): array
    {
        $perfilId = Cache::get(self::CACHE_CLAIM_PREFIX . $claimToken);
        if (!$perfilId) {
            return ['ok' => false, 'code' => 'claim_token', 'message' => translate('la_verificacion_expiro_vuelve_a_empezar')];
        }

        $perfil = $this->affiliateProfileRepo->getFirstWhere(params: ['id' => $perfilId], relations: ['customer']);
        if (!$perfil || !$perfil->customer || $perfil->reclamada) {
            Cache::forget(self::CACHE_CLAIM_PREFIX . $claimToken);
            return ['ok' => false, 'code' => 'claim_token', 'message' => translate('la_verificacion_expiro_vuelve_a_empezar')];
        }

        $correo = strtolower(trim($correoReal));
        $correoOcupado = $this->customerRepo->getFirstWhere(params: ['email' => $correo]);
        if ($correoOcupado && $correoOcupado->id !== $perfil->customer->id) {
            return ['ok' => false, 'code' => 'correo_en_uso', 'message' => translate('ese_correo_ya_tiene_cuenta_inicia_sesion')];
        }

        DB::transaction(function () use ($perfil, $correo, $password) {
            $this->customerRepo->updateWhere(params: ['id' => $perfil->customer->id], data: [
                'email' => $correo,
                'password' => bcrypt($password),
                'updated_at' => now(),
            ]);
            $this->affiliateProfileRepo->update(id: $perfil->id, data: [
                'reclamada' => 1,
                'fecha_reclamo' => now(),
            ]);
        });

        Cache::forget(self::CACHE_CLAIM_PREFIX . $claimToken);
        Cache::forget(self::CACHE_FALLOS_PREFIX . strtoupper((string)$perfil->numero_anp));

        $user = $this->customerRepo->getFirstWhere(params: ['id' => $perfil->customer->id]);

        return ['ok' => true, 'message' => translate('cuenta_activada_correctamente'), 'user' => $user];
    }

    /**
     * El teléfono capturado (10 dígitos) debe aparecer como secuencia contenida
     * en el campo del Excel: hay celdas con DOS teléfonos ("744.../744...") y
     * comparar solo "los últimos 10" dejaría fuera al primero.
     */
    private function coincideTelefono(string $capturado, string $telefonoContacto): bool
    {
        $digitosCapturados = preg_replace('/\D+/', '', $capturado);
        if (strlen($digitosCapturados) < 10) {
            return false;
        }
        $digitosCapturados = substr($digitosCapturados, -10);
        $digitosCampo = preg_replace('/\D+/', '', $telefonoContacto);

        return $digitosCampo !== '' && str_contains($digitosCampo, $digitosCapturados);
    }

    /**
     * Matching tolerante: sin acentos, mayúsculas, espacios colapsados. Acepta
     * si los tokens capturados (mínimo 2) están contenidos en el nombre del
     * Excel o viceversa.
     */
    private function coincideNombre(string $capturado, string $nombreExcel): bool
    {
        $tokensCapturados = $this->tokenizarNombre(valor: $capturado);
        $tokensExcel = $this->tokenizarNombre(valor: $nombreExcel);
        if (count($tokensCapturados) < 2 || empty($tokensExcel)) {
            return false;
        }

        return empty(array_diff($tokensCapturados, $tokensExcel))
            || empty(array_diff($tokensExcel, $tokensCapturados));
    }

    private function tokenizarNombre(string $valor): array
    {
        $valor = strtoupper(Str::ascii(trim($valor)));
        $valor = preg_replace('/[^A-Z0-9 ]+/', ' ', $valor);
        $valor = trim(preg_replace('/\s+/', ' ', $valor));

        return $valor === '' ? [] : explode(' ', $valor);
    }

    private function registrarFallo(string $numero): void
    {
        $key = self::CACHE_FALLOS_PREFIX . $numero;
        Cache::add($key, 0, now()->addHours(24));
        Cache::increment($key);
    }

    /**
     * Los 2 casos sin teléfono NI nombre quedan para verificación manual: el
     * correo que dejó el interesado se registra en las observaciones del número
     * (visibles y buscables en el panel de Números ANP).
     */
    private function registrarSolicitudManual(string $numero, string $correo): void
    {
        $numeroRow = $this->numeroAnpRepo->getFirstWhere(params: ['numero_anp' => $numero]);
        if (!$numeroRow) {
            return;
        }
        $nota = '[' . now()->format('Y-m-d H:i') . '] ' . translate('verificacion_manual_solicitada_correo') . ': ' . $correo;
        $observaciones = trim(($numeroRow->observaciones ? $numeroRow->observaciones . "\n" : '') . $nota);
        $this->numeroAnpRepo->update(id: $numeroRow->id, data: ['observaciones' => $observaciones]);
    }
}
