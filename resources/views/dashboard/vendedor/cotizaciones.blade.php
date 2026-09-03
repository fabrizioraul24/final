@extends('layouts.sidebar-vendedor')

@section('title', 'Cotizaciones | Vendedor')
@section('page-title', 'Cotizaciones comerciales')

@php
    $saleTypeLabels = [
        'empresa_institucional' => 'Empresa institucional',
        'tienda_barrio' => 'Tienda de barrio',
        'comprador_minorista' => 'Comprador minorista',
    ];
    $statusLabels = [
        'borrador' => 'Borrador',
        'enviada' => 'Enviada',
        'aceptada' => 'Aceptada',
        'rechazada' => 'Rechazada',
    ];
    $activeMetric = $filters['status'] ?: 'all';
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
        <a class="fit-metric-card indigo {{ $activeMetric === 'all' ? 'active' : '' }}" href="{{ route($listRoute) }}">
            <span><small>Total Cotizaciones</small><strong>{{ $stats['total'] }}</strong><em>Ver todas</em></span>
            <span class="fit-metric-icon"><i class="ri-file-list-3-line"></i></span>
        </a>
        <a class="fit-metric-card amber {{ $activeMetric === 'borrador' ? 'active' : '' }}" href="{{ route($listRoute, ['status' => 'borrador', 'sale_type' => $filters['sale_type'], 'search' => $filters['search']]) }}">
            <span><small>Borradores</small><strong>{{ $stats['draft'] }}</strong><em>En preparacion</em></span>
            <span class="fit-metric-icon"><i class="ri-draft-line"></i></span>
        </a>
        <a class="fit-metric-card blue {{ $activeMetric === 'enviada' ? 'active' : '' }}" href="{{ route($listRoute, ['status' => 'enviada', 'sale_type' => $filters['sale_type'], 'search' => $filters['search']]) }}">
            <span><small>Enviadas</small><strong>{{ $stats['sent'] }}</strong><em>En negociacion</em></span>
            <span class="fit-metric-icon"><i class="ri-send-plane-line"></i></span>
        </a>
        <a class="fit-metric-card green {{ $activeMetric === 'aceptada' ? 'active' : '' }}" href="{{ route($listRoute, ['status' => 'aceptada', 'sale_type' => $filters['sale_type'], 'search' => $filters['search']]) }}">
            <span><small>Aceptadas</small><strong>{{ $stats['accepted'] }}</strong><em>Ganadas</em></span>
            <span class="fit-metric-icon"><i class="ri-checkbox-circle-line"></i></span>
        </a>
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
            <label class="fit-select-control" for="status_filter">
                <i class="ri-checkbox-circle-line"></i>
                <select id="status_filter" name="status">
                    <option value="">Todos los estados</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="fit-primary-button compact"><i class="ri-search-line"></i><span>Buscar</span></button>
            @if($filters['search'] || $filters['sale_type'] || $filters['status'])
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
                            <th>Estado</th>
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
                                $itemsPayload = $quotation->items->map(function ($item) {
                                    return [
                                        'product' => $item->product->name ?? 'Producto',
                                        'sku' => $item->product->sku ?? '',
                                        'qty' => (int) $item->quantity,
                                        'price' => (float) $item->unit_price,
                                        'subtotal' => (float) $item->subtotal,
                                    ];
                                })->values();
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
                                <td><span class="fit-quotation-status {{ $quotation->status }}"><span></span> {{ $statusLabels[$quotation->status] ?? ucfirst($quotation->status) }}</span></td>
                                <td><strong class="fit-sale-amount">Bs {{ number_format((float) $quotation->total_amount, 2) }}</strong></td>
                                <td><span class="fit-muted-text">{{ optional($quotation->valid_until)->format('d/m/Y') }}</span></td>
                                <td class="text-right">
                                    <div class="fit-row-actions">
                                        <button type="button" class="fit-action-button success btn-vendor-quotation-detail" title="Ver detalles"
                                            data-id="{{ $quotation->id }}"
                                            data-client="{{ $clientName }}"
                                            data-type="{{ $saleTypeLabels[$quotation->sale_type] ?? $quotation->sale_type }}"
                                            data-status="{{ $statusLabels[$quotation->status] ?? $quotation->status }}"
                                            data-valid="{{ optional($quotation->valid_until)->format('d/m/Y') }}"
                                            data-total="Bs {{ number_format((float) $quotation->total_amount, 2) }}"
                                            data-notes="{{ $quotation->notes ?: 'Sin notas registradas.' }}"
                                            data-items='@json($itemsPayload)'>
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        <a class="fit-action-button warning" target="_blank" rel="noopener" href="{{ route($pdfRoute, $quotation) }}" title="Descargar PDF">
                                            <i class="ri-file-download-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align:center;padding:1rem;">No hay cotizaciones registradas para este vendedor.</td></tr>
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
                    <div class="fit-form-field">
                        <label for="quotation_status">Estado *</label>
                        <select id="quotation_status" name="status" required>
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', 'borrador') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<small style="color:#f87171">{{ $message }}</small>@enderror
                    </div>
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

    <div class="modal" id="vendorQuotationDetailModal">
        <div class="modal-content fit-modal-content fit-vendor-quotation-modal">
            <div class="modal-header">
                <h3>Detalle de cotizacion</h3>
                <button class="close-button" type="button" data-close-quotation-detail>&times;</button>
            </div>
            <div id="vendorQuotationSummary" class="fit-transfer-summary fit-quotation-summary"></div>
            <div class="fit-transfer-panel">
                <h4>Notas comerciales</h4>
                <p id="vendorQuotationNotes">Sin notas registradas.</p>
            </div>
            <div class="fit-transfer-panel">
                <h4>Productos</h4>
                <div id="vendorQuotationItems" class="vendor-quotation-detail-items"></div>
            </div>
            <div class="fit-modal-footer">
                <button type="button" class="fit-outline-button" data-close-quotation-detail>Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const createModal = document.getElementById('vendorQuotationCreateModal');
    const detailModal = document.getElementById('vendorQuotationDetailModal');
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

    document.querySelectorAll('.btn-vendor-quotation-detail').forEach((button) => {
        button.addEventListener('click', () => {
            let items = [];
            try {
                items = JSON.parse(button.dataset.items || '[]');
            } catch (error) {
                items = [];
            }

            document.getElementById('vendorQuotationSummary').innerHTML = `
                <div><span>ID</span><strong>#${button.dataset.id}</strong></div>
                <div><span>Cliente</span><strong>${button.dataset.client}</strong></div>
                <div><span>Tipo</span><strong>${button.dataset.type}</strong></div>
                <div><span>Estado</span><strong>${button.dataset.status}</strong></div>
                <div><span>Valido hasta</span><strong>${button.dataset.valid || '-'}</strong></div>
                <div><span>Total</span><strong>${button.dataset.total}</strong></div>
            `;
            document.getElementById('vendorQuotationNotes').textContent = button.dataset.notes || 'Sin notas registradas.';
            document.getElementById('vendorQuotationItems').innerHTML = items.length
                ? items.map((item) => `<div><strong>${item.product}</strong><span>${item.sku || 'N/D'} - ${item.qty} uds x Bs ${Number(item.price || 0).toFixed(2)} = Bs ${Number(item.subtotal || 0).toFixed(2)}</span></div>`).join('')
                : '<p class="fit-muted-text">Sin productos registrados.</p>';
            detailModal?.classList.add('active');
        });
    });

    document.querySelectorAll('[data-close-quotation-detail]').forEach((button) => button.addEventListener('click', () => detailModal?.classList.remove('active')));
    detailModal?.addEventListener('click', (event) => {
        if (event.target === detailModal) detailModal.classList.remove('active');
    });

    updateBuyerForm();
    addItemButton?.click();
    if (@json($errors->any())) {
        setCreateOpen(true);
    }
})();
</script>
@endpush
