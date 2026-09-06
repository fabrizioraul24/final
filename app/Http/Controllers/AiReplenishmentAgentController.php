<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LogsAudit;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLot;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\TransferRequest;
use App\Models\Warehouse;
use App\Services\AiEvaluatorAgentService;
use App\Services\AiReplenishmentAgentService;
use App\Services\ReportService;
use App\Support\AdminReact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;

class AiReplenishmentAgentController extends Controller
{
    use LogsAudit;

    private const CACHE_KEY = 'admin_ai_replenishment_dataset_v2';
    private const LAST_RUN_CACHE_KEY = 'admin_ai_replenishment_last_run_at';
    private const STARTED_AT_CACHE_KEY = 'admin_ai_replenishment_started_at';

    public function index(Request $request, AiReplenishmentAgentService $service): View
    {
        $this->authorizeAgentAccess();

        $search = trim((string) $request->input('search', ''));
        $categoryId = $request->input('category_id');
        $agentMode = $request->input('agent') === 'evaluator' ? 'evaluator' : 'replenishment';
        $snapshot = $agentMode === 'evaluator' ? $this->emptySnapshot() : $this->cachedSnapshot($service);
        $health = $snapshot['health'];
        $payload = $snapshot['payload'];
        $forecasts = collect($snapshot['forecasts']);
        $alerts = $snapshot['alerts'];
        $alertProductCards = collect($snapshot['alertProductCards']);

        if ($search !== '') {
            $forecasts = $this->filterAgentProducts($forecasts, $search);
            $alertProductCards = $this->filterAgentProducts($alertProductCards, $search);
        }

        $pendingRequestsQuery = TransferRequest::with('product')
            ->where('created_by_agent', true)
            ->where('status', TransferRequest::STATUS_PENDING)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
        $recentRequestsQuery = TransferRequest::with(['product', 'transfer'])
            ->where('created_by_agent', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($search !== '') {
            $pendingRequestsQuery->whereHas('product', fn ($query) => $query->whereAnyLikeInsensitive(['name', 'sku', 'description'], $search));
            $recentRequestsQuery->whereHas('product', fn ($query) => $query->whereAnyLikeInsensitive(['name', 'sku', 'description'], $search));
        }

        if ($categoryId) {
            $forecasts = $forecasts->where('category_id', (int) $categoryId)->values();
            $alertProductCards = $alertProductCards->where('category_id', (int) $categoryId)->values();
            $pendingRequestsQuery->whereHas('product', fn ($query) => $query->where('category_id', $categoryId));
            $recentRequestsQuery->whereHas('product', fn ($query) => $query->where('category_id', $categoryId));
        }

        $pendingRequestsTotal = (clone $pendingRequestsQuery)->count();
        $recentRequestsTotal = (clone $recentRequestsQuery)->count();

        $forecastsPaginator = $this->paginateCollection($forecasts, 8, 'evaluaciones');
        $pendingRequests = $pendingRequestsQuery->paginate(6, ['*'], 'pendientes')->withQueryString();
        $recentRequests = $recentRequestsQuery->paginate(8, ['*'], 'historial')->withQueryString();

        return view('react-page', AdminReact::page('agentReplenishment', 'Agente de Reposicion | Pil Andina', 'Agente de Reposicion', 'agent', [
            'data' => [
                'agentMode' => $agentMode,
                'search' => $search,
                'categoryId' => $categoryId,
                'categories' => Category::orderBy('name')->get()->map(fn (Category $category) => ['id' => $category->id, 'name' => $category->name]),
                'agentOnline' => (bool) ($health['online'] ?? false),
                'lastRunAt' => $this->lastRunLabel($payload),
                'lastRunAtIso' => Cache::get(self::LAST_RUN_CACHE_KEY),
                'startedAtIso' => Cache::get(self::STARTED_AT_CACHE_KEY),
                'error' => $payload['error'] ?? null,
                'forecasts' => AdminReact::paginator($forecastsPaginator),
                'forecastsTotal' => $forecasts->count(),
                'alerts' => $alerts,
                'alertProductCards' => $alertProductCards->values()->all(),
                'pendingRequests' => AdminReact::paginator($pendingRequests->through(fn (TransferRequest $request) => $this->requestPayload($request))),
                'pendingRequestsTotal' => $pendingRequestsTotal,
                'recentRequests' => AdminReact::paginator($recentRequests->through(fn (TransferRequest $request) => $this->recentRequestPayload($request))),
                'recentRequestsTotal' => $recentRequestsTotal,
                'routes' => [
                    'index' => route('admin.agent.replenishment'),
                    'index_replenishment' => route('admin.agent.replenishment'),
                    'index_evaluator' => route('admin.agent.replenishment', ['agent' => 'evaluator']),
                    'index_insights' => route('admin.agent.replenishment.insights-page'),
                    'report' => route('admin.agent.replenishment.report', ['search' => $search, 'category_id' => $categoryId]),
                    'visual_report' => route('admin.agent.replenishment.visual-report'),
                    'insights' => route('admin.agent.replenishment.insights'),
                    'run' => route('admin.agent.replenishment.run'),
                    'status' => route('admin.agent.replenishment.status'),
                    'evaluator_real' => route('admin.agent.replenishment.evaluator.real'),
                ],
            ],
        ], 'adminAgentReplenishment'));
    }

    public function insightsPage(Request $request): View
    {
        $this->authorizeAgentAccess();

        return view('react-page', AdminReact::page('agentInsights', 'Graficos del Agente | Pil Andina', 'Graficos del Agente', 'agent', [
            'data' => [
                'agentMode' => 'insights',
                'insights' => $this->buildAgentInsights($request),
                'routes' => [
                    'index_replenishment' => route('admin.agent.replenishment'),
                    'index_evaluator' => route('admin.agent.replenishment', ['agent' => 'evaluator']),
                    'index_insights' => route('admin.agent.replenishment.insights-page'),
                    'visual_report' => route('admin.agent.replenishment.visual-report'),
                    'insights' => route('admin.agent.replenishment.insights'),
                ],
            ],
        ], 'adminAgentInsights'));
    }

    public function evaluatorReal(AiEvaluatorAgentService $service): JsonResponse
    {
        $this->authorizeAgentAccess();

        return response()->json([
            'data' => $service->evaluateReal(),
        ]);
    }

    public function insights(Request $request): JsonResponse
    {
        $this->authorizeAgentAccess();

        return response()->json([
            'data' => $this->buildAgentInsights($request),
        ]);
    }

    public function report(Request $request, AiReplenishmentAgentService $service)
    {
        $this->authorizeAgentAccess();

        $search = trim((string) $request->input('search', ''));
        $categoryId = $request->input('category_id');
        $snapshot = $this->cachedSnapshot($service);
        $health = $snapshot['health'];
        $payload = $snapshot['payload'];
        $forecasts = collect($snapshot['forecasts']);
        $alerts = $snapshot['alerts'];
        $alertProductCards = collect($snapshot['alertProductCards']);

        $pendingRequestsQuery = TransferRequest::with('product')
            ->where('created_by_agent', true)
            ->where('status', TransferRequest::STATUS_PENDING)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
        $recentRequestsQuery = TransferRequest::with(['product', 'transfer'])
            ->where('created_by_agent', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($search !== '') {
            $forecasts = $this->filterAgentProducts($forecasts, $search);
            $alertProductCards = $this->filterAgentProducts($alertProductCards, $search);
            $pendingRequestsQuery->whereHas('product', fn ($query) => $query->whereAnyLikeInsensitive(['name', 'sku', 'description'], $search));
            $recentRequestsQuery->whereHas('product', fn ($query) => $query->whereAnyLikeInsensitive(['name', 'sku', 'description'], $search));
        }

        if ($categoryId) {
            $forecasts = $forecasts->where('category_id', (int) $categoryId)->values();
            $alertProductCards = $alertProductCards->where('category_id', (int) $categoryId)->values();
            $pendingRequestsQuery->whereHas('product', fn ($query) => $query->where('category_id', $categoryId));
            $recentRequestsQuery->whereHas('product', fn ($query) => $query->where('category_id', $categoryId));
        }

        $categoryName = $categoryId ? Category::find($categoryId)?->name : null;

        return ReportService::download('reports.replenishment-agent', [
            'title' => 'Reporte del agente de reposicion',
            'generatedAt' => now(),
            'agentOnline' => (bool) ($health['online'] ?? false),
            'lastRunAt' => $payload['last_run_at'] ?? now(),
            'error' => $payload['error'] ?? null,
            'forecasts' => $forecasts,
            'alerts' => $alerts,
            'alertProductCards' => $alertProductCards,
            'pendingRequests' => $pendingRequestsQuery->get(),
            'recentRequests' => $recentRequestsQuery->limit(30)->get(),
            'filters' => [
                'search' => $search !== '' ? $search : 'Todos',
                'category' => $categoryName ?: 'Todas',
            ],
        ], 'reporte-agente-reposicion.pdf');
    }

    public function visualReport(Request $request)
    {
        $this->authorizeAgentAccess();

        $insights = $this->buildAgentInsights($request);

        return ReportService::download('reports.agent-visual-dashboard', [
            'title' => 'Dashboard comparativo del agente',
            'generatedAt' => now(),
            'insights' => $insights,
        ], 'dashboard-visual-agente.pdf');
    }

    public function runNow(Request $request, AiReplenishmentAgentService $service): RedirectResponse|JsonResponse
    {
        $this->authorizeAgentAccess();

        $timeout = (int) config('services.ai_agent.predict_timeout', 180);
        if (function_exists('set_time_limit')) {
            set_time_limit($timeout + 10);
        }

        $payload = $service->predict();
        if (! $payload['online']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'El agente no respondio: ' . ($payload['error'] ?? 'sin detalle'),
                    'error' => $payload['error'] ?? null,
                ], 502);
            }

            return back()->with('error', 'El agente no respondio: ' . ($payload['error'] ?? 'sin detalle'));
        }

        $result = $service->createPendingRequests($payload['transfer_requests'] ?? []);
        $health = $service->health();
        Cache::put(self::CACHE_KEY, $this->buildSnapshotFromPayload($health, $payload), now()->addMinutes(30));
        $now = now();
        Cache::put(self::LAST_RUN_CACHE_KEY, $now->toIso8601String());
        Cache::add(self::STARTED_AT_CACHE_KEY, $now->toIso8601String(), now()->addYear());

        $this->logAudit('ai_replenishment_agent', 'run_now', [], [
            'created' => count($result['created']),
            'skipped' => count($result['skipped']),
        ], 'Ejecucion manual del agente de reposicion');

        $message = 'Analisis ejecutado. Creadas: ' . count($result['created']) . '. Omitidas por duplicado/datos: ' . count($result['skipped']) . '.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'created' => count($result['created']),
                'skipped' => count($result['skipped']),
            ]);
        }

        return back()->with('status', $message);
    }

    public function status(AiReplenishmentAgentService $service): JsonResponse
    {
        $this->authorizeAgentAccess();

        $health = $service->health();

        return response()->json([
            'agentOnline' => (bool) ($health['online'] ?? false),
            'lastRunAtIso' => Cache::get(self::LAST_RUN_CACHE_KEY),
            'startedAtIso' => Cache::get(self::STARTED_AT_CACHE_KEY),
            'checkedAtIso' => now()->toIso8601String(),
        ]);
    }

    public function approveTransferRequest(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAgentAccess();

        $transferRequest = TransferRequest::with('product')->findOrFail($id);
        if ($transferRequest->status !== TransferRequest::STATUS_PENDING) {
            return back()->with('error', 'La solicitud ya fue procesada.');
        }

        DB::transaction(function () use ($transferRequest, $request) {
            $targetWarehouse = $this->targetWarehouse();
            $sourceWarehouse = $this->sourceWarehouseForProduct($transferRequest->product_id);
            if (! $targetWarehouse || ! $sourceWarehouse) {
                throw new \RuntimeException('Configura los almacenes SCZ, CBA y LPZ antes de aprobar traspasos.');
            }

            $transfer = Transfer::create([
                'from_warehouse_id' => $sourceWarehouse->id,
                'to_warehouse_id' => $targetWarehouse->id,
                'requested_by' => $transferRequest->approved_by ?: Auth::id(),
                'approved_by' => Auth::id(),
                'status' => Transfer::STATUS_PENDING,
                'expected_date' => Carbon::now()->addDay()->toDateString(),
                'notes' => $this->agentTransferNotes($transferRequest, $request->input('decision_reason')),
            ]);

            TransferItem::create([
                'transfer_id' => $transfer->id,
                'product_id' => $transferRequest->product_id,
                'requested_qty' => $transferRequest->requested_qty,
                'notes' => 'Producto sugerido por el agente inteligente.',
            ]);

            $old = $transferRequest->toArray();
        $transferRequest->update([
            'status' => TransferRequest::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'decision_reason' => $request->input('decision_reason'),
            'transfer_id' => $transfer->id,
        ]);
        Cache::forget(self::CACHE_KEY);

        $this->logAudit($transferRequest, 'approve', $old, $transferRequest->fresh()->toArray(), 'Solicitud del agente aprobada');
    });

        return back()->with('status', 'Solicitud aprobada y traspaso creado.');
    }

    public function rejectTransferRequest(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAgentAccess();

        $transferRequest = TransferRequest::findOrFail($id);
        if ($transferRequest->status !== TransferRequest::STATUS_PENDING) {
            return back()->with('error', 'La solicitud ya fue procesada.');
        }

        $old = $transferRequest->toArray();
        $transferRequest->update([
            'status' => TransferRequest::STATUS_REJECTED,
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'decision_reason' => $request->input('decision_reason'),
        ]);
        Cache::forget(self::CACHE_KEY);

        $this->logAudit($transferRequest, 'reject', $old, $transferRequest->fresh()->toArray(), 'Solicitud del agente rechazada');

        return back()->with('status', 'Solicitud rechazada.');
    }

    private function authorizeAgentAccess(): void
    {
        abort_unless(Auth::check(), 401);

        $role = Str::slug(optional(Auth::user()->role)->name ?? '');
        abort_unless(in_array($role, ['administrador', 'almacen', 'inventario', 'responsable-de-inventario'], true), 403);
    }

    private function agentTransferNotes(TransferRequest $transferRequest, ?string $decisionReason = null): string
    {
        $lines = [
            'Sugerencia de agente inteligente aprobada por el usuario.',
            'Solicitud del agente: #' . $transferRequest->id . ' creada el ' . optional($transferRequest->created_at)->format('d/m/Y H:i'),
            'Prioridad: ' . ($transferRequest->priority ?: 'Normal'),
            'Motivo del agente: ' . ($transferRequest->reason ?: 'Sin motivo registrado.'),
        ];

        if ($decisionReason) {
            $lines[] = 'Motivo de aprobacion: ' . $decisionReason;
        }

        return implode("\n", $lines);
    }

    private function sourceWarehouses(): Collection
    {
        return Warehouse::query()
            ->whereIn('code', ['SCZ', 'CBA'])
            ->orderBy('name')
            ->get();
    }

    private function buildAgentInsights(Request $request): array
    {
        $granularity = $request->input('granularity') === 'month' ? 'month' : 'week';
        $productId = $request->integer('product_id') ?: null;
        $periods = $this->agentPeriods($granularity);
        $keys = collect($periods)->pluck('key');

        $sales = $this->salesSeries($granularity, $productId);
        $transfers = $this->transferSeries($granularity, $productId);
        $evaluations = $this->evaluationSeries($granularity, $productId);

        $series = collect($periods)->map(function (array $period) use ($sales, $transfers, $evaluations) {
            $key = $period['key'];
            $salesPoint = $sales[$key] ?? ['qty' => 0, 'amount' => 0, 'orders' => 0];
            $transferPoint = $transfers[$key] ?? ['requested' => 0, 'received' => 0, 'transfers' => 0];
            $evaluationPoint = $evaluations[$key] ?? ['avg_wape' => null, 'changed_factors' => 0, 'good' => 0, 'regular' => 0, 'low' => 0];

            return array_merge($period, [
                'sales_qty' => (int) data_get($salesPoint, 'qty', 0),
                'sales_amount' => (float) data_get($salesPoint, 'amount', 0),
                'sales_orders' => (int) data_get($salesPoint, 'orders', 0),
                'transfer_requested' => (int) data_get($transferPoint, 'requested', 0),
                'transfer_received' => (int) data_get($transferPoint, 'received', 0),
                'transfer_count' => (int) data_get($transferPoint, 'transfers', 0),
                'avg_wape_percent' => data_get($evaluationPoint, 'avg_wape') === null ? null : round(((float) data_get($evaluationPoint, 'avg_wape')) * 100, 2),
                'changed_factors' => (int) data_get($evaluationPoint, 'changed_factors', 0),
                'good' => (int) data_get($evaluationPoint, 'good', 0),
                'regular' => (int) data_get($evaluationPoint, 'regular', 0),
                'low' => (int) data_get($evaluationPoint, 'low', 0),
            ]);
        })->values();

        $activeSeries = $series
            ->filter(fn (array $item) => $item['sales_qty'] > 0 || $item['transfer_requested'] > 0 || $item['avg_wape_percent'] !== null)
            ->values();
        $current = $activeSeries->last() ?? $series->last();
        $previous = $activeSeries->count() > 1 ? $activeSeries[$activeSeries->count() - 2] : null;

        return [
            'granularity' => $granularity,
            'product_id' => $productId,
            'product_name' => $productId ? Product::find($productId)?->name : 'General',
            'products' => $this->agentProductOptions(),
            'series' => $series->all(),
            'current' => $current,
            'previous' => $previous,
            'deltas' => $this->agentDeltas($current, $previous),
            'top_products' => $this->topAgentProducts($keys->all(), $granularity),
        ];
    }

    private function salesSeries(string $granularity, ?int $productId): Collection
    {
        return DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($productId, fn ($query) => $query->where('sale_items.product_id', $productId))
            ->whereBetween('sales.created_at', ['2025-01-01 00:00:00', '2025-12-31 23:59:59'])
            ->selectRaw($this->periodSql('sales.created_at', $granularity).' as period_key')
            ->selectRaw('SUM(sale_items.quantity) as qty')
            ->selectRaw('SUM(sale_items.subtotal) as amount')
            ->selectRaw('COUNT(DISTINCT sales.id) as orders')
            ->groupBy('period_key')
            ->get()
            ->keyBy('period_key');
    }

    private function transferSeries(string $granularity, ?int $productId): Collection
    {
        return DB::table('transfer_items')
            ->join('transfers', 'transfers.id', '=', 'transfer_items.transfer_id')
            ->when($productId, fn ($query) => $query->where('transfer_items.product_id', $productId))
            ->whereBetween('transfers.created_at', ['2025-01-01 00:00:00', '2025-12-31 23:59:59'])
            ->selectRaw($this->periodSql('transfers.created_at', $granularity).' as period_key')
            ->selectRaw('SUM(transfer_items.requested_qty) as requested')
            ->selectRaw('SUM(COALESCE(transfer_items.received_qty, 0)) as received')
            ->selectRaw('COUNT(DISTINCT transfers.id) as transfers')
            ->groupBy('period_key')
            ->get()
            ->keyBy('period_key');
    }

    private function evaluationSeries(string $granularity, ?int $productId): Collection
    {
        if (! Schema::hasTable('ai_forecast_snapshots')) {
            return collect();
        }

        return DB::table('ai_forecast_snapshots')
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->whereYear('forecast_start', 2025)
            ->whereYear('forecast_end', 2025)
            ->selectRaw($this->periodSql('forecast_start', $granularity).' as period_key')
            ->selectRaw('AVG(wape) as avg_wape')
            ->selectRaw('SUM(CASE WHEN ABS(COALESCE(factor_after, learning_factor_used) - learning_factor_used) >= 0.0001 THEN 1 ELSE 0 END) as changed_factors')
            ->selectRaw("SUM(CASE WHEN level = 'BUENO' THEN 1 ELSE 0 END) as good")
            ->selectRaw("SUM(CASE WHEN level = 'REGULAR' THEN 1 ELSE 0 END) as regular")
            ->selectRaw("SUM(CASE WHEN level = 'BAJO' THEN 1 ELSE 0 END) as low")
            ->groupBy('period_key')
            ->get()
            ->keyBy('period_key');
    }

    private function agentPeriods(string $granularity): array
    {
        if ($granularity === 'month') {
            return collect(range(1, 12))->map(fn (int $month) => [
                'key' => sprintf('2025-%02d', $month),
                'label' => Carbon::create(2025, $month, 1)->locale('es')->isoFormat('MMM'),
                'start' => Carbon::create(2025, $month, 1)->toDateString(),
                'end' => Carbon::create(2025, $month, 1)->endOfMonth()->toDateString(),
            ])->all();
        }

        $periods = [];
        $cursor = Carbon::create(2025, 1, 1)->startOfWeek();
        if ((int) $cursor->year < 2025) {
            $cursor->addWeek();
        }
        $end = Carbon::create(2025, 12, 31)->startOfWeek();
        if ($end->copy()->addDays(6)->year > 2025) {
            $end->subWeek();
        }

        while ($cursor->lte($end)) {
            $periods[] = [
                'key' => $cursor->format('o-W'),
                'label' => 'S'.$cursor->isoWeek(),
                'start' => $cursor->toDateString(),
                'end' => $cursor->copy()->addDays(6)->toDateString(),
            ];
            $cursor->addWeek();
        }

        return $periods;
    }

    private function periodSql(string $column, string $granularity): string
    {
        return $granularity === 'month'
            ? "DATE_FORMAT($column, '%Y-%m')"
            : "DATE_FORMAT(DATE_SUB(DATE($column), INTERVAL WEEKDAY($column) DAY), '%x-%v')";
    }

    private function agentDeltas(?array $current, ?array $previous): array
    {
        $delta = function (string $key, bool $lowerIsBetter = false) use ($current, $previous) {
            $now = (float) ($current[$key] ?? 0);
            $before = (float) ($previous[$key] ?? 0);
            $change = $now - $before;
            $percent = $before != 0 ? ($change / $before) * 100 : ($now > 0 ? 100 : 0);

            return [
                'current' => $now,
                'previous' => $before,
                'change' => $change,
                'percent' => round($percent, 1),
                'direction' => abs($change) < 0.0001 ? 'flat' : ($change > 0 ? 'up' : 'down'),
                'good' => abs($change) < 0.0001 || ($lowerIsBetter ? $change <= 0 : $change >= 0),
            ];
        };

        return [
            'sales_qty' => $delta('sales_qty'),
            'sales_amount' => $delta('sales_amount'),
            'transfer_requested' => $delta('transfer_requested'),
            'avg_wape_percent' => $delta('avg_wape_percent', true),
            'changed_factors' => $delta('changed_factors', true),
        ];
    }

    private function agentProductOptions(): array
    {
        return Product::query()
            ->whereIn('id', DB::table('sale_items')->distinct()->pluck('product_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'sku'])
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
            ])
            ->all();
    }

    private function topAgentProducts(array $periodKeys, string $granularity): array
    {
        $latestKey = end($periodKeys) ?: null;
        if (! $latestKey) {
            return [];
        }

        return DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', ['2025-01-01 00:00:00', '2025-12-31 23:59:59'])
            ->whereRaw($this->periodSql('sales.created_at', $granularity).' = ?', [$latestKey])
            ->selectRaw('products.id, products.name, products.sku, SUM(sale_items.quantity) as qty, SUM(sale_items.subtotal) as amount')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('qty')
            ->limit(6)
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'name' => $row->name,
                'sku' => $row->sku,
                'qty' => (int) $row->qty,
                'amount' => (float) $row->amount,
            ])
            ->all();
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

    private function sourceWarehouseForProduct(?int $productId): ?Warehouse
    {
        $sources = $this->sourceWarehouses();
        if ($sources->isEmpty()) {
            return null;
        }

        if (! $productId) {
            return $sources->first();
        }

        return $sources
            ->sortByDesc(fn (Warehouse $warehouse) => ProductLot::available($productId, $warehouse->id))
            ->first();
    }

    private function enrichForecasts(Collection $forecasts): Collection
    {
        $productIds = $forecasts->pluck('product_id')->filter()->unique()->values();
        $products = Product::with('category')->whereIn('id', $productIds)->get()->keyBy('id');
        $stockByProduct = ProductLot::query()
            ->whereIn('product_id', $productIds)
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');

        $inTransitByProduct = DB::table('transfer_items')
            ->join('transfers', 'transfers.id', '=', 'transfer_items.transfer_id')
            ->whereIn('transfer_items.product_id', $productIds)
            ->whereIn('transfers.status', [Transfer::STATUS_PENDING, Transfer::STATUS_IN_TRANSIT])
            ->selectRaw('transfer_items.product_id, SUM(transfer_items.requested_qty) as qty')
            ->groupBy('transfer_items.product_id')
            ->pluck('qty', 'product_id');

        return $forecasts->map(function (array $item) use ($products, $stockByProduct, $inTransitByProduct) {
            $productId = (int) ($item['product_id'] ?? 0);
            $product = $products->get($productId);
            $forecast = (float) ($item['forecast_7_days'] ?? $item['forecast'] ?? $item['demand'] ?? 0);
            $stock = (int) ($stockByProduct[$productId] ?? 0);
            $inTransit = (int) ($inTransitByProduct[$productId] ?? 0);
            $safety = (int) ($item['safety_threshold'] ?? $product?->min_quantity ?? 0);
            $result = $stock + $inTransit - $forecast;

            return [
                'product_id' => $productId,
                'name' => $item['name'] ?? $product?->name ?? 'Producto ' . $productId,
                'sku' => $product?->sku ?? 'N/D',
                'category_id' => $product?->category_id,
                'category' => $product?->category?->name ?? 'Sin categoria',
                'forecast_7_days' => $forecast,
                'stock' => $stock,
                'in_transit' => $inTransit,
                'result' => $result,
                'safety_threshold' => $safety,
                'decision' => $this->humanDecisionLabel($item['decision'] ?? ($result < $safety ? 'Reponer' : 'Mantener')),
                'priority' => $item['priority'] ?? ($result < 0 ? 'Urgente' : null),
                'raw' => $item,
            ];
        })->values();
    }

    private function filterAgentProducts(Collection $items, string $search): Collection
    {
        $needle = Str::lower($search);

        return $items
            ->filter(function (array $item) use ($needle) {
                return Str::contains(Str::lower((string) ($item['name'] ?? '')), $needle)
                    || Str::contains(Str::lower((string) ($item['sku'] ?? '')), $needle)
                    || Str::contains(Str::lower((string) ($item['category'] ?? '')), $needle);
            })
            ->values();
    }

    private function buildOperationalAlertCards(array $alerts, Collection $forecasts): Collection
    {
        $today = Carbon::today();
        $twoMonths = $today->copy()->addMonths(2);
        $fiveMonths = $today->copy()->addMonths(5);
        $targetWarehouse = $this->targetWarehouse();
        $severityRank = ['normal' => 0, 'warning' => 1, 'critical' => 2, 'expired' => 3];

        $alertIds = collect($alerts)
            ->flatten(1)
            ->filter(fn ($alert) => is_array($alert))
            ->pluck('product_id')
            ->filter()
            ->map(fn ($id) => (int) $id);

        $alertNames = collect($alerts)
            ->flatten(1)
            ->filter(fn ($alert) => is_array($alert))
            ->map(fn ($alert) => $alert['product_name'] ?? $alert['name'] ?? null)
            ->filter()
            ->unique()
            ->values();

        $productsByName = $alertNames->isNotEmpty()
            ? Product::whereIn('name', $alertNames)->pluck('id', 'name')
            : collect();

        $nearLotProductIds = ProductLot::query()
            ->when($targetWarehouse, fn ($query) => $query->where('warehouse_id', $targetWarehouse->id))
            ->where('quantity', '>', 0)
            ->whereDate('expires_at', '<=', $fiveMonths->toDateString())
            ->pluck('product_id');

        $productIds = $alertIds
            ->merge($productsByName->values())
            ->merge($nearLotProductIds)
            ->filter()
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return collect();
        }

        $products = Product::with('category')->whereIn('id', $productIds)->get()->keyBy('id');
        $lotsByProduct = ProductLot::with('warehouse')
            ->whereIn('product_id', $productIds)
            ->when($targetWarehouse, fn ($query) => $query->where('warehouse_id', $targetWarehouse->id))
            ->where('quantity', '>', 0)
            ->orderBy('expires_at')
            ->get()
            ->groupBy('product_id');

        $forecastsById = $forecasts->keyBy('product_id');
        $forecastsByName = $forecasts->keyBy('name');

        $cards = $products->map(function (Product $product) use ($alerts, $lotsByProduct, $forecastsById, $forecastsByName, $today, $twoMonths, $fiveMonths, $severityRank) {
            $problems = [];
            $severity = 'normal';
            $forecast = $forecastsById->get($product->id) ?? $forecastsByName->get($product->name);
            $metrics = [];

            $lowStockAlert = collect($alerts['low_stock'] ?? [])->first(function ($alert) use ($product) {
                return is_array($alert)
                    && (((int) ($alert['product_id'] ?? 0) === $product->id)
                        || (($alert['product_name'] ?? $alert['name'] ?? null) === $product->name));
            });

            if ($lowStockAlert || ($forecast && ($forecast['result'] ?? 0) < ($forecast['safety_threshold'] ?? 0))) {
                $stock = (int) ($lowStockAlert['stock_actual'] ?? $lowStockAlert['stock'] ?? $forecast['stock'] ?? 0);
                $demand = (int) ($lowStockAlert['forecast'] ?? $lowStockAlert['forecast_7_days'] ?? $forecast['forecast_7_days'] ?? 0);
                $available = $lowStockAlert['available_after_demand'] ?? ($forecast['result'] ?? null);
                $minimum = (int) ($lowStockAlert['safety_threshold'] ?? $forecast['safety_threshold'] ?? $product->min_quantity ?? 0);
                $missing = $available !== null && $available < 0
                    ? abs((int) $available)
                    : max($demand - $stock, 0);

                $problems[] = [
                    'label' => 'Stock bajo',
                    'message' => $missing > 0
                        ? 'Faltan '.$missing.' unidades para cubrir la demanda prevista.'
                        : 'El producto puede bajar del stock minimo.',
                    'severity' => 'critical',
                    'meta' => [
                        'Stock' => $stock.' uds',
                        'Demanda prevista' => $demand.' uds',
                        'Faltante' => $missing.' uds',
                        'Stock minimo' => $minimum.' uds',
                    ],
                ];
                $metrics = array_merge($metrics, [
                    'Stock' => $stock.' uds',
                    'Demanda prevista' => $demand.' uds',
                    'Faltante' => $missing.' uds',
                    'Stock minimo' => $minimum.' uds',
                ]);
                $severity = 'critical';
            }

            $postPeakAlert = collect($alerts['post_peak_drop'] ?? [])->first(function ($alert) use ($product) {
                return is_array($alert)
                    && (((int) ($alert['product_id'] ?? 0) === $product->id)
                        || (($alert['product_name'] ?? $alert['name'] ?? null) === $product->name));
            });

            if ($postPeakAlert) {
                $problems[] = [
                    'label' => 'Demanda despues de pico',
                    'message' => $postPeakAlert['message'] ?? $postPeakAlert['reason'] ?? 'La demanda viene bajando despues de un pico. Revisar antes de mover stock.',
                    'severity' => 'warning',
                    'meta' => [],
                ];
                if (($severityRank['warning'] ?? 0) > ($severityRank[$severity] ?? 0)) {
                    $severity = 'warning';
                }
            }

            $lots = ($lotsByProduct->get($product->id) ?? collect())->map(function (ProductLot $lot) use ($today, $twoMonths, $fiveMonths, &$problems, &$severity, $severityRank) {
                $expiresAt = $lot->expires_at ? $lot->expires_at->copy()->startOfDay() : null;
                $days = $expiresAt ? $today->diffInDays($expiresAt, false) : null;
                $status = 'normal';
                $label = 'Normal';
                $message = 'Tiene 5 meses o mas de vida util.';

                if ($expiresAt && $expiresAt->lt($today)) {
                    $status = 'expired';
                    $label = 'Vencido';
                    $message = 'Vencio hace '.abs($days).' dias. No debe venderse.';
                } elseif ($expiresAt && $expiresAt->lte($twoMonths)) {
                    $status = 'critical';
                    $label = 'Peligro de vencer';
                    $message = $days === 0 ? 'Vence hoy.' : 'Vence en '.$days.' dias.';
                } elseif ($expiresAt && $expiresAt->lt($fiveMonths)) {
                    $status = 'warning';
                    $label = 'Lote por vencer';
                    $message = 'Vence en '.$days.' dias.';
                }

                if (($severityRank[$status] ?? 0) > ($severityRank[$severity] ?? 0)) {
                    $severity = $status;
                }

                if ($status !== 'normal') {
                    $problems[] = [
                        'label' => $label,
                        'message' => $message.' Lote '.$lot->lote_code.' con '.$lot->quantity.' uds.',
                        'severity' => $status,
                        'meta' => [
                            'Codigo' => $lot->lote_code ?: 'Sin codigo',
                            'Cantidad' => $lot->quantity.' uds',
                            'Vence' => optional($lot->expires_at)->format('d/m/Y') ?? 'Sin fecha',
                        ],
                    ];
                }

                return [
                    'id' => $lot->id,
                    'code' => $lot->lote_code ?: 'Sin codigo',
                    'quantity' => (int) $lot->quantity,
                    'expires_at' => optional($lot->expires_at)->format('d/m/Y') ?? 'Sin fecha',
                    'status' => $status,
                    'label' => $label,
                    'message' => $message,
                ];
            })->values();

            if (empty($problems)) {
                return null;
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category_id' => $product->category_id,
                'category' => $product->category?->name ?? 'Sin categoria',
                'image' => $product->getImageUrl(),
                'severity' => $severity,
                'severity_label' => match ($severity) {
                    'expired' => 'Vencido',
                    'critical' => 'Critico',
                    'warning' => 'Medio',
                    default => 'Normal',
                },
                'metrics' => $metrics,
                'problems' => $problems,
                'lots' => $lots,
            ];
        })->filter()->values();

        return $cards->sortByDesc(fn ($card) => $severityRank[$card['severity']] ?? 0)->values();
    }

    private function paginateCollection(Collection $items, int $perPage, string $pageName): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
                'query' => request()->query(),
            ]
        );
    }

    private function humanDecisionLabel(?string $decision): string
    {
        $key = Str::upper(Str::slug((string) $decision, '_'));

        return match ($key) {
            'CREATE_TRANSFER_REQUEST' => 'Crear solicitud de traspaso',
            'TRANSFER_REQUEST' => 'Solicitar traspaso',
            'URGENT_REPLENISHMENT' => 'Reposicion urgente',
            'REPLENISH', 'REPONER' => 'Reponer producto',
            'KEEP', 'MAINTAIN' => 'Mantener stock',
            default => $decision ?: 'Sin decision',
        };
    }

    private function cachedSnapshot(AiReplenishmentAgentService $service): array
    {
        $snapshot = Cache::get(self::CACHE_KEY);
        if (is_array($snapshot)) {
            return $snapshot;
        }

        $empty = $this->emptySnapshot();
        $empty['health'] = $service->health();

        return $empty;
    }

    private function buildSnapshotFromPayload(array $health, array $payload): array
    {
        $forecasts = $this->enrichForecasts(collect($payload['forecasts'] ?? []))->values();
        $alerts = $payload['alerts'] ?? [];
        $alertProductCards = $this->buildOperationalAlertCards($alerts, $forecasts)->values();

        return [
            'health' => $health,
            'payload' => $payload,
            'forecasts' => $forecasts->all(),
            'alerts' => $alerts,
            'alertProductCards' => $alertProductCards->all(),
        ];
    }

    private function emptySnapshot(): array
    {
        return [
            'health' => [
                'online' => false,
                'status' => null,
                'data' => [],
                'error' => null,
            ],
            'payload' => [
                'online' => false,
                'last_run_at' => now(),
                'forecasts' => [],
                'transfer_requests' => [],
                'alerts' => [
                    'low_stock' => [],
                    'expiring' => [],
                    'post_peak_drop' => [],
                ],
                'raw' => [],
                'error' => null,
            ],
            'forecasts' => [],
            'alerts' => [
                'low_stock' => [],
                'expiring' => [],
                'post_peak_drop' => [],
            ],
            'alertProductCards' => [],
        ];
    }

    private function lastRunLabel(array $payload): string
    {
        $lastRun = Cache::get(self::LAST_RUN_CACHE_KEY, $payload['last_run_at'] ?? null);

        return $lastRun ? Carbon::parse($lastRun)->format('d/m/Y H:i') : 'Pendiente de primera revisión';
    }

    private function requestPayload(TransferRequest $request): array
    {
        $parsedReason = null;
        if ($request->reason && preg_match('/Stock\s+(-?\d+)\s+\+\s+traspasos\s+7d\s+(-?\d+)\s+-\s+demanda\s+proyectada\s+7d\s+(-?\d+)\s+=\s+(-?\d+);\s+cae\s+bajo\s+umbral\s+(-?\d+)/i', $request->reason, $matches)) {
            $parsedReason = [
                'stock' => (int) $matches[1],
                'transfers' => (int) $matches[2],
                'demand' => (int) $matches[3],
                'result' => (int) $matches[4],
                'threshold' => (int) $matches[5],
            ];
        }

        return [
            'id' => $request->id,
            'product_name' => $request->product?->name ?? ('Producto '.$request->product_id),
            'requested_qty' => (int) $request->requested_qty,
            'priority' => $request->priority ?? 'Normal',
            'reason' => $request->reason,
            'parsedReason' => $parsedReason,
            'status' => $request->status,
            'created_at_formatted' => optional($request->created_at)->format('d/m/Y H:i'),
            'approve_url' => route('admin.agent.replenishment.approve', $request),
            'reject_url' => route('admin.agent.replenishment.reject', $request),
        ];
    }

    private function recentRequestPayload(TransferRequest $request): array
    {
        return [
            'id' => $request->id,
            'created_at_formatted' => optional($request->created_at)->format('d/m/Y H:i'),
            'product_name' => $request->product?->name ?? ('Producto '.$request->product_id),
            'requested_qty' => (int) $request->requested_qty,
            'status' => $request->status,
            'decision_label' => $request->approved_by ? 'Aprobado por usuario' : ($request->rejected_by ? 'Rechazado por usuario' : 'Pendiente de revision'),
            'transfer_label' => $request->transfer ? 'Traspaso #'.$request->transfer->id : 'N/D',
        ];
    }
}
