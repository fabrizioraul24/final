<?php

namespace App\Http\Controllers;

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

        $reports = DamageReport::with(['lot.product', 'warehouse', 'reporter'])
            ->when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
            ->latest()
            ->paginate(10);

        $stats = [
            'reports' => DamageReport::when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))->count(),
            'units' => DamageReport::when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))->sum('damaged_qty'),
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

        if ($lot->quantity < $data['damaged_qty']) {
            return back()->withErrors(['damaged_qty' => 'El lote solo tiene ' . $lot->quantity . ' unidades disponibles.'])->withInput();
        }

        DB::transaction(function () use ($data, $lot, $request) {
            $lot->quantity -= $data['damaged_qty'];
            $lot->save();

            DamageReport::create([
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

        return redirect()
            ->route('dashboard.almacen.damages')
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
                'lots' => function ($query) use ($lotScope) {
                    $lotScope($query);
                    $query->with(['warehouse']);
                },
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
            $product->lots_count = (int) ($product->lots_count ?? $product->lots->count());
            $product->damage_lots = $product->lots->map(fn (ProductLot $lot) => [
                'id' => $lot->id,
                'code' => $lot->lote_code ?: 'Sin codigo',
                'quantity' => (int) $lot->quantity,
                'warehouse' => $lot->warehouse?->name ?? 'Almacen',
                'expires_at' => optional($lot->expires_at)?->format('d/m/Y') ?? 'Sin fecha',
            ])->values();

            return $product;
        });
    }
}
