<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AffiliateProfile
 *
 * @property int $id
 * @property int $customer_id
 * @property string|null $numero_anp
 * @property string|null $nombre_negocio
 * @property string|null $whatsapp
 * @property string|null $telefono_contacto
 * @property string|null $direccion
 * @property string|null $estado
 * @property string|null $municipio
 * @property string|null $colonia
 * @property string|null $foto_negocio
 * @property string $estatus
 * @property bool $reclamada
 * @property Carbon|null $fecha_reclamo
 * @property Carbon|null $approved_at
 * @property string|null $approved_by
 * @property array|null $datos_importacion
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class AffiliateProfile extends Model
{
    use HasFactory;

    protected $table = 'affiliate_profiles';

    protected $fillable = [
        'customer_id',
        'numero_anp',
        'nombre_negocio',
        'whatsapp',
        'telefono_contacto',
        'direccion',
        'estado',
        'municipio',
        'colonia',
        'foto_negocio',
        'estatus',
        'reclamada',
        'fecha_reclamo',
        'approved_at',
        'approved_by',
        'datos_importacion',
    ];

    protected $casts = [
        'id' => 'integer',
        'customer_id' => 'integer',
        'reclamada' => 'boolean',
        'fecha_reclamo' => 'datetime',
        'approved_at' => 'datetime',
        'datos_importacion' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
