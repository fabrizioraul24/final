@extends('layouts.sidebar-vendedor')

@section('title', 'Crear venta | Vendedor')
@section('page-title', 'Nueva venta')

@php
    $saleTypeLabels = [
        'empresa_institucional' => 'Empresa institucional',
        'tienda_barrio' => 'Tienda de barrio',
        'comprador_minorista' => 'Comprador minorista',
    ];
    $statusLabels = [
        'sin_entregar' => 'Sin entregar',
        'entregado' => 'Entregado',
    ];
@endphp

@section('content')
<div class="vendor-sale-create-page">
    @if($errors->any())
        <div class="card">
            <span class="chip" style="color:#f87171;">Revisa los datos del formulario antes de registrar la venta.</span>
        </div>
    @endif

    <section class="fit-users-header">
        <div class="fit-users-header-left">
            <div class="fit-header-icon"><i class="ri-add-box-line"></i></div>
            <div>
                <h1>Crear Venta</h1>
                <p>Registra una venta desde tu cartera comercial sin mezclar datos del administrador.</p>
            </div>
        </div>
        <div class="fit-users-header-actions">
            <a href="{{ route($listRoute) }}" class="fit-outline-button">
                <i class="ri-arrow-left-line"></i>
                <span>Volver a ventas</span>
            </a>
        </div>
    </section>

    <form method="POST" action="{{ route($storeRoute) }}" id="vendorSaleForm" class="vendor-sale-create-shell">
        @csrf
        <input type="hidden" id="warehouse_id" name="warehouse_id" value="{{ $laPazWarehouse->id ?? '' }}">

        <section class="vendor-sale-create-main">
            <article class="vendor-sale-form-card">
                <div class="fit-section-head">
                    <div>
                        <h2>Datos de la venta</h2>
                        <p>Define tipo, estado, pago y almacen asignado.</p>
                    </div>
                    <span class="fit-section-badge green">Paso 1</span>
                </div>

                <div class="fit-form-grid vendor-sale-create-grid">
                    <div class="fit-form-field">
                        <label for="sale_type">Tipo de venta *</label>
                        <select id="sale_type" name="sale_type" required>
                            @foreach($saleTypeLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('sale_type', 'empresa_institucional') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('sale_type')<small style="color:#f87171">{{ $message }}</small>@enderror
                    </div>
                    <div class="fit-form-field">
                        <label for="sale_status">Estado *</label>
                        <select id="sale_status" name="status" required>
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', 'sin_entregar') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<small style="color:#f87171">{{ $message }}</small>@enderror
                    </div>
                    <div class="fit-form-field">
                        <label for="payment_method">Metodo de pago *</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Seleccionar</option>
                            @foreach($paymentLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('payment_method')<small style="color:#f87171">{{ $message }}</small>@enderror
                    </div>
                    <div class="fit-form-field">
                        <label>Almacen asignado</label>
                        <input type="text" value="{{ $laPazWarehouse ? $laPazWarehouse->name . ' (' . $laPazWarehouse->code . ')' : 'Configura almacen de La Paz' }}" disabled>
                        @error('warehouse_id')<small style="color:#f87171">{{ $message }}</small>@enderror
                    </div>
                </div>
            </article>

            <article class="vendor-sale-form-card">
                <div class="fit-section-head">
                    <div>
                        <h2>Cliente y entrega</h2>
                        <p>Selecciona cliente segun el tipo de venta y registra destino.</p>
                    </div>
                    <span class="fit-section-badge green">Paso 2</span>
                </div>

                <div class="fit-form-grid vendor-sale-create-grid" id="companyFieldset">
                    <div class="fit-form-field">
                        <label for="company_search">Buscar empresa/tienda</label>
                        <input type="text" id="company_search" placeholder="Nombre o NIT">
                    </div>
                    <div class="fit-form-field span-2">
                        <label for="company_id">Empresa / Tienda del vendedor</label>
                        <select id="company_id" name="company_id">
                            <option value="">Seleccionar</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" data-type="{{ $company->company_type }}" data-nit="{{ $company->nit }}" @selected(old('company_id') == $company->id)>
                                    {{ $company->name }} - {{ $company->city }} (NIT: {{ $company->nit }})
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')<small style="color:#f87171">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="fit-form-grid vendor-sale-create-grid" id="customerFieldset" style="display:none;">
                    <div class="fit-form-field">
                        <label for="customer_search">Buscar comprador</label>
                        <input type="text" id="customer_search" placeholder="NIT o nombre">
                    </div>
                    <div class="fit-form-field span-2">
                        <label for="customer_id">Comprador minorista</label>
                        <select id="customer_id" name="customer_id">
                            <option value="">Seleccionar</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" data-nit="{{ $customer->nit }}" @selected(old('customer_id') == $customer->id)>
                                    {{ $customer->user->name ?? 'Cliente' }} - {{ $customer->city }} @if($customer->nit) (NIT: {{ $customer->nit }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')<small style="color:#f87171">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="fit-form-grid vendor-sale-create-grid">
                    <div class="fit-form-field">
                        <label for="delivery_address">Direccion entrega</label>
                        <input type="text" id="delivery_address" name="delivery_address" value="{{ old('delivery_address') }}" placeholder="Calle / referencia">
                    </div>
                    <div class="fit-form-field">
                        <label for="delivery_city_id">Ciudad entrega *</label>
                        <select id="delivery_city_id" name="delivery_city_id" required>
                            <option value="">Seleccionar</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" @selected(old('delivery_city_id') == $city->id)>{{ $city->name }}</option>
                            @endforeach
                        </select>
                        @error('delivery_city_id')<small style="color:#f87171">{{ $message }}</small>@enderror
                    </div>
                </div>
            </article>

            <article class="vendor-sale-form-card vendor-sale-products-card">
                <div class="fit-section-head">
                    <div>
                        <h2>Productos de la venta</h2>
                        <p>Busca por SKU para traer precio y disponibilidad del almacen asignado.</p>
                    </div>
                    <button type="button" class="fit-outline-button" id="addSaleItem" data-lookup-url="{{ route($lookupRoute) }}">
                        <i class="ri-add-line"></i>
                        <span>Agregar producto</span>
                    </button>
                </div>
                <div id="saleItems" class="vendor-sale-items-list"></div>
                @error('items')<small style="color:#f87171">{{ $message }}</small>@enderror
            </article>
        </section>

        <aside class="vendor-sale-summary-card">
            <span>Resumen</span>
            <h2>Nueva venta</h2>
            <div class="vendor-sale-summary-row">
                <span>Total estimado</span>
                <strong id="saleTotal">Bs 0.00</strong>
            </div>
            <div class="vendor-sale-summary-row">
                <span>Almacen</span>
                <strong>{{ $laPazWarehouse?->code ?? 'N/D' }}</strong>
            </div>
            <div class="form-group" style="margin-top: 1rem;">
                <label for="audit_reason">Motivo para bitacora</label>
                <textarea id="audit_reason" name="audit_reason" rows="3" placeholder="Ej. Precio especial autorizado para este cliente">{{ old('audit_reason') }}</textarea>
                @error('audit_reason')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="vendor-sale-summary-actions">
                <button type="submit" class="fit-primary-button">
                    <i class="ri-save-line"></i>
                    <span>Registrar venta</span>
                </button>
                <a href="{{ route($listRoute) }}" class="fit-outline-button">Cancelar</a>
            </div>
        </aside>
    </form>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const saleTypeSelect = document.getElementById('sale_type');
    const companyField = document.getElementById('companyFieldset');
    const customerField = document.getElementById('customerFieldset');
    const companySelect = document.getElementById('company_id');
    const customerSelect = document.getElementById('customer_id');
    const companySearch = document.getElementById('company_search');
    const customerSearch = document.getElementById('customer_search');
    const saleItemsContainer = document.getElementById('saleItems');
    const addSaleItemBtn = document.getElementById('addSaleItem');
    const warehouseInput = document.getElementById('warehouse_id');
    const saleTotalLabel = document.getElementById('saleTotal');
    const lookupUrl = addSaleItemBtn?.dataset.lookupUrl;
    let saleItemIndex = 0;

    function filterSelect(select, term, typeFilter = null) {
        if (!select) return;
        const normalizedTerm = (term || '').toLowerCase();
        select.querySelectorAll('option').forEach((option) => {
            if (!option.value) return;
            const nit = (option.dataset.nit || '').toLowerCase();
            const matchesTerm = option.textContent.toLowerCase().includes(normalizedTerm) || nit.includes(normalizedTerm);
            const matchesType = typeFilter ? option.dataset.type === typeFilter : true;
            option.hidden = !(matchesTerm && matchesType);
        });
    }

    function updateBuyerFields() {
        const type = saleTypeSelect?.value;
        if (type === 'comprador_minorista') {
            companyField.style.display = 'none';
            customerField.style.display = '';
            if (companySelect) companySelect.value = '';
            return;
        }

        companyField.style.display = '';
        customerField.style.display = 'none';
        if (customerSelect) customerSelect.value = '';
        filterSelect(companySelect, companySearch?.value, type === 'tienda_barrio' ? 'tienda_barrio' : 'empresa_institucional');
    }

    function createItemRow(index) {
        const wrapper = document.createElement('div');
        wrapper.className = 'vendor-sale-item-row';
        wrapper.innerHTML = `
            <div class="fit-form-grid vendor-sale-product-grid">
                <div class="fit-form-field">
                    <label>SKU *</label>
                    <input type="text" class="sale-sku" placeholder="Ej. 133">
                    <input type="hidden" name="items[${index}][product_id]" class="product-id-input" required>
                </div>
                <div class="fit-form-field">
                    <label>Producto</label>
                    <input type="text" class="product-name" placeholder="Busca por SKU" readonly>
                </div>
                <div class="fit-form-field">
                    <label>Disponible</label>
                    <input type="text" class="available-qty" readonly>
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
            <button type="button" class="fit-action-button danger remove-item" title="Quitar producto"><i class="ri-delete-bin-line"></i></button>
        `;
        return wrapper;
    }

    function updateTotal() {
        let total = 0;
        saleItemsContainer?.querySelectorAll('.vendor-sale-item-row').forEach((row) => {
            const quantity = parseFloat(row.querySelector('.quantity-input')?.value || 0);
            const price = parseFloat(row.querySelector('.unit-price-input')?.value || 0);
            total += quantity * price;
        });
        if (saleTotalLabel) saleTotalLabel.textContent = `Bs ${total.toFixed(2)}`;
    }

    function lookupProduct(row, sku) {
        if (!lookupUrl || !warehouseInput?.value) {
            alert('Configura el almacen de La Paz antes de agregar productos.');
            return;
        }

        const productInput = row.querySelector('.product-id-input');
        const nameInput = row.querySelector('.product-name');
        const availableInput = row.querySelector('.available-qty');
        const quantityInput = row.querySelector('.quantity-input');
        const priceInput = row.querySelector('.unit-price-input');
        nameInput.value = 'Buscando...';

        const params = new URLSearchParams({
            sku,
            sale_type: saleTypeSelect?.value || 'empresa_institucional',
            warehouse_id: warehouseInput.value,
        });

        fetch(`${lookupUrl}?${params.toString()}`)
            .then((response) => {
                if (!response.ok) throw response;
                return response.json();
            })
            .then((data) => {
                productInput.value = data.product_id;
                const available = data.available_quantity ?? 0;
                nameInput.value = `${data.name} (${data.sku})`;
                availableInput.value = available > 0 ? `${available} uds` : 'Fuera de stock';
                priceInput.value = data.price ?? 0;
                if (!quantityInput.value) quantityInput.value = available > 0 ? 1 : '';
                updateTotal();
            })
            .catch(async (error) => {
                let message = 'No pudimos encontrar el producto.';
                if (error.json) {
                    const payload = await error.json();
                    if (payload?.message) message = payload.message;
                }
                productInput.value = '';
                nameInput.value = message;
                availableInput.value = 'Fuera de stock';
                quantityInput.value = '';
                updateTotal();
            });
    }

    saleTypeSelect?.addEventListener('change', updateBuyerFields);
    companySearch?.addEventListener('input', () => filterSelect(companySelect, companySearch.value, saleTypeSelect.value === 'tienda_barrio' ? 'tienda_barrio' : 'empresa_institucional'));
    customerSearch?.addEventListener('input', () => filterSelect(customerSelect, customerSearch.value));

    addSaleItemBtn?.addEventListener('click', () => {
        if (!warehouseInput?.value) {
            alert('Configura el almacen de La Paz antes de agregar productos.');
            return;
        }
        saleItemsContainer?.appendChild(createItemRow(saleItemIndex++));
    });

    saleItemsContainer?.addEventListener('click', (event) => {
        const removeButton = event.target.closest('.remove-item');
        if (!removeButton) return;
        removeButton.closest('.vendor-sale-item-row')?.remove();
        updateTotal();
    });

    saleItemsContainer?.addEventListener('blur', (event) => {
        if (!event.target.classList.contains('sale-sku')) return;
        const sku = event.target.value.trim();
        if (!sku) return;
        lookupProduct(event.target.closest('.vendor-sale-item-row'), sku);
    }, true);

    saleItemsContainer?.addEventListener('input', (event) => {
        if (event.target.classList.contains('quantity-input') || event.target.classList.contains('unit-price-input')) {
            updateTotal();
        }
    });

    updateBuyerFields();
    addSaleItemBtn?.click();
})();
</script>
@endpush
