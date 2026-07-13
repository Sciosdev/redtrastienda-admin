<?php

namespace App\Services;

use App\Contracts\Repositories\AffiliateProfileRepositoryInterface;
use App\Contracts\Repositories\ChatTiendaBloqueoRepositoryInterface;
use App\Contracts\Repositories\ChatTiendaMensajeRepositoryInterface;
use App\Contracts\Repositories\ChatTiendaRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * R-Chat-Tiendas (D5, junta 2026-07-09): mensajería interna afiliado↔afiliado.
 * Toda respuesta hacia terceros expone ÚNICAMENTE user_id, nombre,
 * nombre_negocio y estado — nunca teléfono, correo, dirección ni numero_anp.
 */
class ChatTiendaService
{
    public const ERROR_PERFIL_NO_ELEGIBLE = 'perfil_no_elegible';
    public const ERROR_YO_MISMO = 'yo_mismo';
    public const ERROR_BLOQUEADO_POR_MI = 'bloqueado_por_mi';
    public const ERROR_NO_DISPONIBLE = 'no_disponible';
    public const ERROR_CHAT_AJENO = 'chat_ajeno';

    private const TRUNCADO_ULTIMO_MENSAJE = 80;

    public function __construct(
        private readonly ChatTiendaRepositoryInterface         $chatTiendaRepo,
        private readonly ChatTiendaMensajeRepositoryInterface  $chatTiendaMensajeRepo,
        private readonly ChatTiendaBloqueoRepositoryInterface  $chatTiendaBloqueoRepo,
        private readonly AffiliateProfileRepositoryInterface   $affiliateProfileRepo,
    )
    {
    }

    /**
     * Elegible = perfil activo + reclamada + numero_anp asignado (el mismo
     * criterio "utilizable" de R-Afiliación). Leads y precargados sin
     * reclamar quedan fuera del directorio e inalcanzables.
     */
    public function esElegible(int $userId): bool
    {
        $perfil = $this->affiliateProfileRepo->getFirstWhere(params: [
            'customer_id' => $userId,
            'estatus' => 'activo',
            'reclamada' => 1,
        ]);

        return $perfil !== null && $perfil->numero_anp !== null;
    }

    public function getDirectorio(int $solicitanteId, ?string $search, int $limit, int $offset): array
    {
        $paginator = $this->affiliateProfileRepo->getDirectorioChat(
            solicitanteId: $solicitanteId,
            searchValue: $search,
            limit: $limit,
            offset: $offset,
        );

        $data = collect($paginator->items())->map(function ($row) {
            return [
                'user_id' => (int) $row->customer_id,
                'nombre' => trim($row->f_name . ' ' . $row->l_name),
                'nombre_negocio' => $row->nombre_negocio,
                'estado' => $row->estado,
            ];
        })->values()->toArray();

        return ['total_size' => $paginator->total(), 'data' => $data];
    }

    public function getInbox(int $userId, int $limit, int $offset): array
    {
        $paginator = $this->chatTiendaRepo->getInboxPaginado(userId: $userId, limit: $limit, offset: $offset);

        $chats = collect($paginator->items());
        $contraparteIds = $chats->map(fn($chat) => $chat->contraparteId($userId))->unique()->values()->toArray();
        $datosContrapartes = empty($contraparteIds)
            ? collect()
            : $this->affiliateProfileRepo->getDatosChatWhereCustomerIn(customerIds: $contraparteIds);

        $data = $chats->map(function ($chat) use ($userId, $datosContrapartes) {
            $contraparteId = $chat->contraparteId($userId);
            $ultimoMensaje = $chat->ultimoMensaje;

            return [
                'chat_id' => $chat->id,
                'contraparte' => $this->contraparteData(datos: $datosContrapartes->get($contraparteId), contraparteId: $contraparteId),
                'ultimo_mensaje' => $ultimoMensaje ? [
                    'texto' => Str::limit($ultimoMensaje->mensaje, self::TRUNCADO_ULTIMO_MENSAJE),
                    'mia' => $ultimoMensaje->sender_id === $userId,
                    'fecha' => $ultimoMensaje->created_at,
                ] : null,
                'no_leidos' => (int) $chat->no_leidos,
            ];
        })->values()->toArray();

        return ['total_size' => $paginator->total(), 'data' => $data];
    }

    /**
     * Página de mensajes (del más reciente al más viejo). Efecto colateral:
     * marca como leídos los mensajes recibidos — la app usa este endpoint
     * también como polling, así el badge del inbox siempre cuadra.
     */
    public function getMensajes(int $userId, int $chatId, int $limit, int $offset): array
    {
        $chat = $this->chatTiendaRepo->getFirstWhere(params: ['id' => $chatId]);
        if (!$chat || !$chat->esParticipante($userId)) {
            return ['error' => self::ERROR_CHAT_AJENO];
        }

        $this->chatTiendaMensajeRepo->markReceivedAsRead(chatId: $chatId, receptorId: $userId);

        $paginator = $this->chatTiendaMensajeRepo->getPageByChat(chatId: $chatId, limit: $limit, offset: $offset);
        $contraparteId = $chat->contraparteId($userId);
        $datosContrapartes = $this->affiliateProfileRepo->getDatosChatWhereCustomerIn(customerIds: [$contraparteId]);

        $bloqueoMio = $this->chatTiendaBloqueoRepo->getFirstWhere(params: [
            'bloqueador_id' => $userId,
            'bloqueado_id' => $contraparteId,
        ]);

        return [
            'chat_id' => $chat->id,
            'contraparte' => $this->contraparteData(datos: $datosContrapartes->get($contraparteId), contraparteId: $contraparteId),
            'bloqueado_por_mi' => $bloqueoMio !== null,
            'total_size' => $paginator->total(),
            'data' => collect($paginator->items())
                ->map(fn($mensaje) => $this->formatMensaje(mensaje: $mensaje, userId: $userId))
                ->values()
                ->toArray(),
        ];
    }

    public function enviarMensaje(int $remitenteId, int $destinatarioId, string $mensaje): array
    {
        if ($remitenteId === $destinatarioId) {
            return ['error' => self::ERROR_YO_MISMO];
        }

        // Bloqueo propio: se avisa claro (no hay nada que ocultarle al bloqueador).
        $bloqueoMio = $this->chatTiendaBloqueoRepo->getFirstWhere(params: [
            'bloqueador_id' => $remitenteId,
            'bloqueado_id' => $destinatarioId,
        ]);
        if ($bloqueoMio) {
            return ['error' => self::ERROR_BLOQUEADO_POR_MI];
        }

        // Bloqueo inverso y destinatario no elegible/inexistente responden lo
        // mismo a propósito: nunca se revela que existe un bloqueo ajeno.
        $bloqueoInverso = $this->chatTiendaBloqueoRepo->getFirstWhere(params: [
            'bloqueador_id' => $destinatarioId,
            'bloqueado_id' => $remitenteId,
        ]);
        if ($bloqueoInverso || !$this->esElegible(userId: $destinatarioId)) {
            return ['error' => self::ERROR_NO_DISPONIBLE];
        }

        $mensajeCreado = DB::transaction(function () use ($remitenteId, $destinatarioId, $mensaje) {
            $chat = $this->chatTiendaRepo->getOrCreateForPair(
                menorId: min($remitenteId, $destinatarioId),
                mayorId: max($remitenteId, $destinatarioId),
            );

            $mensajeCreado = $this->chatTiendaMensajeRepo->add([
                'chat_id' => $chat->id,
                'sender_id' => $remitenteId,
                'mensaje' => $mensaje,
            ]);

            $this->chatTiendaRepo->update(id: $chat->id, data: ['last_message_at' => now()]);

            return $mensajeCreado;
        });

        return [
            'chat_id' => $mensajeCreado->chat_id,
            'mensaje' => $this->formatMensaje(mensaje: $mensajeCreado, userId: $remitenteId),
        ];
    }

    /**
     * Bloqueo idempotente; con motivo es además REPORTE (requisito Google
     * Play UGC) y dispara un correo best-effort a company_email.
     */
    public function bloquear(int $bloqueadorId, int $bloqueadoId, ?string $motivo): array
    {
        if ($bloqueadorId === $bloqueadoId) {
            return ['error' => self::ERROR_YO_MISMO];
        }

        $this->chatTiendaBloqueoRepo->upsertBloqueo(
            bloqueadorId: $bloqueadorId,
            bloqueadoId: $bloqueadoId,
            motivo: $motivo,
        );

        if (!empty($motivo)) {
            $this->notificarReporte(bloqueadorId: $bloqueadorId, bloqueadoId: $bloqueadoId, motivo: $motivo);
        }

        return [];
    }

    public function desbloquear(int $bloqueadorId, int $bloqueadoId): array
    {
        $this->chatTiendaBloqueoRepo->delete(params: [
            'bloqueador_id' => $bloqueadorId,
            'bloqueado_id' => $bloqueadoId,
        ]);

        return [];
    }

    private function formatMensaje(object $mensaje, int $userId): array
    {
        return [
            'id' => $mensaje->id,
            'mia' => $mensaje->sender_id === $userId,
            'mensaje' => $mensaje->mensaje,
            'fecha' => $mensaje->created_at,
            'leido' => $mensaje->read_at !== null,
        ];
    }

    private function contraparteData(?object $datos, int $contraparteId): array
    {
        return [
            'user_id' => $contraparteId,
            'nombre' => $datos ? trim($datos->f_name . ' ' . $datos->l_name) : '',
            'nombre_negocio' => $datos->nombre_negocio ?? null,
            'estado' => $datos->estado ?? null,
        ];
    }

    /**
     * Aviso best-effort del reporte (patrón notificarNuevoLead): si el mail
     * no está configurado o falla, el bloqueo NO debe fallar.
     */
    private function notificarReporte(int $bloqueadorId, int $bloqueadoId, string $motivo): void
    {
        try {
            $companyEmail = getWebConfig(name: 'company_email');
            if (empty($companyEmail)) {
                return;
            }

            $datos = $this->affiliateProfileRepo->getDatosChatWhereCustomerIn(customerIds: [$bloqueadorId, $bloqueadoId]);
            $nombreDe = function (int $userId) use ($datos): string {
                $registro = $datos->get($userId);
                $nombre = $registro ? trim($registro->f_name . ' ' . $registro->l_name) : '';
                return ($nombre !== '' ? $nombre : translate('afiliado')) . " (user_id {$userId})";
            };

            $mensaje = translate('reporte_chat_tiendas_intro') . "\n\n"
                . translate('reporta') . ': ' . $nombreDe($bloqueadorId) . "\n"
                . translate('reportado') . ': ' . $nombreDe($bloqueadoId) . "\n"
                . translate('motivo') . ': ' . $motivo . "\n"
                . translate('fecha') . ': ' . now()->format('Y-m-d H:i');

            Mail::raw($mensaje, function ($message) use ($companyEmail) {
                $message->to($companyEmail)->subject(translate('reporte_chat_tiendas_asunto'));
            });
        } catch (\Throwable $exception) {
            // Notificación no crítica: nunca debe tumbar el bloqueo.
        }
    }
}
