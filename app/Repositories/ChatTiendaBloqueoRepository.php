<?php

namespace App\Repositories;

use App\Contracts\Repositories\ChatTiendaBloqueoRepositoryInterface;
use App\Models\ChatTiendaBloqueo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ChatTiendaBloqueoRepository implements ChatTiendaBloqueoRepositoryInterface
{
    public function __construct(
        private readonly ChatTiendaBloqueo $chatTiendaBloqueo,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->chatTiendaBloqueo->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->chatTiendaBloqueo->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->chatTiendaBloqueo->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->chatTiendaBloqueo->with($relations)
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
        return $this->chatTiendaBloqueo->where('id', $id)->update($data);
    }

    public function delete(array $params): bool
    {
        return $this->chatTiendaBloqueo->where($params)->delete();
    }

    public function existeEntre(int $userA, int $userB): bool
    {
        return $this->chatTiendaBloqueo
            ->where(function ($query) use ($userA, $userB) {
                $query->where('bloqueador_id', $userA)->where('bloqueado_id', $userB);
            })
            ->orWhere(function ($query) use ($userA, $userB) {
                $query->where('bloqueador_id', $userB)->where('bloqueado_id', $userA);
            })
            ->exists();
    }

    public function upsertBloqueo(int $bloqueadorId, int $bloqueadoId, ?string $motivo): Model
    {
        $bloqueo = $this->chatTiendaBloqueo->firstOrCreate(
            ['bloqueador_id' => $bloqueadorId, 'bloqueado_id' => $bloqueadoId],
            ['motivo_reporte' => $motivo],
        );

        if (!$bloqueo->wasRecentlyCreated && $motivo !== null && $bloqueo->motivo_reporte !== $motivo) {
            $bloqueo->update(['motivo_reporte' => $motivo]);
        }

        return $bloqueo;
    }
}
