<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'sku',
        'suggested_price_public',
        'price_institutional',
        'is_active',
        'image_path',
        'min_quantity',
        'max_quantity',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'suggested_price_public' => 'decimal:2',
        'price_institutional' => 'decimal:2',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
    ];

    public function lots(): HasMany
    {
        return $this->hasMany(ProductLot::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Obtiene la URL de la imagen del producto
     * Devuelve una imagen placeholder si no hay imagen
     */
    public function getImageUrl(): string
    {
        if (! $this->image_path) {
            return 'https://placehold.co/400x400?text=Producto';
        }

        // Usar asset() con la ruta relativa storage/
        // Funciona porque el enlace simbólico de storage:link está creado
        return asset('storage/' . $this->image_path);
    }
}
