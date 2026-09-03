<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LogsAudit;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductLot;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\TransferRequest;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ReportService;
use App\Support\AdminReact;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransferController extends Controller
{
    use LogsAudit;
    public function index(Request $request): View
    {
        $this->ensureApprovedAgentRequestsHaveTransfer();
        $targetWarehouse = $this->targetWarehouse();
        $statusFilter = $request->input('status');

        $transfers = Transfer::with([
            'fromWarehouse',
            'toWarehouse',
            'requestedByUser',
            'approvedByUser',
            'items.product',
            'agentTransferRequest.product',
        ])
            ->when($statusFilter, fn ($query) => $query->where('status', $statusFilter))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Transfer::count(),
            'pending' => Transfer::where('status', Transfer::STATUS_PENDING)->count(),
            'in_transit' => Transfer::where('status', Transfer::STATUS_IN_TRANSIT)->count(),
            'received' => Transfer::where('status', Transfer::STATUS_RECEIVED)->count(),
        ];

        return view('react-page', AdminReact::page('transfers', 'Traspasos internos | Pil Andina', 'Traspasos de productos', 'transfers', [
            'data' => [
                'sourceWarehouses' => $this->sourceWarehouses()->map(fn (Warehouse $warehouse) => ['id' => $warehouse->id, 'name' => $warehouse->name, 'code' => $warehouse->code]),
                'targetWarehouse' => $targetWarehouse ? ['id' => $targetWarehouse->id, 'name' => $targetWarehouse->name, 'code' => $targetWarehouse->code] : null,
                'transfers' => AdminReact::paginator($transfers->through(fn (Transfer $transfer) => $this->transferPayload($transfer))),
                'stats' => $stats,
                'statuses' => Transfer::STATUSES,
                'filters' => [
                    'status' => $statusFilter,
                ],
                'routes' => [
                    'index' => route('dashboard.transfers'),
                    'store' => route('dashboard.transfers.store'),
                    'lookup' => route('dashboard.transfers.lookup'),
                    'report' => route('dashboard.transfers.report', ['status' => $statusFilter]),
                ],
            ],
        ], 'adminTransfers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $targetWarehouse = $this->targetWarehouse();
        $sourceWarehouses = $this->sourceWarehouses();

        if (! $targetWarehouse || $sourceWarehouses->isEmpty()) {
            return back()
                ->withErrors(['from_warehouse_id' => 'Configura los almacenes SCZ, CBA y LPZ antes de registrar traspasos.'])
                ->withInput();
        }

        $data = $request->validate([
            'from_warehouse_id' => ['required', Rule::in($sourceWarehouses->pluck('id')->all())],
            'expected_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(Transfer::STATUSES)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.requested_qty' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string'],
        ], [
            'items.required' => 'Debes agregar al menos un producto al traspaso.',
            'items.*.product_id.required' => 'Completa el código del producto.',
            'from_warehouse_id.required' => 'Selecciona Santa Cruz o Cochabamba como origen.',
            'from_warehouse_id.in' => 'El origen solo puede ser Santa Cruz o Cochabamba.',
        ]);

        $transfer = DB::transaction(function () use ($data, $request, $targetWarehouse) {
            $transfer = Transfer::create([
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $targetWarehouse->id,
                'requested_by' => $request->user()?->id ?? auth()->id(),
                'status' => $data['status'] ?? Transfer::STATUS_PENDING,
                'expected_date' => $data['expected_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'requested_qty' => $item['requested_qty'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $transfer;
        });

        $this->logAudit($transfer, 'create', [], [
            'from_warehouse_id' => $transfer->from_warehouse_id,
            'to_warehouse_id' => $transfer->to_warehouse_id,
            'status' => $transfer->status,
            'expected_date' => $transfer->expected_date,
            'items_count' => $transfer->items()->count(),
        ], 'Creacion de traspaso');

        return redirect()
            ->route('dashboard.transfers')
            ->with('status', 'Traspaso registrado correctamente.');
    }

    public function report(Request $request)
    {
        $this->ensureApprovedAgentRequestsHaveTransfer();
        $statusFilter = $request->input('status');

        $transfers = Transfer::with([
            'fromWarehouse',
            'toWarehouse',
            'requestedByUser',
            'approvedByUser',
            'items.product',
            'agentTransferRequest.product',
        ])
            ->when($statusFilter, fn ($query) => $query->where('status', $statusFilter))
            ->orderByDesc('id')
            ->get();

        return ReportService::download('reports.transfers', [
            'title' => 'Reporte de traspasos internos',
            'generatedAt' => now(),
            'transfers' => $transfers,
        ], 'reporte-traspasos.pdf');
    }

    public function reportSingle(Transfer $transfer)
    {
        $transfer->load([
            'fromWarehouse',
            'toWarehouse',
            'requestedByUser',
            'approvedByUser',
            'items.product',
            'agentTransferRequest.product',
        ]);

        $transfers = collect([$transfer]);

        return ReportService::download('reports.transfers', [
            'title' => 'Traspaso #' . $transfer->id,
            'generatedAt' => now(),
            'transfers' => $transfers,
        ], "traspaso-{$transfer->id}.pdf");
    }

    public function lookup(Request $request): JsonResponse
    {
        $sku = $request->query('sku');

        if (! $sku) {
            return response()->json(['message' => 'Debes proporcionar un código de producto.'], 422);
        }

        $product = Product::where('sku', $sku)->first();

        if (! $product) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        $warehouseId = $request->query('warehouse_id');
        $availableQuantity = $warehouseId
            ? ProductLot::available($product->id, $warehouseId)
            : null;

        return response()->json([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'description' => $product->description,
            'available_quantity' => $availableQuantity,
        ]);
    }

    private function ensureApprovedAgentRequestsHaveTransfer(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn('transfer_requests', 'transfer_id')) {
            return;
        }

        $targetWarehouse = $this->targetWarehouse();
        if (! $targetWarehouse) {
            return;
        }

        TransferRequest::with(['product'])
            ->where('created_by_agent', true)
            ->where('status', TransferRequest::STATUS_APPROVED)
            ->whereNull('transfer_id')
            ->orderBy('id')
            ->get()
            ->each(function (TransferRequest $request) use ($targetWarehouse) {
                DB::transaction(function () use ($request, $targetWarehouse) {
                    $requestedBy = $request->approved_by ?: auth()->id() ?: \App\Models\User::query()->value('id');
                    $sourceWarehouse = $this->sourceWarehouseForProduct($request->product_id);

                    if (! $requestedBy || ! $sourceWarehouse) {
                        return;
                    }

                    $transfer = Transfer::create([
                        'from_warehouse_id' => $sourceWarehouse->id,
                        'to_warehouse_id' => $targetWarehouse->id,
                        'requested_by' => $requestedBy,
                        'approved_by' => $request->approved_by,
                        'status' => Transfer::STATUS_PENDING,
                        'expected_date' => now()->addDay()->toDateString(),
                        'notes' => $this->agentTransferNotes($request),
                        'created_at' => $request->approved_at ?? $request->updated_at ?? now(),
                        'updated_at' => now(),
                    ]);

                    TransferItem::create([
                        'transfer_id' => $transfer->id,
                        'product_id' => $request->product_id,
                        'requested_qty' => $request->requested_qty,
                        'notes' => 'Producto sugerido por el agente inteligente.',
                    ]);

                    $request->update(['transfer_id' => $transfer->id]);
                });
            });
    }

    private function agentTransferNotes(TransferRequest $request): string
    {
        $lines = [
            'Sugerencia de agente inteligente aprobada por el usuario.',
            'Solicitud del agente: #' . $request->id . ' creada el ' . optional($request->created_at)->format('d/m/Y H:i'),
            'Prioridad: ' . ($request->priority ?: 'Normal'),
            'Motivo del agente: ' . ($request->reason ?: 'Sin motivo registrado.'),
        ];

        if ($request->decision_reason) {
            $lines[] = 'Motivo de aprobacion: ' . $request->decision_reason;
        }

        return implode("\n", $lines);
    }

    private function sourceWarehouses(): \Illuminate\Support\Collection
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

    private function transferPayload(Transfer $transfer): array
    {
        $agentRequest = $transfer->agentTransferRequest;

        return [
            'id' => $transfer->id,
            'fromWarehouse' => $transfer->fromWarehouse ? ['name' => $transfer->fromWarehouse->name] : null,
            'toWarehouse' => $transfer->toWarehouse ? ['name' => $transfer->toWarehouse->name] : null,
            'status' => $transfer->status,
            'expected_date_formatted' => optional($transfer->expected_date)->format('d/m/Y') ?? 'Sin fecha',
            'created_at_formatted' => optional($transfer->created_at)->format('d/m/Y H:i'),
            'requested_by_label' => $agentRequest ? 'Agente inteligente' : ($transfer->requestedByUser->name ?? 'Usuario Pil'),
            'approved_by_label' => $transfer->approvedByUser?->name ?? 'Usuario',
            'notes' => $transfer->notes ?: 'Sin notas registradas.',
            'items_count' => $transfer->items->count(),
            'items' => $transfer->items->map(fn (TransferItem $item) => [
                'product_name' => $item->product?->name ?? ('Producto '.$item->product_id),
                'sku' => $item->product?->sku ?? 'N/D',
                'requested_qty' => (int) $item->requested_qty,
                'received_qty' => (int) ($item->received_qty ?? 0),
                'damaged_qty' => (int) ($item->damaged_qty ?? 0),
                'notes' => $item->notes ?? $item->receiving_note ?? 'Sin comentarios',
            ])->values(),
            'agentRequest' => $agentRequest ? [
                'created_at_formatted' => optional($agentRequest->created_at)->format('d/m/Y H:i'),
                'approved_at_formatted' => optional($agentRequest->approved_at)->format('d/m/Y H:i'),
                'priority' => $agentRequest->priority,
                'reason' => $agentRequest->reason,
            ] : null,
            'report_url' => route('dashboard.transfers.report.single', $transfer),
        ];
    }
}
