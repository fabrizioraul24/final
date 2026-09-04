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
        $snapshot = $this->cachedSnapshot($service);
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
                    'report' => route('admin.agent.replenishment.report', ['search' => $search, 'category_id' => $categoryId]),
                    'run' => route('admin.agent.replenishment.run'),
                    'status' => route('admin.agent.replenishment.status'),
                ],
            ],
        ], 'adminAgentReplenishment'));
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

    public function runNow(AiReplenishmentAgentService $service): RedirectResponse
    {
        $this->authorizeAgentAccess();

        $payload = $service->predict();
        if (! $payload['online']) {
            return back()->with('error', 'El agente no respondio: ' . ($payload['error'] ?? 'sin detalle'));
        }

        $result = $service->createPendingRequests($payload['transfer_requests'] ?? []);
        Cache::forget(self::CACHE_KEY);
        $now = now();
        Cache::put(self::LAST_RUN_CACHE_KEY, $now->toIso8601String());
        Cache::add(self::STARTED_AT_CACHE_KEY, $now->toIso8601String(), now()->addYear());

        $this->logAudit('ai_replenishment_agent', 'run_now', [], [
            'created' => count($result['created']),
            'skipped' => count($result['skipped']),
        ], 'Ejecucion manual del agente de reposicion');

        return back()->with('status', 'Analisis ejecutado. Creadas: ' . count($result['created']) . '. Omitidas por duplicado/datos: ' . count($result['skipped']) . '.');
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
        return Cache::remember(self::CACHE_KEY, now()->addMinute(), function () use ($service) {
            $health = $service->health();
            $payload = $service->predict();
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
        });
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
