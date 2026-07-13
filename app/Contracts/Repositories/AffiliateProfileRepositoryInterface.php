<?php

namespace App\Contracts\Repositories;

interface AffiliateProfileRepositoryInterface extends RepositoryInterface
{
    /**
     * Return the profiles whose numero_anp is in the given list.
     *
     * @param array $numeros
     * @param array $relations
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getListWhereNumeroIn(array $numeros, array $relations = []): \Illuminate\Database\Eloquent\Collection;

    /**
     * Bulk insert already-prepared rows.
     *
     * @param array $rows
     * @return bool
     */
    public function insertMany(array $rows): bool;

    /**
     * R-Chat-Tiendas: paginated open directory of eligible affiliates
     * (estatus activo + reclamada + numero_anp), excluding the requester and
     * anyone with a block in either direction. Rows expose ONLY
     * user_id / f_name / l_name / nombre_negocio / estado. $offset is the page number.
     *
     * @param int $solicitanteId
     * @param string|null $searchValue matches nombre, negocio or estado
     * @param int $limit
     * @param int $offset
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getDirectorioChat(int $solicitanteId, ?string $searchValue, int $limit, int $offset): \Illuminate\Pagination\LengthAwarePaginator;

    /**
     * R-Chat-Tiendas: chat-safe display data (customer_id / f_name / l_name /
     * nombre_negocio / estado, nothing else) for the given customer ids.
     *
     * @param array $customerIds
     * @return \Illuminate\Support\Collection
     */
    public function getDatosChatWhereCustomerIn(array $customerIds): \Illuminate\Support\Collection;
}
