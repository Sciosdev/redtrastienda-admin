<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface MercadoReporteRepositoryInterface extends RepositoryInterface
{
    /**
     * Idempotent report: returns the existing row or creates it. The caller
     * checks wasRecentlyCreated to decide whether to notify.
     *
     * @param int $publicacionId
     * @param int $reporterId
     * @param string|null $motivo
     * @return Model
     */
    public function firstOrCreateReporte(int $publicacionId, int $reporterId, ?string $motivo): Model;
}
