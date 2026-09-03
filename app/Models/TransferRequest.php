<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'Pendiente';
    public const STATUS_APPROVED = 'Aprobado';
    public const STATUS_REJECTED = 'Rechazado';

    protected $fillable = [
        'product_id',
        'requested_qty',
        'status',
        'priority',
        'reason',
        'created_by_agent',
        'approved_by',
        'rejected_by',
        'approved_at',
        'rejected_at',
        'decision_reason',
        'transfer_id',
    ];

    protected $casts = [
        'requested_qty' => 'integer',
        'created_by_agent' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }
}
