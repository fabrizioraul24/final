<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfer extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pendiente';
    public const STATUS_IN_TRANSIT = 'en_transito';
    public const STATUS_RECEIVED = 'recibido';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_TRANSIT,
        self::STATUS_RECEIVED,
    ];

    protected $fillable = [
        'from_warehouse_id',
        'to_warehouse_id',
        'requested_by',
        'approved_by',
        'received_by',
        'status',
        'expected_date',
        'received_date',
        'notes',
    ];

    protected $casts = [
        'expected_date' => 'date',
        'received_date' => 'date',
    ];

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }

    public function agentTransferRequest(): HasOne
    {
        return $this->hasOne(TransferRequest::class);
    }
}
