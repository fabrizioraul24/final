<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductLot;
use App\Models\ProductLotMovement;
use App\Models\Warehouse;
use App\Services\ReportService;
use App\Support\AdminReact;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProductLotController extends Controller
{
    private bool $laPazWarehouseLoaded = false;

    private ?Warehouse $laPazWarehouse = null;

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $productId = $request->input('product_id');
        $warehouseId = $request->input('warehouse_id');
        $expires = $request->input('expires_at');

        $productsWithLots = $this->buildLotProductsQuery($search, $productId, $warehouseId, $expires)
            ->paginate(8)
            ->withQueryString();

        $productsWithLots->setCollection($this->decorateLotProducts($productsWithLots->getCollection()));

        $today = Carbon::today();
        $stats = [
            'products' => ProductLot::query()->distinct('product_id')->count('product_id'),
            'lots' => ProductLot::count(),
            'stock' => (int) ProductLot::sum('quantity'),
            'expiring' => ProductLot::query()
                ->where('quantity', '>', 0)
                ->whereBetween('expires_at', [$today, $today->copy()->addDays(30)])
                ->count(),
        ];

        return view('react-page', AdminReact::page('lots', 'Lotes | Pil Andina', 'Gestion de Lotes (FEFO)', 'lots', [
            'data' => [
                'productsWithLots' => AdminReact::paginator($productsWithLots->through(fn (Product $product) => $this->lotProductPayload($product))),
                'products' => Product::orderBy('name')->get()->map(fn (Product $product) => ['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku]),
                'warehouses' => $this->lotWarehouses()->map(fn ($warehouse) => ['id' => $warehouse->id, 'name' => $warehouse->name]),
                'filters' => [
                    'search' => $search,
                    'product_id' => $productId,
                    'warehouse_id' => $this->resolvedWarehouseId($warehouseId),
                    'expires_at' => $expires,
                ],
                'stats' => $stats,
                'routes' => [
                    'index' => route('dashboard.lots'),
                    'store' => route('dashboard.lots.store'),
                    'report' => route('dashboard.lots.report', [
                        'search' => $search,
                        'product_id' => $productId,
                        'warehouse_id' => $this->resolvedWarehouseId($warehouseId),
                        'expires_at' => $expires,
                    ]),
                ],
                'modalError' => session('modal_error'),
            ],
        ], 'adminLots'));
    }

    public function report(Request $request)
    {
        $search = $request->input('search');
        $productId = $request->input('product_id');
        $warehouseId = $request->input('warehouse_id');
        $expires = $request->input('expires_at');

        $products = $this->decorateLotProducts(
            $this->buildLotProductsQuery($search, $productId, $warehouseId, $expires)->get()
        );
        $expiringTimeline = collect(range(0, 3))->map(function (int $offset) use ($products) {
            $month = Carbon::now()->startOfMonth()->addMonths($offset);
            $count = $products->sum(function (Product $product) use ($month) {
                return collect($product->history_rows)->filter(function (array $row) use ($month) {
                    if (empty($row['raw_expires_at'])) {
                        return false;
                    }

                    $expiresAt = Carbon::parse($row['raw_expires_at']);

                    return $expiresAt->year === $month->year && $expiresAt->month === $month->month;
                })->count();
            });

            return [
                'label' => $month->translatedFormat('M Y'),
                'count' => $count,
            ];
        })->values();
        $totalLots = $products->sum(fn (Product $product) => (int) $product->lots_count);

        $selectedProduct = $productId ? Product::find($productId)?->name : null;
        $selectedWarehouse = $this->lotWarehouses()->firstWhere('id', (int) $this->resolvedWarehouseId($warehouseId))?->name;

        return ReportService::download('reports.lots', [
            'title' => 'Reporte de lotes por producto',
            'generatedAt' => now(),
            'products' => $products,
            'totalLots' => $totalLots,
            'expiringTimeline' => $expiringTimeline,
            'filters' => [
                'search' => $search,
                'product' => $selectedProduct,
                'warehouse' => $selectedWarehouse,
                'expires_at' => $expires,
            ],
        ], 'reporte-lotes.pdf');
    }

    public function store(Request $request): RedirectResponse
    {
        $laPaz = $this->getLaPazWarehouse();

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'lote_code' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1'],
            'expires_at' => ['required', 'date'],
        ]);

        if (! $laPaz) {
            return back()->withErrors(['warehouse_id' => 'No existe la bodega de La Paz configurada.'])->withInput();
        }

        try {
            $lot = ProductLot::addStock(
                $data['product_id'],
                $laPaz->id,
                $data['quantity'],
                $data['lote_code'],
                $data['expires_at'],
                'ingreso',
                $request->user()?->id,
                'Alta manual de lote'
            );
        } catch (\RuntimeException $e) {
            return back()
                ->withErrors(['quantity' => $e->getMessage()])
                ->with('modal_error', $e->getMessage())
                ->withInput();
        }

        return back()->with('status', "Lote #{$lot->id} creado.");
    }

    public function adjust(Request $request, ProductLot $lot): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer'],
            'lote_code' => ['nullable', 'string', 'max:100'],
            'expires_at' => ['required', 'date'],
        ]);

        $previous = $lot->quantity;
        $newQuantity = max(0, $data['quantity']);

        $currentTotal = ProductLot::where('product_id', $lot->product_id)->sum('quantity');
        $newTotal = $currentTotal - $previous + $newQuantity;

        if ($newQuantity > $previous) {
            try {
                ProductLot::assertWithinMaxCapacity($lot->product_id, $newTotal);
            } catch (\RuntimeException $e) {
                return back()
                    ->withErrors(['quantity' => $e->getMessage()])
                    ->with('modal_error', $e->getMessage())
                    ->withInput();
            }
        }

        $lot->lote_code = $data['lote_code'] ?? $lot->lote_code;
        $lot->expires_at = $data['expires_at'];
        $lot->quantity = $newQuantity;
        $lot->save();

        if ($lot->quantity !== $previous) {
            ProductLotMovement::create([
                'lot_id' => $lot->id,
                'user_id' => $request->user()?->id,
                'type' => 'ajuste',
                'quantity' => $lot->quantity - $previous,
                'note' => 'Ajuste lote',
            ]);
        }

        return back()->with('status', 'Ajuste registrado.');
    }

    private function getLaPazWarehouse(): ?\App\Models\Warehouse
    {
        if ($this->laPazWarehouseLoaded) {
            return $this->laPazWarehouse;
        }

        $this->laPazWarehouseLoaded = true;
        $this->laPazWarehouse = Warehouse::where('code', 'LPZ')
            ->orWhere('city', 'La Paz')
            ->first();

        return $this->laPazWarehouse;
    }

    private function resolvedWarehouseId(?string $warehouseId): ?int
    {
        return $this->getLaPazWarehouse()?->id ?? ($warehouseId ? (int) $warehouseId : null);
    }

    private function lotWarehouses()
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
            $product->current_lot_quantity = (int) optional($lots->first())->quantity;
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
                    'action' => route('dashboard.lots.adjust', $lot),
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

    private function lotProductPayload(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->category ? ['name' => $product->category->name] : null,
            'description' => $product->description ?: 'Sin descripcion registrada.',
            'image' => $product->getImageUrl(),
            'current_stock' => (int) ($product->current_stock ?? 0),
            'minimum_stock' => (int) ($product->min_quantity ?? 0),
            'lots_count' => (int) ($product->lots_count ?? 0),
            'next_expiry' => $product->next_expiry ? Carbon::parse($product->next_expiry)->format('d/m/Y') : 'Sin fecha',
            'history_rows' => $product->history_rows,
            'movement_history' => $product->movement_history,
        ];
    }
}
