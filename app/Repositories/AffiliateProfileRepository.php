<?php

namespace App\Repositories;

use App\Contracts\Repositories\AffiliateProfileRepositoryInterface;
use App\Models\AffiliateProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class AffiliateProfileRepository implements AffiliateProfileRepositoryInterface
{
    public function __construct(
        private readonly AffiliateProfile $affiliateProfile,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->affiliateProfile->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->affiliateProfile->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->affiliateProfile->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->affiliateProfile
            ->with($relations)
            ->when(isset($filters['estatus']) && $filters['estatus'] != '', function ($query) use ($filters) {
                return $query->where('estatus', $filters['estatus']);
            })
            ->when(!empty($searchValue), function ($query) use ($searchValue) {
                return $query->where(function ($query) use ($searchValue) {
                    $query->where('numero_anp', 'like', "%{$searchValue}%")
                        ->orWhere('nombre_negocio', 'like', "%{$searchValue}%")
                        ->orWhereHas('customer', function ($query) use ($searchValue) {
                            $query->where('f_name', 'like', "%{$searchValue}%")
                                ->orWhere('l_name', 'like', "%{$searchValue}%")
                                ->orWhere('phone', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%");
                        });
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
        return $this->affiliateProfile->where('id', $id)->update($data);
    }

    public function delete(array $params): bool
    {
        return $this->affiliateProfile->where($params)->delete();
    }
}
