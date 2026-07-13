<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\ChatTienda
 *
 * Conversación afiliado↔afiliado. El par se guarda normalizado:
 * afiliado_menor_id < afiliado_mayor_id siempre.
 *
 * @property int $id
 * @property int $afiliado_menor_id
 * @property int $afiliado_mayor_id
 * @property Carbon|null $last_message_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ChatTienda extends Model
{
    use HasFactory;

    protected $table = 'chats_tienda';

    protected $fillable = [
        'afiliado_menor_id',
        'afiliado_mayor_id',
        'last_message_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'afiliado_menor_id' => 'integer',
        'afiliado_mayor_id' => 'integer',
        'last_message_at' => 'datetime',
    ];

    public function afiliadoMenor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'afiliado_menor_id');
    }

    public function afiliadoMayor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'afiliado_mayor_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(ChatTiendaMensaje::class, 'chat_id');
    }

    public function ultimoMensaje(): HasOne
    {
        return $this->hasOne(ChatTiendaMensaje::class, 'chat_id')->latestOfMany();
    }

    public function esParticipante(int $userId): bool
    {
        return $this->afiliado_menor_id === $userId || $this->afiliado_mayor_id === $userId;
    }

    public function contraparteId(int $userId): int
    {
        return $this->afiliado_menor_id === $userId ? $this->afiliado_mayor_id : $this->afiliado_menor_id;
    }
}
