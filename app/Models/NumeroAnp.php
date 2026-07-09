<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\NumeroAnp
 *
 * @property int $id
 * @property string $numero_anp
 * @property string $estatus
 * @property int|null $afiliado_asignado
 * @property Carbon $fecha_generacion
 * @property Carbon|null $fecha_activacion
 * @property string|null $operador
 * @property string|null $observaciones
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class NumeroAnp extends Model
{
    use HasFactory;

    protected $table = 'numeros_anp';

    protected $fillable = [
        'numero_anp',
        'estatus',
        'afiliado_asignado',
        'fecha_generacion',
        'fecha_activacion',
        'operador',
        'observaciones',
    ];

    protected $casts = [
        'id' => 'integer',
        'afiliado_asignado' => 'integer',
        'fecha_generacion' => 'datetime',
        'fecha_activacion' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function afiliado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'afiliado_asignado');
    }
}
