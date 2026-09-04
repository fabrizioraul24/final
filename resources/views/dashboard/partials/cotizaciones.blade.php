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
    @endphp

    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    <section class="fit-metric-grid fit-metric-grid-three">
        <div class="fit-metric-card indigo">
            <span><small>Total cotizaciones</small><strong>{{ $stats['total'] }}</strong><em>Historico comercial</em></span>
            <span class="fit-metric-icon"><i class="ri-file-list-3-line"></i></span>
        </div>
        <div class="fit-metric-card blue">
            <span><small>Enviadas</small><strong>{{ $stats['sent'] }}</strong><em>En ruta</em></span>
            <span class="fit-metric-icon"><i class="ri-send-plane-line"></i></span>
        </div>
        <div class="fit-metric-card green">
            <span><small>Aceptadas</small><strong>{{ $stats['accepted'] }}</strong><em>Ganadas</em></span>
            <span class="fit-metric-icon"><i class="ri-checkbox-circle-line"></i></span>
        </div>
    </section>

    <div class="card">
        <div class="chart-head">
            <h4>Nueva cotización</h4>
        </div>
        <form method="POST" action="{{ route($storeRoute ?? 'dashboard.quotations.store') }}" id="quotationForm">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label for="quotation_sale_type">Tipo</label>
                    <select id="quotation_sale_type" name="sale_type" class="select-light" required>
                        @foreach($saleTypeLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('sale_type', 'empresa_institucional') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('sale_type')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="valid_until">Válido hasta</label>
                    <input type="date" id="valid_until" name="valid_until" class="input-ghost" value="{{ old('valid_until', now()->addWeek()->format('Y-m-d')) }}" required>
                    @error('valid_until')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="quotation_status">Estado</label>
                    <select id="quotation_status" name="status" class="select-light" required>
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', 'borrador') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="form-group" style="grid-column:1 / -1;">
                    <label for="notes">Notas</label>
                    <textarea id="notes" name="notes" class="input-ghost" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="form-grid" id="quotationCompanyField">
                <div class="form-group">
                    <label for="quotation_company_search">Buscar empresa / tienda</label>
                    <input type="text" id="quotation_company_search" class="input-ghost" data-filter-target="quotation_company_id" placeholder="Escribe para filtrar">
                </div>
                <div class="form-group" style="grid-column:1 / -1;">
                    <label for="quotation_company_id">Empresa / tienda</label>
                    <select id="quotation_company_id" name="company_id" class="select-light">
                        <option value="">Seleccionar</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" data-type="{{ $company->company_type }}" @selected(old('company_id') == $company->id)>
                                {{ $company->name }} · {{ $company->city }} ({{ $company->nit }})
                            </option>
                        @endforeach
                    </select>
                    @error('company_id')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="form-grid" id="quotationCustomerField" style="display:none;">
                <div class="form-group">
                    <label for="quotation_customer_search">Buscar comprador</label>
                    <input type="text" id="quotation_customer_search" class="input-ghost" data-filter-target="quotation_customer_id" placeholder="Nombre del comprador">
                </div>
                <div class="form-group" style="grid-column:1 / -1;">
                    <label for="quotation_customer_id">Comprador minorista</label>
                    <select id="quotation_customer_id" name="customer_id" class="select-light">
                        <option value="">Seleccionar</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                {{ $customer->user->name ?? 'Cliente' }} · {{ $customer->city }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="transfer-items-wrapper" style="margin-top:1.5rem;">
                <div class="chart-head">
                    <h4>Productos de la cotización</h4>
                    <button type="button" class="pill-button" id="addQuotationItem" data-lookup-url="{{ route($lookupRoute ?? 'dashboard.quotations.lookup') }}">
                        <i class="ri-add-line"></i>Agregar producto
                    </button>
                </div>
                <div id="quotationItems"></div>
                @error('items')<small style="color:#f87171">{{ $message }}</small>@enderror
                <div style="display:flex; justify-content:flex-end; margin-top:1rem;">
                    <span class="chip">Total estimado: <strong id="quotationTotal">Bs 0.00</strong></span>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:1.4rem;">
                <button type="submit" class="pill-button">Generar cotización</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Filtrar cotizaciones</h4>
        </div>
        <form method="GET" action="{{ route($listRoute ?? 'dashboard.quotations') }}" class="form-grid" data-live-search-form>
            <div class="form-group">
                <label for="search">Buscar por ID o cliente</label>
                <input type="text" id="search" name="search" class="input-ghost" value="{{ $filters['search'] }}" placeholder="Ej. 102 o Almacenes Central" data-live-search-input>
            </div>
            <div class="form-group">
                <label for="sale_type_filter">Tipo</label>
                <select id="sale_type_filter" name="sale_type" class="select-light">
                    <option value="">Todos</option>
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
                <a href="{{ route($listRoute ?? 'dashboard.quotations') }}" class="clean-link">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Cotizaciones recientes</h4>
            <span class="chip">{{ $quotations->total() }} registros</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Válido hasta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $quotation)
                        <tr>
                            <td>#{{ $quotation->id }}</td>
                            <td>
                                @if($quotation->company)
                                    <strong>{{ $quotation->company->name }}</strong><br>
                                    <small>{{ $quotation->company->city }}</small>
                                @elseif($quotation->customer)
                                    <strong>{{ $quotation->customer->user->name ?? 'Cliente' }}</strong><br>
                                    <small>{{ $quotation->customer->city }}</small>
                                @endif
                            </td>
                            <td>{{ $saleTypeLabels[$quotation->sale_type] ?? $quotation->sale_type }}</td>
                            <td>
                                <span class="status-pill {{ \Illuminate\Support\Str::slug($quotation->status, '_') }}">
                                    {{ $statusLabels[$quotation->status] ?? ucfirst($quotation->status) }}
                                </span>
                            </td>
                            <td>Bs {{ number_format($quotation->total_amount, 2) }}</td>
                            <td>{{ optional($quotation->valid_until)->format('d/m/Y') }}</td>
                            <td>
                                <a class="btn-secondary" target="_blank" rel="noopener" href="{{ route($pdfRoute ?? 'dashboard.quotations.pdf', $quotation) }}">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:1rem;">No hay cotizaciones registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $quotations->appends($filters)->links() }}
        </div>
    </div>

    @push('scripts')
    <script>
        (function() {
            const saleTypeSelect = document.getElementById('quotation_sale_type');
            const companyField = document.getElementById('quotationCompanyField');
            const customerField = document.getElementById('quotationCustomerField');
            const companySelect = document.getElementById('quotation_company_id');
            const customerSelect = document.getElementById('quotation_customer_id');
            const companySearch = document.getElementById('quotation_company_search');
            const customerSearch = document.getElementById('quotation_customer_search');

            function filterOptions(select, term, typeFilter = null) {
                term = (term || '').toLowerCase();
                select.querySelectorAll('option').forEach(option => {
                    if (!option.value) return;
                    const matchesTerm = option.textContent.toLowerCase().includes(term);
                    const matchesType = typeFilter ? option.dataset.type === typeFilter : true;
                    option.hidden = !(matchesTerm && matchesType);
                });
            }

            function updateBuyerForm() {
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
                    filterOptions(companySelect, companySearch.value, filterType);
                }
            }

            saleTypeSelect.addEventListener('change', updateBuyerForm);
            companySearch?.addEventListener('input', () => {
                const filterType = saleTypeSelect.value === 'tienda_barrio'
                    ? 'tienda_barrio'
                    : (saleTypeSelect.value === 'empresa_institucional' ? 'empresa_institucional' : null);
                filterOptions(companySelect, companySearch.value, filterType);
            });
            customerSearch?.addEventListener('input', () => filterOptions(customerSelect, customerSearch.value));

            updateBuyerForm();

            const itemsContainer = document.getElementById('quotationItems');
            const addItemButton = document.getElementById('addQuotationItem');
            const lookupUrl = addItemButton.dataset.lookupUrl;
            let itemIndex = 0;
            let totalAmount = 0;

            function createRow(index) {
                const wrapper = document.createElement('div');
                wrapper.className = 'transfer-item-row';
                wrapper.dataset.index = index;
                wrapper.innerHTML = `
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Código (SKU)</label>
                            <input type="text" class="input-ghost quotation-sku" placeholder="Ej. 120" data-index="${index}">
                            <input type="hidden" name="items[${index}][product_id]" class="product-id-input" required>
                        </div>
                        <div class="form-group">
                            <label>Producto</label>
                            <input type="text" class="input-ghost product-name" placeholder="Busca por código" readonly>
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
                    <button type="button" class="btn-danger remove-quotation-item" style="margin-top:0.8rem;">Quitar</button>
                `;
                return wrapper;
            }

            function recalcTotal() {
                totalAmount = 0;
                itemsContainer.querySelectorAll('.transfer-item-row').forEach(row => {
                    const qty = parseFloat(row.querySelector('.quantity-input')?.value || 0);
                    const price = parseFloat(row.querySelector('.unit-price-input')?.value || 0);
                    totalAmount += qty * price;
                });
                document.getElementById('quotationTotal').textContent = `Bs ${totalAmount.toFixed(2)}`;
            }

            function lookupProduct(row, sku) {
                const productInput = row.querySelector('.product-id-input');
                const nameInput = row.querySelector('.product-name');
                const qtyInput = row.querySelector('.quantity-input');
                const priceInput = row.querySelector('.unit-price-input');

                productInput.value = '';
                nameInput.value = 'Buscando...';

                const params = new URLSearchParams({
                    sku,
                    sale_type: saleTypeSelect.value,
                });

                fetch(`${lookupUrl}?${params.toString()}`)
                    .then(response => {
                        if (!response.ok) throw response;
                        return response.json();
                    })
                    .then(data => {
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
                        nameInput.value = message;
                        productInput.value = '';
                    });
            }

            addItemButton.addEventListener('click', () => {
                const row = createRow(itemIndex++);
                itemsContainer.appendChild(row);
            });

            itemsContainer.addEventListener('click', (event) => {
                if (event.target.classList.contains('remove-quotation-item')) {
                    event.target.closest('.transfer-item-row')?.remove();
                    recalcTotal();
                }
            });

            itemsContainer.addEventListener('blur', (event) => {
                if (event.target.classList.contains('quotation-sku')) {
                    const sku = event.target.value.trim();
                    if (!sku) return;
                    const row = event.target.closest('.transfer-item-row');
                    lookupProduct(row, sku);
                }
            }, true);

            itemsContainer.addEventListener('input', (event) => {
                if (event.target.classList.contains('quantity-input') || event.target.classList.contains('unit-price-input')) {
                    recalcTotal();
                }
            });

            addItemButton.click();
        })();
    </script>
    @endpush
