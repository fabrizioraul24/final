<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\ReportService;
use App\Http\Controllers\Concerns\LogsAudit;
use App\Support\AdminReact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyController extends Controller
{
    use LogsAudit;
    /**
     * Vista principal para empresas institucionales y tiendas de barrio.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $typeFilter = $request->input('type');

        $baseQuery = Company::withTrashed()->with('creator')->latest();

        $applyFilters = function ($query) use ($search, $typeFilter) {
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
        };

        $activeCompanies = (clone $baseQuery)
            ->whereNull('deleted_at')
            ->tap($applyFilters)
            ->paginate(10, ['*'], 'activos_page')
            ->withQueryString()
            ->through(fn (Company $company) => $this->companyPayload($company));

        $inactiveCompanies = (clone $baseQuery)
            ->onlyTrashed()
            ->tap($applyFilters)
            ->paginate(10, ['*'], 'inactivos_page')
            ->withQueryString()
            ->through(fn (Company $company) => $this->companyPayload($company));

        $stats = [
            'total' => Company::withTrashed()->count(),
            'active' => Company::count(),
            'inactive' => Company::onlyTrashed()->count(),
            'institutional' => Company::where('company_type', 'empresa_institucional')->count(),
            'retail' => Company::where('company_type', 'tienda_barrio')->count(),
        ];

        return view('react-page', AdminReact::page('companies', 'Clientes empresariales | Pil Andina', 'Empresas institucionales y Tiendas de Barrio', 'companies', [
            'data' => [
                'activeCompanies' => AdminReact::paginator($activeCompanies),
                'inactiveCompanies' => AdminReact::paginator($inactiveCompanies),
                'filters' => [
                    'search' => $search,
                    'type' => $typeFilter,
                ],
                'stats' => $stats,
                'companyTypes' => Company::TYPES,
                'routes' => [
                    'index' => route('dashboard.companies'),
                    'store' => route('dashboard.companies.store'),
                    'report' => route('dashboard.companies.report', ['search' => $search, 'type' => $typeFilter]),
                ],
            ],
        ], 'adminCompanies'));
    }

    /**
     * Registrar un nuevo cliente empresarial o tienda de barrio.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);
        $data['created_by'] = $request->user()?->id ?? auth()->id();

        $company = Company::create($data);

        $this->logAudit($company, 'create', [], $company->only([
            'name','nit','company_type','email','phone','city','created_by'
        ]), 'Creación de cliente');

        return redirect()
            ->route('dashboard.companies')
            ->with('status', 'Cliente registrado correctamente.');
    }

    /**
     * Actualizar un registro existente.
     */
    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $this->validatePayload($request, $company->id);
        $old = $company->only(['name','nit','company_type','email','phone','city']);
        $company->update($data);

        $this->logAudit($company, 'update', $old, $company->only(['name','nit','company_type','email','phone','city']), 'Actualización de cliente');

        return redirect()
            ->route('dashboard.companies')
            ->with('status', 'Cliente actualizado.');
    }

    /**
     * Enviar a papelera mediante soft delete.
     */
    public function destroy(Company $company): RedirectResponse
    {
        $old = $company->only(['name','nit','company_type','email','phone','city']);
        $company->delete();

        $this->logAudit($company, 'deactivate', $old, [], 'Desactivación de cliente');

        return redirect()
            ->route('dashboard.companies')
            ->with('status', 'Cliente desactivado correctamente.');
    }

    /**
     * Reactivar un cliente desactivado.
     */
    public function restore(int $companyId): RedirectResponse
    {
        $company = Company::withTrashed()->findOrFail($companyId);
        $new = $company->only(['name','nit','company_type','email','phone','city']);
        $company->restore();

        $this->logAudit($company, 'restore', [], $new, 'Reactivación de cliente');

        return redirect()
            ->route('dashboard.companies')
            ->with('status', 'Cliente reactivado correctamente.');
    }

    /**
     * Descargar reporte PDF.
     */
    public function report(Request $request)
    {
        $search = $request->input('search');
        $typeFilter = $request->input('type');

        $companiesQuery = Company::query()->with('creator')->latest();

        if ($search) {
            $companiesQuery->whereAnyLikeInsensitive([
                'name',
                'nit',
                'email',
                'city',
                'owner_first_name',
                'owner_last_name_paterno',
            ], $search);
        }

        if ($typeFilter) {
            $companiesQuery->where('company_type', $typeFilter);
        }

        return ReportService::download('reports.companies', [
            'title' => 'Reporte de clientes empresariales',
            'generatedAt' => now(),
            'companies' => $companiesQuery->get(),
            'companyTypes' => Company::TYPES,
            'filters' => [
                'type_label' => $typeFilter ? (Company::TYPES[$typeFilter] ?? null) : null,
            ],
        ], 'reporte-clientes.pdf');
    }

    /**
     * Validacion reutilizable para store/update.
     */
    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
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
    }

    private function companyPayload(Company $company): array
    {
        return [
            'id' => $company->id,
            'company_type' => $company->company_type,
            'type_label' => $company->type_label,
            'name' => $company->name,
            'nit' => $company->nit,
            'email' => $company->email,
            'phone' => $company->phone,
            'address' => $company->address,
            'city' => $company->city,
            'owner_first_name' => $company->owner_first_name,
            'owner_last_name_paterno' => $company->owner_last_name_paterno,
            'owner_last_name_materno' => $company->owner_last_name_materno,
            'owner_full_name' => trim($company->owner_first_name . ' ' . $company->owner_last_name_paterno . ' ' . $company->owner_last_name_materno),
            'creator' => $company->creator ? ['name' => $company->creator->name] : null,
            'created_at_formatted' => optional($company->created_at)->format('d/m/Y'),
            'destroy_url' => route('dashboard.companies.destroy', $company),
            'restore_url' => route('dashboard.companies.restore', $company->id),
            'update_url' => route('dashboard.companies.update', $company),
        ];
    }
}
