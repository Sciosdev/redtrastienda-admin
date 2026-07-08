<?php

namespace App\Repositories;

use App\Contracts\Repositories\OpportunityRequestRepositoryInterface;
use App\Models\OpportunityRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class OpportunityRequestRepository implements OpportunityRequestRepositoryInterface
{
    public function __construct(
        private readonly OpportunityRequest $opportunityRequest,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->opportunityRequest->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->opportunityRequest->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->opportunityRequest->with($relations)
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->opportunityRequest
            ->with($relations)
            ->when(isset($filters['product_id']), function ($query) use ($filters) {
                return $query->where(['product_id' => $filters['product_id']]);
            })
            ->when(isset($filters['seller_id']), function ($query) use ($filters) {
                return $query->where(['seller_id' => $filters['seller_id']]);
            })
            ->when(isset($filters['customer_id']), function ($query) use ($filters) {
                return $query->where(['customer_id' => $filters['customer_id']]);
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                return $query->where(['status' => $filters['status']]);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        return $this->opportunityRequest->where('id', $id)->update($data);
    }

    public function delete(array $params): bool
    {
        return $this->opportunityRequest->where($params)->delete();
    }
}
