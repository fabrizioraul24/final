<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LogsAudit;
use App\Models\Company;
use App\Models\VendorVisit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VendorVisitController extends Controller
{
    use LogsAudit;

    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $search = $request->input('search');
        $date = $request->input('visit_date');
        $status = $request->input('status');

        $visitsQuery = VendorVisit::with('company')
            ->where('user_id', $userId)
            ->whereHas('company', fn ($query) => $query->where('created_by', $userId))
            ->orderBy('visit_date')
            ->orderByDesc('id');

        if ($search) {
            $visitsQuery->whereHas('company', function ($q) use ($search) {
                $q->whereAnyLikeInsensitive(['name', 'nit'], $search);
            });
        }

        if ($date) {
            $visitsQuery->whereDate('visit_date', $date);
        }

        if ($status) {
            $visitsQuery->where('status', $status);
        }

        $statsBase = VendorVisit::where('user_id', $userId)
            ->whereHas('company', fn ($query) => $query->where('created_by', $userId));

        $stats = [
            'total' => (clone $statsBase)->count(),
            'today' => (clone $statsBase)->whereDate('visit_date', now()->toDateString())->count(),
            'upcoming' => (clone $statsBase)->whereDate('visit_date', '>=', now()->toDateString())->where('status', 'pendiente')->count(),
            'completed' => (clone $statsBase)->where('status', 'completada')->count(),
            'canceled' => (clone $statsBase)->where('status', 'cancelada')->count(),
        ];

        return view('dashboard.vendedor.visitas', [
            'visits' => $visitsQuery->paginate(10)->withQueryString(),
            'companies' => Company::where('created_by', $userId)->orderBy('name')->get(),
            'filters' => [
                'search' => $search,
                'visit_date' => $date,
                'status' => $status,
            ],
            'stats' => $stats,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;
        $data = $request->validate([
            'company_id' => [
                'required',
                Rule::exists('companies', 'id')->where(fn ($query) => $query->where('created_by', $userId)),
            ],
            'visit_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $visit = VendorVisit::create([
            'user_id' => $userId,
            'company_id' => $data['company_id'],
            'visit_date' => $data['visit_date'],
            'status' => 'pendiente',
            'note' => $data['note'] ?? null,
        ]);

        $this->logAudit($visit, 'create', [], $visit->only(['user_id','company_id','visit_date','status','note']), $visit->note ?: 'Visita agendada por vendedor');

        return back()->with('status', 'Visita agendada.');
    }

    public function update(Request $request, VendorVisit $visit): RedirectResponse
    {
        $this->authorizeVisit($request, $visit);
        $userId = $request->user()->id;

        $data = $request->validate([
            'company_id' => [
                'required',
                Rule::exists('companies', 'id')->where(fn ($query) => $query->where('created_by', $userId)),
            ],
            'visit_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['pendiente', 'completada', 'cancelada'])],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $old = $visit->only(['company_id','visit_date','status','note']);
        $visit->update($data);

        $this->logAudit($visit, 'update', $old, $visit->only(['company_id','visit_date','status','note']), $visit->note ?: 'Visita actualizada por vendedor');

        return back()->with('status', 'Visita actualizada.');
    }

    public function destroy(Request $request, VendorVisit $visit): RedirectResponse
    {
        $this->authorizeVisit($request, $visit);
        $old = $visit->only(['user_id','company_id','visit_date','status','note']);
        $visit->delete();

        $this->logAudit($visit, 'delete', $old, [], 'Visita eliminada por vendedor');

        return back()->with('status', 'Visita eliminada.');
    }

    private function authorizeVisit(Request $request, VendorVisit $visit): void
    {
        if ($visit->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
