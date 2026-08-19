<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = [
        'empresa_institucional',
        'tienda_barrio',
        'comprador_minorista',
    ];

    public const STATUSES = [
        'sin_entregar',
        'entregado',
    ];

    protected $fillable = [
        'company_id',
        'customer_id',
        'seller_id',
        'warehouse_id',
        'sale_type',
        'delivery_address',
        'delivery_city',
        'delivery_city_id',
        'status',
        'payment_method',
        'amount_received',
        'change_amount',
        'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
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

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function deliveryCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'delivery_city_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
