<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\OpportunityRequest
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $seller_id
 * @property int|null $customer_id
 * @property string|null $comment
 * @property string $status
 * @property string|null $provider_response
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class OpportunityRequest extends Model
{
    use HasFactory;

    protected $table = 'opportunity_requests';

    protected $fillable = [
        'product_id',
        'seller_id',
        'customer_id',
        'comment',
        'status',
        'provider_response',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'seller_id' => 'integer',
        'customer_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }
}
