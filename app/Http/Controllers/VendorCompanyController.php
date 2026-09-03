<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\City;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VendorCompanyController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $typeFilter = $request->input('type');

        $baseQuery = Company::withTrashed()->latest();

        $activeCompanies = (clone $baseQuery)
            ->whereNull('deleted_at')
            ->tap(fn ($query) => $this->applyFilters($query, $search, $typeFilter))
            ->paginate(10, ['*'], 'activos_page')
            ->withQueryString();

        $inactiveCompanies = (clone $baseQuery)
            ->onlyTrashed()
            ->tap(fn ($query) => $this->applyFilters($query, $search, $typeFilter))
            ->paginate(10, ['*'], 'inactivos_page')
            ->withQueryString();

        $stats = [
            'total' => Company::withTrashed()->count(),
            'active' => Company::count(),
            'inactive' => Company::onlyTrashed()->count(),
            'institutional' => Company::where('company_type', 'empresa_institucional')->count(),
            'retail' => Company::where('company_type', 'tienda_barrio')->count(),
        ];

        return view('dashboard.vendedor.clientes', [
            'activeCompanies' => $activeCompanies,
            'inactiveCompanies' => $inactiveCompanies,
            'search' => $search,
            'typeFilter' => $typeFilter,
            'stats' => $stats,
            'companyTypes' => Company::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);
        $data['created_by'] = $request->user()?->id ?? auth()->id();

        Company::create($data);

        return redirect()
            ->route('dashboard.vendedor.companies')
            ->with('status', 'Cliente registrado correctamente.');
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $this->validatePayload($request, $company->id);
        $company->update($data);

        return redirect()
            ->route('dashboard.vendedor.companies')
            ->with('status', 'Cliente actualizado.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()
            ->route('dashboard.vendedor.companies')
            ->with('status', 'Cliente desactivado correctamente.');
    }

    public function restore(int $companyId): RedirectResponse
    {
        $company = Company::withTrashed()->findOrFail($companyId);
        $company->restore();

        return redirect()
            ->route('dashboard.vendedor.companies')
            ->with('status', 'Cliente reactivado correctamente.');
    }

    public function report(Request $request)
    {
        $user = $request->user();
        abort_if(! $user, 403);

        $search = $request->query('search');
        $typeFilter = $request->query('type');

        $query = Company::query()
            ->where('created_by', $user->id)
            ->orderBy('name');

        $this->applyFilters($query, $search, $typeFilter);

        $companies = $query->get();

        $stats = [
            'total' => $companies->count(),
            'institutional' => $companies->where('company_type', 'empresa_institucional')->count(),
            'retail' => $companies->where('company_type', 'tienda_barrio')->count(),
            'with_email' => $companies->whereNotNull('email')->count(),
        ];

        return ReportService::download('reports.vendor-companies', [
            'title' => 'Clientes registrados por ' . ($user->name ?? 'vendedor'),
            'generatedAt' => now(),
            'vendor' => $user,
            'companies' => $companies,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'type' => $typeFilter,
            ],
        ], 'clientes-vendedor-' . $user->id . '.pdf');
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'company_type' => ['required', Rule::in(array_keys(Company::TYPES))],
            'name' => ['required', 'string', 'max:255'],
            'nit' => [
                'required',
                'string',
                'max:100',
                Rule::unique('companies', 'nit')->ignore($ignoreId),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'google_maps_url' => ['nullable', 'url', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'owner_first_name' => ['required', 'string', 'max:255'],
            'owner_last_name_paterno' => ['required', 'string', 'max:255'],
            'owner_last_name_materno' => ['nullable', 'string', 'max:255'],
        ]);

        $cityName = trim($data['city']);
        $city = City::firstOrCreate(
            ['code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]+/', '', $cityName), 0, 10))],
            ['name' => $cityName]
        );

        $data['city'] = $city->name;
        $data['city_id'] = $city->id;

        return $data;
    }

    private function applyFilters($query, ?string $search, ?string $typeFilter): void
    {
        if ($search) {
            $query->whereAnyLikeInsensitive([
                'name',
                'nit',
                'email',
                'city',
                'owner_first_name',
                'owner_last_name_paterno',
            ], $search);
        }

        if ($typeFilter) {
            $query->where('company_type', $typeFilter);
        }
    }

}
