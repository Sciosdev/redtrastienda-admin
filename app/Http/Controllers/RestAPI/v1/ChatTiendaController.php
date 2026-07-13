<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Services\ChatTiendaService;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatTiendaController extends Controller
{
    public function __construct(
        private readonly ChatTiendaService $chatTiendaService,
    )
    {
    }

    /**
     * Mis conversaciones, ordenadas por actividad.
     */
    public function inbox(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        if ($respuesta = $this->getPerfilNoElegibleResponse(userId: $user->id)) {
            return $respuesta;
        }

        $resultado = $this->chatTiendaService->getInbox(
            userId: $user->id,
            limit: (int) ($request['limit'] ?? 20),
            offset: (int) ($request['offset'] ?? 1),
        );

        return response()->json($this->paginado(resultado: $resultado, request: $request), 200);
    }

    /**
     * Directorio abierto de afiliados elegibles: solo nombre/negocio/estado.
     */
    public function directorio(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        if ($respuesta = $this->getPerfilNoElegibleResponse(userId: $user->id)) {
            return $respuesta;
        }

        $resultado = $this->chatTiendaService->getDirectorio(
            solicitanteId: $user->id,
            search: $request['search'] ?? null,
            limit: (int) ($request['limit'] ?? 20),
            offset: (int) ($request['offset'] ?? 1),
        );

        return response()->json($this->paginado(resultado: $resultado, request: $request), 200);
    }

    /**
     * Página de mensajes de una conversación mía; marca leídos los recibidos.
     */
    public function mensajes(Request $request, string $chatId): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        if ($respuesta = $this->getPerfilNoElegibleResponse(userId: $user->id)) {
            return $respuesta;
        }

        $resultado = $this->chatTiendaService->getMensajes(
            userId: $user->id,
            chatId: (int) $chatId,
            limit: (int) ($request['limit'] ?? 30),
            offset: (int) ($request['offset'] ?? 1),
        );

        if (isset($resultado['error'])) {
            return $this->chatTiendaErrorResponse(error: $resultado['error']);
        }

        return response()->json($resultado + ['limit' => $request['limit'] ?? 30, 'offset' => $request['offset'] ?? 1], 200);
    }

    public function enviar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'destinatario_id' => 'required|integer',
            'mensaje' => 'required|string|max:1000',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $user = Helpers::getCustomerInformation($request);
        if ($respuesta = $this->getPerfilNoElegibleResponse(userId: $user->id)) {
            return $respuesta;
        }

        $resultado = $this->chatTiendaService->enviarMensaje(
            remitenteId: $user->id,
            destinatarioId: (int) $request['destinatario_id'],
            mensaje: $request['mensaje'],
        );

        if (isset($resultado['error'])) {
            return $this->chatTiendaErrorResponse(error: $resultado['error']);
        }

        return response()->json($resultado, 200);
    }

    public function bloquear(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'motivo' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $user = Helpers::getCustomerInformation($request);
        if ($respuesta = $this->getPerfilNoElegibleResponse(userId: $user->id)) {
            return $respuesta;
        }

        $resultado = $this->chatTiendaService->bloquear(
            bloqueadorId: $user->id,
            bloqueadoId: (int) $request['user_id'],
            motivo: $request['motivo'] ?? null,
        );

        if (isset($resultado['error'])) {
            return $this->chatTiendaErrorResponse(error: $resultado['error']);
        }

        return response()->json(['message' => translate('usuario_bloqueado_chat_tiendas')], 200);
    }

    public function desbloquear(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $user = Helpers::getCustomerInformation($request);
        if ($respuesta = $this->getPerfilNoElegibleResponse(userId: $user->id)) {
            return $respuesta;
        }

        $this->chatTiendaService->desbloquear(bloqueadorId: $user->id, bloqueadoId: (int) $request['user_id']);

        return response()->json(['message' => translate('usuario_desbloqueado_chat_tiendas')], 200);
    }

    /**
     * Todo el módulo exige que el SOLICITANTE tenga perfil elegible.
     */
    private function getPerfilNoElegibleResponse(int $userId): ?JsonResponse
    {
        if ($this->chatTiendaService->esElegible(userId: $userId)) {
            return null;
        }

        return response()->json(['errors' => [
            ['code' => ChatTiendaService::ERROR_PERFIL_NO_ELEGIBLE, 'message' => translate('perfil_no_activo_para_chat')],
        ]], 403);
    }

    /**
     * No puede llamarse errorResponse: la clase base Controller ya define uno
     * protected y PHP fatalea al reducir visibilidad/cambiar la firma.
     */
    private function chatTiendaErrorResponse(string $error): JsonResponse
    {
        return match ($error) {
            ChatTiendaService::ERROR_YO_MISMO => response()->json(['errors' => [
                ['code' => 'chat_tienda', 'message' => translate('no_puedes_conversar_contigo_mismo')],
            ]], 403),
            ChatTiendaService::ERROR_BLOQUEADO_POR_MI => response()->json(['errors' => [
                ['code' => 'bloqueaste_a_este_usuario', 'message' => translate('bloqueaste_a_este_usuario_desbloquealo')],
            ]], 403),
            ChatTiendaService::ERROR_CHAT_AJENO => response()->json(['errors' => [
                ['code' => 'chat_tienda', 'message' => translate('no_tienes_acceso_a_esta_conversacion')],
            ]], 403),
            default => response()->json(['errors' => [
                ['code' => 'chat_tienda', 'message' => translate('usuario_no_disponible_chat_tiendas')],
            ]], 403),
        };
    }

    private function paginado(array $resultado, Request $request): array
    {
        return [
            'total_size' => $resultado['total_size'],
            'limit' => $request['limit'] ?? 20,
            'offset' => $request['offset'] ?? 1,
            'data' => $resultado['data'],
        ];
    }
}
