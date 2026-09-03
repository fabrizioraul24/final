<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LogsAudit;
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
    use LogsAudit;

    private bool $laPazWarehouseLoaded = false;

    private ?Warehouse $laPazWarehouse = null;

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $productId = $request->input('product_id');
        $warehouseId = $request->input('warehouse_id');
        $expires = $request->input('expires_at');
        $scope = $request->input('scope');

        $productsWithLots = $this->buildLotProductsQuery($search, $productId, $warehouseId, $expires, $scope)
            ->paginate(8)
            ->withQueryString();

        $productsWithLots->setCollection($this->decorateLotProductSummaries($productsWithLots->getCollection()));

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
                    'scope' => $scope,
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
                        'scope' => $scope,
                    ]),
                    'show_template' => route('dashboard.lots.show', ['product' => '__PRODUCT__']),
                ],
                'modalError' => session('modal_error'),
            ],
        ], 'adminLots'));
    }

    public function show(Request $request, Product $product): View
    {
        $warehouseId = $request->input('warehouse_id');
        $expires = $request->input('expires_at');
        $scope = $request->input('scope');
        $search = $request->input('search');
        $resolvedWarehouseId = $this->resolvedWarehouseId($warehouseId);
        $today = Carbon::today();

        $summaryScope = function ($query) use ($resolvedWarehouseId, $expires, $scope, $today) {
            $query
                ->when($resolvedWarehouseId, fn ($builder) => $builder->where('warehouse_id', $resolvedWarehouseId))
                ->when($expires, fn ($builder) => $builder->whereDate('expires_at', $expires))
                ->when($scope === 'expiring', fn ($builder) => $builder
                    ->where('quantity', '>', 0)
                    ->whereBetween('expires_at', [$today, $today->copy()->addDays(30)]));
        };

        $product = Product::query()
            ->select(['id', 'category_id', 'name', 'sku', 'description', 'image_path', 'min_quantity'])
            ->with('category:id,name')
            ->withSum(['lots as current_stock' => $summaryScope], 'quantity')
            ->withCount(['lots as lots_count' => $summaryScope])
            ->withMin(['lots as next_expiry' => $summaryScope], 'expires_at')
            ->findOrFail($product->id);

        $lots = ProductLot::query()
            ->with([
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
            ])
            ->where('product_id', $product->id)
            ->when($resolvedWarehouseId, fn ($query) => $query->where('warehouse_id', $resolvedWarehouseId))
            ->when($expires, fn ($query) => $query->whereDate('expires_at', $expires))
            ->when($search, fn ($query) => $query->where('lote_code', 'like', '%' . $search . '%'))
            ->when($scope === 'expiring', fn ($query) => $query
                ->where('quantity', '>', 0)
                ->whereBetween('expires_at', [$today, $today->copy()->addDays(30)]))
            ->orderBy('expires_at')
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        $lots->through(fn (ProductLot $lot) => [
            'id' => $lot->id,
            'code' => $lot->lote_code ?: 'Sin codigo',
            'quantity' => (int) $lot->quantity,
            'warehouse' => $lot->warehouse?->name ?? '-',
            'expires_at' => optional($lot->expires_at)?->format('d/m/Y') ?? 'Sin fecha',
            'raw_expires_at' => optional($lot->expires_at)?->format('Y-m-d') ?? '',
            'last_movement' => $lot->latestMovement?->type ? ucfirst($lot->latestMovement->type) : 'Sin movimientos',
            'last_movement_qty' => $lot->latestMovement?->quantity,
            'last_movement_at' => optional($lot->latestMovement?->created_at)?->format('d/m/Y H:i'),
            'last_movement_user' => $lot->latestMovement?->user?->name ?: 'Sistema',
            'action' => route('dashboard.lots.adjust', $lot),
        ]);

        $movementHistory = ProductLotMovement::query()
            ->with(['lot:id,product_id,lote_code,warehouse_id', 'user:id,name'])
            ->whereHas('lot', function ($query) use ($product, $resolvedWarehouseId) {
                $query->where('product_id', $product->id)
                    ->when($resolvedWarehouseId, fn ($builder) => $builder->where('warehouse_id', $resolvedWarehouseId));
            })
            ->latest()
            ->take(12)
            ->get()
            ->map(fn (ProductLotMovement $movement) => [
                'lot_code' => $movement->lot?->lote_code ?: 'Sin codigo',
                'type' => ucfirst($movement->type),
                'quantity' => (int) $movement->quantity,
                'note' => $movement->note ?: 'Sin nota',
                'user' => $movement->user?->name ?: 'Sistema',
                'date' => optional($movement->created_at)->format('d/m/Y H:i'),
            ]);

        return view('dashboard.lotes-detalle', [
            'product' => $product,
            'lots' => $lots,
            'movementHistory' => $movementHistory,
            'warehouses' => $this->lotWarehouses(),
            'warehouse' => $this->lotWarehouses()->firstWhere('id', (int) $resolvedWarehouseId),
            'filters' => [
                'search' => $search,
                'warehouse_id' => $resolvedWarehouseId,
                'expires_at' => $expires,
                'scope' => $scope,
            ],
        ]);
    }

    public function report(Request $request)
    {
        $search = $request->input('search');
        $productId = $request->input('product_id');
        $warehouseId = $request->input('warehouse_id');
        $expires = $request->input('expires_at');
        $scope = $request->input('scope');

        $products = $this->decorateLotProducts(
            $this->buildLotProductsQuery($search, $productId, $warehouseId, $expires, $scope, true)->get()
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

        $lot->load(['product:id,name,sku', 'warehouse:id,name']);
        $this->logAudit($lot, 'stock_in', [], $this->lotAuditPayload($lot), 'Ingreso manual de lote');

        return back()->with('status', "Lote #{$lot->id} creado.");
    }

    public function adjust(Request $request, ProductLot $lot): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer'],
            'lote_code' => ['nullable', 'string', 'max:100'],
            'expires_at' => ['required', 'date'],
        ]);

        $old = $this->lotAuditPayload($lot->loadMissing(['product:id,name,sku', 'warehouse:id,name']));
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

        $lot->refresh()->load(['product:id,name,sku', 'warehouse:id,name']);
        $this->logAudit($lot, 'stock_adjustment', $old, $this->lotAuditPayload($lot), 'Ajuste manual de lote');

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

    private function buildLotProductsQuery(?string $search, ?string $productId, ?string $warehouseId, ?string $expires, ?string $scope = null, bool $withDetails = false)
    {
        $resolvedWarehouseId = $this->resolvedWarehouseId($warehouseId);
        $today = Carbon::today();
        $lotScope = function ($query) use ($resolvedWarehouseId, $expires, $scope, $today) {
            $query
                ->when($resolvedWarehouseId, fn ($builder) => $builder->where('warehouse_id', $resolvedWarehouseId))
                ->when($expires, fn ($builder) => $builder->whereDate('expires_at', $expires))
                ->when($scope === 'expiring', fn ($builder) => $builder
                    ->where('quantity', '>', 0)
                    ->whereBetween('expires_at', [$today, $today->copy()->addDays(30)]))
                ->orderBy('expires_at')
                ->orderBy('id');
        };

        $query = Product::query()
            ->select(['id', 'category_id', 'name', 'sku', 'description', 'image_path', 'min_quantity'])
            ->with('category:id,name')
            ->when($withDetails, function ($productQuery) use ($lotScope) {
                $productQuery->with(['lots' => function ($query) use ($lotScope) {
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
                }]);
            })
            ->when($search, fn ($query) => $query->whereAnyLikeInsensitive(['name', 'sku', 'description'], $search))
            ->when($productId, fn ($query) => $query->where('id', $productId))
            ->whereHas('lots', $lotScope)
            ->withSum(['lots as current_stock' => $lotScope], 'quantity')
            ->withCount(['lots as lots_count' => $lotScope])
            ->withMin(['lots as next_expiry' => $lotScope], 'expires_at')
            ->orderBy('name');

        return $query;
    }

    private function decorateLotProductSummaries(Collection $products): Collection
    {
        return $products->transform(function (Product $product) {
            $product->current_stock = (int) ($product->current_stock ?? 0);
            $product->lots_count = (int) ($product->lots_count ?? 0);

            return $product;
        });
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
        ];
    }

    private function lotAuditPayload(ProductLot $lot): array
    {
        return [
            'product_id' => $lot->product_id,
            'product' => $lot->product?->name,
            'sku' => $lot->product?->sku,
            'warehouse_id' => $lot->warehouse_id,
            'warehouse' => $lot->warehouse?->name,
            'lote_code' => $lot->lote_code,
            'quantity' => (int) $lot->quantity,
            'expires_at' => optional($lot->expires_at)->format('Y-m-d'),
        ];
    }
}
