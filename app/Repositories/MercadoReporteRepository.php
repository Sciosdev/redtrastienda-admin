<?php

namespace App\Repositories;

use App\Contracts\Repositories\MercadoReporteRepositoryInterface;
use App\Models\MercadoReporte;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;

class MercadoReporteRepository implements MercadoReporteRepositoryInterface
{
    public function __construct(
        private readonly MercadoReporte $mercadoReporte,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->mercadoReporte->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->mercadoReporte->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->mercadoReporte->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->mercadoReporte->with($relations)
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
        return $this->mercadoReporte->where('id', $id)->update($data);
    }

    public function delete(array $params): bool
    {
        return $this->mercadoReporte->where($params)->delete();
    }

    public function firstOrCreateReporte(int $publicacionId, int $reporterId, ?string $motivo): Model
    {
        try {
            return $this->mercadoReporte->firstOrCreate(
                ['publicacion_id' => $publicacionId, 'reporter_id' => $reporterId],
                ['motivo' => $motivo],
            );
        } catch (QueryException $exception) {
            // Carrera contra el UNIQUE del par (MySQL 1062): el otro request ya
            // creó el reporte; se re-consulta en lugar de fallar.
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                return $this->mercadoReporte
                    ->where('publicacion_id', $publicacionId)
                    ->where('reporter_id', $reporterId)
                    ->firstOrFail();
            }
            throw $exception;
        }
    }
}
