<?php

namespace App\Contracts\Repositories;

interface NumeroAnpRepositoryInterface extends RepositoryInterface
{
    /**
     * Return the subset of the given numbers that already exist in the table.
     *
     * @param array $numeros
     * @return array
     */
    public function getExistingNumeros(array $numeros): array;

    /**
     * Bulk insert already-prepared rows.
     *
     * @param array $rows
     * @return bool
     */
    public function insertMany(array $rows): bool;

    /**
     * Return the full rows whose numero_anp is in the given list.
     *
     * @param array $numeros
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getListWhereNumeroIn(array $numeros): \Illuminate\Database\Eloquent\Collection;
}
