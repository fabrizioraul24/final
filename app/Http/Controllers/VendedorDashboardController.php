<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\VendorVisit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VendedorDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $data = $this->getDashboardData($request->user()->id);
        return view('dashboard.vendedor', $data);
    }

    public function liveStats(Request $request): JsonResponse
    {
        $data = $this->getDashboardData($request->user()->id);
        
        // Format lists for JSON response to avoid Eloquent circular issues
        $data['recentSales'] = $data['recentSales']->map(fn ($s) => [
            'id' => $s->id,
            'client_name' => $s->company->name ?? 'Venta minorista',
            'sale_type' => ucfirst(str_replace('_', ' ', $s->sale_type ?? 'sin tipo')),
            'time' => optional($s->created_at)->format('d/m H:i'),
            'total_amount' => number_format((float) $s->total_amount, 2),
        ]);

        $data['upcomingVisitsList'] = $data['upcomingVisitsList']->map(fn ($v) => [
            'id' => $v->id,
            'client_name' => $v->company->name ?? 'Cliente sin nombre',
            'visit_date' => optional($v->visit_date)->format('d/m/Y'),
            'city' => $v->company->city ?? 'Sin ciudad',
            'note_status' => $v->note ? 'Con nota' : 'Pendiente',
        ]);

        $data['unvisitedCompanies'] = $data['unvisitedCompanies']->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'city' => $c->city ?? 'Sin ciudad',
        ]);

        return response()->json($data);
    }

    private function getDashboardData(int $userId): array
    {
        $startMonth = Carbon::now()->startOfMonth();
        $salesMonth = Sale::where('seller_id', $userId)->whereDate('created_at', '>=', $startMonth);
        $countSales = $salesMonth->count();
        $amountMonth = (float) $salesMonth->sum('total_amount');
        $clientsCount = Company::count();
        $pendingVisits = VendorVisit::where('user_id', $userId)
            ->whereDate('visit_date', '>=', now()->toDateString())
            ->count();

        $saleTypeLabels = [
            'empresa_institucional' => 'Empresas',
            'tienda_barrio' => 'Tiendas',
            'comprador_minorista' => 'Minoristas',
        ];

        // Last 7 days sales
        $last7 = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $daily = Sale::where('seller_id', $userId)->whereDate('created_at', $date)->sum('total_amount');
            $last7->push([
                'date' => Carbon::parse($date)->format('d/m'),
                'value' => (float) $daily
            ]);
        }

        // Last 7 days clients registration
        $clients7 = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $daily = Company::where('created_by', $userId)->whereDate('created_at', $date)->count();
            $clients7->push([
                'date' => Carbon::parse($date)->format('d/m'),
                'value' => (int) $daily
            ]);
        }

        // Sale types distribution
        $typeSummary = Sale::where('seller_id', $userId)
            ->selectRaw('sale_type, COUNT(*) as total')
            ->groupBy('sale_type')
            ->get()
            ->map(function ($row) use ($saleTypeLabels) {
                return [
                    'label' => $saleTypeLabels[$row->sale_type] ?? ucfirst(str_replace('_', ' ', $row->sale_type ?? 'Otros')),
                    'value' => (int) $row->total,
                ];
            });

        if ($typeSummary->isEmpty()) {
            $typeSummary = collect([['label' => 'Sin datos', 'value' => 1]]);
        }

        // Lists
        $recentSales = Sale::with(['company'])
            ->where('seller_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        $upcomingVisitsList = VendorVisit::with('company')
            ->where('user_id', $userId)
            ->whereDate('visit_date', '>=', now()->toDateString())
            ->orderBy('visit_date')
            ->take(5)
            ->get();

        // Advanced Metrics
        $avgTicket = $countSales > 0 ? ($amountMonth / $countSales) : 0;
        $bestDay = $last7->sortByDesc('value')->first();
        $topSaleType = $typeSummary->sortByDesc('value')->first();

        // Target: 15,000 Bs, Commission: 5%
        $monthlyTarget = 15000.0;
        $commissionRate = 0.05;
        $targetProgress = min(100.0, round(($amountMonth / $monthlyTarget) * 100, 1));
        $estimatedCommission = $amountMonth * $commissionRate;

        // Quotation conversion rate
        $totalQuotations = Quotation::where('seller_id', $userId)->count();
        $acceptedQuotations = Quotation::where('seller_id', $userId)->where('status', 'aceptada')->count();
        $quotationConversion = $totalQuotations > 0 ? round(($acceptedQuotations / $totalQuotations) * 100, 1) : 0;

        // Clients without visits in last 15 days
        $unvisitedCompanies = Company::where('created_by', $userId)
            ->whereNotExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('vendor_visits')
                    ->whereRaw('vendor_visits.company_id = companies.id')
                    ->where('vendor_visits.user_id', $userId)
                    ->whereDate('vendor_visits.visit_date', '>=', now()->subDays(15));
            })
            ->take(4)
            ->get();

        return [
            'countSales' => $countSales,
            'amountMonth' => $amountMonth,
            'clientsCount' => $clientsCount,
            'pendingVisits' => $pendingVisits,
            'last7' => $last7,
            'clients7' => $clients7,
            'typeSummary' => $typeSummary,
            'recentSales' => $recentSales,
            'upcomingVisitsList' => $upcomingVisitsList,
            'avgTicket' => $avgTicket,
            'bestDay' => $bestDay,
            'topSaleType' => $topSaleType,
            
            // New KPI metrics
            'monthlyTarget' => $monthlyTarget,
            'targetProgress' => $targetProgress,
            'estimatedCommission' => $estimatedCommission,
            'totalQuotations' => $totalQuotations,
            'acceptedQuotations' => $acceptedQuotations,
            'quotationConversion' => $quotationConversion,
            'unvisitedCompanies' => $unvisitedCompanies,
        ];
    }
}
