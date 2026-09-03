<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\VendorVisit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        return response()->json($data);
    }

    private function getDashboardData(int $userId): array
    {
        $startMonth = Carbon::now()->startOfMonth();
        $salesMonth = Sale::where('seller_id', $userId)->whereDate('created_at', '>=', $startMonth);
        $countSales = $salesMonth->count();
        $amountMonth = (float) $salesMonth->sum('total_amount');
        $todaySales = (float) Sale::where('seller_id', $userId)
            ->whereDate('created_at', now()->toDateString())
            ->sum('total_amount');
        $clientsCount = Company::where('created_by', $userId)->count();
        $pendingVisits = VendorVisit::where('user_id', $userId)
            ->whereDate('visit_date', '>=', now()->toDateString())
            ->count();

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

        // Target: 15,000 Bs
        $monthlyTarget = 15000.0;
        $targetProgress = min(100.0, round(($amountMonth / $monthlyTarget) * 100, 1));

        // Quotation conversion rate
        $totalQuotations = Quotation::where('seller_id', $userId)->count();
        $acceptedQuotations = Quotation::where('seller_id', $userId)->where('status', 'aceptada')->count();
        $quotationConversion = $totalQuotations > 0 ? round(($acceptedQuotations / $totalQuotations) * 100, 1) : 0;

        return [
            'countSales' => $countSales,
            'amountMonth' => $amountMonth,
            'todaySales' => $todaySales,
            'clientsCount' => $clientsCount,
            'pendingVisits' => $pendingVisits,
            'last7' => $last7,
            'clients7' => $clients7,
            'recentSales' => $recentSales,
            'upcomingVisitsList' => $upcomingVisitsList,
            'monthlyTarget' => $monthlyTarget,
            'targetProgress' => $targetProgress,
            'totalQuotations' => $totalQuotations,
            'acceptedQuotations' => $acceptedQuotations,
            'quotationConversion' => $quotationConversion,
        ];
    }
}
