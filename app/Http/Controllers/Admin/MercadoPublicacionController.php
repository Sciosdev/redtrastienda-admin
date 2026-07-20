<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\MercadoPublicacionRepositoryInterface;
use App\Http\Controllers\BaseController;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * R-Mercado: moderación posterior. Solo listar y ocultar/restaurar —
 * sin edición, sin creación, sin estadísticas.
 * R-Mercado-remoto: además el interruptor `anpec_mercado_activo` que
 * muestra/oculta el Mercado en la app sin recompilar.
 */
class MercadoPublicacionController extends BaseController
{
    public function __construct(
        private readonly MercadoPublicacionRepositoryInterface $mercadoPublicacionRepo,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    )
    {
    }

    public function index(Request|null $request, ?string $type = null): View
    {
        $publicaciones = $this->mercadoPublicacionRepo->getListForAdmin(
            searchValue: $request['searchValue'] ?? null,
            visibilidad: $request['visibilidad'] ?? null,
            limit: getWebConfig(name: 'pagination_limit'),
            offset: (int) ($request['page'] ?? 1),
        );
        $mercadoActivo = (int) ($this->businessSettingRepo->getFirstWhere(params: ['type' => 'anpec_mercado_activo'])?->value ?? 0);

        return view('admin-views.mercado.list', compact('publicaciones', 'mercadoActivo'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        // El repo limpia el cache de getWebConfig al guardar — /api/v1/config
        // refleja el cambio al instante (clave para prenderlo el día de la expo).
        $this->businessSettingRepo->updateOrInsert(type: 'anpec_mercado_activo', value: $request->get('anpec_mercado_activo', 0));
        ToastMagic::success(translate('configuracion_actualizada'));
        return back();
    }

    public function updateVisibilidad(Request $request): RedirectResponse
    {
        $publicacion = $this->mercadoPublicacionRepo->getFirstWhere(params: ['id' => $request['id']]);
        if (!$publicacion) {
            ToastMagic::error(translate('publicacion_no_encontrada'));
            return back();
        }

        $ocultar = (int) $request['oculto'] === 1;
        $this->mercadoPublicacionRepo->update(id: (string) $publicacion->id, data: ['oculto_por_admin' => $ocultar]);

        ToastMagic::success(translate($ocultar ? 'publicacion_oculta_correctamente' : 'publicacion_restaurada_correctamente'));
        return back();
    }
}
