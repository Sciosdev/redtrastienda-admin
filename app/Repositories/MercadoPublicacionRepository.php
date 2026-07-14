<?php

namespace App\Repositories;

use App\Contracts\Repositories\MercadoPublicacionRepositoryInterface;
use App\Models\MercadoPublicacion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class MercadoPublicacionRepository implements MercadoPublicacionRepositoryInterface
{
    // Ofertas vigentes primero, luego lo más reciente.
    private const ORDEN_OFERTAS_PRIMERO = '(mercado_publicaciones.es_oferta = 1 AND (mercado_publicaciones.vigencia_hasta IS NULL OR mercado_publicaciones.vigencia_hasta >= CURDATE())) DESC';

    public function __construct(
        private readonly MercadoPublicacion $mercadoPublicacion,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->mercadoPublicacion->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->mercadoPublicacion->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->mercadoPublicacion->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->mercadoPublicacion->with($relations)
            ->when(!empty($filters), function ($query) use ($filters) {
                return $query->where($filters);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function update(string $id, array $data): bool
    {
        return $this->mercadoPublicacion->where('id', $id)->update($data);
    }

    public function delete(array $params): bool
    {
        return $this->mercadoPublicacion->where($params)->delete();
    }

    public function getVisiblesPaginado(int $solicitanteId, ?string $search, ?string $estado, ?string $tipo, int $limit, int $offset): LengthAwarePaginator
    {
        return $this->mercadoPublicacion
            ->join('users', 'users.id', '=', 'mercado_publicaciones.user_id')
            ->join('affiliate_profiles', 'affiliate_profiles.customer_id', '=', 'mercado_publicaciones.user_id')
            ->where('mercado_publicaciones.activo', 1)
            ->where('mercado_publicaciones.oculto_por_admin', 0)
            // Dueño elegible: misma regla que el directorio del chat.
            ->where('affiliate_profiles.estatus', 'activo')
            ->where('affiliate_profiles.reclamada', 1)
            ->whereNotNull('affiliate_profiles.numero_anp')
            ->whereNotExists(function ($query) use ($solicitanteId) {
                $query->selectRaw('1')
                    ->from('chat_tienda_bloqueos')
                    ->where(function ($query) use ($solicitanteId) {
                        $query->where('chat_tienda_bloqueos.bloqueador_id', $solicitanteId)
                            ->whereColumn('chat_tienda_bloqueos.bloqueado_id', 'mercado_publicaciones.user_id');
                    })
                    ->orWhere(function ($query) use ($solicitanteId) {
                        $query->whereColumn('chat_tienda_bloqueos.bloqueador_id', 'mercado_publicaciones.user_id')
                            ->where('chat_tienda_bloqueos.bloqueado_id', $solicitanteId);
                    });
            })
            // Ajuste de auditoría: el search también descubre por estado del dueño.
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('mercado_publicaciones.titulo', 'like', "%{$search}%")
                        ->orWhere('mercado_publicaciones.descripcion', 'like', "%{$search}%")
                        ->orWhere('affiliate_profiles.estado', 'like', "%{$search}%");
                });
            })
            ->when(!empty($estado), function ($query) use ($estado) {
                return $query->where('affiliate_profiles.estado', 'like', "%{$estado}%");
            })
            ->when(!empty($tipo), function ($query) use ($tipo) {
                return $query->where('mercado_publicaciones.tipo', $tipo);
            })
            ->orderByRaw(self::ORDEN_OFERTAS_PRIMERO)
            ->orderByDesc('mercado_publicaciones.created_at')
            // Select explícito: es imposible que viaje teléfono/correo/dirección/ANP.
            ->select([
                'mercado_publicaciones.id',
                'mercado_publicaciones.user_id',
                'mercado_publicaciones.tipo',
                'mercado_publicaciones.titulo',
                'mercado_publicaciones.descripcion',
                'mercado_publicaciones.precio',
                'mercado_publicaciones.unidad',
                'mercado_publicaciones.foto',
                'mercado_publicaciones.es_oferta',
                'mercado_publicaciones.vigencia_hasta',
                'mercado_publicaciones.created_at',
                'users.f_name',
                'users.l_name',
                'affiliate_profiles.nombre_negocio',
                'affiliate_profiles.estado',
            ])
            ->paginate($limit, ['*'], 'page', $offset);
    }

    public function getVisiblesDeUsuario(int $userId, int $limit, int $offset): LengthAwarePaginator
    {
        return $this->mercadoPublicacion
            ->where('user_id', $userId)
            ->where('activo', 1)
            ->where('oculto_por_admin', 0)
            ->orderByRaw(self::ORDEN_OFERTAS_PRIMERO)
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $offset);
    }

    public function getMisPublicaciones(int $userId, int $limit, int $offset): LengthAwarePaginator
    {
        return $this->mercadoPublicacion
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $offset);
    }

    public function countActivasDe(int $userId): int
    {
        return $this->mercadoPublicacion
            ->where('user_id', $userId)
            ->where('activo', 1)
            ->count();
    }

    public function getListForAdmin(?string $searchValue, ?string $visibilidad, int $limit, int $offset): LengthAwarePaginator
    {
        return $this->mercadoPublicacion
            ->with(['dueno', 'perfilDueno'])
            ->when($visibilidad === 'ocultas', function ($query) {
                return $query->where('oculto_por_admin', 1);
            })
            ->when($visibilidad === 'visibles', function ($query) {
                return $query->where('oculto_por_admin', 0);
            })
            ->when(!empty($searchValue), function ($query) use ($searchValue) {
                return $query->where(function ($query) use ($searchValue) {
                    $query->where('titulo', 'like', "%{$searchValue}%")
                        ->orWhereHas('dueno', function ($query) use ($searchValue) {
                            $query->where('f_name', 'like', "%{$searchValue}%")
                                ->orWhere('l_name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('perfilDueno', function ($query) use ($searchValue) {
                            $query->where('nombre_negocio', 'like', "%{$searchValue}%");
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $offset)
            ->appends(['searchValue' => $searchValue, 'visibilidad' => $visibilidad]);
    }
}
