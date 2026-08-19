<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = [
        'empresa_institucional',
        'tienda_barrio',
        'comprador_minorista',
    ];

    public const STATUSES = [
        'borrador',
        'enviada',
        'aceptada',
        'rechazada',
    ];

    protected $fillable = [
        'company_id',
        'customer_id',
        'seller_id',
        'sale_type',
        'valid_until',
        'status',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }
}
