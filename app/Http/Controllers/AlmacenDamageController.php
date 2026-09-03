<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LogsAudit;
use App\Models\DamageReport;
use App\Models\Product;
use App\Models\ProductLot;
use App\Models\ProductLotMovement;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AlmacenDamageController extends Controller
{
    use LogsAudit;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);
        $search = $filters['search'] ?? null;
        $productId = $filters['product_id'] ?? null;
        $warehouseId = $this->targetWarehouse()?->id;

        $productsWithLots = $this->buildProductsQuery($search, $productId, $warehouseId)
            ->paginate(8)
            ->withQueryString();
        $productsWithLots->setCollection($this->decorateProducts($productsWithLots->getCollection()));

        $reports = DamageReport::with(['lot.product', 'product', 'warehouse', 'reporter'])
            ->when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'reports' => DamageReport::when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))->count(),
            'units' => DamageReport::when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))->sum('damaged_qty'),
            'products' => DamageReport::when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))->distinct('product_id')->count('product_id'),
            'today' => DamageReport::when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))->whereDate('created_at', today())->count(),
        ];

        return view('dashboard.almacen-danos', [
            'productsWithLots' => $productsWithLots,
            'products' => Product::orderBy('name')->get(),
            'reports' => $reports,
            'filters' => [
                'search' => $search,
                'product_id' => $productId,
            ],
            'stats' => $stats,
            'targetWarehouse' => $this->targetWarehouse(),
        ]);
    }

    public function create(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        $search = $filters['search'] ?? null;
        $productId = $filters['product_id'] ?? null;
        $targetWarehouse = $this->targetWarehouse();
        $warehouseId = $targetWarehouse?->id;

        $lots = ProductLot::query()
            ->with(['product.category', 'warehouse'])
            ->when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
            ->where('quantity', '>', 0)
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->when($search, function ($query, string $value) {
                $query->where(function ($subQuery) use ($value) {
                    $subQuery
                        ->where('lote_code', 'like', '%' . $value . '%')
                        ->orWhereHas('product', function ($productQuery) use ($value) {
                            $productQuery->whereAnyLikeInsensitive(['name', 'sku', 'description'], $value);
                        });
                });
            })
            ->orderBy('expires_at')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'lots' => ProductLot::when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))->where('quantity', '>', 0)->count(),
            'stock' => ProductLot::when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))->where('quantity', '>', 0)->sum('quantity'),
            'expiring' => ProductLot::when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
                ->where('quantity', '>', 0)
                ->whereBetween('expires_at', [now(), now()->addDays(30)])
                ->count(),
        ];

        return view('dashboard.almacen-danos-registrar', [
            'lots' => $lots,
            'products' => Product::whereHas('lots', fn ($query) => $query
                ->when($warehouseId, fn ($builder) => $builder->where('warehouse_id', $warehouseId))
                ->where('quantity', '>', 0))
                ->orderBy('name')
                ->get(),
            'filters' => [
                'search' => $search,
                'product_id' => $productId,
            ],
            'stats' => $stats,
            'targetWarehouse' => $targetWarehouse,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_lot_id' => ['required', 'exists:product_lots,id'],
            'damaged_qty' => ['required', 'integer', 'min:1'],
            'comment' => ['nullable', 'string'],
        ]);

        $lot = ProductLot::with(['product', 'warehouse'])->findOrFail($data['product_lot_id']);
        $targetWarehouse = $this->targetWarehouse();

        abort_unless(! $targetWarehouse || (int) $lot->warehouse_id === (int) $targetWarehouse->id, 404);

        if ($lot->quantity < $data['damaged_qty']) {
            return back()->withErrors(['damaged_qty' => 'El lote solo tiene ' . $lot->quantity . ' unidades disponibles.'])->withInput();
        }

        $oldLot = [
            'product_id' => $lot->product_id,
            'product' => $lot->product?->name,
            'warehouse_id' => $lot->warehouse_id,
            'warehouse' => $lot->warehouse?->name,
            'lote_code' => $lot->lote_code,
            'quantity' => (int) $lot->quantity,
        ];
        $report = null;

        DB::transaction(function () use ($data, $lot, $request, &$report) {
            $lot->quantity -= $data['damaged_qty'];
            $lot->save();

            $report = DamageReport::create([
                'product_lot_id' => $lot->id,
                'product_id' => $lot->product_id,
                'warehouse_id' => $lot->warehouse_id,
                'reported_by' => $request->user()?->id,
                'damaged_qty' => $data['damaged_qty'],
                'comment' => $data['comment'] ?? null,
            ]);

            ProductLotMovement::create([
                'lot_id' => $lot->id,
                'user_id' => $request->user()?->id,
                'type' => 'danio',
                'quantity' => -$data['damaged_qty'],
                'note' => $data['comment'] ?? 'Ajuste por daño',
            ]);
        });

        if ($report) {
            $lot->refresh()->load(['product', 'warehouse']);
            $this->logAudit($report, 'damage', $oldLot, [
                'product_lot_id' => $lot->id,
                'product_id' => $lot->product_id,
                'product' => $lot->product?->name,
                'warehouse_id' => $lot->warehouse_id,
                'warehouse' => $lot->warehouse?->name,
                'lote_code' => $lot->lote_code,
                'damaged_qty' => (int) $data['damaged_qty'],
                'remaining_quantity' => (int) $lot->quantity,
                'comment' => $data['comment'] ?? null,
            ], $data['comment'] ?? 'Registro de producto danado y ajuste de stock');
        }

        return redirect()
            ->route('dashboard.almacen.damages.create')
            ->with('status', 'Daño registrado y stock ajustado.');
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $lot = ProductLot::with(['product', 'warehouse'])
            ->where('lote_code', $data['code'])
            ->where('quantity', '>', 0)
            ->when($this->targetWarehouse(), fn ($query, $warehouse) => $query->where('warehouse_id', $warehouse->id))
            ->first();

        if (! $lot) {
            return response()->json(['message' => 'No encontramos un lote activo con ese código.'], 404);
        }

        return response()->json([
            'lot_id' => $lot->id,
            'lot_code' => $lot->lote_code,
            'product' => $lot->product->name ?? 'Producto',
            'sku' => $lot->product->sku ?? 'N/A',
            'quantity' => $lot->quantity,
            'expires_at' => optional($lot->expires_at)->format('Y-m-d'),
            'warehouse' => $lot->warehouse->name ?? 'Almacén',
        ]);
    }

    private function targetWarehouse(): ?Warehouse
    {
        return Warehouse::query()
            ->where(function ($query) {
                $query->where('code', 'LPZ')
                    ->orWhere('city', 'La Paz');
            })
            ->first();
    }

    private function buildProductsQuery(?string $search, ?string $productId, ?int $warehouseId)
    {
        $lotScope = function ($query) use ($warehouseId) {
            $query
                ->when($warehouseId, fn ($builder) => $builder->where('warehouse_id', $warehouseId))
                ->where('quantity', '>', 0)
                ->orderBy('expires_at')
                ->orderBy('id');
        };

        return Product::query()
            ->with([
                'category',
            ])
            ->when($search, fn ($query) => $query->whereAnyLikeInsensitive(['name', 'sku', 'description'], $search))
            ->when($productId, fn ($query) => $query->where('id', $productId))
            ->whereHas('lots', $lotScope)
            ->withSum(['lots as current_stock' => $lotScope], 'quantity')
            ->withCount(['lots as lots_count' => $lotScope])
            ->orderBy('name');
    }

    private function decorateProducts(Collection $products): Collection
    {
        return $products->transform(function (Product $product) {
            $product->current_stock = (int) ($product->current_stock ?? 0);
            $product->lots_count = (int) ($product->lots_count ?? 0);

            return $product;
        });
    }
}
