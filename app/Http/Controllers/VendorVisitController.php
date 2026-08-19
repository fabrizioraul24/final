<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\VendorVisit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorVisitController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $search = $request->input('search');
        $date = $request->input('visit_date');

        $visitsQuery = VendorVisit::with('company')
            ->where('user_id', $userId)
            ->orderBy('visit_date');

        if ($search) {
            $visitsQuery->whereHas('company', function ($q) use ($search) {
                $q->whereAnyLikeInsensitive(['name', 'nit'], $search);
            });
        }

        if ($date) {
            $visitsQuery->whereDate('visit_date', $date);
        }

        $upcomingCount = VendorVisit::where('user_id', $userId)
            ->whereDate('visit_date', '>=', now()->toDateString())
            ->count();

        return view('dashboard.vendedor.visitas', [
            'visits' => $visitsQuery->paginate(10)->withQueryString(),
            'companies' => Company::orderBy('name')->get(),
            'search' => $search,
            'visitDate' => $date,
            'upcomingCount' => $upcomingCount,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'visit_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        VendorVisit::create([
            'user_id' => $request->user()->id,
            'company_id' => $data['company_id'],
            'visit_date' => $data['visit_date'],
            'status' => 'pendiente',
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('status', 'Visita agendada.');
    }

    public function update(Request $request, VendorVisit $visit): RedirectResponse
    {
        $this->authorizeVisit($request, $visit);

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'visit_date' => ['required', 'date'],
            'status' => ['required', 'string', 'max:30'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $visit->update($data);

        return back()->with('status', 'Visita actualizada.');
    }

    public function destroy(Request $request, VendorVisit $visit): RedirectResponse
    {
        $this->authorizeVisit($request, $visit);
        $visit->delete();

        return back()->with('status', 'Visita eliminada.');
    }

    private function authorizeVisit(Request $request, VendorVisit $visit): void
    {
        if ($visit->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
