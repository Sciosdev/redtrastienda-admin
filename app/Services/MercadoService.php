<?php

namespace App\Services;

use App\Contracts\Repositories\AffiliateProfileRepositoryInterface;
use App\Contracts\Repositories\ChatTiendaBloqueoRepositoryInterface;
use App\Contracts\Repositories\MercadoPublicacionRepositoryInterface;
use App\Contracts\Repositories\MercadoReporteRepositoryInterface;
use App\Models\MercadoPublicacion;
use App\Utils\ImageManager;
use Illuminate\Support\Facades\Mail;

/**
 * R-Mercado Fase A: vitrina entre tenderos. Publicar producto/oferta/aviso,
 * descubrir y contactar por el chat tienda↔tienda existente. Toda respuesta
 * hacia terceros expone ÚNICAMENTE user_id, nombre, nombre_negocio y estado
 * (más foto_negocio en el perfil de tienda) — nunca teléfono, correo,
 * dirección ni numero_anp.
 */
class MercadoService
{
    public const ERROR_PERFIL_NO_ELEGIBLE = 'perfil_no_elegible';
    public const ERROR_PUBLICACION_NO_ENCONTRADA = 'publicacion_no_encontrada';
    public const ERROR_PUBLICACION_AJENA = 'publicacion_ajena';
    public const ERROR_LIMITE_PUBLICACIONES = 'limite_publicaciones';
    public const ERROR_PRECIO_REQUERIDO = 'precio_requerido';
    public const ERROR_YO_MISMO = 'yo_mismo';

    // Límite acordado provisionalmente (pregunta abierta con ANPEC).
    public const MAX_PUBLICACIONES_ACTIVAS = 20;

    private const DIR_FOTOS = 'mercado/';

    public function __construct(
        private readonly MercadoPublicacionRepositoryInterface $mercadoPublicacionRepo,
        private readonly MercadoReporteRepositoryInterface     $mercadoReporteRepo,
        private readonly AffiliateProfileRepositoryInterface   $affiliateProfileRepo,
        private readonly ChatTiendaBloqueoRepositoryInterface  $chatTiendaBloqueoRepo,
        private readonly ChatTiendaService                     $chatTiendaService,
    )
    {
    }

    /**
     * Misma elegibilidad que el chat tienda↔tienda (reuso, no duplicación).
     */
    public function esElegible(int $userId): bool
    {
        return $this->chatTiendaService->esElegible(userId: $userId);
    }

    public function getPublicaciones(int $solicitanteId, ?string $search, ?string $estado, ?string $tipo, int $limit, int $offset): array
    {
        $paginator = $this->mercadoPublicacionRepo->getVisiblesPaginado(
            solicitanteId: $solicitanteId,
            search: $search,
            estado: $estado,
            tipo: $tipo,
            limit: $limit,
            offset: $offset,
        );

        return [
            'total_size' => $paginator->total(),
            'data' => collect($paginator->items())
                ->map(fn($publicacion) => $this->formatPublicacion(publicacion: $publicacion, conDueno: true))
                ->values()
                ->toArray(),
        ];
    }

    /**
     * Perfil público de tienda. Null = no disponible (inexistente, no elegible
     * o bloqueado en cualquier dirección — nunca se revela cuál, misma
     * política que el chat).
     */
    public function getTienda(int $solicitanteId, int $userId, int $limit, int $offset): ?array
    {
        $perfil = $this->affiliateProfileRepo->getPerfilPublicoMercado(customerId: $userId);
        if (!$perfil) {
            return null;
        }

        if ($solicitanteId !== $userId && $this->chatTiendaBloqueoRepo->existeEntre(userA: $solicitanteId, userB: $userId)) {
            return null;
        }

        $paginator = $this->mercadoPublicacionRepo->getVisiblesDeUsuario(userId: $userId, limit: $limit, offset: $offset);

        return [
            'user_id' => (int) $perfil->customer_id,
            'nombre' => trim($perfil->f_name . ' ' . $perfil->l_name),
            'nombre_negocio' => $perfil->nombre_negocio,
            'estado' => $perfil->estado,
            'foto_negocio_url' => $perfil->foto_negocio ? asset('storage/affiliate/' . $perfil->foto_negocio) : null,
            'publicaciones' => [
                'total_size' => $paginator->total(),
                'data' => collect($paginator->items())
                    ->map(fn($publicacion) => $this->formatPublicacion(publicacion: $publicacion))
                    ->values()
                    ->toArray(),
            ],
        ];
    }

    public function getMisPublicaciones(int $userId, int $limit, int $offset): array
    {
        $paginator = $this->mercadoPublicacionRepo->getMisPublicaciones(userId: $userId, limit: $limit, offset: $offset);

        return [
            'total_size' => $paginator->total(),
            'data' => collect($paginator->items())
                ->map(fn($publicacion) => $this->formatPublicacion(publicacion: $publicacion, conEstadoPropio: true))
                ->values()
                ->toArray(),
        ];
    }

    public function crear(int $userId, array $datos, ?object $foto): array
    {
        if ($this->mercadoPublicacionRepo->countActivasDe(userId: $userId) >= self::MAX_PUBLICACIONES_ACTIVAS) {
            return ['error' => self::ERROR_LIMITE_PUBLICACIONES];
        }

        $publicacion = $this->mercadoPublicacionRepo->add([
            'user_id' => $userId,
            'tipo' => $datos['tipo'],
            'titulo' => $datos['titulo'],
            'descripcion' => $datos['descripcion'] ?? null,
            'precio' => $datos['precio'] ?? null,
            'unidad' => $datos['unidad'] ?? null,
            'foto' => $foto ? ImageManager::upload(dir: self::DIR_FOTOS, format: 'webp', image: $foto) : null,
            'es_oferta' => (bool) ($datos['es_oferta'] ?? false),
            'vigencia_hasta' => $datos['vigencia_hasta'] ?? null,
        ]);

        return ['publicacion' => $this->formatPublicacion(publicacion: $publicacion, conEstadoPropio: true)];
    }

    public function actualizar(int $userId, int $publicacionId, array $datos, ?object $foto): array
    {
        $publicacion = $this->mercadoPublicacionRepo->getFirstWhere(params: ['id' => $publicacionId]);
        if (!$publicacion) {
            return ['error' => self::ERROR_PUBLICACION_NO_ENCONTRADA];
        }
        if ($publicacion->user_id !== $userId) {
            return ['error' => self::ERROR_PUBLICACION_AJENA];
        }

        $cambios = array_intersect_key($datos, array_flip(['tipo', 'titulo', 'descripcion', 'precio', 'unidad', 'es_oferta', 'vigencia_hasta']));

        // Un producto nunca queda sin precio, ni cambiando el tipo después.
        $tipoFinal = $cambios['tipo'] ?? $publicacion->tipo;
        $precioFinal = array_key_exists('precio', $cambios) ? $cambios['precio'] : $publicacion->precio;
        if ($tipoFinal === MercadoPublicacion::TIPO_PRODUCTO && $precioFinal === null) {
            return ['error' => self::ERROR_PRECIO_REQUERIDO];
        }

        if ($foto) {
            $cambios['foto'] = ImageManager::update(dir: self::DIR_FOTOS, old_image: $publicacion->foto, format: 'webp', image: $foto);
        }

        if (!empty($cambios)) {
            $this->mercadoPublicacionRepo->update(id: (string) $publicacion->id, data: $cambios);
        }

        $actualizada = $this->mercadoPublicacionRepo->getFirstWhere(params: ['id' => $publicacion->id]);

        return ['publicacion' => $this->formatPublicacion(publicacion: $actualizada, conEstadoPropio: true)];
    }

    public function toggle(int $userId, int $publicacionId): array
    {
        $publicacion = $this->mercadoPublicacionRepo->getFirstWhere(params: ['id' => $publicacionId]);
        if (!$publicacion) {
            return ['error' => self::ERROR_PUBLICACION_NO_ENCONTRADA];
        }
        if ($publicacion->user_id !== $userId) {
            return ['error' => self::ERROR_PUBLICACION_AJENA];
        }

        $nuevoActivo = !$publicacion->activo;
        if ($nuevoActivo && $this->mercadoPublicacionRepo->countActivasDe(userId: $userId) >= self::MAX_PUBLICACIONES_ACTIVAS) {
            return ['error' => self::ERROR_LIMITE_PUBLICACIONES];
        }

        $this->mercadoPublicacionRepo->update(id: (string) $publicacion->id, data: ['activo' => $nuevoActivo]);

        return ['activo' => $nuevoActivo];
    }

    /**
     * Reporte idempotente por (publicación, reportante): el UNIQUE de BD
     * garantiza un solo registro y el correo solo sale con la fila nueva.
     */
    public function reportar(int $reporterId, int $publicacionId, ?string $motivo): array
    {
        $publicacion = $this->mercadoPublicacionRepo->getFirstWhere(params: ['id' => $publicacionId]);
        if (!$publicacion) {
            return ['error' => self::ERROR_PUBLICACION_NO_ENCONTRADA];
        }
        if ($publicacion->user_id === $reporterId) {
            return ['error' => self::ERROR_YO_MISMO];
        }

        $reporte = $this->mercadoReporteRepo->firstOrCreateReporte(
            publicacionId: $publicacion->id,
            reporterId: $reporterId,
            motivo: $motivo,
        );

        if ($reporte->wasRecentlyCreated) {
            $this->notificarReporte(publicacion: $publicacion, reporterId: $reporterId, motivo: $motivo);
        }

        return [];
    }

    private function formatPublicacion(object $publicacion, bool $conDueno = false, bool $conEstadoPropio = false): array
    {
        $data = [
            'id' => $publicacion->id,
            'tipo' => $publicacion->tipo,
            'titulo' => $publicacion->titulo,
            'descripcion' => $publicacion->descripcion,
            'precio' => $publicacion->precio,
            'unidad' => $publicacion->unidad,
            'es_oferta' => (bool) $publicacion->es_oferta,
            'oferta_vigente' => $publicacion->esOfertaVigente(),
            'vigencia_hasta' => $publicacion->vigencia_hasta?->format('Y-m-d'),
            'foto_url' => $publicacion->foto ? asset('storage/' . self::DIR_FOTOS . $publicacion->foto) : null,
            'fecha' => $publicacion->created_at,
        ];

        if ($conDueno) {
            $data['dueno'] = [
                'user_id' => (int) $publicacion->user_id,
                'nombre' => trim($publicacion->f_name . ' ' . $publicacion->l_name),
                'nombre_negocio' => $publicacion->nombre_negocio,
                'estado' => $publicacion->estado,
            ];
        }

        if ($conEstadoPropio) {
            $data['activo'] = (bool) $publicacion->activo;
            $data['oculto_por_admin'] = (bool) $publicacion->oculto_por_admin;
            $data['motivo_no_visible'] = $publicacion->oculto_por_admin
                ? 'oculta_por_admin'
                : ($publicacion->activo ? null : 'pausada');
        }

        return $data;
    }

    /**
     * Aviso best-effort del reporte (patrón del chat tienda↔tienda): si el
     * mail no está configurado o falla, el reporte NO debe fallar.
     */
    private function notificarReporte(object $publicacion, int $reporterId, ?string $motivo): void
    {
        try {
            $companyEmail = getWebConfig(name: 'company_email');
            if (empty($companyEmail)) {
                return;
            }

            $datos = $this->affiliateProfileRepo->getDatosChatWhereCustomerIn(customerIds: [$publicacion->user_id, $reporterId]);
            $nombreDe = function (int $userId) use ($datos): string {
                $registro = $datos->get($userId);
                $nombre = $registro ? trim($registro->f_name . ' ' . $registro->l_name) : '';
                return ($nombre !== '' ? $nombre : translate('afiliado')) . " (user_id {$userId})";
            };

            $mensaje = translate('reporte_mercado_intro') . "\n\n"
                . translate('publicacion') . ': #' . $publicacion->id . ' — ' . $publicacion->titulo . "\n"
                . translate('dueno_publicacion') . ': ' . $nombreDe($publicacion->user_id) . "\n"
                . translate('reporta') . ': ' . $nombreDe($reporterId) . "\n"
                . translate('motivo') . ': ' . ($motivo ?: '-') . "\n"
                . translate('fecha') . ': ' . now()->format('Y-m-d H:i');

            Mail::raw($mensaje, function ($message) use ($companyEmail) {
                $message->to($companyEmail)->subject(translate('reporte_mercado_asunto'));
            });
        } catch (\Throwable $exception) {
            // Notificación no crítica: nunca debe tumbar el reporte.
        }
    }
}
