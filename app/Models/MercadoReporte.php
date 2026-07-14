<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MercadoReporte
 *
 * Reporte de una publicación del Mercado. Único por (publicación, reportante):
 * el UNIQUE de BD hace idempotente el reporte y el correo al admin.
 *
 * @property int $id
 * @property int $publicacion_id
 * @property int $reporter_id
 * @property string|null $motivo
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MercadoReporte extends Model
{
    use HasFactory;

    protected $table = 'mercado_reportes';

    protected $fillable = [
        'publicacion_id',
        'reporter_id',
        'motivo',
    ];

    protected $casts = [
        'id' => 'integer',
        'publicacion_id' => 'integer',
        'reporter_id' => 'integer',
    ];
}
