<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'product_id',
        'requested_qty',
        'received_qty',
        'notes',
        'damaged_qty',
        'lot_code',
        'receiving_expires_at',
        'receiving_note',
    ];

    protected $casts = [
        'receiving_expires_at' => 'date',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function generatedLot(): HasOne
    {
        return $this->hasOne(ProductLot::class, 'lote_code', 'lot_code');
    }
}
