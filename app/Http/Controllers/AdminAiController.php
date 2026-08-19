<?php

namespace App\Http\Controllers;

use App\Models\ProductLot;
use App\Models\SaleItem;
use App\Services\AiAgentService;
use App\Services\ReportService;
use App\Support\AdminReact;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminAiController extends Controller
{
    private const CACHE_KEY = 'admin_ai_overview_dataset_v2';

    public function index(Request $request, AiAgentService $service): View
    {
        return view('react-page', AdminReact::page('agentOverview', 'Agente Inteligente | Pil Andina', 'Agente Inteligente', 'agent', [
            'data' => [
                'routes' => [
                    'report' => route('dashboard.agent.report'),
                    'data' => route('dashboard.agent.data'),
                ],
                'initialData' => null,
            ],
        ], 'adminAgentOverview'));
    }

    public function data(AiAgentService $service): JsonResponse
    {
        return response()->json([
            'data' => $this->buildOverviewPayload($service),
        ]);
    }

    public function report(AiAgentService $service)
    {
        $data = $this->cachedDataset($service);
        return ReportService::download('reports.agent', [
            'title' => 'Informe Agente Inteligente',
            'generatedAt' => now(),
            'data' => $data,
            'charts' => [
                'forecast' => collect($data['forecast'] ?? [])->sortByDesc('forecast')->take(5),
                'restock' => collect($data['restock'] ?? [])->sortByDesc('suggested_qty')->take(5),
            ],
        ], 'agente-inteligente.pdf');
    }

    public function detail(int $productId, AiAgentService $service): JsonResponse
    {
        $data = $this->cachedDataset($service);
        $forecast = collect($data['forecast'] ?? [])->first(fn (array $item) => (int) ($item['product_id'] ?? 0) === $productId);

        if (! $forecast) {
            return response()->json(['message' => 'No se encontro detalle para este producto.'], 404);
        }

        $salesSeries = $this->buildSalesSeries([$productId]);
        $stockTotals = $this->getStockTotals([$productId]);
        $expiringLots = $this->buildExpiringLots([$productId]);
        $restockMap = $this->mapRestockToProducts($data);
        $restockItem = $restockMap['byId'][$productId] ?? ($restockMap['byName'][strtolower($forecast['name'] ?? '')] ?? []);
        $series = $salesSeries[$productId] ?? ['weekly' => ['labels' => [], 'data' => [], 'recent_total' => 0], 'monthly' => ['labels' => [], 'data' => [], 'recent_total' => 0]];

        return response()->json([
            'data' => [
                'product_id' => $productId,
                'name' => $forecast['name'],
                'forecast' => $forecast['forecast'],
                'trend' => $forecast['trend'] ?? 'sin datos',
                'stock' => $stockTotals[$productId] ?? 0,
                'weekly' => $series['weekly'],
                'monthly' => $series['monthly'],
                'history' => $forecast['history'] ?? [],
                'capacity_flag' => !empty($forecast['capacity_flag']),
                'capacity_note' => $forecast['capacity_note'] ?? '',
                'max_quantity' => $forecast['max_quantity'] ?? '',
                'min_quantity' => $forecast['min_quantity'] ?? '',
                'expiring_lots' => $expiringLots[$productId]['lots'] ?? [],
                'restock' => $restockItem,
            ],
        ]);
    }

    private function normalizeAiData(array $data): array
    {
        $data['alerts']['expiring'] = collect($data['alerts']['expiring'] ?? [])
            ->sortBy('expires_in_days')
            ->values()
            ->all();

        $data['alerts']['low_stock'] = collect($data['alerts']['low_stock'] ?? [])
            ->sortBy('stock')
            ->values()
            ->all();

        $data['restock'] = collect($data['restock'] ?? [])
            ->sortByDesc('suggested_qty')
            ->values()
            ->all();

        return $data;
    }

    private function buildOverviewPayload(AiAgentService $service): array
    {
        $data = $this->cachedDataset($service);
        $productIds = collect($data['forecast'] ?? [])->pluck('product_id')->filter()->unique()->values();
        $salesTotals = $this->buildSalesTotals($productIds);
        $stockTotals = $this->getStockTotals($productIds);

        $stats = [
            'restock' => count($data['restock'] ?? []),
            'alerts_low' => count($data['alerts']['low_stock'] ?? []),
            'alerts_expiring' => count($data['alerts']['expiring'] ?? []),
            'capacity' => count($data['capacity_alerts'] ?? []),
        ];

        $forecastTop = collect($data['forecast'] ?? [])->sortByDesc('forecast')->take(5);
        $restockTop = collect($data['restock'] ?? [])->sortByDesc('suggested_qty')->take(5);

        return [
            'raw' => $data,
            'stats' => $stats,
            'charts' => [
                'forecast' => [
                    'labels' => $forecastTop->pluck('name')->values(),
                    'data' => $forecastTop->pluck('forecast')->values(),
                ],
                'restock' => [
                    'labels' => $restockTop->pluck('name')->values(),
                    'data' => $restockTop->pluck('suggested_qty')->values(),
                ],
            ],
            'forecastItems' => collect($data['forecast'] ?? [])->map(function (array $item) use ($salesTotals, $stockTotals) {
                $productId = $item['product_id'] ?? 0;
                $totals = $salesTotals[$productId] ?? ['weekly' => 0, 'monthly' => 0];

                return [
                    'product_id' => $productId,
                    'name' => $item['name'],
                    'forecast' => $item['forecast'],
                    'trend' => $item['trend'] ?? 'sin datos',
                    'stock' => $stockTotals[$productId] ?? 0,
                    'weekly_recent_total' => $totals['weekly'],
                    'monthly_recent_total' => $totals['monthly'],
                    'history' => $item['history'] ?? [],
                    'capacity_flag' => !empty($item['capacity_flag']),
                    'detail_url' => route('dashboard.agent.detail', ['product' => $productId]),
                ];
            })->values(),
        ];
    }

    private function cachedDataset(AiAgentService $service): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinute(), function () use ($service) {
            return $this->normalizeAiData($service->generate());
        });
    }

    private function buildSalesTotals($productIds): array
    {
        $ids = collect($productIds)->filter()->unique();
        if ($ids->isEmpty()) {
            return [];
        }

        $now = Carbon::now();
        $weeklyStart = $now->copy()->startOfWeek()->subWeeks(5);
        $monthlyStart = $now->copy()->startOfMonth()->subMonths(5);

        $weeklyTotals = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereIn('sale_items.product_id', $ids)
            ->whereBetween('sales.created_at', [$weeklyStart, $now])
            ->selectRaw('sale_items.product_id, SUM(sale_items.quantity) as qty')
            ->groupBy('sale_items.product_id')
            ->pluck('qty', 'product_id');

        $monthlyTotals = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereIn('sale_items.product_id', $ids)
            ->whereBetween('sales.created_at', [$monthlyStart, $now])
            ->selectRaw('sale_items.product_id, SUM(sale_items.quantity) as qty')
            ->groupBy('sale_items.product_id')
            ->pluck('qty', 'product_id');

        return $ids->mapWithKeys(fn ($productId) => [
            $productId => [
                'weekly' => (int) ($weeklyTotals[$productId] ?? 0),
                'monthly' => (int) ($monthlyTotals[$productId] ?? 0),
            ],
        ])->toArray();
    }

    private function buildSalesSeries($productIds): array
    {
        $ids = collect($productIds)->filter()->unique();
        if ($ids->isEmpty()) {
            return [];
        }

        $now = Carbon::now();
        $weeklyStart = $now->copy()->startOfWeek()->subWeeks(5);
        $monthlyStart = $now->copy()->startOfMonth()->subMonths(5);

        $weeklyRaw = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereIn('sale_items.product_id', $ids)
            ->whereBetween('sales.created_at', [$weeklyStart, $now])
            ->selectRaw('sale_items.product_id, YEARWEEK(sales.created_at, 1) as week_key, SUM(sale_items.quantity) as qty')
            ->groupBy('sale_items.product_id', 'week_key')
            ->get()
            ->groupBy('product_id');

        $monthlyRaw = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereIn('sale_items.product_id', $ids)
            ->whereBetween('sales.created_at', [$monthlyStart, $now])
            ->selectRaw('sale_items.product_id, DATE_FORMAT(sales.created_at, "%Y-%m") as month_key, SUM(sale_items.quantity) as qty')
            ->groupBy('sale_items.product_id', 'month_key')
            ->get()
            ->groupBy('product_id');

        $series = [];

        foreach ($ids as $productId) {
            $weekCursor = $weeklyStart->copy();
            $weeklyLabels = [];
            $weeklyData = [];
            $weeklyMap = ($weeklyRaw->get($productId) ?? collect())->keyBy(fn ($row) => (string) $row->week_key);

            for ($i = 0; $i < 6; $i++) {
                $week = $weekCursor->copy()->addWeeks($i);
                $weekKey = $week->format('oW');
                $weeklyLabels[] = 'S' . $week->isoWeek;
                $weeklyData[] = (int) ($weeklyMap[$weekKey]->qty ?? 0);
            }

            $monthCursor = $monthlyStart->copy();
            $monthlyLabels = [];
            $monthlyData = [];
            $monthlyMap = ($monthlyRaw->get($productId) ?? collect())->keyBy('month_key');

            for ($i = 0; $i < 6; $i++) {
                $month = $monthCursor->copy()->addMonths($i);
                $monthKey = $month->format('Y-m');
                $monthlyLabels[] = $month->format('M');
                $monthlyData[] = (int) ($monthlyMap[$monthKey]->qty ?? 0);
            }

            $series[$productId] = [
                'weekly' => [
                    'labels' => $weeklyLabels,
                    'data' => $weeklyData,
                    'recent_total' => array_sum($weeklyData),
                ],
                'monthly' => [
                    'labels' => $monthlyLabels,
                    'data' => $monthlyData,
                    'recent_total' => array_sum($monthlyData),
                ],
            ];
        }

        return $series;
    }

    private function getStockTotals($productIds): array
    {
        $ids = collect($productIds)->filter()->unique();
        if ($ids->isEmpty()) {
            return [];
        }

        return ProductLot::query()
            ->whereIn('product_id', $ids)
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->get()
            ->pluck('qty', 'product_id')
            ->map(fn ($qty) => (int) $qty)
            ->toArray();
    }

    private function mapRestockToProducts(array $data): array
    {
        $forecastByName = collect($data['forecast'] ?? [])
            ->keyBy(fn ($item) => strtolower($item['name'] ?? ''));

        $mapped = [];
        $byName = [];

        foreach ($data['restock'] ?? [] as $item) {
            if (!empty($item['name'])) {
                $byName[strtolower($item['name'])] = $item;
            }

            $productId = $item['product_id'] ?? null;
            if (! $productId && ! empty($item['name'])) {
                $match = $forecastByName->get(strtolower($item['name']));
                $productId = $match['product_id'] ?? null;
            }

            if ($productId) {
                $mapped[$productId] = $item;
            }
        }

        return ['byId' => $mapped, 'byName' => $byName];
    }

    private function buildExpiringLots($productIds = null): array
    {
        $today = Carbon::today();
        $limit = $today->copy()->addDays(60);

        $lots = ProductLot::with(['product', 'warehouse'])
            ->when(collect($productIds)->filter()->isNotEmpty(), fn ($query) => $query->whereIn('product_id', collect($productIds)->filter()->values()))
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', $limit)
            ->orderBy('expires_at')
            ->get();

        return $lots->groupBy('product_id')->map(function ($group) use ($today) {
            $product = $group->first()->product;
            return [
                'product_name' => $product?->name ?? 'Producto',
                'product_id' => $product?->id,
                'lots' => $group->map(function (ProductLot $lot) use ($today) {
                    return [
                        'code' => $lot->lote_code ?? 'Sin codigo',
                        'expires_in_days' => $today->diffInDays($lot->expires_at, false),
                        'expires_at' => optional($lot->expires_at)->format('d/m/Y'),
                        'quantity' => $lot->quantity,
                        'warehouse' => $lot->warehouse?->name ?? null,
                    ];
                })->values(),
            ];
        })->toArray();
    }
}
