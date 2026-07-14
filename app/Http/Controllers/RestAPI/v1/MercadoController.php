<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Services\MercadoService;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MercadoController extends Controller
{
    public function __construct(
        private readonly MercadoService $mercadoService,
    )
    {
    }

    /**
     * Explorar: publicaciones visibles de dueños elegibles no bloqueados.
     */
    public function publicaciones(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        if ($respuesta = $this->getPerfilNoElegibleResponse(userId: $user->id)) {
            return $respuesta;
        }

        $resultado = $this->mercadoService->getPublicaciones(
            solicitanteId: $user->id,
            search: $request['search'] ?? null,
            estado: $request['estado'] ?? null,
            tipo: $request['tipo'] ?? null,
            limit: (int) ($request['limit'] ?? 15),
            offset: (int) ($request['offset'] ?? 1),
        );

        return response()->json($this->paginado(resultado: $resultado, request: $request), 200);
    }

    /**
     * Perfil público de tienda: 3 campos del directorio + foto_negocio +
     * publicaciones visibles. 404 opaco si no está disponible.
     */
    public function tienda(Request $request, string $userId): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        if ($respuesta = $this->getPerfilNoElegibleResponse(userId: $user->id)) {
            return $respuesta;
        }

        $resultado = $this->mercadoService->getTienda(
            solicitanteId: $user->id,
            userId: (int) $userId,
            limit: (int) ($request['limit'] ?? 15),
            offset: (int) ($request['offset'] ?? 1),
        );

        if ($resultado === null) {
            return response()->json(['errors' => [
                ['code' => 'tienda_no_disponible', 'message' => translate('tienda_no_disponible_mercado')],
            ]], 404);
        }

        $resultado['publicaciones'] += ['limit' => $request['limit'] ?? 15, 'offset' => $request['offset'] ?? 1];

        return response()->json($resultado, 200);
    }

    /**
     * Mi tiendita: todas mis publicaciones, con el porqué cuando no se ven.
     */
    public function misPublicaciones(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        if ($respuesta = $this->getPerfilNoElegibleResponse(userId: $user->id)) {
            return $respuesta;
        }

        $resultado = $this->mercadoService->getMisPublicaciones(
            userId: $user->id,
            limit: (int) ($request['limit'] ?? 15),
            offset: (int) ($request['offset'] ?? 1),
        );

        return response()->json($this->paginado(resultado: $resultado, request: $request), 200);
    }

    public function crear(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|in:producto,aviso',
            'titulo' => 'required|string|max:120',
            'descripcion' => 'nullable|string|max:1000',
            'precio' => 'required_if:tipo,producto|nullable|numeric|min:0|max:99999999.99',
            'unidad' => 'nullable|string|max:30',
            'es_oferta' => 'nullable|boolean',
            'vigencia_hasta' => 'nullable|date|after_or_equal:today',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $user = Helpers::getCustomerInformation($request);
        if ($respuesta = $this->getPerfilNoElegibleResponse(userId: $user->id)) {
            return $respuesta;
        }

        $resultado = $this->mercadoService->crear(
            userId: $user->id,
            datos: $request->only(['tipo', 'titulo', 'descripcion', 'precio', 'unidad', 'es_oferta', 'vigencia_hasta']),
            foto: $request->file('foto'),
        );

        if (isset($resultado['error'])) {
            return $this->mercadoErrorResponse(error: $resultado['error']);
        }

        return response()->json(['message' => translate('publicacion_creada')] + $resultado, 200);
    }

    public function actualizar(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'sometimes|in:producto,aviso',
            'titulo' => 'sometimes|string|max:120',
            'descripcion' => 'sometimes|nullable|string|max:1000',
            'precio' => 'sometimes|nullable|numeric|min:0|max:99999999.99',
            'unidad' => 'sometimes|nullable|string|max:30',
            'es_oferta' => 'sometimes|boolean',
            'vigencia_hasta' => 'sometimes|nullable|date',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $user = Helpers::getCustomerInformation($request);
        if ($respuesta = $this->getPerfilNoElegibleResponse(userId: $user->id)) {
            return $respuesta;
        }

        $resultado = $this->mercadoService->actualizar(
            userId: $user->id,
            publicacionId: (int) $id,
            datos: $request->only(['tipo', 'titulo', 'descripcion', 'precio', 'unidad', 'es_oferta', 'vigencia_hasta']),
            foto: $request->file('foto'),
        );

        if (isset($resultado['error'])) {
            return $this->mercadoErrorResponse(error: $resultado['error']);
        }

        return response()->json(['message' => translate('publicacion_actualizada')] + $resultado, 200);
    }

    public function toggle(Request $request, string $id): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        if ($respuesta = $this->getPerfilNoElegibleResponse(userId: $user->id)) {
            return $respuesta;
        }

        $resultado = $this->mercadoService->toggle(userId: $user->id, publicacionId: (int) $id);

        if (isset($resultado['error'])) {
            return $this->mercadoErrorResponse(error: $resultado['error']);
        }

        return response()->json([
            'message' => translate($resultado['activo'] ? 'publicacion_reactivada' : 'publicacion_pausada'),
            'activo' => $resultado['activo'],
        ], 200);
    }

    public function reportar(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'motivo' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $user = Helpers::getCustomerInformation($request);
        if ($respuesta = $this->getPerfilNoElegibleResponse(userId: $user->id)) {
            return $respuesta;
        }

        $resultado = $this->mercadoService->reportar(
            reporterId: $user->id,
            publicacionId: (int) $id,
            motivo: $request['motivo'] ?? null,
        );

        if (isset($resultado['error'])) {
            return $this->mercadoErrorResponse(error: $resultado['error']);
        }

        return response()->json(['message' => translate('publicacion_reportada')], 200);
    }

    /**
     * Todo el módulo exige que el SOLICITANTE tenga perfil elegible.
     */
    private function getPerfilNoElegibleResponse(int $userId): ?JsonResponse
    {
        if ($this->mercadoService->esElegible(userId: $userId)) {
            return null;
        }

        return response()->json(['errors' => [
            ['code' => MercadoService::ERROR_PERFIL_NO_ELEGIBLE, 'message' => translate('perfil_no_activo_para_mercado')],
        ]], 403);
    }

    /**
     * No puede llamarse errorResponse: la clase base Controller ya define uno
     * protected y PHP fatalea al reducir visibilidad/cambiar la firma.
     */
    private function mercadoErrorResponse(string $error): JsonResponse
    {
        return match ($error) {
            MercadoService::ERROR_PUBLICACION_NO_ENCONTRADA => response()->json(['errors' => [
                ['code' => $error, 'message' => translate('publicacion_no_encontrada')],
            ]], 404),
            MercadoService::ERROR_PUBLICACION_AJENA => response()->json(['errors' => [
                ['code' => $error, 'message' => translate('no_tienes_acceso_a_esta_publicacion')],
            ]], 403),
            MercadoService::ERROR_LIMITE_PUBLICACIONES => response()->json(['errors' => [
                ['code' => $error, 'message' => translate('limite_de_publicaciones_activas_alcanzado')],
            ]], 403),
            MercadoService::ERROR_PRECIO_REQUERIDO => response()->json(['errors' => [
                ['code' => $error, 'message' => translate('el_precio_es_obligatorio_para_productos')],
            ]], 403),
            MercadoService::ERROR_YO_MISMO => response()->json(['errors' => [
                ['code' => $error, 'message' => translate('no_puedes_reportar_tu_propia_publicacion')],
            ]], 403),
            default => response()->json(['errors' => [
                ['code' => 'mercado', 'message' => translate('publicacion_no_encontrada')],
            ]], 403),
        };
    }

    private function paginado(array $resultado, Request $request): array
    {
        return [
            'total_size' => $resultado['total_size'],
            'limit' => $request['limit'] ?? 15,
            'offset' => $request['offset'] ?? 1,
            'data' => $resultado['data'],
        ];
    }
}
