<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductLot;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Transfer;
use App\Models\User;
use App\Support\AdminReact;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $data = $this->getDashboardData();
        $layout = AdminReact::layout('Panel de control', 'dashboard');

        return view('react-page', [
            'page' => 'adminDashboard',
            'title' => 'Panel de control Administrador | Pil Andina',
            'stylesheets' => [asset('dash/assets/index-Dllbp2XJ.css'), asset('landing/dashboard.css'), asset('dash/final-dashboard.css')],
            'props' => [
                'layout' => $layout,
                'kpis' => [
                    [
                        'label' => 'Ventas del dia',
                        'value' => 'Bs ' . number_format($data['kpis']['sales_today'], 2),
                        'icon' => 'ri-line-chart-line',
                        'chip' => 'Actualizado hoy',
                        'chipClass' => 'chip-success',
                    ],
                    [
                        'label' => 'Clientes registrados',
                        'value' => (string) $data['kpis']['customers'],
                        'icon' => 'ri-community-line',
                        'chip' => 'Empresas + tiendas',
                        'chipClass' => 'chip-muted',
                    ],
                    [
                        'label' => 'Productos activos',
                        'value' => (string) $data['kpis']['products_active'],
                        'icon' => 'ri-shopping-bag-3-line',
                        'chip' => 'Catalogo disponible',
                        'chipClass' => 'chip-muted',
                    ],
                    [
                        'label' => 'Traspasos abiertos',
                        'value' => (string) $data['kpis']['transfers_active'],
                        'icon' => 'ri-shuffle-line',
                        'chip' => 'Pendientes o en transito',
                    ],
                ],
                'salesSeries' => $data['salesSeries'],
                'categoryMix' => $data['categoryMix'],
                'transferStatuses' => $data['transferStatuses'],
                'roleMix' => $data['roleMix'],
                'summaryCards' => [
                    [
                        'label' => 'Total semanal',
                        'value' => 'Bs ' . number_format($data['weeklySalesTotal'], 2),
                        'detail' => $data['weeklySalesCount'] . ' ventas registradas',
                    ],
                    [
                        'label' => 'Ticket promedio',
                        'value' => 'Bs ' . number_format($data['averageTicket'], 2),
                        'detail' => 'Promedio por venta',
                    ],
                    [
                        'label' => 'Mejor dia',
                        'value' => $data['bestSalesIndex'] !== false ? $data['salesSeries']['labels'][$data['bestSalesIndex']] : 'Sin datos',
                        'detail' => 'Pico: Bs ' . number_format($data['bestSalesValue'], 2),
                    ],
                    [
                        'label' => 'Vs ayer',
                        'value' => ($data['salesDelta'] >= 0 ? '+' : '') . number_format($data['salesDelta'], 1) . '%',
                        'detail' => 'Hoy: Bs ' . number_format($data['kpis']['sales_today'], 2),
                    ],
                ],
                'insights' => $data['insights'],
                'recentActivity' => $data['recentActivity'],
                'categoryBreakdown' => $data['categoryBreakdown'],
                'roleBreakdown' => $data['roleBreakdown'],
                
                // New dashboard features
                'monthlySales' => $data['monthlySales'],
                'monthlyTarget' => $data['monthlyTarget'],
                'monthlyTargetProgress' => $data['monthlyTargetProgress'],
                'topSellers' => $data['topSellers'],
                'criticalStocks' => $data['criticalStocks'],

                'csrfToken' => csrf_token(),
                'logoutAction' => route('logout'),
            ],
        ]);
    }

    public function liveStats(): \Illuminate\Http\JsonResponse
    {
        $data = $this->getDashboardData();
        return response()->json($data);
    }

    private function getDashboardData(): array
    {
        $today = now()->startOfDay();
        $yesterday = $today->copy()->subDay();
        $weekStart = $today->copy()->subDays(6);

        $salesToday = (float) Sale::whereDate('created_at', $today)->sum('total_amount');
        $salesTodayCount = Sale::whereDate('created_at', $today)->count();
        $salesYesterday = (float) Sale::whereDate('created_at', $yesterday)->sum('total_amount');
        $activeUsersTotal = User::count();
        $inactiveUsersTotal = User::onlyTrashed()->count();

        $kpis = [
            'sales_today' => $salesToday,
            'sales_today_count' => $salesTodayCount,
            'customers' => Company::count(),
            'products_active' => Product::where('is_active', true)->count(),
            'transfers_active' => Transfer::where('status', '!=', Transfer::STATUS_RECEIVED)->count(),
            'users_active' => $activeUsersTotal,
            'users_inactive' => $inactiveUsersTotal,
        ];

        $dates = collect(range(6, 0))->map(fn ($i) => $today->copy()->subDays($i));
        $rawSales = Sale::select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total_amount) as total'))
            ->whereDate('created_at', '>=', $weekStart)
            ->groupBy('day')
            ->pluck('total', 'day');
        $salesSeries = [
            'labels' => $dates->map->format('d/m'),
            'data' => $dates->map(fn ($d) => (float) ($rawSales[$d->toDateString()] ?? 0)),
        ];

        $categoryMixData = Product::select('category_id', DB::raw('COUNT(*) as total'))
            ->with('category')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(6)
            ->get();
        $categoryMix = [
            'labels' => $categoryMixData->map(fn ($row) => $row->category->name ?? 'Sin categoria'),
            'data' => $categoryMixData->pluck('total')->map(fn ($v) => (int) $v),
        ];

        $transferStatusData = Transfer::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
        $transferStatuses = [
            'labels' => $transferStatusData->keys(),
            'data' => $transferStatusData->values()->map(fn ($v) => (int) $v),
        ];

        $usersByRole = User::select('role_id', DB::raw('COUNT(*) as total'))
            ->with('role')
            ->groupBy('role_id')
            ->get();
        $roleMix = [
            'labels' => $usersByRole->map(fn ($row) => $row->role->name ?? 'Sin rol'),
            'data' => $usersByRole->pluck('total')->map(fn ($v) => (int) $v),
        ];

        $weeklySalesCount = Sale::whereDate('created_at', '>=', $weekStart)->count();
        $weeklySalesTotal = (float) collect($salesSeries['data'])->sum();
        $averageTicket = $weeklySalesCount > 0 ? $weeklySalesTotal / $weeklySalesCount : 0;
        $bestSalesValue = (float) collect($salesSeries['data'])->max();
        $bestSalesIndex = collect($salesSeries['data'])->search($bestSalesValue);
        $salesDelta = $salesYesterday > 0
            ? (($salesToday - $salesYesterday) / $salesYesterday) * 100
            : ($salesToday > 0 ? 100 : 0);

        $transferTotal = (int) $transferStatusData->sum();
        $receivedTransfers = (int) ($transferStatusData[Transfer::STATUS_RECEIVED] ?? 0);
        $receivedShare = $transferTotal > 0 ? round(($receivedTransfers / $transferTotal) * 100, 1) : 0;

        $topCategoryTotal = (int) ($categoryMix['data'][0] ?? 0);
        $topCategoryShare = $kpis['products_active'] > 0 ? round(($topCategoryTotal / $kpis['products_active']) * 100, 1) : 0;
        $topRoleTotal = (int) ($roleMix['data'][0] ?? 0);
        $usersTotal = (int) collect($roleMix['data'])->sum();
        $topRoleShare = $usersTotal > 0 ? round(($topRoleTotal / $usersTotal) * 100, 1) : 0;

        $recentSales = Sale::with(['company:id,name', 'customer:id,user_id', 'customer.user:id,name', 'warehouse:id,name'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Sale $sale) => [
                'type' => 'sale',
                'title' => $sale->company->name ?? $sale->customer?->user?->name ?? 'Venta sin cliente',
                'meta' => 'Bs ' . number_format((float) $sale->total_amount, 2) . ' - ' . ($sale->warehouse->name ?? 'Sin almacen'),
                'time' => optional($sale->created_at)->format('d/m H:i'),
                'icon' => 'ri-shopping-cart-2-line',
                'sort' => optional($sale->created_at)->timestamp ?? 0,
            ]);

        $recentTransfers = Transfer::with(['fromWarehouse:id,name', 'toWarehouse:id,name'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Transfer $transfer) => [
                'type' => 'transfer',
                'title' => ($transfer->fromWarehouse->name ?? 'Origen') . ' -> ' . ($transfer->toWarehouse->name ?? 'Destino'),
                'meta' => ucfirst(str_replace('_', ' ', $transfer->status)),
                'time' => optional($transfer->created_at)->format('d/m H:i'),
                'icon' => 'ri-shuffle-line',
                'sort' => optional($transfer->created_at)->timestamp ?? 0,
            ]);

        $recentActivity = $recentSales
            ->concat($recentTransfers)
            ->sortByDesc('sort')
            ->map(function (array $item) {
                unset($item['sort']);
                return $item;
            })
            ->take(6)
            ->values();

        $categoryBreakdown = collect($categoryMix['labels'])
            ->zip($categoryMix['data'])
            ->map(fn ($item) => [
                'label' => $item[0],
                'value' => (int) $item[1],
                'share' => $kpis['products_active'] > 0 ? round((((int) $item[1]) / $kpis['products_active']) * 100, 1) : 0,
            ])
            ->take(4)
            ->values();

        $roleBreakdown = collect($roleMix['labels'])
            ->zip($roleMix['data'])
            ->map(fn ($item) => [
                'label' => $item[0],
                'value' => (int) $item[1],
                'share' => $usersTotal > 0 ? round((((int) $item[1]) / $usersTotal) * 100, 1) : 0,
            ])
            ->take(4)
            ->values();

        // Target progress (150,000 Bs)
        $monthlyTarget = 150000.0;
        $monthlySales = (float) Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        $monthlyTargetProgress = min(100.0, round(($monthlySales / $monthlyTarget) * 100, 1));

        // Top Sellers
        $topSellers = Sale::select('seller_id', DB::raw('SUM(total_amount) as total'))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy('seller_id')
            ->with('seller:id,name')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->seller->name ?? 'Vendedor sin nombre',
                'total' => (float) $row->total,
            ])->values();

        $topProductsData = SaleItem::select('product_id', DB::raw('SUM(quantity) as quantity'), DB::raw('SUM(subtotal) as total'))
            ->whereHas('sale')
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderByDesc('quantity')
            ->take(8)
            ->get();
        $maxProductQuantity = max(1, (int) ($topProductsData->max('quantity') ?? 1));
        $topProducts = $topProductsData->map(fn ($row) => [
            'name' => $row->product->name ?? 'Producto sin nombre',
            'quantity' => (int) $row->quantity,
            'total' => (float) $row->total,
            'popularity' => (int) round(((int) $row->quantity / $maxProductQuantity) * 100),
        ])->values();

        $regionSales = Sale::select('delivery_city', DB::raw('COUNT(*) as orders'), DB::raw('SUM(total_amount) as sales'))
            ->whereNotNull('delivery_city')
            ->where('delivery_city', '!=', '')
            ->groupBy('delivery_city')
            ->orderByDesc('sales')
            ->take(3)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->delivery_city,
                'orders' => (int) $row->orders,
                'sales' => 'Bs ' . number_format((float) $row->sales, 0),
            ])->values();

        // Critical stock alerts
        $criticalStocksQuery = ProductLot::whereColumn('quantity', '<', 'safety_threshold');
        $criticalStocksTotal = (clone $criticalStocksQuery)->count();
        $expiredLotsTotal = ProductLot::query()
            ->where('quantity', '>', 0)
            ->whereDate('expires_at', '<', $today)
            ->count();

        $criticalStocks = $criticalStocksQuery
            ->with(['product:id,name,sku', 'warehouse:id,name'])
            ->limit(6)
            ->get()
            ->map(fn ($lot) => [
                'product' => $lot->product->name ?? 'Producto desconocido',
                'sku' => $lot->product->sku ?? '',
                'warehouse' => $lot->warehouse->name ?? 'Sin almacén',
                'quantity' => (int) $lot->quantity,
                'threshold' => (int) $lot->safety_threshold,
            ])->values();

        $insights = collect([
            $criticalStocksTotal > 0 ? [
                'label' => 'Stock para reponer',
                'value' => $criticalStocksTotal . ' producto' . ($criticalStocksTotal === 1 ? '' : 's'),
                'detail' => 'Cantidad disponible por debajo del nivel de seguridad.',
            ] : [
                'label' => 'Stock operativo',
                'value' => 'Estable',
                'detail' => 'No hay productos por debajo del nivel de seguridad.',
            ],
            $expiredLotsTotal > 0 ? [
                'label' => 'Lotes vencidos',
                'value' => $expiredLotsTotal . ' lote' . ($expiredLotsTotal === 1 ? '' : 's'),
                'detail' => 'Requieren revision antes de una nueva salida.',
            ] : [
                'label' => 'Vencimientos',
                'value' => 'Al dia',
                'detail' => 'No hay lotes vencidos con stock disponible.',
            ],
            $kpis['transfers_active'] > 0 ? [
                'label' => 'Traspasos en curso',
                'value' => (string) $kpis['transfers_active'],
                'detail' => 'Pendientes o en transito entre almacenes.',
            ] : [
                'label' => 'Traspasos',
                'value' => 'Sin pendientes',
                'detail' => 'No hay movimientos internos abiertos.',
            ],
            [
                'label' => 'Ventas frente a ayer',
                'value' => ($salesDelta >= 0 ? '+' : '') . number_format($salesDelta, 1) . '%',
                'detail' => $salesDelta >= 0 ? 'Variacion favorable del volumen diario.' : 'Variacion por revisar frente al dia anterior.',
            ],
        ])->values();

        return [
            'kpis' => $kpis,
            'salesSeries' => $salesSeries,
            'categoryMix' => $categoryMix,
            'transferStatuses' => $transferStatuses,
            'roleMix' => $roleMix,
            'weeklySalesTotal' => $weeklySalesTotal,
            'weeklySalesCount' => $weeklySalesCount,
            'averageTicket' => $averageTicket,
            'bestSalesValue' => $bestSalesValue,
            'bestSalesIndex' => $bestSalesIndex,
            'salesDelta' => $salesDelta,
            'receivedShare' => $receivedShare,
            'receivedTransfers' => $receivedTransfers,
            'transferTotal' => $transferTotal,
            'topCategoryShare' => $topCategoryShare,
            'topRoleShare' => $topRoleShare,
            'recentActivity' => $recentActivity,
            'categoryBreakdown' => $categoryBreakdown,
            'roleBreakdown' => $roleBreakdown,
            'monthlySales' => $monthlySales,
            'monthlyTarget' => $monthlyTarget,
            'monthlyTargetProgress' => $monthlyTargetProgress,
            'topSellers' => $topSellers,
            'topProducts' => $topProducts,
            'regionSales' => $regionSales,
            'criticalStocks' => $criticalStocks,
            'insights' => $insights,
        ];
    }
}
