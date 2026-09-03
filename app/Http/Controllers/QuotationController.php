<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LogsAudit;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Services\ReportService;
use App\Support\AdminReact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuotationController extends Controller
{
    use LogsAudit;

    public function index(Request $request): View
    {
        $saleType = $request->input('sale_type');
        $status = $request->input('status');
        $search = $request->input('search');
        $isVendor = $request->routeIs('dashboard.vendedor.*');
        $userId = $request->user()?->id;

        $quotationsQuery = Quotation::with(['company', 'customer.user', 'seller', 'items.product'])->latest();

        if ($isVendor && $userId) {
            $quotationsQuery->where('seller_id', $userId);
        }

        if ($saleType) {
            $quotationsQuery->where('sale_type', $saleType);
        }

        if ($status) {
            $quotationsQuery->where('status', $status);
        }

        if ($search) {
            $quotationsQuery->where(function ($query) use ($search) {
                $query->whereHas('company', fn ($q) => $q->whereAnyLikeInsensitive(['name'], $search))
                    ->orWhereHas('customer.user', fn ($q) => $q->whereAnyLikeInsensitive(['name'], $search))
                    ->orWhere('id', $search);
            });
        }

        $quotationsPaginator = $quotationsQuery->paginate(10)->withQueryString();

        $statsBase = Quotation::query();
        if ($isVendor && $userId) {
            $statsBase->where('seller_id', $userId);
        }
        $stats = [
            'total' => (clone $statsBase)->count(),
            'draft' => (clone $statsBase)->where('status', 'borrador')->count(),
            'sent' => (clone $statsBase)->where('status', 'enviada')->count(),
            'accepted' => (clone $statsBase)->where('status', 'aceptada')->count(),
            'rejected' => (clone $statsBase)->where('status', 'rechazada')->count(),
        ];

        $listRoute = $isVendor ? 'dashboard.vendedor.quotations' : 'dashboard.quotations';
        $storeRoute = $isVendor ? 'dashboard.vendedor.quotations.store' : 'dashboard.quotations.store';
        $lookupRoute = $isVendor ? 'dashboard.vendedor.quotations.lookup' : 'dashboard.quotations.lookup';
        $pdfRoute = $isVendor ? 'dashboard.vendedor.quotations.pdf' : 'dashboard.quotations.pdf';

        if ($isVendor) {
            return view('dashboard.vendedor.cotizaciones', [
                'quotations' => $quotationsPaginator,
                'saleTypes' => Quotation::TYPES,
                'statuses' => Quotation::STATUSES,
                'stats' => $stats,
                'companies' => Company::where('created_by', $userId)->orderBy('name')->get(),
                'customers' => Customer::with('user')->orderBy('id', 'desc')->get(),
                'filters' => [
                    'sale_type' => $saleType,
                    'status' => $status,
                    'search' => $search,
                ],
                'listRoute' => $listRoute,
                'storeRoute' => $storeRoute,
                'lookupRoute' => $lookupRoute,
                'pdfRoute' => $pdfRoute,
            ]);
        }

        $quotations = $quotationsPaginator
            ->through(function (Quotation $quotation) use ($request) {
                return [
                    'id' => $quotation->id,
                    'company' => $quotation->company ? ['name' => $quotation->company->name, 'city' => $quotation->company->city] : null,
                    'customer' => $quotation->customer ? ['name' => $quotation->customer->user->name ?? 'Cliente', 'city' => $quotation->customer->city] : null,
                    'seller' => $quotation->seller ? ['name' => $quotation->seller->name] : null,
                    'sale_type' => $quotation->sale_type,
                    'status' => $quotation->status,
                    'total_amount' => (float) $quotation->total_amount,
                    'notes' => $quotation->notes,
                    'valid_until_formatted' => optional($quotation->valid_until)->format('d/m/Y'),
                    'items' => $quotation->items->map(fn (QuotationItem $item) => [
                        'product' => $item->product->name ?? 'Producto',
                        'sku' => $item->product->sku ?? '',
                        'qty' => (int) $item->quantity,
                        'price' => (float) $item->unit_price,
                        'subtotal' => (float) $item->subtotal,
                    ])->values(),
                    'pdf_url' => route($request->routeIs('dashboard.vendedor.*') ? 'dashboard.vendedor.quotations.pdf' : 'dashboard.quotations.pdf', $quotation),
                ];
            });

        return view('react-page', AdminReact::page('quotations', 'Cotizaciones | Pil Andina', 'Cotizaciones corporativas', 'quotations', [
            'data' => [
                'quotations' => AdminReact::paginator($quotations),
                'saleTypes' => Quotation::TYPES,
                'statuses' => Quotation::STATUSES,
                'stats' => $stats,
                'companies' => Company::orderBy('name')->get()->map(fn (Company $company) => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'city' => $company->city,
                    'nit' => $company->nit,
                    'company_type' => $company->company_type,
                ]),
                'customers' => Customer::with('user')->get()->map(fn (Customer $customer) => [
                    'id' => $customer->id,
                    'name' => $customer->user->name ?? 'Cliente',
                    'city' => $customer->city,
                ]),
                'filters' => [
                    'sale_type' => $saleType,
                    'status' => $status,
                    'search' => $search,
                ],
                'routes' => [
                    'index' => route($listRoute),
                    'store' => route($storeRoute),
                    'lookup' => route($lookupRoute),
                ],
            ],
        ], 'adminQuotations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $isVendor = $request->routeIs('dashboard.vendedor.*');
        $userId = $request->user()?->id;

        $data = $request->validate([
            'sale_type' => ['required', Rule::in(Quotation::TYPES)],
            'company_id' => ['nullable', 'exists:companies,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'valid_until' => ['required', 'date'],
            'status' => ['required', Rule::in(Quotation::STATUSES)],
            'notes' => ['nullable', 'string'],
            'audit_reason' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Debes agregar al menos un producto en la cotización.',
        ]);

        if ($data['sale_type'] === 'comprador_minorista' && empty($data['customer_id'])) {
            return back()->withErrors(['customer_id' => 'Selecciona un comprador minorista.'])->withInput();
        }

        if ($data['sale_type'] !== 'comprador_minorista' && empty($data['company_id'])) {
            return back()->withErrors(['company_id' => 'Selecciona una empresa o tienda.'])->withInput();
        }

        if ($isVendor && ! empty($data['company_id'])) {
            $belongsToSeller = Company::where('id', $data['company_id'])
                ->where('created_by', $userId)
                ->exists();

            if (! $belongsToSeller) {
                return back()
                    ->withErrors(['company_id' => 'Este cliente no pertenece a tu cartera de vendedor.'])
                    ->withInput();
            }
        }

        $quotation = null;

        DB::transaction(function () use (&$quotation, $data, $request) {
            $total = collect($data['items'])->reduce(function ($carry, $item) {
                return $carry + ($item['quantity'] * $item['unit_price']);
            }, 0);

            $quotation = Quotation::create([
                'company_id' => $data['sale_type'] === 'comprador_minorista' ? null : $data['company_id'],
                'customer_id' => $data['sale_type'] === 'comprador_minorista' ? $data['customer_id'] : null,
                'seller_id' => $request->user()?->id ?? auth()->id(),
                'sale_type' => $data['sale_type'],
                'valid_until' => $data['valid_until'],
                'status' => $data['status'],
                'total_amount' => $total,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);
            }
        });

        if ($quotation) {
            $quotation->load(['items.product', 'company', 'customer.user', 'seller']);
            $reason = trim((string) ($data['audit_reason'] ?? ''));
            $this->logAudit($quotation, 'create', [], $this->quotationAuditPayload($quotation), $reason ?: 'Cotizacion registrada con precios unitarios auditados.');
        }

        $route = $request->routeIs('dashboard.vendedor.*') ? 'dashboard.vendedor.quotations' : 'dashboard.quotations';

        return redirect()
            ->route($route)
            ->with('status', 'Cotización generada correctamente.');
    }

    public function lookupProduct(Request $request): JsonResponse
    {
        $sku = $request->query('sku');

        if (! $sku) {
            return response()->json(['message' => 'Ingresa un código de producto.'], 422);
        }

        $product = Product::where('sku', $sku)->where('is_active', true)->first();

        if (! $product) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        $saleType = $request->query('sale_type', 'empresa_institucional');

        $price = $saleType === 'empresa_institucional'
            ? $product->price_institutional
            : $product->suggested_price_public;

        $available = $product->inventory()->sum('quantity');

        return response()->json([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'price' => $price,
            'available_quantity' => $available,
        ]);
    }

    public function pdf(Quotation $quotation)
    {
        if (request()->routeIs('dashboard.vendedor.*') && $quotation->seller_id !== request()->user()?->id) {
            abort(403);
        }

        return ReportService::download('reports.quotations', [
            'title' => 'Cotización #' . $quotation->id,
            'generatedAt' => now(),
            'quotation' => $quotation->load(['items.product', 'company', 'customer.user', 'seller']),
        ], 'cotizacion-' . $quotation->id . '.pdf');
    }

    private function quotationAuditPayload(Quotation $quotation): array
    {
        return [
            'sale_type' => $quotation->sale_type,
            'status' => $quotation->status,
            'valid_until' => optional($quotation->valid_until)->format('Y-m-d'),
            'total_amount' => (float) $quotation->total_amount,
            'seller' => $quotation->seller?->name,
            'customer' => $quotation->company?->name ?? $quotation->customer?->user?->name,
            'notes' => $quotation->notes,
            'items' => $quotation->items->map(fn (QuotationItem $item) => [
                'product_id' => $item->product_id,
                'product' => $item->product?->name,
                'sku' => $item->product?->sku,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'catalog_public_price' => $item->product ? (float) $item->product->suggested_price_public : null,
                'catalog_institutional_price' => $item->product ? (float) $item->product->price_institutional : null,
                'subtotal' => (float) $item->subtotal,
            ])->values()->all(),
        ];
    }
}
