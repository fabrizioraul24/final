<?php

namespace App\Http\Controllers;

use App\Models\ProductLot;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlmacenSaleController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $baseQuery = Sale::query();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'sin_entregar')->count(),
            'delivered' => (clone $baseQuery)->where('status', 'entregado')->count(),
            'today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
        ];

        $sales = Sale::with(['company', 'customer.user', 'items.product', 'warehouse'])
            ->when($status, fn ($query, $value) => $query->where('status', $value))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('dashboard.almacen-recepciones', [
            'sales' => $sales,
            'statuses' => Sale::STATUSES,
            'filters' => ['status' => $status],
            'stats' => $stats,
        ]);
    }

    public function show(Sale $sale): View
    {
        $sale->load(['company', 'customer.user', 'seller', 'items.product', 'warehouse']);

        return view('dashboard.almacen-recepciones-detalle', [
            'sale' => $sale,
            'statuses' => Sale::STATUSES,
            'suggestions' => $this->suggestLotsForSale($sale),
        ]);
    }

    public function updateStatus(Request $request, Sale $sale): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Sale::STATUSES)],
        ]);

        $sale->update([
            'status' => $data['status'],
        ]);

        return back()->with('status', 'Estado de pedido actualizado.');
    }

    private function suggestLotsForSale(Sale $sale): Collection
    {
        return $sale->items->mapWithKeys(function ($item) use ($sale) {
            $lot = ProductLot::query()
                ->where('product_id', $item->product_id)
                ->when($sale->warehouse_id, fn ($query, $warehouseId) => $query->where('warehouse_id', $warehouseId))
                ->where('quantity', '>', 0)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhereDate('expires_at', '>=', now()->toDateString());
                })
                ->with('warehouse:id,name')
                ->orderBy('expires_at')
                ->first();

            return [$item->id => $lot];
        });
    }
}
