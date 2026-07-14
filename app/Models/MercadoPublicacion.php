<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\MercadoPublicacion
 *
 * R-Mercado Fase A: publicación de la vitrina entre tenderos
 * (producto u aviso; oferta = destacado con vigencia opcional).
 *
 * @property int $id
 * @property int $user_id
 * @property string $tipo producto|aviso
 * @property string $titulo
 * @property string|null $descripcion
 * @property string|null $precio
 * @property string|null $unidad
 * @property string|null $foto
 * @property bool $es_oferta
 * @property Carbon|null $vigencia_hasta
 * @property bool $activo
 * @property bool $oculto_por_admin
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MercadoPublicacion extends Model
{
    use HasFactory;

    public const TIPO_PRODUCTO = 'producto';
    public const TIPO_AVISO = 'aviso';

    protected $table = 'mercado_publicaciones';

    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'descripcion',
        'precio',
        'unidad',
        'foto',
        'es_oferta',
        'vigencia_hasta',
        'activo',
        'oculto_por_admin',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'precio' => 'decimal:2',
        'es_oferta' => 'boolean',
        'vigencia_hasta' => 'date',
        'activo' => 'boolean',
        'oculto_por_admin' => 'boolean',
    ];

    public function dueno(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function perfilDueno(): HasOne
    {
        return $this->hasOne(AffiliateProfile::class, 'customer_id', 'user_id');
    }

    public function esOfertaVigente(): bool
    {
        return $this->es_oferta
            && ($this->vigencia_hasta === null || $this->vigencia_hasta->endOfDay()->isFuture());
    }
}
