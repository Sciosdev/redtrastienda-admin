<?php

namespace App\Repositories;

use App\Contracts\Repositories\ChatTiendaMensajeRepositoryInterface;
use App\Models\ChatTiendaMensaje;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ChatTiendaMensajeRepository implements ChatTiendaMensajeRepositoryInterface
{
    public function __construct(
        private readonly ChatTiendaMensaje $chatTiendaMensaje,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->chatTiendaMensaje->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->chatTiendaMensaje->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->chatTiendaMensaje->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->chatTiendaMensaje->with($relations)
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
        return $this->chatTiendaMensaje->where('id', $id)->update($data);
    }

    public function delete(array $params): bool
    {
        return $this->chatTiendaMensaje->where($params)->delete();
    }

    public function getPageByChat(int $chatId, int $limit, int $offset): LengthAwarePaginator
    {
        return $this->chatTiendaMensaje
            ->where('chat_id', $chatId)
            ->orderByDesc('id')
            ->paginate($limit, ['*'], 'page', $offset);
    }

    public function markReceivedAsRead(int $chatId, int $receptorId): int
    {
        return $this->chatTiendaMensaje
            ->where('chat_id', $chatId)
            ->where('sender_id', '!=', $receptorId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
