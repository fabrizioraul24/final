@extends(request()->routeIs('dashboard.vendedor.*') ? 'layouts.sidebar-vendedor' : 'layouts.sidebar')

@section('title', 'Ventas | Pil Andina')
@section('page-title', 'Gestión de ventas')

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
    $paymentLabels = $paymentLabels ?? [
        'efectivo' => 'Efectivo',
        'qr' => 'QR',
        'tarjeta_debito' => 'Tarjeta de débito',
    ];
@endphp

@section('title', 'Ventas | Pil Andina')
@section('page-title', 'Gestión de ventas')

@section('content')
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    <div class="stats-grid">
        <div class="card">
            <h3>Ventas registradas</h3>
            <div class="value">{{ $stats['count'] }}</div>
            <span class="chip text-white/70"><i class="ri-bar-chart-grouped-line"></i>Total histórico</span>
        </div>
        <div class="card">
            <h3>Ventas entregadas</h3>
            <div class="value">{{ $stats['delivered'] }}</div>
            <span class="chip text-green-300"><i class="ri-check-double-line"></i>Completadas</span>
        </div>
        <div class="card">
            <h3>Monto total</h3>
            <div class="value">Bs {{ number_format($stats['total_amount'], 2) }}</div>
            <span class="chip text-white/70"><i class="ri-money-dollar-circle-line"></i>HistÃ³rico</span>
        </div>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Nueva venta</h4>
        </div>
        <form method="POST" action="{{ route($storeRoute ?? 'dashboard.sales.store') }}" id="saleForm">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label for="sale_type">Tipo de venta</label>
                    <select id="sale_type" name="sale_type" class="select-light" required>
                        @foreach($saleTypeLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('sale_type', 'empresa_institucional') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('sale_type')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="sale_status">Estado de la venta</label>
                    <select id="sale_status" name="status" class="select-light" required>
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', 'sin_entregar') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <input type="hidden" id="warehouse_id" name="warehouse_id" value="{{ $laPazWarehouse->id ?? '' }}">
                <div class="form-group" style="grid-column:1 / -1;">
                    <label>Almacén asignado</label>
                    <input type="text" class="input-ghost" value="{{ $laPazWarehouse ? $laPazWarehouse->name . ' (' . $laPazWarehouse->code . ')' : 'Configura el almacén de La Paz para permitir ventas' }}" disabled>
                    @if(!$laPazWarehouse)
                        <small style="color:#fbbf24;">Sin almacén de La Paz no se podrá registrar stock ni ventas.</small>
                    @endif
                    @error('warehouse_id')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="payment_method">Método de pago</label>
                    <select id="payment_method" name="payment_method" class="select-light" required>
                        <option value="">Seleccionar</option>
                        @foreach($paymentLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('payment_method')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="delivery_address">Dirección entrega</label>
                    <input type="text" id="delivery_address" name="delivery_address" class="input-ghost" value="{{ old('delivery_address') }}" placeholder="Calle / referencia">
                </div>
                <div class="form-group">
                    <label for="delivery_city_id">Ciudad entrega</label>
                    <select id="delivery_city_id" name="delivery_city_id" class="select-light" required>
                        <option value="">Seleccionar</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" @selected(old('delivery_city_id') == $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                    @error('delivery_city_id')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="form-grid" id="companyFieldset">
                <div class="form-group">
                    <label for="company_search">Buscar empresa/tienda</label>
                    <input type="text" id="company_search" class="input-ghost" placeholder="Nombre o NIT" data-filter-target="company_id">
                </div>
                <div class="form-group" style="grid-column:1 / -1;">
                    <label for="company_id">Empresa / Tienda</label>
                    <select id="company_id" name="company_id" class="select-light">
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

            <div class="form-grid" id="customerFieldset" style="display:none;">
                <div class="form-group">
                    <label for="customer_search">Buscar comprador (NIT)</label>
                    <input type="text" id="customer_search" class="input-ghost" placeholder="Ingresa NIT o nombre" data-filter-target="customer_id">
                </div>
                <div class="form-group" style="grid-column:1 / -1;">
                    <label for="customer_id">Comprador minorista</label>
                    <select id="customer_id" name="customer_id" class="select-light">
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

            <div class="transfer-items-wrapper" style="margin-top:1.5rem;">
                <div class="chart-head">
                    <h4>Productos de la venta</h4>
                    <button type="button" class="pill-button" id="addSaleItem" data-lookup-url="{{ route($lookupRoute ?? 'dashboard.sales.lookup') }}">
                        <i class="ri-add-line"></i>Agregar producto
                    </button>
                </div>
                <p class="text-white/70" style="margin-bottom:1rem;">Ingresa el código (SKU) para obtener el precio sugerido y la disponibilidad del almacén seleccionado.</p>
                <div id="saleItems"></div>
                @error('items')<small style="color:#f87171">{{ $message }}</small>@enderror
                <div style="display:flex; justify-content:flex-end; margin-top:1rem;">
                    <span class="chip">Total estimado: <strong id="saleTotal">Bs 0.00</strong></span>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:1.5rem;">
                <button type="submit" class="pill-button">Registrar venta</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Filtrar ventas</h4>
        </div>
        <form method="GET" action="{{ route($listRoute ?? 'dashboard.sales') }}" class="form-grid" data-live-search-form>
            <div class="form-group">
                <label for="search">Buscar por ID o cliente</label>
                <input type="text" id="search" name="search" class="input-ghost" value="{{ $filters['search'] }}" placeholder="ID, empresa o comprador" data-live-search-input>
            </div>
            <div class="form-group">
                <label for="sale_type_filter">Tipo de venta</label>
                <select id="sale_type_filter" name="sale_type" class="select-light">
                    <option value="">Todas</option>
                    @foreach($saleTypeLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['sale_type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="status_filter">Estado</label>
                <select id="status_filter" name="status" class="select-light">
                    <option value="">Todos</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <a href="{{ route($listRoute ?? 'dashboard.sales') }}" class="clean-link">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Ventas recientes</h4>
            <span class="chip">{{ $sales->total() }} registros</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Pago</th>
                        <th>Monto</th>
                        <th>Almacen</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>#{{ $sale->id }}</td>
                            <td>
                                @if($sale->company)
                                    <strong>{{ $sale->company->name }}</strong><br>
                                    <small>{{ $sale->company->city }}</small>
                                @elseif($sale->customer)
                                    <strong>{{ $sale->customer->user->name ?? 'Cliente' }}</strong><br>
                                    <small>{{ $sale->customer->city }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $saleTypeLabels[$sale->sale_type] ?? $sale->sale_type }}</td>
                            <td>
                                <span class="status-pill {{ \Illuminate\Support\Str::slug($sale->status, '_') }}">
                                    {{ $statusLabels[$sale->status] ?? ucfirst($sale->status) }}
                                </span>
                            </td>
                            <td>{{ $paymentLabels[$sale->payment_method] ?? 'Sin método' }}</td>
                            <td>Bs {{ number_format($sale->total_amount, 2) }}</td>
                            <td>{{ $sale->warehouse->name ?? '-' }}</td>
                            <td>{{ optional($sale->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                @php
                                    $itemsPayload = $sale->items->map(function($item) {
                                        return [
                                            'product' => $item->product->name ?? 'Producto',
                                            'sku' => $item->product->sku ?? '',
                                            'qty' => $item->quantity,
                                            'price' => $item->unit_price,
                                        ];
                                    })->values();
                                @endphp
                                <button type="button"
                                        class="pill-button ghost btn-sale-detail"
                                        data-sale-id="{{ $sale->id }}"
                                        data-customer="{{ $sale->company->name ?? $sale->customer->user->name ?? 'Sin cliente' }}"
                                        data-type="{{ $sale->sale_type }}"
                                        data-status="{{ $sale->status }}"
                                        data-payment="{{ $paymentLabels[$sale->payment_method] ?? 'Sin método' }}"
                                        data-warehouse="{{ $sale->warehouse->name ?? 'Sin almacen' }}"
                                        data-total="Bs {{ number_format($sale->total_amount, 2) }}"
                                        data-items='@json($itemsPayload)'>
                                    Ver
                                </button>
                                <button type="button"
                                        class="btn-secondary btn-sale-update"
                                        data-update-url="{{ route($updateRoute ?? 'dashboard.sales.update', $sale) }}"
                                        data-status="{{ $sale->status }}">
                                    Actualizar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:1rem;">No hay ventas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $sales->appends($filters)->links() }}
        </div>
    </div>

    <div class="modal" id="saleDetailModal" style="display:none;">
        <div class="modal-content" style="max-width:800px;">
            <div class="modal-header">
                <h3>Detalle de venta</h3>
                <button class="close-button" type="button" id="closeSaleDetail">&times;</button>
            </div>
            <div id="saleDetailBody" style="display:grid; gap:1rem;">
                <div id="saleSummary"></div>
                <div id="saleItemsList"></div>
            </div>
        </div>
    </div>

    @include('dashboard.partials.sale-status-modal', ['statusLabels' => $statusLabels, 'paymentLabels' => $paymentLabels])
@endsection

@push('scripts')
<script>
    (function() {
        const saleTypeSelect = document.getElementById('sale_type');
        const companyField = document.getElementById('companyFieldset');
        const customerField = document.getElementById('customerFieldset');
        const companySelect = document.getElementById('company_id');
        const customerSelect = document.getElementById('customer_id');
        const companySearch = document.getElementById('company_search');
    const customerSearch = document.getElementById('customer_search');
    const saleDetailButtons = document.querySelectorAll('.btn-sale-detail');
    const saleDetailModal = document.getElementById('saleDetailModal');
    const saleSummary = document.getElementById('saleSummary');
    const saleItemsList = document.getElementById('saleItemsList');
    const closeSaleDetail = document.getElementById('closeSaleDetail');

    function filterSelect(select, term, typeFilter = null) {
        term = (term || '').toLowerCase();
        select.querySelectorAll('option').forEach(option => {
                if (!option.value) return;
                const nit = (option.dataset.nit || '').toLowerCase();
                const matchesTerm = option.textContent.toLowerCase().includes(term) || nit.includes(term);
                const matchesType = typeFilter ? option.dataset.type === typeFilter : true;
                option.hidden = !(matchesTerm && matchesType);
            });
        }

    function updateBuyerFields() {
            const type = saleTypeSelect.value;
            if (type === 'comprador_minorista') {
                companyField.style.display = 'none';
                customerField.style.display = '';
                companySelect.value = '';
            } else {
                companyField.style.display = '';
                customerField.style.display = 'none';
                customerSelect.value = '';

                const filterType = type === 'tienda_barrio' ? 'tienda_barrio' : 'empresa_institucional';
                filterSelect(companySelect, companySearch.value, type === 'tienda_barrio' ? 'tienda_barrio' : null);

                companySelect.querySelectorAll('option').forEach(option => {
                    if (!option.value) return;
                    option.hidden = false;
                    if (type === 'tienda_barrio' && option.dataset.type !== 'tienda_barrio') {
                        option.hidden = true;
                    }
                    if (type === 'empresa_institucional' && option.dataset.type !== 'empresa_institucional') {
                        option.hidden = true;
                    }
                });
            }
        }

        saleTypeSelect.addEventListener('change', updateBuyerFields);

        if (companySearch) {
            companySearch.addEventListener('input', () => {
                filterSelect(companySelect, companySearch.value, saleTypeSelect.value === 'tienda_barrio' ? 'tienda_barrio' : null);
            });
        }
        if (customerSearch) {
            customerSearch.addEventListener('input', () => filterSelect(customerSelect, customerSearch.value));
        }

    updateBuyerFields();
    function renderSaleDetail(btn) {
        const customer = btn.dataset.customer || 'Sin cliente';
        const type = btn.dataset.type || '';
        const status = btn.dataset.status || '';
        const payment = btn.dataset.payment || '';
        const warehouse = btn.dataset.warehouse || '';
        const total = btn.dataset.total || '';
        let items = [];
        try {
            items = JSON.parse(btn.dataset.items || '[]');
        } catch (e) {
            items = [];
        }

        saleSummary.innerHTML = `
            <p style="margin:0;"><strong>Cliente:</strong> ${customer}</p>
            <p style="margin:0;"><strong>Tipo:</strong> ${type}</p>
            <p style="margin:0;"><strong>Estado:</strong> ${status}</p>
            <p style="margin:0;"><strong>Pago:</strong> ${payment}</p>
            <p style="margin:0;"><strong>Almacen:</strong> ${warehouse}</p>
            <p style="margin:0;"><strong>Total:</strong> ${total}</p>
        `;

        saleItemsList.innerHTML = '';
        items.forEach(item => {
            const row = document.createElement('div');
            row.style.border = '1px solid rgba(255,255,255,0.12)';
            row.style.borderRadius = '0.75rem';
            row.style.padding = '0.75rem 1rem';
            row.innerHTML = `
                <p style="margin:0;"><strong>${item.product}</strong> (${item.sku})</p>
                <p style="margin:0.2rem 0 0;">Cantidad: ${item.qty}</p>
                <p style="margin:0.1rem 0 0;">Precio: Bs ${parseFloat(item.price || 0).toFixed(2)}</p>
            `;
            saleItemsList.appendChild(row);
        });

        saleDetailModal.style.display = 'flex';
    }

    saleDetailButtons.forEach((btn) => {
        btn.addEventListener('click', () => renderSaleDetail(btn));
    });

    closeSaleDetail?.addEventListener('click', () => {
        saleDetailModal.style.display = 'none';
    });

    saleDetailModal?.addEventListener('click', (event) => {
        if (event.target === saleDetailModal) {
            saleDetailModal.style.display = 'none';
        }
    });

        const saleItemsContainer = document.getElementById('saleItems');
        const addSaleItemBtn = document.getElementById('addSaleItem');
        const lookupUrl = addSaleItemBtn.dataset.lookupUrl;
        const warehouseSelect = document.getElementById('warehouse_id');
        const amountReceivedInput = document.getElementById('amount_received');
        const changeAmountInput = document.getElementById('change_amount');
        const saleTotalLabel = document.getElementById('saleTotal');
        let saleItemIndex = 0;
        let currentSaleTotal = 0;

        function createItemRow(index) {
            const wrapper = document.createElement('div');
            wrapper.className = 'transfer-item-row';
            wrapper.dataset.index = index;
            wrapper.innerHTML = `
                <div class="form-grid">
                    <div class="form-group">
                        <label>Código (SKU)</label>
                        <input type="text" class="input-ghost sale-sku" placeholder="Ej. 133" data-index="${index}">
                        <input type="hidden" name="items[${index}][product_id]" class="product-id-input" required>
                    </div>
                    <div class="form-group">
                        <label>Producto</label>
                        <input type="text" class="input-ghost product-name" placeholder="Busca por código" readonly>
                    </div>
                    <div class="form-group">
                        <label>Disponible</label>
                        <input type="text" class="input-ghost available-qty" placeholder="0" readonly>
                    </div>
                    <div class="form-group">
                        <label>Cantidad</label>
                        <input type="number" min="1" class="input-ghost quantity-input" name="items[${index}][quantity]" required>
                    </div>
                    <div class="form-group">
                        <label>Precio unitario</label>
                        <input type="number" min="0" step="0.01" class="input-ghost unit-price-input" name="items[${index}][unit_price]" required>
                    </div>
                </div>
                <button type="button" class="btn-danger remove-item" style="margin-top:0.8rem;">Quitar</button>
            `;
            return wrapper;
        }

        function updateTotal() {
            let total = 0;
            saleItemsContainer.querySelectorAll('.transfer-item-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('.quantity-input')?.value || 0);
                const price = parseFloat(row.querySelector('.unit-price-input')?.value || 0);
                total += quantity * price;
            });
            currentSaleTotal = total;
            saleTotalLabel.textContent = `Bs ${total.toFixed(2)}`;
            updateChangeField();
        }

        function updateChangeField(forceManualReset = false) {
            if (!amountReceivedInput || !changeAmountInput) return;
            const amount = parseFloat(amountReceivedInput.value);
            if (isNaN(amount)) return;
            if (changeAmountInput.dataset.manual === 'true' && !forceManualReset) return;
            const change = Math.max(amount - currentSaleTotal, 0);
            changeAmountInput.value = change.toFixed(2);
        }

        function handleSaleLookup(row, sku) {
            const productInput = row.querySelector('.product-id-input');
            const nameInput = row.querySelector('.product-name');
            const availableInput = row.querySelector('.available-qty');
            const quantityInput = row.querySelector('.quantity-input');
            const priceInput = row.querySelector('.unit-price-input');

            productInput.value = '';
            nameInput.value = 'Buscando...';
            availableInput.value = '';

            const warehouseId = warehouseSelect.value;
            if (!warehouseId) {
                nameInput.value = 'Selecciona un almacén primero';
                return;
            }

            const params = new URLSearchParams({
                sku,
                sale_type: saleTypeSelect.value,
                warehouse_id: warehouseId
            });

            fetch(`${lookupUrl}?${params.toString()}`)
                .then(response => {
                    if (!response.ok) throw response;
                    return response.json();
                })
                .then(data => {
                    productInput.value = data.product_id;
                    const available = data.available_quantity ?? 0;
                    nameInput.value = `${data.name} (${data.sku})`;
                    availableInput.value = available > 0 ? available + ' uds' : 'Fuera de stock';
                    priceInput.value = data.price ?? 0;
                    if (!quantityInput.value) {
                        quantityInput.value = available > 0 ? 1 : '';
                    }
                    updateTotal();
                })
                .catch(async (error) => {
                    let message = 'No pudimos encontrar el producto.';
                    if (error.json) {
                        const payload = await error.json();
                        if (payload?.message) message = payload.message;
                    }
                    nameInput.value = message;
                    availableInput.value = 'Fuera de stock';
                    quantityInput.value = '';
                    productInput.value = '';
                });
        }

        addSaleItemBtn.addEventListener('click', () => {
            if (!warehouseSelect?.value) {
                alert('Configura el almacén de La Paz antes de agregar productos.');
                return;
            }
            const row = createItemRow(saleItemIndex++);
            saleItemsContainer.appendChild(row);
        });

        saleItemsContainer.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-item')) {
                event.target.closest('.transfer-item-row')?.remove();
                updateTotal();
            }
        });

        saleItemsContainer.addEventListener('blur', (event) => {
            if (event.target.classList.contains('sale-sku')) {
                const sku = event.target.value.trim();
                if (!sku) return;
                const row = event.target.closest('.transfer-item-row');
                handleSaleLookup(row, sku);
            }
        }, true);

        saleItemsContainer.addEventListener('input', (event) => {
            if (event.target.classList.contains('quantity-input') || event.target.classList.contains('unit-price-input')) {
                updateTotal();
            }
        });

        addSaleItemBtn.click();

        amountReceivedInput?.addEventListener('input', () => {
            changeAmountInput.dataset.manual = '';
            updateChangeField(true);
        });

        changeAmountInput?.addEventListener('input', () => {
            changeAmountInput.dataset.manual = changeAmountInput.value !== '' ? 'true' : '';
        });
    })();
</script>
@endpush

