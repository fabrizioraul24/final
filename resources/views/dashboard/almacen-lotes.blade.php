@extends('layouts.sidebar-almacen')

@section('title', 'Inventario por lotes | Pil Andina')
@section('page-title', 'Inventario por lotes')

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

        .lot-stat-grid,
        .lot-detail-grid {
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
            display: block;
            font-size: 1.15rem;
            line-height: 1.15;
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
            background: rgba(78, 107, 175, 0.28);
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.18);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
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

        .lot-history-scroll {
            overflow-x: auto;
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
        }

        @media (max-width: 720px) {
            .lot-stat-grid,
            .lot-filter-bar,
            .lot-detail-grid {
                grid-template-columns: 1fr;
            }

            .lot-detail-header {
                flex-direction: column;
            }
        }
    </style>

    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    <div class="stats-grid">
        <div class="card">
            <h3>Lotes registrados</h3>
            <div class="value">{{ $stats['lots'] }}</div>
            <span class="chip text-white/70"><i class="ri-barcode-line"></i>Total activos</span>
        </div>
        <div class="card">
            <h3>Stock total</h3>
            <div class="value">{{ number_format($stats['stock']) }} uds</div>
            <span class="chip text-white/70"><i class="ri-dropbox-line"></i>Inventario fisico</span>
        </div>
        <div class="card">
            <h3>Vencen en 30 dias</h3>
            <div class="value">{{ $stats['expiring'] }}</div>
            <span class="chip text-red-300"><i class="ri-error-warning-line"></i>Priorizar</span>
        </div>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Explorar lotes por producto</h4>
            <span class="chip">{{ $productsWithLots->total() }} productos con lotes</span>
        </div>
        <form method="GET" action="{{ route('dashboard.almacen.lots') }}" class="lot-filter-bar" style="margin-top:1rem;" data-live-search-form>
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
                <button class="pill-button" type="submit">Aplicar</button>
                <a href="{{ route('dashboard.almacen.lots') }}" class="clean-link">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="lot-product-stack">
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
                        </div>
                    @endif
                </div>

                <div class="lot-action-panel">
                    <button type="button" class="lot-action-button btn-view-product-lots" data-product='@json($productPayload)'>
                        <i class="ri-stack-line"></i> Ver lotes
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
@endsection

@push('scripts')
<script>
    const productLotsModal = document.getElementById('productLotsModal');
    const productLotsModalBody = document.getElementById('productLotsModalBody');
    const closeProductLotsModal = document.getElementById('closeProductLotsModal');

    function closeProductModal() {
        productLotsModal.style.display = 'none';
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
                            <button type="button" class="btn-secondary" onclick="document.getElementById('closeProductLotsModal').click()">Cerrar</button>
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
                                        <th>Autorizado por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${historyRows.length ? historyRows.map((row) => `
                                        <tr>
                                            <td>${row.code}</td>
                                            <td>${row.quantity}</td>
                                            <td>${row.warehouse}</td>
                                            <td>${row.expires_at}</td>
                                            <td>${row.last_movement_user || 'Sistema'}</td>
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
                                    <strong>${item.type} &middot; ${item.quantity > 0 ? '+' : ''}${item.quantity}</strong>
                                    <div style="color:rgba(255,255,255,0.78);">Lote: ${item.lot_code}</div>
                                    <div style="color:rgba(255,255,255,0.62); margin-top:0.2rem;">${item.note}</div>
                                    <div style="color:rgba(255,255,255,0.55); margin-top:0.35rem; font-size:0.82rem;">Autorizado por: ${item.user} &middot; ${item.date}</div>
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

    closeProductLotsModal?.addEventListener('click', closeProductModal);
    productLotsModal?.addEventListener('click', (event) => {
        if (event.target === productLotsModal) closeProductModal();
    });
</script>
@endpush
