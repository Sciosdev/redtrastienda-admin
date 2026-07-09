<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\BaseController;
use App\Services\DeployService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeployController extends BaseController
{
    public function __construct(
        private readonly DeployService $deployService,
    )
    {
    }

    public function index(?Request $request, ?string $type = null): View
    {
        $this->authorizeDeployAccess();

        $status = $this->deployService->getStatus();
        return view('admin-views.system-setup.deploy', compact('status'));
    }

    public function deploy(Request $request): JsonResponse
    {
        $this->authorizeDeployAccess();

        $result = $this->deployService->deploy(
            adminId: (int)auth('admin')->id(),
            adminName: auth('admin')->user()?->name,
        );

        return response()->json([
            'success' => $result['success'],
            'steps' => $result['steps'],
            'message' => $result['success']
                ? translate('deploy_completado_correctamente')
                : translate('el_deploy_termino_con_errores'),
        ]);
    }

    /**
     * Hard gate: the whole module is off unless DEPLOY_PANEL_ENABLED is truthy,
     * and only the master admin (id 1) may reach it. Anything else 404/403s.
     */
    private function authorizeDeployAccess(): void
    {
        if (!filter_var(env('DEPLOY_PANEL_ENABLED'), FILTER_VALIDATE_BOOLEAN)) {
            abort(404);
        }
        if ((int)auth('admin')->id() !== 1) {
            abort(403);
        }
    }
}
