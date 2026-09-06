@extends('layouts.sidebar-vendedor')

@section('title', 'Cotizaciones | Vendedor')
@section('page-title', 'Cotizaciones comerciales')

@php
    $saleTypeLabels = [
        'empresa_institucional' => 'Empresa institucional',
        'tienda_barrio' => 'Tienda de barrio',
        'comprador_minorista' => 'Comprador minorista',
    ];
@endphp

@section('content')
<div class="vendor-quotations-page">
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="card">
            <span class="chip" style="color:#f87171;">Revisa los datos de la cotizacion antes de registrar.</span>
        </div>
    @endif

    <section class="fit-users-header">
        <div class="fit-users-header-left">
            <div class="fit-header-icon"><i class="ri-file-list-3-line"></i></div>
            <div>
                <h1>Cotizaciones del Vendedor</h1>
                <p>Genera proformas y consulta solo las cotizaciones asociadas a tu usuario.</p>
            </div>
        </div>
        <div class="fit-users-header-actions">
            <button type="button" class="fit-primary-button" id="openQuotationCreate">
                <i class="ri-file-add-line"></i>
                <span>Crear Cotizacion</span>
            </button>
        </div>
    </section>

    <section class="fit-metric-grid fit-quotation-metric-grid">
        <a class="fit-metric-card indigo active" href="{{ route($listRoute) }}">
            <span><small>Total Cotizaciones</small><strong>{{ $stats['total'] }}</strong><em>Ver todas</em></span>
            <span class="fit-metric-icon"><i class="ri-file-list-3-line"></i></span>
        </a>
        <div class="fit-metric-card blue">
            <span><small>Monto total</small><strong>Bs {{ number_format((float) ($stats['total_amount'] ?? 0), 2) }}</strong><em>Cotizado</em></span>
            <span class="fit-metric-icon"><i class="ri-money-dollar-circle-line"></i></span>
        </div>
        <div class="fit-metric-card amber">
            <span><small>Promedio</small><strong>Bs {{ number_format((float) ($stats['average_amount'] ?? 0), 2) }}</strong><em>Por cotizacion</em></span>
            <span class="fit-metric-icon"><i class="ri-line-chart-line"></i></span>
        </div>
        <div class="fit-metric-card green">
            <span><small>Productos</small><strong>{{ $stats['items_count'] ?? 0 }}</strong><em>Items cotizados</em></span>
            <span class="fit-metric-icon"><i class="ri-box-3-line"></i></span>
        </div>
    </section>

    <section class="fit-filter-card">
        <form method="GET" action="{{ route($listRoute) }}" class="fit-filter-form fit-quotation-filter-form" data-live-search-form>
            <label class="fit-search-control" for="search">
                <i class="ri-search-line"></i>
                <input type="search" id="search" name="search" value="{{ $filters['search'] }}" placeholder="Buscar ID o cliente..." data-live-search-input>
            </label>
            <label class="fit-select-control" for="sale_type_filter">
                <i class="ri-price-tag-3-line"></i>
                <select id="sale_type_filter" name="sale_type">
                    <option value="">Todos los tipos</option>
                    @foreach($saleTypeLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['sale_type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="fit-primary-button compact"><i class="ri-search-line"></i><span>Buscar</span></button>
            @if($filters['search'] || $filters['sale_type'])
                <a href="{{ route($listRoute) }}" class="fit-clear-button">Limpiar filtros</a>
            @endif
        </form>
    </section>

    <section class="fit-section">
        <div class="fit-section-head">
            <div>
                <h2>Cotizaciones recientes</h2>
                <p>Proformas ordenadas por fecha. No muestra registros del administrador ni de otros vendedores.</p>
            </div>
            <span class="fit-section-badge green">{{ $quotations->total() }} registros</span>
        </div>

        <div class="fit-table-card">
            <div class="fit-table-scroll">
                <table class="fit-users-table fit-quotations-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Total</th>
                            <th>Valido hasta</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotations as $quotation)
                            @php
                                $clientName = $quotation->company->name ?? $quotation->customer->user->name ?? 'Sin cliente';
                                $clientCity = $quotation->company->city ?? $quotation->customer->city ?? 'Ciudad no registrada';
                            @endphp
                            <tr>
                                <td><code class="fit-code fit-sale-id">#{{ $quotation->id }}</code></td>
                                <td>
                                    <div class="fit-user-cell fit-sale-client">
                                        <span class="fit-sale-client-icon"><i class="{{ $quotation->company ? 'ri-building-4-line' : 'ri-user-smile-line' }}"></i></span>
                                        <div><strong>{{ $clientName }}</strong><small>{{ $clientCity }}</small></div>
                                    </div>
                                </td>
                                <td><span class="fit-role-badge default"><i class="ri-price-tag-3-line"></i> {{ $saleTypeLabels[$quotation->sale_type] ?? $quotation->sale_type }}</span></td>
                                <td><strong class="fit-sale-amount">Bs {{ number_format((float) $quotation->total_amount, 2) }}</strong></td>
                                <td><span class="fit-muted-text">{{ optional($quotation->valid_until)->format('d/m/Y') }}</span></td>
                                <td class="text-right">
                                    <div class="fit-row-actions">
                                        <a href="{{ route('dashboard.vendedor.quotations.show', $quotation) }}" class="fit-action-button success" title="Ver detalles">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        <a class="fit-action-button warning" target="_blank" rel="noopener" href="{{ route($pdfRoute, $quotation) }}" title="Descargar PDF">
                                            <i class="ri-file-download-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center;padding:1rem;">No hay cotizaciones registradas para este vendedor.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top:1rem;">{{ $quotations->appends($filters)->links() }}</div>
    </section>

    <div class="modal" id="vendorQuotationCreateModal">
        <div class="modal-content fit-modal-content fit-vendor-quotation-modal">
            <div class="modal-header">
                <h3>Crear cotizacion</h3>
                <button class="close-button" type="button" data-close-quotation-create>&times;</button>
            </div>
            <form method="POST" action="{{ route($storeRoute) }}" id="vendorQuotationForm">
                @csrf
                <div class="fit-form-grid">
                    <div class="fit-form-field">
                        <label for="quotation_sale_type">Tipo *</label>
                        <select id="quotation_sale_type" name="sale_type" required>
                            @foreach($saleTypeLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('sale_type', 'empresa_institucional') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('sale_type')<small style="color:#f87171">{{ $message }}</small>@enderror
                    </div>
                    <div class="fit-form-field">
                        <label for="valid_until">Valido hasta *</label>
                        <input type="date" id="valid_until" name="valid_until" value="{{ old('valid_until', now()->addWeek()->format('Y-m-d')) }}" required>
                        @error('valid_until')<small style="color:#f87171">{{ $message }}</small>@enderror
                    </div>
                    <input type="hidden" name="status" value="enviada">
                    <div class="fit-form-field span-2" id="quotationCompanyField">
                        <label for="quotation_company_id">Empresa / tienda de tu cartera *</label>
                        <select id="quotation_company_id" name="company_id">
                            <option value="">Seleccionar</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" data-type="{{ $company->company_type }}" @selected(old('company_id') == $company->id)>
                                    {{ $company->name }} - {{ $company->city }} (NIT: {{ $company->nit }})
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')<small style="color:#f87171">{{ $message }}</small>@enderror
                    </div>
                    <div class="fit-form-field span-2" id="quotationCustomerField" style="display:none;">
                        <label for="quotation_customer_id">Comprador minorista *</label>
                        <select id="quotation_customer_id" name="customer_id">
                            <option value="">Seleccionar</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                    {{ $customer->user->name ?? 'Cliente' }} - {{ $customer->city }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')<small style="color:#f87171">{{ $message }}</small>@enderror
                    </div>
                    <div class="fit-form-field span-2">
                        <label for="notes">Notas</label>
                        <textarea id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="vendor-quotation-products">
                    <div class="fit-section-head">
                        <div>
                            <h2>Productos</h2>
                            <p>Busca por SKU para cargar precio segun tipo de cotizacion.</p>
                        </div>
                        <button type="button" class="fit-outline-button" id="addQuotationItem" data-lookup-url="{{ route($lookupRoute) }}">
                            <i class="ri-add-line"></i>
                            <span>Agregar producto</span>
                        </button>
                    </div>
                    <div id="quotationItems" class="vendor-quotation-items-list"></div>
                    @error('items')<small style="color:#f87171">{{ $message }}</small>@enderror
                    <div class="fit-form-field span-2">
                        <label for="quotation_audit_reason">Motivo para bitacora</label>
                        <textarea id="quotation_audit_reason" name="audit_reason" rows="3" placeholder="Ej. Precio negociado o promocion temporal autorizada">{{ old('audit_reason') }}</textarea>
                        @error('audit_reason')<small style="color:#f87171">{{ $message }}</small>@enderror
                    </div>
                    <div class="fit-sale-total">
                        <span>Total estimado</span>
                        <strong id="quotationTotal">Bs 0.00</strong>
                    </div>
                </div>

                <div class="fit-modal-footer">
                    <button type="button" class="fit-outline-button" data-close-quotation-create>Cancelar</button>
                    <button type="submit" class="fit-primary-button"><i class="ri-file-add-line"></i><span>Generar cotizacion</span></button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(() => {
    const createModal = document.getElementById('vendorQuotationCreateModal');
    const saleTypeSelect = document.getElementById('quotation_sale_type');
    const companyField = document.getElementById('quotationCompanyField');
    const customerField = document.getElementById('quotationCustomerField');
    const companySelect = document.getElementById('quotation_company_id');
    const customerSelect = document.getElementById('quotation_customer_id');
    const itemsContainer = document.getElementById('quotationItems');
    const addItemButton = document.getElementById('addQuotationItem');
    const lookupUrl = addItemButton?.dataset.lookupUrl;
    const totalLabel = document.getElementById('quotationTotal');
    let itemIndex = 0;

    function setCreateOpen(open) {
        createModal?.classList.toggle('active', open);
    }

    function updateBuyerForm() {
        const type = saleTypeSelect?.value || 'empresa_institucional';
        if (type === 'comprador_minorista') {
            companyField.style.display = 'none';
            customerField.style.display = '';
            if (companySelect) companySelect.value = '';
            return;
        }

        companyField.style.display = '';
        customerField.style.display = 'none';
        if (customerSelect) customerSelect.value = '';
        companySelect?.querySelectorAll('option').forEach((option) => {
            if (!option.value) return;
            option.hidden = option.dataset.type !== type;
        });
    }

    function createRow(index) {
        const wrapper = document.createElement('div');
        wrapper.className = 'vendor-quotation-item-row';
        wrapper.innerHTML = `
            <div class="fit-form-grid vendor-quotation-product-grid">
                <div class="fit-form-field">
                    <label>SKU *</label>
                    <input type="text" class="quotation-sku" placeholder="Ej. 120">
                    <input type="hidden" name="items[${index}][product_id]" class="product-id-input" required>
                </div>
                <div class="fit-form-field">
                    <label>Producto</label>
                    <input type="text" class="product-name" placeholder="Busca por SKU" readonly>
                </div>
                <div class="fit-form-field">
                    <label>Cantidad *</label>
                    <input type="number" min="1" class="quantity-input" name="items[${index}][quantity]" required>
                </div>
                <div class="fit-form-field">
                    <label>Precio *</label>
                    <input type="number" min="0" step="0.01" class="unit-price-input" name="items[${index}][unit_price]" required>
                </div>
            </div>
            <button type="button" class="fit-action-button danger remove-quotation-item" title="Quitar producto"><i class="ri-delete-bin-line"></i></button>
        `;
        return wrapper;
    }

    function recalcTotal() {
        let total = 0;
        itemsContainer?.querySelectorAll('.vendor-quotation-item-row').forEach((row) => {
            const qty = parseFloat(row.querySelector('.quantity-input')?.value || 0);
            const price = parseFloat(row.querySelector('.unit-price-input')?.value || 0);
            total += qty * price;
        });
        if (totalLabel) totalLabel.textContent = `Bs ${total.toFixed(2)}`;
    }

    function lookupProduct(row, sku) {
        if (!lookupUrl) return;
        const productInput = row.querySelector('.product-id-input');
        const nameInput = row.querySelector('.product-name');
        const qtyInput = row.querySelector('.quantity-input');
        const priceInput = row.querySelector('.unit-price-input');
        nameInput.value = 'Buscando...';

        const params = new URLSearchParams({
            sku,
            sale_type: saleTypeSelect?.value || 'empresa_institucional',
        });

        fetch(`${lookupUrl}?${params.toString()}`)
            .then((response) => {
                if (!response.ok) throw response;
                return response.json();
            })
            .then((data) => {
                productInput.value = data.product_id;
                nameInput.value = `${data.name} (${data.sku})`;
                priceInput.value = data.price ?? 0;
                if (!qtyInput.value) qtyInput.value = 1;
                recalcTotal();
            })
            .catch(async (error) => {
                let message = 'Producto no encontrado.';
                if (error.json) {
                    const payload = await error.json();
                    if (payload?.message) message = payload.message;
                }
                productInput.value = '';
                nameInput.value = message;
                recalcTotal();
            });
    }

    document.getElementById('openQuotationCreate')?.addEventListener('click', () => setCreateOpen(true));
    document.querySelectorAll('[data-close-quotation-create]').forEach((button) => button.addEventListener('click', () => setCreateOpen(false)));
    createModal?.addEventListener('click', (event) => {
        if (event.target === createModal) setCreateOpen(false);
    });

    saleTypeSelect?.addEventListener('change', updateBuyerForm);
    addItemButton?.addEventListener('click', () => itemsContainer?.appendChild(createRow(itemIndex++)));

    itemsContainer?.addEventListener('click', (event) => {
        const removeButton = event.target.closest('.remove-quotation-item');
        if (!removeButton) return;
        removeButton.closest('.vendor-quotation-item-row')?.remove();
        recalcTotal();
    });

    itemsContainer?.addEventListener('blur', (event) => {
        if (!event.target.classList.contains('quotation-sku')) return;
        const sku = event.target.value.trim();
        if (!sku) return;
        lookupProduct(event.target.closest('.vendor-quotation-item-row'), sku);
    }, true);

    itemsContainer?.addEventListener('input', (event) => {
        if (event.target.classList.contains('quantity-input') || event.target.classList.contains('unit-price-input')) {
            recalcTotal();
        }
    });

    updateBuyerForm();
    addItemButton?.click();
    if (@json($errors->any())) {
        setCreateOpen(true);
    }
})();
</script>
@endpush
