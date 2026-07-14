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
            ->when(!empty($filters['sin_numero']), function ($query) {
                return $query->whereNull('numero_anp');
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

    public function getListWhereNumeroIn(array $numeros, array $relations = []): Collection
    {
        return $this->affiliateProfile->with($relations)->whereIn('numero_anp', $numeros)->get();
    }

    public function insertMany(array $rows): bool
    {
        return $this->affiliateProfile->insert($rows);
    }

    public function getDirectorioChat(int $solicitanteId, ?string $searchValue, int $limit, int $offset): LengthAwarePaginator
    {
        return $this->affiliateProfile
            ->join('users', 'users.id', '=', 'affiliate_profiles.customer_id')
            ->where('affiliate_profiles.estatus', 'activo')
            ->where('affiliate_profiles.reclamada', 1)
            ->whereNotNull('affiliate_profiles.numero_anp')
            ->where('affiliate_profiles.customer_id', '!=', $solicitanteId)
            ->whereNotExists(function ($query) use ($solicitanteId) {
                $query->selectRaw('1')
                    ->from('chat_tienda_bloqueos')
                    ->where(function ($query) use ($solicitanteId) {
                        $query->where('chat_tienda_bloqueos.bloqueador_id', $solicitanteId)
                            ->whereColumn('chat_tienda_bloqueos.bloqueado_id', 'affiliate_profiles.customer_id');
                    })
                    ->orWhere(function ($query) use ($solicitanteId) {
                        $query->whereColumn('chat_tienda_bloqueos.bloqueador_id', 'affiliate_profiles.customer_id')
                            ->where('chat_tienda_bloqueos.bloqueado_id', $solicitanteId);
                    });
            })
            ->when(!empty($searchValue), function ($query) use ($searchValue) {
                return $query->where(function ($query) use ($searchValue) {
                    $query->where('users.f_name', 'like', "%{$searchValue}%")
                        ->orWhere('users.l_name', 'like', "%{$searchValue}%")
                        ->orWhere('affiliate_profiles.nombre_negocio', 'like', "%{$searchValue}%")
                        ->orWhere('affiliate_profiles.estado', 'like', "%{$searchValue}%");
                });
            })
            ->orderBy('users.f_name')
            ->orderBy('affiliate_profiles.customer_id')
            // Select explícito: es imposible que viaje teléfono/correo/dirección/ANP.
            ->select([
                'affiliate_profiles.customer_id',
                'users.f_name',
                'users.l_name',
                'affiliate_profiles.nombre_negocio',
                'affiliate_profiles.estado',
            ])
            ->paginate($limit, ['*'], 'page', $offset);
    }

    public function getDatosChatWhereCustomerIn(array $customerIds): \Illuminate\Support\Collection
    {
        return $this->affiliateProfile
            ->join('users', 'users.id', '=', 'affiliate_profiles.customer_id')
            ->whereIn('affiliate_profiles.customer_id', $customerIds)
            ->select([
                'affiliate_profiles.customer_id',
                'users.f_name',
                'users.l_name',
                'affiliate_profiles.nombre_negocio',
                'affiliate_profiles.estado',
            ])
            ->get()
            ->keyBy('customer_id');
    }

    public function getPerfilPublicoMercado(int $customerId): ?object
    {
        return $this->affiliateProfile
            ->join('users', 'users.id', '=', 'affiliate_profiles.customer_id')
            ->where('affiliate_profiles.customer_id', $customerId)
            ->where('affiliate_profiles.estatus', 'activo')
            ->where('affiliate_profiles.reclamada', 1)
            ->whereNotNull('affiliate_profiles.numero_anp')
            // Select explícito: foto_negocio es el ÚNICO dato extra que el
            // Mercado expone sobre el directorio del chat.
            ->select([
                'affiliate_profiles.customer_id',
                'users.f_name',
                'users.l_name',
                'affiliate_profiles.nombre_negocio',
                'affiliate_profiles.estado',
                'affiliate_profiles.foto_negocio',
            ])
            ->first();
    }
}
