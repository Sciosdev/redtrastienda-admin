<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ChatTiendaBloqueo
 *
 * Bloqueo direccional bloqueador→bloqueado; con motivo_reporte es además reporte.
 *
 * @property int $id
 * @property int $bloqueador_id
 * @property int $bloqueado_id
 * @property string|null $motivo_reporte
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ChatTiendaBloqueo extends Model
{
    use HasFactory;

    protected $table = 'chat_tienda_bloqueos';

    protected $fillable = [
        'bloqueador_id',
        'bloqueado_id',
        'motivo_reporte',
    ];

    protected $casts = [
        'id' => 'integer',
        'bloqueador_id' => 'integer',
        'bloqueado_id' => 'integer',
    ];

    public function bloqueador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bloqueador_id');
    }

    public function bloqueado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bloqueado_id');
    }
}
