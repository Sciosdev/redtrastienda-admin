<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ChatTiendaMensaje
 *
 * @property int $id
 * @property int $chat_id
 * @property int $sender_id
 * @property string $mensaje
 * @property Carbon|null $read_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ChatTiendaMensaje extends Model
{
    use HasFactory;

    protected $table = 'chat_tienda_mensajes';

    protected $fillable = [
        'chat_id',
        'sender_id',
        'mensaje',
        'read_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'chat_id' => 'integer',
        'sender_id' => 'integer',
        'read_at' => 'datetime',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(ChatTienda::class, 'chat_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
