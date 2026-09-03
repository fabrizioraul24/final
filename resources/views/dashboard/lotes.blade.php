@extends('layouts.sidebar')

@section('title', 'Lotes | Pil Andina')
@section('page-title', 'Gestion de Lotes (FEFO)')

@section('content')
    <style>
        .lot-product-stack {
            display: grid;
            gap: 1rem;
        }

        .lot-product-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 210px;
            gap: 1.25rem;
            align-items: stretch;
        }

        .lot-product-main {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .lot-product-head {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .lot-product-cover {
            width: 82px;
            height: 82px;
            border-radius: 1.35rem;
            object-fit: cover;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.18);
        }

        .lot-product-title {
            margin: 0;
            font-size: 1.35rem;
            line-height: 1.15;
        }

        .lot-product-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.45rem;
        }

        .lot-product-separator {
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
        }

        .lot-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(120px, 1fr));
            gap: 1rem;
        }

        .lot-stat-box small {
            display: block;
            margin-bottom: 0.35rem;
            color: rgba(255, 255, 255, 0.62);
            font-size: 0.76rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .lot-stat-box strong {
            font-size: 1.15rem;
        }

        .lot-inline-alert {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.15rem;
            border-radius: 1.2rem;
            border: 1px solid rgba(248, 113, 113, 0.25);
            background: rgba(248, 113, 113, 0.09);
        }

        .lot-inline-alert strong {
            color: #fecaca;
        }

        .lot-action-panel {
            border-left: 1px solid rgba(255, 255, 255, 0.08);
            padding-left: 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 0.9rem;
        }

        .lot-action-button {
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 1rem;
            padding: 0.95rem 1.1rem;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.18);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .lot-action-button.view {
            background: rgba(78, 107, 175, 0.28);
        }

        .lot-action-button.restock {
            background: rgba(255, 255, 255, 0.1);
        }

        .lot-action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 30px rgba(0, 0, 0, 0.24);
            background: rgba(255, 255, 255, 0.18);
        }

        .lot-filter-bar {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .lot-detail-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 1rem;
            align-items: start;
        }

        .lot-detail-layout > * {
            min-width: 0;
        }

        .lot-detail-column {
            display: grid;
            gap: 1rem;
            min-width: 0;
        }

        .lot-detail-panel {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.4rem;
            padding: 1.1rem;
            min-width: 0;
            overflow: hidden;
        }

        .lot-detail-panel h4 {
            margin: 0 0 1rem;
        }

        .lot-detail-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .lot-detail-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            width: 180px;
            flex-shrink: 0;
        }

        .lot-modal-button {
            width: 100%;
            justify-content: center;
        }

        .lot-history-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .lot-history-table th,
        .lot-history-table td {
            padding: 0.8rem 0.6rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-align: left;
            font-size: 0.9rem;
            vertical-align: middle;
            overflow-wrap: anywhere;
        }

        .lot-history-table th {
            color: rgba(255, 255, 255, 0.62);
            text-transform: uppercase;
            font-size: 0.74rem;
        }

        .lot-movement-list {
            display: grid;
            gap: 0.75rem;
        }

        .lot-movement-item {
            border-radius: 1rem;
            padding: 0.9rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .lot-movement-item strong {
            display: block;
            margin-bottom: 0.2rem;
        }

        .lot-detail-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .lot-detail-grid .lot-stat-box strong {
            display: block;
            line-height: 1.15;
        }

        .lot-history-scroll {
            overflow-x: auto;
        }

        .lot-empty-state {
            margin: 0;
            color: rgba(255, 255, 255, 0.72);
        }

        @media (max-width: 1080px) {
            .lot-product-card,
            .lot-detail-layout {
                grid-template-columns: 1fr;
            }

            .lot-action-panel {
                border-left: 0;
                border-top: 1px solid rgba(255, 255, 255, 0.08);
                padding-left: 0;
                padding-top: 1rem;
            }

            .lot-stat-grid,
            .lot-filter-bar,
            .lot-detail-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .lot-detail-header {
                flex-direction: column;
            }

            .lot-detail-actions {
                width: 100%;
                flex-direction: row;
            }
        }

        @media (max-width: 720px) {
            .lot-stat-grid,
            .lot-filter-bar,
            .lot-detail-grid {
                grid-template-columns: 1fr;
            }

            .lot-inline-alert {
                flex-direction: column;
                align-items: flex-start;
            }

            .lot-detail-actions {
                flex-direction: column;
            }
        }
    </style>

    @if(session('status'))
        <div class="card"><span class="chip">{{ session('status') }}</span></div>
    @endif

    <div class="modal" id="lotErrorModal" style="display:none;">
        <div class="modal-content" style="max-width:520px;">
            <div class="modal-header">
                <h3>Atencion</h3>
                <button class="close-button" type="button" id="closeLotError">&times;</button>
            </div>
            <p id="lotErrorMessage" style="margin:0.5rem 0 0;">No puedes ingresar esa cantidad porque se excede el maximo configurado.</p>
            <div style="margin-top:1rem; display:flex; justify-content:flex-end;">
                <button type="button" class="pill-button" id="dismissLotError">Entendido</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Crear lote</h4>
            <span class="chip text-white/70">Prueba 1 · Vista por producto</span>
        </div>
        <form method="POST" action="{{ route('dashboard.lots.store') }}" class="form-grid" id="lotCreateForm">
            @csrf
            <div class="form-group">
                <label>Producto</label>
                <select name="product_id" id="lot_create_product" class="select-light" required>
                    <option value="">Seleccionar</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }} ({{ $product->sku }})</option>
                    @endforeach
                </select>
                @error('product_id')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label>Bodega</label>
                <select name="warehouse_id" class="select-light" required>
                    <option value="">Seleccionar</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" @selected(old('warehouse_id', $filters['warehouse_id'] ?? null) == $w->id)>{{ $w->name }}</option>
                    @endforeach
                </select>
                @error('warehouse_id')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label>Codigo de lote</label>
                <input type="text" name="lote_code" class="input-ghost" placeholder="Opcional" value="{{ old('lote_code') }}">
            </div>
            <div class="form-group">
                <label>Cantidad</label>
                <input type="number" min="1" name="quantity" class="input-ghost" value="{{ old('quantity') }}" required>
                @error('quantity')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label>Fecha expiracion</label>
                <input type="date" name="expires_at" class="input-ghost" value="{{ old('expires_at') }}" required>
                @error('expires_at')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <button type="submit" class="pill-button">Guardar lote</button>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top:1rem;">
        <div class="chart-head">
            <h4>Explorar lotes por producto</h4>
            <span class="chip">{{ $productsWithLots->total() }} productos con lotes</span>
        </div>
        <form method="GET" action="{{ route('dashboard.lots') }}" class="lot-filter-bar" style="margin-top:1rem;" data-live-search-form>
            <div class="form-group">
                <label for="filter_search">Buscar producto</label>
                <input type="text" id="filter_search" name="search" class="input-ghost" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre, SKU o descripcion" data-live-search-input>
            </div>
            <div class="form-group">
                <label for="filter_product_id">Producto</label>
                <select id="filter_product_id" name="product_id" class="select-light">
                    <option value="">Todos</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(($filters['product_id'] ?? null) == $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="filter_warehouse_id">Bodega</label>
                <select id="filter_warehouse_id" name="warehouse_id" class="select-light">
                    <option value="">Todas</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(($filters['warehouse_id'] ?? null) == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="filter_expires_at">Vence el</label>
                <input type="date" id="filter_expires_at" name="expires_at" class="input-ghost" value="{{ $filters['expires_at'] ?? '' }}">
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <a href="{{ route('dashboard.lots.report', $filters) }}" class="pill-button" target="_blank" rel="noopener" data-live-report-link>Generar reporte PDF</a>
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <a href="{{ route('dashboard.lots') }}" class="clean-link">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="lot-product-stack" style="margin-top:1rem;">
        @forelse($productsWithLots as $product)
            @php
                $currentStock = (int) ($product->current_stock ?? 0);
                $minimumStock = (int) ($product->min_quantity ?? 0);
                $isCritical = $minimumStock > 0 && $currentStock <= $minimumStock;
                $nextExpiry = $product->next_expiry ? \Carbon\Carbon::parse($product->next_expiry)->format('d/m/Y') : 'Sin fecha';
                $productPayload = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category->name ?? 'Sin categoria',
                    'description' => $product->description ?: 'Sin descripcion registrada.',
                    'image' => $product->getImageUrl(),
                    'current_stock' => $currentStock,
                    'minimum_stock' => $minimumStock,
                    'lots_count' => (int) ($product->lots_count ?? 0),
                    'next_expiry' => $nextExpiry,
                    'history_rows' => $product->history_rows,
                    'movement_history' => $product->movement_history,
                ];
            @endphp
            <div class="card lot-product-card">
                <div class="lot-product-main">
                    <div class="lot-product-head">
                        <img src="{{ $product->getImageUrl() }}" alt="{{ $product->name }}" class="lot-product-cover">
                        <div>
                            <h3 class="lot-product-title">{{ $product->name }}</h3>
                            <div class="lot-product-meta">
                                <span class="chip">SKU: {{ $product->sku }}</span>
                                <span class="chip">{{ $product->category->name ?? 'Sin categoria' }}</span>
                                <span class="chip">{{ $product->lots_count }} lote{{ $product->lots_count === 1 ? '' : 's' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="lot-product-separator"></div>

                    <div class="lot-stat-grid">
                        <div class="lot-stat-box">
                            <small>Stock actual</small>
                            <strong>{{ $currentStock }} unidades</strong>
                        </div>
                        <div class="lot-stat-box">
                            <small>Stock minimo</small>
                            <strong>{{ $minimumStock ?: 'No definido' }}</strong>
                        </div>
                        <div class="lot-stat-box">
                            <small>Total lotes</small>
                            <strong>{{ $product->lots_count }}</strong>
                        </div>
                        <div class="lot-stat-box">
                            <small>Proximo vencimiento</small>
                            <strong>{{ $nextExpiry }}</strong>
                        </div>
                    </div>

                    @if($isCritical)
                        <div class="lot-inline-alert">
                            <div>
                                <strong>Atencion: este producto necesita reabastecimiento.</strong>
                                <div style="color:rgba(255,255,255,0.76); margin-top:0.2rem;">El stock actual alcanzo o bajo del minimo configurado.</div>
                            </div>
                            <button type="button" class="pill-button btn-restock-product" data-product-id="{{ $product->id }}">Agregar nuevo stock</button>
                        </div>
                    @endif
                </div>

                <div class="lot-action-panel">
                    <button type="button" class="lot-action-button view btn-view-product-lots" data-product='@json($productPayload)'>
                        <i class="ri-stack-line"></i> Ver lotes
                    </button>
                    <button type="button" class="lot-action-button restock btn-restock-product" data-product-id="{{ $product->id }}">
                        <i class="ri-add-line"></i> Reestock
                    </button>
                </div>
            </div>
        @empty
            <div class="card">
                <p style="margin:0; text-align:center;">No encontramos productos con lotes para esos filtros.</p>
            </div>
        @endforelse
    </div>

    <div style="margin-top:1rem;">
        {{ $productsWithLots->links() }}
    </div>

    <div class="modal" id="productLotsModal" style="display:none;">
        <div class="modal-content" style="width:min(1180px, 94vw); max-width:1180px; max-height:88vh; overflow:auto;">
            <div class="modal-header">
                <h3>Detalle de lotes por producto</h3>
                <button class="close-button" type="button" id="closeProductLotsModal">&times;</button>
            </div>
            <div id="productLotsModalBody"></div>
        </div>
    </div>

    <div class="modal" id="lotEditModal" style="display:none;">
        <div class="modal-content" style="max-width:520px;">
            <div class="modal-header">
                <h3>Editar lote</h3>
                <button class="close-button" type="button" id="closeLotEdit">&times;</button>
            </div>
            <form method="POST" id="lotEditForm" class="form-grid" style="grid-template-columns:1fr;">
                @csrf
                <div class="form-group">
                    <label>Codigo de lote</label>
                    <input type="text" name="lote_code" id="lot_code_input" class="input-ghost" placeholder="Codigo de lote">
                </div>
                <div class="form-group">
                    <label>Fecha de expiracion</label>
                    <input type="date" name="expires_at" id="lot_expires_input" class="input-ghost" required>
                </div>
                <div class="form-group">
                    <label>Cantidad total del lote</label>
                    <input type="number" name="quantity" id="lot_quantity_input" class="input-ghost" required>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                    <button type="button" class="btn-secondary" id="cancelLotEdit">Cancelar</button>
                    <button type="submit" class="pill-button">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const productLotsModal = document.getElementById('productLotsModal');
    const productLotsModalBody = document.getElementById('productLotsModalBody');
    const closeProductLotsModal = document.getElementById('closeProductLotsModal');
    const lotCreateProduct = document.getElementById('lot_create_product');
    const lotCreateForm = document.getElementById('lotCreateForm');

    const lotModal = document.getElementById('lotEditModal');
    const lotForm = document.getElementById('lotEditForm');
    const lotCodeInput = document.getElementById('lot_code_input');
    const lotExpiresInput = document.getElementById('lot_expires_input');
    const lotQuantityInput = document.getElementById('lot_quantity_input');
    const closeLotEdit = document.getElementById('closeLotEdit');
    const cancelLotEdit = document.getElementById('cancelLotEdit');
    const lotErrorModal = document.getElementById('lotErrorModal');
    const closeLotError = document.getElementById('closeLotError');
    const dismissLotError = document.getElementById('dismissLotError');

    function openLotEditModal(payload) {
        lotForm.action = payload.action;
        lotCodeInput.value = payload.code === 'Sin codigo' ? '' : payload.code;
        lotExpiresInput.value = payload.raw_expires_at || '';
        lotQuantityInput.value = payload.quantity || 0;
        lotModal.style.display = 'flex';
    }

    function closeLotModal() {
        lotModal.style.display = 'none';
    }

    function closeProductModal() {
        productLotsModal.style.display = 'none';
    }

    function jumpToRestock(productId) {
        if (!lotCreateProduct) return;
        lotCreateProduct.value = String(productId);
        lotCreateForm?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        lotCreateProduct.focus();
    }

    function renderProductModal(product) {
        const historyRows = product.history_rows || [];
        const movementHistory = product.movement_history || [];
        const critical = Number(product.minimum_stock || 0) > 0 && Number(product.current_stock || 0) <= Number(product.minimum_stock || 0);

        productLotsModalBody.innerHTML = `
            <div class="lot-detail-layout">
                <div class="lot-detail-column">
                    <div class="lot-detail-panel">
                        <div class="lot-detail-header">
                            <div class="lot-product-head">
                                <img src="${product.image}" alt="${product.name}" class="lot-product-cover">
                                <div>
                                    <h2 class="lot-product-title">${product.name}</h2>
                                    <div class="lot-product-meta">
                                        <span class="chip">SKU: ${product.sku}</span>
                                        <span class="chip">${product.category}</span>
                                        <span class="chip">${product.lots_count} lote(s)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="lot-detail-actions">
                                <button type="button" class="pill-button lot-modal-button btn-restock-product" data-product-id="${product.id}">Reestock</button>
                                <button type="button" class="btn-secondary lot-modal-button" onclick="document.getElementById('closeProductLotsModal').click()">Cerrar</button>
                            </div>
                        </div>
                    </div>

                    <div class="lot-detail-panel">
                        <h4>Estado de inventario</h4>
                        <div class="lot-detail-grid">
                            <div class="lot-stat-box">
                                <small>Stock actual</small>
                                <strong>${product.current_stock}</strong>
                            </div>
                            <div class="lot-stat-box">
                                <small>Stock minimo</small>
                                <strong>${product.minimum_stock || 'No definido'}</strong>
                            </div>
                            <div class="lot-stat-box">
                                <small>Total lotes</small>
                                <strong>${product.lots_count}</strong>
                            </div>
                            <div class="lot-stat-box">
                                <small>Proximo vencimiento</small>
                                <strong>${product.next_expiry}</strong>
                            </div>
                        </div>
                        ${critical ? `
                            <div class="lot-inline-alert" style="margin-top:1rem;">
                                <div>
                                    <strong>Alerta de stock bajo.</strong>
                                    <div style="color:rgba(255,255,255,0.76); margin-top:0.2rem;">La suma de lotes actuales es baja y conviene reabastecer.</div>
                                </div>
                                <button type="button" class="pill-button btn-restock-product" data-product-id="${product.id}">Agregar stock</button>
                            </div>
                        ` : ''}
                    </div>

                    <div class="lot-detail-panel">
                        <h4>Caracteristicas del producto</h4>
                        <p style="margin:0; color:rgba(255,255,255,0.78);">${product.description}</p>
                    </div>
                </div>

                <div class="lot-detail-column">
                    <div class="lot-detail-panel">
                        <h4>Historial de lotes</h4>
                        <div class="lot-history-scroll">
                            <table class="lot-history-table">
                                <thead>
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Stock</th>
                                        <th>Bodega</th>
                                        <th>Vence</th>
                                        <th>Accion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${historyRows.length ? historyRows.map((row) => `
                                        <tr>
                                            <td>${row.code}</td>
                                            <td>${row.quantity}</td>
                                            <td>${row.warehouse}</td>
                                            <td>${row.expires_at}</td>
                                            <td>
                                                <button type="button" class="btn-secondary btn-edit-lot-inline"
                                                    data-action="${row.action}"
                                                    data-code="${row.code}"
                                                    data-expires="${row.raw_expires_at}"
                                                    data-quantity="${row.quantity}">
                                                    Editar
                                                </button>
                                            </td>
                                        </tr>
                                    `).join('') : `
                                        <tr>
                                            <td colspan="5" style="text-align:center;">Sin lotes registrados.</td>
                                        </tr>
                                    `}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="lot-detail-panel">
                        <h4>Movimientos recientes</h4>
                        <div class="lot-movement-list">
                            ${movementHistory.length ? movementHistory.map((item) => `
                                <div class="lot-movement-item">
                                    <strong>${item.type} · ${item.quantity > 0 ? '+' : ''}${item.quantity}</strong>
                                    <div style="color:rgba(255,255,255,0.78);">Lote: ${item.lot_code}</div>
                                    <div style="color:rgba(255,255,255,0.62); margin-top:0.2rem;">${item.note}</div>
                                    <div style="color:rgba(255,255,255,0.55); margin-top:0.35rem; font-size:0.82rem;">${item.user} · ${item.date}</div>
                                </div>
                            `).join('') : '<p class="lot-empty-state">Sin movimientos recientes.</p>'}
                        </div>
                    </div>
                </div>
            </div>
        `;

        productLotsModal.style.display = 'flex';
    }

    document.querySelectorAll('.btn-view-product-lots').forEach((button) => {
        button.addEventListener('click', () => renderProductModal(JSON.parse(button.dataset.product)));
    });

    document.querySelectorAll('.btn-restock-product').forEach((button) => {
        button.addEventListener('click', () => jumpToRestock(button.dataset.productId));
    });

    productLotsModalBody?.addEventListener('click', (event) => {
        const editButton = event.target.closest('.btn-edit-lot-inline');
        const restockButton = event.target.closest('.btn-restock-product');

        if (editButton) {
            openLotEditModal({
                action: editButton.dataset.action,
                code: editButton.dataset.code,
                raw_expires_at: editButton.dataset.expires,
                quantity: editButton.dataset.quantity,
            });
        }

        if (restockButton) {
            closeProductModal();
            jumpToRestock(restockButton.dataset.productId);
        }
    });

    closeProductLotsModal?.addEventListener('click', closeProductModal);
    productLotsModal?.addEventListener('click', (event) => {
        if (event.target === productLotsModal) closeProductModal();
    });

    closeLotEdit?.addEventListener('click', closeLotModal);
    cancelLotEdit?.addEventListener('click', closeLotModal);
    lotModal?.addEventListener('click', (e) => {
        if (e.target === lotModal) closeLotModal();
    });

    function closeLotErrorModal() {
        lotErrorModal.style.display = 'none';
    }

    closeLotError?.addEventListener('click', closeLotErrorModal);
    dismissLotError?.addEventListener('click', closeLotErrorModal);

    @if(session('modal_error'))
        const lotErrorMsg = document.getElementById('lotErrorMessage');
        lotErrorMsg.textContent = @json(session('modal_error'));
        lotErrorModal.style.display = 'flex';
    @endif
</script>
@endpush
