<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ProductLotMovement;

class ProductLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'lote_code',
        'quantity',
        'expires_at',
        'safety_threshold',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'safety_threshold' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(ProductLotMovement::class, 'lot_id');
    }

    public function latestMovement(): HasOne
    {
        return $this->hasOne(ProductLotMovement::class, 'lot_id')->latestOfMany();
    }

    protected static function booted(): void
    {
        static::saved(function (ProductLot $lot) {
            $lot->syncInventory();
        });

        static::deleted(function (ProductLot $lot) {
            $lot->syncInventory();
        });
    }

    /**
     * Valida que la suma total de stock de un producto no exceda su maximo configurado.
     *
     * @throws \RuntimeException
     */
    public static function assertWithinMaxCapacity(int $productId, int $newTotal): void
    {
        $product = Product::find($productId);
        if (! $product || ! $product->max_quantity || $product->max_quantity <= 0) {
            return; // sin limite configurado
        }

        if ($newTotal > $product->max_quantity) {
            throw new \RuntimeException("Se supera el stock maximo permitido ({$product->max_quantity}). Intento: {$newTotal}.");
        }
    }

    public function syncInventory(): void
    {
        $total = static::where('product_id', $this->product_id)
            ->where('warehouse_id', $this->warehouse_id)
            ->sum('quantity');

        DB::table('inventory')->updateOrInsert(
            [
                'product_id' => $this->product_id,
                'warehouse_id' => $this->warehouse_id,
            ],
            [
                'quantity' => $total,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public static function available(int $productId, int $warehouseId): int
    {
        return static::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', now()->toDateString());
            })
            ->sum('quantity');
    }

    public static function consumeFefo(int $productId, int $warehouseId, int $quantity, string $type = 'venta', ?int $userId = null, ?string $note = null): void
    {
        $remaining = $quantity;
        $lots = static::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', now()->toDateString());
            })
            ->orderBy('expires_at')
            ->lockForUpdate()
            ->get();

        foreach ($lots as $lot) {
            if ($remaining <= 0) {
                break;
            }
            $take = min($remaining, $lot->quantity);
            $lot->quantity = $lot->quantity - $take;
            $lot->save();
            ProductLotMovement::create([
                'lot_id' => $lot->id,
                'user_id' => $userId,
                'type' => $type,
                'quantity' => -$take,
                'note' => $note,
            ]);
            $remaining -= $take;
        }

        if ($remaining > 0) {
            throw new \RuntimeException('No hay stock suficiente por lotes.');
        }
    }

    public static function addStock(int $productId, int $warehouseId, int $quantity, ?string $loteCode, \Carbon\Carbon|string $expiresAt, string $type = 'ingreso', ?int $userId = null, ?string $note = null): ProductLot
    {
        $currentTotal = static::where('product_id', $productId)->sum('quantity');
        static::assertWithinMaxCapacity($productId, $currentTotal + $quantity);

        $lot = static::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'lote_code' => $loteCode,
            'quantity' => $quantity,
            'expires_at' => $expiresAt,
        ]);

        ProductLotMovement::create([
            'lot_id' => $lot->id,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $quantity,
            'note' => $note,
        ]);

        return $lot;
    }
}
