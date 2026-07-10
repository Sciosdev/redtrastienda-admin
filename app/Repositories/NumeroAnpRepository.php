<?php

namespace App\Repositories;

use App\Contracts\Repositories\NumeroAnpRepositoryInterface;
use App\Models\NumeroAnp;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class NumeroAnpRepository implements NumeroAnpRepositoryInterface
{
    public function __construct(
        private readonly NumeroAnp $numeroAnp,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->numeroAnp->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->numeroAnp->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->numeroAnp->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->numeroAnp
            ->with($relations)
            ->when(isset($filters['estatus']) && $filters['estatus'] != '', function ($query) use ($filters) {
                return $query->where('estatus', $filters['estatus']);
            })
            ->when(!empty($searchValue), function ($query) use ($searchValue) {
                return $query->where(function ($query) use ($searchValue) {
                    $query->where('numero_anp', 'like', "%{$searchValue}%")
                        ->orWhere('observaciones', 'like', "%{$searchValue}%");
                });
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        return $this->numeroAnp->where('id', $id)->update($data);
    }

    public function delete(array $params): bool
    {
        return $this->numeroAnp->where($params)->delete();
    }

    /**
     * Return the subset of the given numbers that already exist in the table.
     *
     * @param array $numeros
     * @return array
     */
    public function getExistingNumeros(array $numeros): array
    {
        return $this->numeroAnp->whereIn('numero_anp', $numeros)->pluck('numero_anp')->all();
    }

    public function insertMany(array $rows): bool
    {
        return $this->numeroAnp->insert($rows);
    }

    public function getListWhereNumeroIn(array $numeros): Collection
    {
        return $this->numeroAnp->whereIn('numero_anp', $numeros)->get();
    }
}
