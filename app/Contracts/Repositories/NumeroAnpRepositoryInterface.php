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
}
