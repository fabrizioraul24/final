<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuyerOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'receipt_number',
        'payment_method',
        'payment_status',
        'status',
        'subtotal',
        'shipping',
        'total',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(BuyerOrderItem::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
