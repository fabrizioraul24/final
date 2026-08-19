<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductLot;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AlmacenLotController extends Controller
{
    private bool $laPazWarehouseLoaded = false;

    private ?Warehouse $laPazWarehouse = null;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string'],
            'product_id' => ['nullable', 'exists:products,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $search = $filters['search'] ?? null;
        $productId = $filters['product_id'] ?? null;
        $warehouseId = $filters['warehouse_id'] ?? null;
        $expires = $filters['expires_at'] ?? null;
        $resolvedWarehouseId = $this->resolvedWarehouseId($warehouseId);

        $productsWithLots = $this->buildLotProductsQuery($search, $productId, $warehouseId, $expires)
            ->paginate(8)
            ->withQueryString();

        $productsWithLots->setCollection($this->decorateLotProducts($productsWithLots->getCollection()));

        $stats = [
            'lots' => ProductLot::when($resolvedWarehouseId, fn ($q) => $q->where('warehouse_id', $resolvedWarehouseId))->count(),
            'stock' => ProductLot::when($resolvedWarehouseId, fn ($q) => $q->where('warehouse_id', $resolvedWarehouseId))->sum('quantity'),
            'expiring' => ProductLot::when($resolvedWarehouseId, fn ($q) => $q->where('warehouse_id', $resolvedWarehouseId))
                ->whereBetween('expires_at', [now(), now()->addDays(30)])
                ->count(),
        ];

        return view('dashboard.almacen-lotes', [
            'productsWithLots' => $productsWithLots,
            'products' => Product::orderBy('name')->get(),
            'warehouses' => $this->lotWarehouses(),
            'filters' => [
                'search' => $search,
                'product_id' => $productId,
                'warehouse_id' => $resolvedWarehouseId,
                'expires_at' => $expires,
            ],
            'stats' => $stats,
        ]);
    }

    private function getLaPazWarehouse(): ?Warehouse
    {
        if ($this->laPazWarehouseLoaded) {
            return $this->laPazWarehouse;
        }

        $this->laPazWarehouseLoaded = true;
        $this->laPazWarehouse = Warehouse::query()
            ->where(function ($query) {
                $query->where('code', 'LPZ')
                    ->orWhere('city', 'La Paz');
            })
            ->first();

        return $this->laPazWarehouse;
    }

    private function resolvedWarehouseId(?string $warehouseId): ?int
    {
        return $this->getLaPazWarehouse()?->id ?? ($warehouseId ? (int) $warehouseId : null);
    }

    private function lotWarehouses(): Collection
    {
        $laPaz = $this->getLaPazWarehouse();

        return $laPaz ? collect([$laPaz]) : Warehouse::orderBy('name')->get();
    }

    private function buildLotProductsQuery(?string $search, ?string $productId, ?string $warehouseId, ?string $expires)
    {
        $resolvedWarehouseId = $this->resolvedWarehouseId($warehouseId);
        $lotScope = function ($query) use ($resolvedWarehouseId, $expires) {
            $query
                ->when($resolvedWarehouseId, fn ($builder) => $builder->where('warehouse_id', $resolvedWarehouseId))
                ->when($expires, fn ($builder) => $builder->whereDate('expires_at', $expires))
                ->orderBy('expires_at')
                ->orderBy('id');
        };

        return Product::query()
            ->select(['id', 'category_id', 'name', 'sku', 'description', 'image_path', 'min_quantity'])
            ->with([
                'category:id,name',
                'lots' => function ($query) use ($lotScope) {
                    $lotScope($query);
                    $query->select(['id', 'product_id', 'warehouse_id', 'lote_code', 'quantity', 'expires_at']);
                    $query->with([
                        'warehouse:id,name',
                        'latestMovement' => fn ($movementQuery) => $movementQuery
                            ->select([
                                'product_lot_movements.id',
                                'product_lot_movements.lot_id',
                                'product_lot_movements.user_id',
                                'product_lot_movements.type',
                                'product_lot_movements.quantity',
                                'product_lot_movements.note',
                                'product_lot_movements.created_at',
                            ])
                            ->with('user:id,name'),
                    ]);
                },
            ])
            ->when($search, fn ($query) => $query->whereAnyLikeInsensitive(['name', 'sku', 'description'], $search))
            ->when($productId, fn ($query) => $query->where('id', $productId))
            ->whereHas('lots', $lotScope)
            ->withSum(['lots as current_stock' => $lotScope], 'quantity')
            ->withCount(['lots as lots_count' => $lotScope])
            ->withMin(['lots as next_expiry' => $lotScope], 'expires_at')
            ->orderBy('name');
    }

    private function decorateLotProducts(Collection $products): Collection
    {
        return $products->transform(function (Product $product) {
            $lots = $product->lots->values();
            $product->current_stock = (int) ($product->current_stock ?? 0);
            $product->lots_count = (int) ($product->lots_count ?? $lots->count());
            $product->history_rows = $lots->map(function (ProductLot $lot) {
                $lastMovement = $lot->latestMovement;

                return [
                    'id' => $lot->id,
                    'code' => $lot->lote_code ?: 'Sin codigo',
                    'quantity' => (int) $lot->quantity,
                    'warehouse' => $lot->warehouse?->name ?? '-',
                    'expires_at' => optional($lot->expires_at)?->format('d/m/Y') ?? 'Sin fecha',
                    'raw_expires_at' => optional($lot->expires_at)?->format('Y-m-d') ?? '',
                    'last_movement' => $lastMovement?->type ? ucfirst($lastMovement->type) : 'Sin movimientos',
                    'last_movement_qty' => $lastMovement?->quantity,
                    'last_movement_at' => optional($lastMovement?->created_at)?->format('d/m/Y H:i'),
                    'last_movement_user' => $lastMovement?->user?->name,
                ];
            })->values();

            $product->movement_history = $lots
                ->map(function (ProductLot $lot) {
                    $movement = $lot->latestMovement;

                    if (! $movement) {
                        return null;
                    }

                    return [
                        'lot_code' => $lot->lote_code ?: 'Sin codigo',
                        'type' => ucfirst($movement->type),
                        'quantity' => (int) $movement->quantity,
                        'note' => $movement->note ?: 'Sin nota',
                        'user' => $movement->user?->name ?: 'Sistema',
                        'date' => optional($movement->created_at)->format('d/m/Y H:i'),
                        'timestamp' => optional($movement->created_at)?->timestamp ?? 0,
                    ];
                })
                ->filter()
                ->sortByDesc('timestamp')
                ->take(8)
                ->values();

            return $product;
        });
    }
}
