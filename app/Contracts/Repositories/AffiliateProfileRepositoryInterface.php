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
}
