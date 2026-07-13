<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface ChatTiendaRepositoryInterface extends RepositoryInterface
{
    /**
     * Return (or create) the single conversation for a normalized pair.
     * $menorId MUST be the smaller users.id of the two.
     *
     * @param int $menorId
     * @param int $mayorId
     * @return Model
     */
    public function getOrCreateForPair(int $menorId, int $mayorId): Model;

    /**
     * User's inbox ordered by last_message_at desc, with ultimoMensaje
     * eager-loaded and no_leidos counted (no N+1). $offset is the page number.
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return LengthAwarePaginator
     */
    public function getInboxPaginado(int $userId, int $limit, int $offset): LengthAwarePaginator;
}
