<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface ChatTiendaBloqueoRepositoryInterface extends RepositoryInterface
{
    /**
     * True when a block exists in EITHER direction between the two users.
     *
     * @param int $userA
     * @param int $userB
     * @return bool
     */
    public function existeEntre(int $userA, int $userB): bool;

    /**
     * Idempotent block: creates the row or refreshes motivo_reporte.
     *
     * @param int $bloqueadorId
     * @param int $bloqueadoId
     * @param string|null $motivo
     * @return Model
     */
    public function upsertBloqueo(int $bloqueadorId, int $bloqueadoId, ?string $motivo): Model;
}
