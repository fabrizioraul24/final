<?php

namespace App\Http\Controllers;

use App\Models\ProductLot;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlmacenTransferController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $targetWarehouse = $this->targetWarehouse();
        $baseQuery = Transfer::query()
            ->when($targetWarehouse, fn ($query) => $query->where('to_warehouse_id', $targetWarehouse->id));

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', Transfer::STATUS_PENDING)->count(),
            'in_transit' => (clone $baseQuery)->where('status', Transfer::STATUS_IN_TRANSIT)->count(),
            'received' => (clone $baseQuery)->where('status', Transfer::STATUS_RECEIVED)->count(),
        ];

        $transfers = Transfer::with([
            'fromWarehouse',
            'toWarehouse',
            'requestedByUser',
            'approvedByUser',
            'agentTransferRequest.product',
            'items.product',
            'items.generatedLot',
        ])
            ->when($targetWarehouse, fn ($query) => $query->where('to_warehouse_id', $targetWarehouse->id))
            ->when($status, fn ($query, $value) => $query->where('status', $value))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('dashboard.almacen-traspasos', [
            'transfers' => $transfers,
            'statuses' => Transfer::STATUSES,
            'filters' => ['status' => $status],
            'stats' => $stats,
            'targetWarehouse' => $targetWarehouse,
        ]);
    }

    public function show(Transfer $transfer): View
    {
        $this->ensureTargetTransfer($transfer);

        $transfer->load([
            'fromWarehouse',
            'toWarehouse',
            'requestedByUser',
            'approvedByUser',
            'agentTransferRequest.product',
            'items.product',
            'items.generatedLot',
        ]);

        return view('dashboard.almacen-traspasos-detalle', [
            'transfer' => $transfer,
            'statuses' => Transfer::STATUSES,
        ]);
    }

    public function updateItem(Request $request, TransferItem $item): RedirectResponse
    {
        $data = $request->validate([
            'received_qty' => ['required', 'integer', 'min:0'],
            'damaged_qty' => ['nullable', 'integer', 'min:0'],
            'lot_code' => ['nullable', 'string', 'max:120'],
            'receiving_expires_at' => ['nullable', 'date'],
            'receiving_note' => ['nullable', 'string'],
        ]);

        $transfer = $item->transfer()->first();
        $this->ensureTargetTransfer($transfer);

        try {
            DB::transaction(function () use ($item, $data, $transfer, $request) {
                $damagedQty = $data['damaged_qty'] ?? 0;
                $receivedQty = $data['received_qty'];
                $goodQty = max($receivedQty - $damagedQty, 0);
                $prevGoodQty = max(($item->received_qty ?? 0) - ($item->damaged_qty ?? 0), 0);
                $delta = $goodQty - $prevGoodQty;
                $lotCode = $data['lot_code'] ?: $this->automaticLotCode($transfer?->id ?? 0, $item->id);

                $item->update([
                    'received_qty' => $receivedQty,
                    'damaged_qty' => $damagedQty,
                    'lot_code' => $lotCode,
                    'receiving_expires_at' => $data['receiving_expires_at'] ?? null,
                    'receiving_note' => $data['receiving_note'] ?? null,
                ]);

                if ($delta !== 0 && $transfer && $transfer->to_warehouse_id && $item->product_id) {
                    if ($delta > 0) {
                        $expiresAt = $data['receiving_expires_at'] ?? now()->addMonths(6);
                        ProductLot::addStock(
                            $item->product_id,
                            $transfer->to_warehouse_id,
                            $delta,
                            $lotCode,
                            $expiresAt,
                            'traspaso',
                            $request->user()?->id,
                            'Recepcion traspaso #' . $transfer->id . ' autorizada por bodega'
                        );
                    } else {
                        ProductLot::consumeFefo(
                            $item->product_id,
                            $transfer->to_warehouse_id,
                            abs($delta),
                            'ajuste_traspaso',
                            $request->user()?->id,
                            'Ajuste traspaso #' . $transfer->id
                        );
                    }
                }
            });
        } catch (\RuntimeException $e) {
            return back()
                ->withErrors(['received_qty' => $e->getMessage()])
                ->with('modal_error', $e->getMessage())
                ->withInput();
        }

        return back()->with('status', 'Detalle de traspaso actualizado.');
    }

    public function updateStatus(Request $request, Transfer $transfer): RedirectResponse
    {
        $this->ensureTargetTransfer($transfer);

        $data = $request->validate([
            'status' => ['required', Rule::in(Transfer::STATUSES)],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () use ($transfer, $data, $request) {
                $transfer->update([
                    'status' => $data['status'],
                    'notes' => $data['notes'] ?? $transfer->notes,
                    'received_by' => $data['status'] === Transfer::STATUS_RECEIVED ? $request->user()?->id : $transfer->received_by,
                    'received_date' => $data['status'] === Transfer::STATUS_RECEIVED ? now() : $transfer->received_date,
                ]);

                if ($data['status'] === Transfer::STATUS_RECEIVED) {
                    $this->receiveTransferItemsAutomatically($transfer->fresh(['items.generatedLot']), $request);
                }
            });
        } catch (\RuntimeException $e) {
            return back()
                ->withErrors(['status' => $e->getMessage()])
                ->with('modal_error', $e->getMessage())
                ->withInput();
        }

        return back()->with('status', 'Estado del traspaso actualizado.');
    }

    private function receiveTransferItemsAutomatically(Transfer $transfer, Request $request): void
    {
        foreach ($transfer->items as $item) {
            $damagedQty = (int) ($item->damaged_qty ?? 0);
            $hasGeneratedLot = $item->generatedLot || ($item->lot_code && ProductLot::query()
                ->where('product_id', $item->product_id)
                ->where('warehouse_id', $transfer->to_warehouse_id)
                ->where('lote_code', $item->lot_code)
                ->exists());

            if ($hasGeneratedLot && ! is_null($item->received_qty)) {
                continue;
            }

            $receivedQty = (int) ($item->received_qty ?: $item->requested_qty);
            $goodQty = max($receivedQty - $damagedQty, 0);
            $lotCode = $item->lot_code ?: $this->automaticLotCode($transfer->id, $item->id);
            $expiresAt = $item->receiving_expires_at ?? now()->addMonths(6);
            $note = $item->receiving_note ?: 'Recepcion automatica al marcar el traspaso como recibido.';

            $item->update([
                'received_qty' => $receivedQty,
                'damaged_qty' => $damagedQty,
                'lot_code' => $lotCode,
                'receiving_expires_at' => $expiresAt,
                'receiving_note' => $note,
            ]);

            if ($goodQty <= 0 || ! $transfer->to_warehouse_id || ! $item->product_id) {
                continue;
            }

            if (! $hasGeneratedLot) {
                ProductLot::addStock(
                    $item->product_id,
                    $transfer->to_warehouse_id,
                    $goodQty,
                    $lotCode,
                    $expiresAt,
                    'traspaso',
                    $request->user()?->id,
                    'Recepcion automatica traspaso #' . $transfer->id . ' autorizada por bodega'
                );
            }
        }
    }

    private function automaticLotCode(int $transferId, int $itemId): string
    {
        return 'TR-' . $transferId . '-' . $itemId . '-' . now()->format('Ymd');
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

    private function ensureTargetTransfer(?Transfer $transfer): void
    {
        $targetWarehouse = $this->targetWarehouse();

        abort_unless(
            $transfer && (! $targetWarehouse || (int) $transfer->to_warehouse_id === (int) $targetWarehouse->id),
            404
        );
    }
}
