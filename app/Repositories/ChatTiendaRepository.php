<?php

namespace App\Repositories;

use App\Contracts\Repositories\ChatTiendaRepositoryInterface;
use App\Models\ChatTienda;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;

class ChatTiendaRepository implements ChatTiendaRepositoryInterface
{
    public function __construct(
        private readonly ChatTienda $chatTienda,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->chatTienda->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->chatTienda->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->chatTienda->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->chatTienda->with($relations)
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
        return $this->chatTienda->where('id', $id)->update($data);
    }

    public function delete(array $params): bool
    {
        return $this->chatTienda->where($params)->delete();
    }

    public function getOrCreateForPair(int $menorId, int $mayorId): Model
    {
        try {
            return $this->chatTienda->firstOrCreate([
                'afiliado_menor_id' => $menorId,
                'afiliado_mayor_id' => $mayorId,
            ]);
        } catch (QueryException $exception) {
            // Carrera contra el UNIQUE del par (MySQL 1062): el otro request ya
            // creó la conversación; se re-consulta en lugar de fallar.
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                return $this->chatTienda
                    ->where('afiliado_menor_id', $menorId)
                    ->where('afiliado_mayor_id', $mayorId)
                    ->firstOrFail();
            }
            throw $exception;
        }
    }

    public function getInboxPaginado(int $userId, int $limit, int $offset): LengthAwarePaginator
    {
        return $this->chatTienda
            ->where(function ($query) use ($userId) {
                $query->where('afiliado_menor_id', $userId)
                    ->orWhere('afiliado_mayor_id', $userId);
            })
            ->with('ultimoMensaje')
            ->withCount(['mensajes as no_leidos' => function ($query) use ($userId) {
                $query->whereNull('read_at')->where('sender_id', '!=', $userId);
            }])
            ->orderByDesc('last_message_at')
            ->paginate($limit, ['*'], 'page', $offset);
    }
}
