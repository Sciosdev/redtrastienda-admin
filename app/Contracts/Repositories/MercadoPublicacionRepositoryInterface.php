<?php

namespace App\Contracts\Repositories;

interface MercadoPublicacionRepositoryInterface extends RepositoryInterface
{
    /**
     * R-Mercado: paginated public listing. Only rows with activo=1,
     * oculto_por_admin=0, an eligible owner (estatus activo + reclamada +
     * numero_anp) and no block in either direction against the requester.
     * Rows expose the publication columns plus ONLY customer_id / f_name /
     * l_name / nombre_negocio / estado of the owner. Ordering: current offers
     * first, then newest. $offset is the page number.
     *
     * @param int $solicitanteId
     * @param string|null $search OR LIKE over titulo, descripcion and owner estado
     * @param string|null $estado LIKE filter over the owner estado (chip filter)
     * @param string|null $tipo exact match producto|aviso
     * @param int $limit
     * @param int $offset
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getVisiblesPaginado(int $solicitanteId, ?string $search, ?string $estado, ?string $tipo, int $limit, int $offset): \Illuminate\Pagination\LengthAwarePaginator;

    /**
     * Visible publications (activo=1, oculto_por_admin=0) of one owner, newest
     * first, offers on top. Block/eligibility checks belong to the caller.
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getVisiblesDeUsuario(int $userId, int $limit, int $offset): \Illuminate\Pagination\LengthAwarePaginator;

    /**
     * Every publication of the owner (paused and admin-hidden included),
     * newest first. $offset is the page number.
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getMisPublicaciones(int $userId, int $limit, int $offset): \Illuminate\Pagination\LengthAwarePaginator;

    /**
     * Count of activo=1 publications of the owner (admin-hidden ones count:
     * hiding must not free slots of the per-affiliate limit).
     *
     * @param int $userId
     * @return int
     */
    public function countActivasDe(int $userId): int;

    /**
     * Admin moderation listing: search by titulo or owner name/business,
     * filter by visibility (todas|visibles|ocultas). $offset is the page number.
     *
     * @param string|null $searchValue
     * @param string|null $visibilidad
     * @param int $limit
     * @param int $offset
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getListForAdmin(?string $searchValue, ?string $visibilidad, int $limit, int $offset): \Illuminate\Pagination\LengthAwarePaginator;
}
