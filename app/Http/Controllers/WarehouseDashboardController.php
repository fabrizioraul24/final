<?php

namespace App\Http\Controllers;

use App\Models\ProductLot;
use App\Models\Sale;
use App\Models\Transfer;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WarehouseDashboardController extends Controller
{
    public function __invoke(): View
    {
        $data = $this->getDashboardData();
        return view('dashboard.almacen', $data);
    }

    public function liveStats(): JsonResponse
    {
        $data = $this->getDashboardData();

        // Map objects to simple array format for JSON responses to avoid Eloquent circular refs
        $data['recentTransfers'] = $data['recentTransfers']->map(fn ($t) => [
            'id' => $t->id,
            'from_warehouse' => $t->fromWarehouse->name ?? 'Sin origen',
            'to_warehouse' => $t->toWarehouse->name ?? 'Sin destino',
            'status' => $t->status,
            'status_label' => ucfirst(str_replace('_', ' ', $t->status)),
            'expected_date' => optional($t->expected_date)->format('d/m/Y') ?? 'Sin fecha',
            'created_at_formatted' => optional($t->created_at)->format('d/m H:i'),
        ]);

        $data['expiringLotsList'] = $data['expiringLotsList']->map(fn ($l) => [
            'id' => $l->id,
            'product_name' => $l->product->name ?? 'Producto desconocido',
            'sku' => $l->product->sku ?? '',
            'warehouse_name' => $l->warehouse->name ?? 'Sin almacén',
            'quantity' => (int) $l->quantity,
            'expires_at_formatted' => optional($l->expires_at)->format('d/m/Y'),
            'days_left' => (int) now()->diffInDays($l->expires_at, false),
        ]);

        $data['criticalLotsList'] = $data['criticalLotsList']->map(fn ($l) => [
            'id' => $l->id,
            'product_name' => $l->product->name ?? 'Producto desconocido',
            'sku' => $l->product->sku ?? '',
            'warehouse_name' => $l->warehouse->name ?? 'Sin almacén',
            'quantity' => (int) $l->quantity,
            'safety_threshold' => (int) $l->safety_threshold,
        ]);

        return response()->json($data);
    }

    private function getDashboardData(): array
    {
        $recentTransfers = Transfer::with(['fromWarehouse', 'toWarehouse'])
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'stock' => ProductLot::sum('quantity'),
            'pending_orders' => Sale::where('status', 'sin_entregar')->count(),
            'transfers_today' => Transfer::whereDate('created_at', today())->count(),
            'expiring_lots' => ProductLot::whereBetween('expires_at', [now(), now()->addDays(30)])->count(),
        ];

        $capacityData = Warehouse::select('warehouses.id', 'warehouses.name', 'warehouses.capacity_max')
            ->leftJoin('product_lots', 'product_lots.warehouse_id', '=', 'warehouses.id')
            ->selectRaw('COALESCE(SUM(product_lots.quantity), 0) as occupancy')
            ->groupBy('warehouses.id', 'warehouses.name', 'warehouses.capacity_max')
            ->get()
            ->map(function ($row) {
                $capacity = max(1, $row->capacity_max ?? 1);
                $row->percent = min(100, round(($row->occupancy / $capacity) * 100, 1));
                return $row;
            });

        $lastDays = collect(range(6, 0))->map(fn ($i) => Carbon::today()->subDays($i));
        $transferSeries = $lastDays->map(function ($day) {
            return [
                'label' => $day->format('d/m'),
                'count' => Transfer::whereDate('created_at', $day)->count(),
            ];
        });

        // Detailed expiring lots (within 30 days)
        $expiringLotsList = ProductLot::whereBetween('expires_at', [now(), now()->addDays(30)])
            ->with(['product:id,name,sku', 'warehouse:id,name'])
            ->orderBy('expires_at')
            ->take(5)
            ->get();

        // Critical stock lots (under safety threshold)
        $criticalLotsList = ProductLot::whereColumn('quantity', '<', 'safety_threshold')
            ->with(['product:id,name,sku', 'warehouse:id,name'])
            ->orderBy('quantity')
            ->take(5)
            ->get();

        return [
            'recentTransfers' => $recentTransfers,
            'stats' => $stats,
            'capacityChart' => [
                'labels' => $capacityData->pluck('name'),
                'data' => $capacityData->pluck('percent'),
            ],
            'transferSeries' => [
                'labels' => $transferSeries->pluck('label'),
                'data' => $transferSeries->pluck('count'),
            ],
            'expiringLotsList' => $expiringLotsList,
            'criticalLotsList' => $criticalLotsList,
        ];
    }
}
