<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\AffiliateProfileRepositoryInterface;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Services\AffiliateProfileService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AffiliateProfileController extends BaseController
{
    public function __construct(
        private readonly AffiliateProfileRepositoryInterface $affiliateProfileRepo,
        private readonly AffiliateProfileService             $affiliateProfileService,
        private readonly BusinessSettingRepositoryInterface  $businessSettingRepo,
    )
    {
    }

    public function index(Request|null $request, ?string $type = null): View
    {
        $affiliates = $this->affiliateProfileRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['estatus' => $request['estatus'] ?? ''],
            relations: ['customer'],
            dataLimit: getWebConfig(name: 'pagination_limit'),
        );
        $totalAffiliates = $this->affiliateProfileRepo->getList(dataLimit: 'all')->count();
        $numeroAnpObligatorio = (int)($this->businessSettingRepo->getFirstWhere(params: ['type' => 'numero_anp_obligatorio'])?->value ?? 0);
        return view('admin-views.afiliados.list', compact('affiliates', 'totalAffiliates', 'numeroAnpObligatorio'));
    }

    public function getView(Request $request, $id): View|RedirectResponse
    {
        $affiliate = $this->affiliateProfileRepo->getFirstWhere(params: ['id' => $id], relations: ['customer']);
        if (!$affiliate) {
            ToastMagic::error(translate('afiliado_no_encontrado'));
            return back();
        }
        return view('admin-views.afiliados.view', compact('affiliate'));
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $affiliate = $this->affiliateProfileRepo->getFirstWhere(params: ['id' => $request['id']]);
        if (!$affiliate) {
            ToastMagic::error(translate('afiliado_no_encontrado'));
            return back();
        }

        $estatus = $request['estatus'];
        if (!in_array($estatus, ['activo', 'rechazado', 'bloqueado', 'pendiente'])) {
            ToastMagic::error(translate('estatus_invalido'));
            return back();
        }

        $this->affiliateProfileService->changeStatus(
            id: $affiliate->id,
            estatus: $estatus,
            adminName: auth('admin')->user()?->name,
        );

        ToastMagic::success(translate('estatus_actualizado_correctamente'));
        return back();
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'numero_anp_obligatorio', value: $request->get('numero_anp_obligatorio', 0));
        ToastMagic::success(translate('configuracion_actualizada'));
        return back();
    }
}
