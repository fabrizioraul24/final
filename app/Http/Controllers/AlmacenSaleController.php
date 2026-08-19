<?php

namespace App\Http\Controllers;

use App\Models\ProductLot;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlmacenSaleController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $sales = Sale::with(['company', 'customer.user', 'items.product', 'warehouse'])
            ->when($status, fn ($query, $value) => $query->where('status', $value))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $suggestions = [];

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $lot = ProductLot::query()
                    ->where('product_id', $item->product_id)
                    ->when($sale->warehouse_id, fn ($q, $warehouseId) => $q->where('warehouse_id', $warehouseId))
                    ->where('quantity', '>', 0)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                            ->orWhereDate('expires_at', '>=', now()->toDateString());
                    })
                    ->orderBy('expires_at')
                    ->first();

                $suggestions[$item->id] = $lot;
            }
        }

        return view('dashboard.almacen-recepciones', [
            'sales' => $sales,
            'statuses' => Sale::STATUSES,
            'filters' => ['status' => $status],
            'suggestions' => $suggestions,
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
}
