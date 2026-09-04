<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LogsAudit;
use App\Models\City;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductLot;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\AdminReact;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SaleController extends Controller
{
    use LogsAudit;

    private const PAYMENT_METHODS = ['efectivo', 'qr', 'tarjeta_debito'];
    private const STATUS_LABELS = [
        'sin_entregar' => 'Sin entregar',
        'entregado' => 'Entregado',
    ];
    private const PAYMENT_LABELS = [
        'efectivo' => 'Efectivo',
        'qr' => 'QR',
        'tarjeta_debito' => 'Tarjeta de débito',
    ];

    public function index(Request $request): View
    {
        $isVendor = $request->routeIs('dashboard.vendedor.*');
        $saleType = $request->input('sale_type');
        $statusFilter = $request->input('status');
        $search = $request->input('search');

        $salesQuery = Sale::with(['company', 'customer.user', 'seller', 'warehouse', 'items.product'])
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($isVendor && $request->user()) {
            $salesQuery->where('seller_id', $request->user()->id);
        }

        if ($saleType) {
            $salesQuery->where('sale_type', $saleType);
        }

        if ($statusFilter) {
            $salesQuery->where('status', $statusFilter);
        }

        if ($search) {
            $salesQuery->where(function (Builder $query) use ($search) {
                $query->whereHas('company', fn($q) => $q->whereAnyLikeInsensitive(['name'], $search))
                    ->orWhereHas('customer.user', fn($q) => $q->whereAnyLikeInsensitive(['name'], $search))
                    ->orWhere('id', $search);
            });
        }

        $paymentLabels = self::PAYMENT_LABELS;

        $salesPaginator = $salesQuery->paginate(10)->withQueryString();

        $statsBase = Sale::query();
        if ($isVendor && $request->user()) {
            $statsBase->where('seller_id', $request->user()->id);
        }
        $stats = [
            'count' => (clone $statsBase)->count(),
            'total_amount' => (clone $statsBase)->sum('total_amount'),
            'pending' => (clone $statsBase)->where('status', 'sin_entregar')->count(),
            'delivered' => (clone $statsBase)->where('status', 'entregado')->count(),
        ];

        $storeRoute = $isVendor ? 'dashboard.vendedor.sales.store' : 'dashboard.sales.store';
        $lookupRoute = $isVendor ? 'dashboard.vendedor.sales.lookup' : 'dashboard.sales.lookup';
        $listRoute = $isVendor ? 'dashboard.vendedor.sales' : 'dashboard.sales';
        $updateRoute = $isVendor ? 'dashboard.vendedor.sales.update' : 'dashboard.sales.update';
        $laPazWarehouse = $this->getLaPazWarehouse();

        if ($isVendor) {
            return view('dashboard.vendedor.ventas', [
                'sales' => $salesPaginator,
                'saleTypes' => Sale::TYPES,
                'statuses' => Sale::STATUSES,
                'paymentLabels' => self::PAYMENT_LABELS,
                'stats' => $stats,
                'companies' => Company::where('created_by', $request->user()->id)->orderBy('name')->get(),
                'customers' => Customer::with('user')->orderBy('id', 'desc')->get(),
                'laPazWarehouse' => $laPazWarehouse,
                'cities' => City::orderBy('name')->get(),
                'filters' => [
                    'sale_type' => $saleType,
                    'status' => $statusFilter,
                    'search' => $search,
                ],
                'storeRoute' => $storeRoute,
                'lookupRoute' => $lookupRoute,
                'listRoute' => $listRoute,
                'updateRoute' => $updateRoute,
                'createRoute' => 'dashboard.vendedor.sales.create',
            ]);
        }

        $sales = $salesPaginator
            ->through(function (Sale $sale) use ($paymentLabels, $request) {
                return [
                    'id' => $sale->id,
                    'company' => $sale->company ? ['name' => $sale->company->name, 'city' => $sale->company->city] : null,
                    'customer' => $sale->customer ? ['name' => $sale->customer->user->name ?? 'Cliente', 'city' => $sale->customer->city] : null,
                    'sale_type' => $sale->sale_type,
                    'status' => $sale->status,
                    'payment_method' => $sale->payment_method,
                    'payment_label' => $paymentLabels[$sale->payment_method] ?? 'Sin metodo',
                    'total_amount' => (float) $sale->total_amount,
                    'warehouse' => $sale->warehouse ? ['name' => $sale->warehouse->name] : null,
                    'seller' => $sale->seller ? ['name' => $sale->seller->name] : null,
                    'delivery_address' => $sale->delivery_address,
                    'delivery_city' => $sale->delivery_city,
                    'amount_received' => $sale->amount_received ? (float) $sale->amount_received : null,
                    'change_amount' => $sale->change_amount ? (float) $sale->change_amount : null,
                    'created_at_formatted' => optional($sale->created_at)->format('d/m/Y H:i'),
                    'items' => $sale->items->map(fn (SaleItem $item) => [
                        'product' => $item->product->name ?? 'Producto',
                        'sku' => $item->product->sku ?? '',
                        'qty' => (int) $item->quantity,
                        'price' => (float) $item->unit_price,
                        'subtotal' => (float) $item->subtotal,
                    ])->values(),
                    'update_url' => route($request->routeIs('dashboard.vendedor.*') ? 'dashboard.vendedor.sales.update' : 'dashboard.sales.update', $sale),
                ];
            });

        return view('react-page', AdminReact::page('sales', 'Ventas | Pil Andina', 'Gestion de ventas', 'sales', [
            'data' => [
                'sales' => AdminReact::paginator($sales),
                'saleTypes' => Sale::TYPES,
                'statuses' => Sale::STATUSES,
                'paymentLabels' => self::PAYMENT_LABELS,
                'stats' => $stats,
                'companies' => Company::orderBy('name')->get()->map(fn (Company $company) => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'city' => $company->city,
                    'nit' => $company->nit,
                    'company_type' => $company->company_type,
                ]),
                'customers' => Customer::with('user')->get()->map(fn (Customer $customer) => [
                    'id' => $customer->id,
                    'name' => $customer->user->name ?? 'Cliente',
                    'city' => $customer->city,
                    'nit' => $customer->nit,
                ]),
                'laPazWarehouse' => $this->getLaPazWarehouse() ? [
                    'id' => $this->getLaPazWarehouse()->id,
                    'name' => $this->getLaPazWarehouse()->name,
                    'code' => $this->getLaPazWarehouse()->code,
                ] : null,
                'cities' => City::orderBy('name')->get()->map(fn (City $city) => ['id' => $city->id, 'name' => $city->name]),
                'filters' => [
                    'sale_type' => $saleType,
                    'status' => $statusFilter,
                    'search' => $search,
                ],
                'routes' => [
                    'index' => route($listRoute),
                    'store' => route($storeRoute),
                    'lookup' => route($lookupRoute),
                ],
            ],
        ], 'adminSales'));
    }

    public function vendorCreate(Request $request): View
    {
        return view('dashboard.vendedor.ventas-crear', [
            'saleTypes' => Sale::TYPES,
            'statuses' => Sale::STATUSES,
            'paymentLabels' => self::PAYMENT_LABELS,
            'companies' => Company::where('created_by', $request->user()->id)->orderBy('name')->get(),
            'customers' => Customer::with('user')->orderBy('id', 'desc')->get(),
            'laPazWarehouse' => $this->getLaPazWarehouse(),
            'cities' => City::orderBy('name')->get(),
            'storeRoute' => 'dashboard.vendedor.sales.store',
            'lookupRoute' => 'dashboard.vendedor.sales.lookup',
            'listRoute' => 'dashboard.vendedor.sales',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->input('warehouse_id')) {
            $request->merge(['warehouse_id' => $this->getLaPazWarehouse()?->id]);
        }

        $data = $request->validate([
            'sale_type' => ['required', Rule::in(Sale::TYPES)],
            'company_id' => ['nullable', 'exists:companies,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'status' => ['required', Rule::in(Sale::STATUSES)],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'delivery_city_id' => ['required', 'exists:cities,id'],
            'payment_method' => ['required', Rule::in(self::PAYMENT_METHODS)],
            'amount_received' => ['nullable', 'numeric', 'min:0'],
            'change_amount' => ['nullable', 'numeric', 'min:0'],
            'audit_reason' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Debes agregar al menos un producto a la venta.',
        ]);

        if ($data['sale_type'] === 'comprador_minorista' && empty($data['customer_id'])) {
            return back()->withErrors(['customer_id' => 'Selecciona un comprador minorista.'])->withInput();
        }

        if ($data['sale_type'] !== 'comprador_minorista' && empty($data['company_id'])) {
            return back()->withErrors(['company_id' => 'Selecciona una empresa o tienda.'])->withInput();
        }

        if ($request->routeIs('dashboard.vendedor.*') && ! empty($data['company_id'])) {
            $belongsToSeller = Company::where('id', $data['company_id'])
                ->where('created_by', $request->user()?->id)
                ->exists();

            if (! $belongsToSeller) {
                return back()
                    ->withErrors(['company_id' => 'Este cliente no pertenece a tu cartera de vendedor.'])
                    ->withInput();
            }
        }

        foreach ($data['items'] as $item) {
            $available = ProductLot::available($item['product_id'], $data['warehouse_id']);
            if ($available < $item['quantity']) {
                return back()
                    ->withErrors(['items' => "Stock insuficiente por lotes para el producto {$item['product_id']}."])
                    ->withInput();
            }
        }

        $sale = null;

        DB::transaction(function () use ($data, $request, &$sale) {

            $total = collect($data['items'])->reduce(function ($carry, $item) {
                return $carry + ($item['quantity'] * $item['unit_price']);
            }, 0);

            $amountReceived = $data['amount_received'] ?? null;
            $changeAmount = $data['change_amount'] ?? null;

            if ($amountReceived !== null && $changeAmount === null) {
                $changeAmount = max($amountReceived - $total, 0);
            }

            $city = City::find($data['delivery_city_id']);

            $sale = Sale::create([
                'company_id' => $data['sale_type'] === 'comprador_minorista' ? null : $data['company_id'],
                'customer_id' => $data['sale_type'] === 'comprador_minorista' ? $data['customer_id'] : null,
                'seller_id' => $request->user()?->id ?? auth()->id(),
                'warehouse_id' => $data['warehouse_id'],
                'sale_type' => $data['sale_type'],
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_city' => $city?->name,
                'delivery_city_id' => $city?->id,
                'status' => $data['status'],
                'payment_method' => $data['payment_method'] ?? null,
                'amount_received' => $amountReceived,
                'change_amount' => $changeAmount,
                'total_amount' => $total,
            ]);

            foreach ($data['items'] as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);

                ProductLot::consumeFefo(
                    $item['product_id'],
                    $data['warehouse_id'],
                    $item['quantity'],
                    'venta',
                    $request->user()?->id,
                    'Venta #' . $sale->id
                );
            }
        });

        if ($sale) {
            $sale->load(['items.product', 'company', 'customer.user', 'warehouse', 'seller']);
            $reason = trim((string) ($data['audit_reason'] ?? ''));
            $this->logAudit($sale, 'create', [], $this->saleAuditPayload($sale), $reason ?: 'Venta registrada. La bitacora conserva los precios unitarios usados en la venta.');
        }

        $route = $request->routeIs('dashboard.vendedor.*') ? 'dashboard.vendedor.sales' : 'dashboard.sales';
        return redirect()->route($route)->with('status', 'Venta registrada correctamente.');
    }

    public function lookupProduct(Request $request): JsonResponse
    {
        $sku = $request->query('sku');
        $query = $request->query('q');

        if (! $sku && ! $query) {
            return response()->json(['message' => 'Ingresa un código o nombre de producto.'], 422);
        }

        $product = null;
        if ($sku) {
            $product = Product::where('sku', $sku)->first();
        } else {
            $product = Product::whereAnyLikeInsensitive(['name', 'sku'], $query)
                ->orderBy('name')
                ->first();
        }

        if (! $product) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        $saleType = $request->query('sale_type', 'comprador_minorista');
        $warehouseId = $request->query('warehouse_id');

        $price = $saleType === 'empresa_institucional'
            ? $product->price_institutional
            : $product->suggested_price_public;

        $availableQuantity = $warehouseId
            ? ProductLot::available($product->id, $warehouseId)
            : null;

        return response()->json([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'price' => $price,
            'available_quantity' => $availableQuantity,
        ]);
    }

    public function update(Request $request, Sale $sale): RedirectResponse
    {
        $isVendorRoute = $request->routeIs('dashboard.vendedor.*');

        if ($isVendorRoute && $sale->seller_id !== $request->user()?->id) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(Sale::STATUSES)],
        ]);

        $old = $sale->only(['status']);
        $sale->update(['status' => $data['status']]);

        $this->logAudit($sale, 'status_update', $old, $sale->only(['status']), 'Cambio de estado de venta');

        return back()->with('status', 'Venta actualizada correctamente.');
    }

    public function vendorLog(Request $request): View
    {
        $sellerId = $request->user()?->id;
        abort_if(! $sellerId, 403);

        $filtersInput = $request->validate([
            'status' => ['nullable', Rule::in(Sale::STATUSES)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $dateRange = $this->normalizeDateRange($filtersInput['start_date'] ?? null, $filtersInput['end_date'] ?? null);

        $baseQuery = Sale::query()->where('seller_id', $sellerId);
        $filteredQuery = clone $baseQuery;

        if (! empty($filtersInput['status'])) {
            $filteredQuery->where('status', $filtersInput['status']);
        }

        if ($dateRange['start']) {
            $filteredQuery->whereDate('created_at', '>=', $dateRange['start']);
        }

        if ($dateRange['end']) {
            $filteredQuery->whereDate('created_at', '<=', $dateRange['end']);
        }

        $sales = (clone $filteredQuery)
            ->with(['company', 'customer.user', 'warehouse'])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'today_total' => (clone $baseQuery)->whereDate('created_at', now()->toDateString())->sum('total_amount'),
            'today_count' => (clone $baseQuery)->whereDate('created_at', now()->toDateString())->count(),
            'month_total' => (clone $baseQuery)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total_amount'),
            'pending_count' => (clone $baseQuery)->where('status', 'sin_entregar')->count(),
        ];

        $chart = $this->buildWeeklySeries($sellerId);

        return view('dashboard.vendedor.ventas-registro', [
            'sales' => $sales,
            'stats' => $stats,
            'filters' => [
                'status' => $filtersInput['status'] ?? null,
                'start_date' => $dateRange['start'],
                'end_date' => $dateRange['end'],
            ],
            'chart' => $chart,
            'statusLabels' => self::STATUS_LABELS,
            'paymentLabels' => self::PAYMENT_LABELS,
            'updateRoute' => 'dashboard.vendedor.sales.update',
            'reportRoute' => 'dashboard.vendedor.sales.report',
        ]);
    }

    public function vendorReport(Request $request)
    {
        $seller = $request->user();
        abort_if(! $seller, 403);

        $filtersInput = $request->validate([
            'status' => ['nullable', Rule::in(Sale::STATUSES)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $dateRange = $this->normalizeDateRange($filtersInput['start_date'] ?? null, $filtersInput['end_date'] ?? null);

        $query = Sale::with(['company', 'customer.user', 'warehouse', 'items.product'])
            ->where('seller_id', $seller?->id);

        if (! empty($filtersInput['status'])) {
            $query->where('status', $filtersInput['status']);
        }

        if ($dateRange['start']) {
            $query->whereDate('created_at', '>=', $dateRange['start']);
        }

        if ($dateRange['end']) {
            $query->whereDate('created_at', '<=', $dateRange['end']);
        }

        $sales = $query->orderByDesc('created_at')->get();

        $dailyBreakdown = $sales->groupBy(fn ($sale) => $sale->created_at->format('Y-m-d'))
            ->map(function ($group, $day) {
                return [
                    'date' => Carbon::parse($day)->format('d/m'),
                    'count' => $group->count(),
                    'total' => $group->sum('total_amount'),
                ];
            })
            ->values();

        $totals = [
            'count' => $sales->count(),
            'amount' => $sales->sum('total_amount'),
        ];

        return ReportService::download('reports.vendor-sales', [
            'title' => 'Ventas personales',
            'generatedAt' => now(),
            'seller' => $seller,
            'sales' => $sales,
            'filters' => [
                'status' => $filtersInput['status'] ?? null,
                'start_date' => $dateRange['start'],
                'end_date' => $dateRange['end'],
            ],
            'dailyBreakdown' => $dailyBreakdown,
            'chart' => $this->buildWeeklySeries($seller->id),
            'totals' => $totals,
            'statusLabels' => self::STATUS_LABELS,
            'paymentLabels' => self::PAYMENT_LABELS,
        ], 'ventas-vendedor-' . ($seller?->id ?? 'reporte') . '.pdf');
    }

    private function getLaPazWarehouse(): ?Warehouse
    {
        return Warehouse::where('code', 'LPZ')
            ->orWhere('city', 'La Paz')
            ->first();
    }

    private function normalizeDateRange(?string $start, ?string $end): array
    {
        $startDate = $start ? Carbon::parse($start)->toDateString() : null;
        $endDate = $end ? Carbon::parse($end)->toDateString() : null;

        if ($startDate && $endDate && $startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        if (! $startDate && ! $endDate) {
            $startDate = now()->subDays(30)->toDateString();
            $endDate = now()->toDateString();
        } elseif ($startDate && ! $endDate) {
            $endDate = now()->toDateString();
        } elseif (! $startDate && $endDate) {
            $startDate = Carbon::parse($endDate)->subDays(30)->toDateString();
        }

        return ['start' => $startDate, 'end' => $endDate];
    }

    private function buildWeeklySeries(?int $sellerId): array
    {
        if (! $sellerId) {
            return ['labels' => [], 'totals' => [], 'counts' => []];
        }

        $start = now()->subDays(6)->startOfDay();
        $end = now()->endOfDay();

        $raw = Sale::query()
            ->where('seller_id', $sellerId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as day, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $totals = [];
        $counts = [];

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $point = $raw->get($key);
            $labels[] = $cursor->format('d/m');
            $totals[] = $point->total ?? 0;
            $counts[] = $point->count ?? 0;
            $cursor->addDay();
        }

        return [
            'labels' => $labels,
            'totals' => $totals,
            'counts' => $counts,
        ];
    }

    private function saleAuditPayload(Sale $sale): array
    {
        return [
            'sale_type' => $sale->sale_type,
            'status' => $sale->status,
            'payment_method' => $sale->payment_method,
            'total_amount' => (float) $sale->total_amount,
            'warehouse' => $sale->warehouse?->name,
            'seller' => $sale->seller?->name,
            'customer' => $sale->company?->name ?? $sale->customer?->user?->name,
            'items' => $sale->items->map(fn (SaleItem $item) => [
                'product_id' => $item->product_id,
                'product' => $item->product?->name,
                'sku' => $item->product?->sku,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'catalog_public_price' => $item->product ? (float) $item->product->suggested_price_public : null,
                'catalog_institutional_price' => $item->product ? (float) $item->product->price_institutional : null,
                'subtotal' => (float) $item->subtotal,
            ])->values()->all(),
        ];
    }
}
