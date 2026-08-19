@extends('layouts.sidebar-almacen')

@section('title', 'Registro de danos | Pil Andina')
@section('page-title', 'Registro de danos')

@section('content')
    <style>
        .damage-product-stack { display:grid; gap:1rem; }
        .damage-product-card {
            display:grid;
            grid-template-columns:minmax(0, 1fr) 210px;
            gap:1.25rem;
            align-items:stretch;
        }
        .damage-product-main { display:flex; flex-direction:column; gap:1rem; }
        .damage-product-head { display:flex; align-items:center; gap:1rem; }
        .damage-product-cover {
            width:82px;
            height:82px;
            border-radius:1.35rem;
            object-fit:cover;
            border:1px solid rgba(255,255,255,0.12);
            background:rgba(255,255,255,0.08);
            box-shadow:0 14px 28px rgba(0,0,0,0.18);
        }
        .damage-product-title { margin:0; font-size:1.35rem; line-height:1.15; }
        .damage-product-meta { display:flex; flex-wrap:wrap; gap:0.5rem; margin-top:0.45rem; }
        .damage-product-separator { height:1px; background:rgba(255,255,255,0.08); }
        .damage-stat-grid {
            display:grid;
            grid-template-columns:repeat(3, minmax(120px, 1fr));
            gap:1rem;
        }
        .damage-stat-box small {
            display:block;
            margin-bottom:0.35rem;
            color:rgba(255,255,255,0.62);
            font-size:0.76rem;
            letter-spacing:0.04em;
            text-transform:uppercase;
        }
        .damage-stat-box strong { display:block; font-size:1.15rem; line-height:1.15; }
        .damage-action-panel {
            border-left:1px solid rgba(255,255,255,0.08);
            padding-left:1.25rem;
            display:flex;
            flex-direction:column;
            justify-content:center;
            gap:0.9rem;
        }
        .damage-action-button {
            width:100%;
            border:1px solid rgba(255,255,255,0.16);
            border-radius:1rem;
            padding:0.95rem 1.1rem;
            color:#fff;
            font-weight:700;
            cursor:pointer;
            background:rgba(78,107,175,0.28);
            box-shadow:0 14px 28px rgba(0,0,0,0.18);
            transition:transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .damage-action-button:hover {
            transform:translateY(-2px);
            box-shadow:0 18px 30px rgba(0,0,0,0.24);
            background:rgba(255,255,255,0.18);
        }
        .damage-filter-bar {
            display:grid;
            grid-template-columns:repeat(3, minmax(0, 1fr));
            gap:1rem;
        }
        .damage-modal {
            position:fixed;
            inset:0;
            display:none;
            align-items:center;
            justify-content:center;
            padding:1.5rem;
            background:rgba(6,11,25,0.65);
            backdrop-filter:blur(6px);
            z-index:80;
        }
        .damage-modal.active { display:flex; }
        .damage-modal .modal-card {
            width:min(920px, 95vw);
            max-height:90vh;
            background:linear-gradient(140deg, #0f172a, #132347);
            border-radius:1.5rem;
            color:#fff;
            box-shadow:0 25px 60px rgba(2,6,23,0.65), inset 0 1px 1px rgba(255,255,255,0.08);
            padding:1.5rem 1.8rem;
            overflow:hidden;
            display:flex;
            flex-direction:column;
        }
        .damage-modal .modal-header {
            display:flex;
            justify-content:space-between;
            gap:1rem;
            align-items:center;
            border-bottom:1px solid rgba(255,255,255,0.08);
            padding-bottom:0.75rem;
        }
        .damage-modal .modal-body { padding-top:1rem; overflow-y:auto; }
        .damage-lot-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:0.75rem; margin:1rem 0; }
        .damage-lot-option {
            text-align:left;
            color:#fff;
            border:1px solid rgba(255,255,255,0.12);
            border-radius:1rem;
            padding:0.9rem 1rem;
            background:rgba(255,255,255,0.05);
            cursor:pointer;
        }
        .damage-lot-option.active {
            border-color:rgba(56,189,248,0.55);
            background:rgba(14,165,233,0.14);
        }
        @media (max-width:1080px) {
            .damage-product-card { grid-template-columns:1fr; }
            .damage-action-panel { border-left:0; border-top:1px solid rgba(255,255,255,0.08); padding-left:0; padding-top:1rem; }
            .damage-stat-grid, .damage-filter-bar { grid-template-columns:repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width:720px) {
            .damage-stat-grid, .damage-filter-bar { grid-template-columns:1fr; }
        }
    </style>

    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="card" style="border:1px solid rgba(248,113,113,0.35); color:#fecdd3;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="stats-grid">
        <div class="card">
            <h3>Reportes</h3>
            <div class="value">{{ $stats['reports'] }}</div>
            <span class="chip text-white/70"><i class="ri-flag-2-fill"></i>Total registrados</span>
        </div>
        <div class="card">
            <h3>Unidades afectadas</h3>
            <div class="value">{{ number_format($stats['units']) }}</div>
            <span class="chip text-red-300"><i class="ri-close-circle-line"></i>Retiradas de stock</span>
        </div>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Productos con lotes activos</h4>
            <span class="chip text-white/70">{{ $productsWithLots->total() }} productos</span>
        </div>
        <form method="GET" action="{{ route('dashboard.almacen.damages') }}" class="damage-filter-bar" style="margin-top:1rem;" data-live-search-form>
            <div class="form-group">
                <label for="damage_search">Buscar producto</label>
                <input type="text" id="damage_search" name="search" class="input-ghost" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre, SKU o descripcion" data-live-search-input>
            </div>
            <div class="form-group">
                <label for="damage_product_id">Producto</label>
                <select id="damage_product_id" name="product_id" class="select-light">
                    <option value="">Todos</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(($filters['product_id'] ?? null) == $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <button class="pill-button" type="submit">Aplicar</button>
                <a href="{{ route('dashboard.almacen.damages') }}" class="clean-link">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="damage-product-stack">
        @forelse($productsWithLots as $product)
            @php
                $payload = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category->name ?? 'Sin categoria',
                    'image' => $product->getImageUrl(),
                    'current_stock' => (int) ($product->current_stock ?? 0),
                    'lots_count' => (int) ($product->lots_count ?? 0),
                    'lots' => $product->damage_lots,
                ];
            @endphp
            <div class="card damage-product-card">
                <div class="damage-product-main">
                    <div class="damage-product-head">
                        <img src="{{ $product->getImageUrl() }}" alt="{{ $product->name }}" class="damage-product-cover">
                        <div>
                            <h3 class="damage-product-title">{{ $product->name }}</h3>
                            <div class="damage-product-meta">
                                <span class="chip">SKU: {{ $product->sku }}</span>
                                <span class="chip">{{ $product->category->name ?? 'Sin categoria' }}</span>
                                <span class="chip">{{ $product->lots_count }} lote{{ $product->lots_count === 1 ? '' : 's' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="damage-product-separator"></div>
                    <div class="damage-stat-grid">
                        <div class="damage-stat-box">
                            <small>Stock actual</small>
                            <strong>{{ (int) $product->current_stock }} unidades</strong>
                        </div>
                        <div class="damage-stat-box">
                            <small>Lotes disponibles</small>
                            <strong>{{ $product->lots_count }}</strong>
                        </div>
                        <div class="damage-stat-box">
                            <small>Ubicacion</small>
                            <strong>La Paz</strong>
                        </div>
                    </div>
                </div>
                <div class="damage-action-panel">
                    <button type="button" class="damage-action-button btn-damage-product" data-product='@json($payload)'>
                        <i class="ri-error-warning-line"></i> Registrar dano
                    </button>
                </div>
            </div>
        @empty
            <div class="card">
                <p style="margin:0;text-align:center;">No encontramos productos con lotes activos.</p>
            </div>
        @endforelse
    </div>

    <div style="margin-top:1rem;">
        {{ $productsWithLots->links() }}
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Historial de danos</h4>
            <span class="chip text-white/70">{{ $reports->total() }} registros</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Almacen</th>
                        <th>Comentado por</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>{{ $report->lot->lote_code ?? 'Sin codigo' }}</td>
                            <td>
                                <strong>{{ $report->product->name ?? 'Producto' }}</strong>
                                <p style="margin:0;color:rgba(255,255,255,0.6);">SKU: {{ $report->product->sku ?? 'N/D' }}</p>
                            </td>
                            <td>{{ $report->damaged_qty }} uds</td>
                            <td>{{ $report->warehouse->name ?? 'Almacen' }}</td>
                            <td>{{ $report->reporter->name ?? 'Sistema' }}</td>
                            <td>{{ optional($report->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:1.5rem;">Sin incidencias registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $reports->links() }}
        </div>
    </div>

    <div class="damage-modal" id="damageModal">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3 style="margin:0;" id="damageModalTitle">Registrar dano</h3>
                    <p style="margin:0.35rem 0 0;color:rgba(255,255,255,0.7);" id="damageModalSubtitle"></p>
                </div>
                <button type="button" class="close-button" data-close-damage>&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('dashboard.almacen.damages.store') }}" class="form-grid" id="damageForm">
                    @csrf
                    <input type="hidden" name="product_lot_id" id="product_lot_id" value="{{ old('product_lot_id') }}">
                    <div style="grid-column:1 / -1;">
                        <label style="display:block;margin-bottom:0.5rem;">Selecciona el lote afectado</label>
                        <div class="damage-lot-grid" id="damageLotGrid"></div>
                    </div>
                    <div class="form-group">
                        <label for="damaged_qty">Cantidad danada</label>
                        <input type="number" min="1" id="damaged_qty" name="damaged_qty" class="input-ghost" value="{{ old('damaged_qty') }}" required>
                    </div>
                    <div class="form-group" style="grid-column:1 / -1;">
                        <label for="comment">Comentario</label>
                        <textarea id="comment" name="comment" rows="3" class="input-ghost" placeholder="Describe el dano o la incidencia">{{ old('comment') }}</textarea>
                    </div>
                    <div style="grid-column:1 / -1; display:flex; justify-content:flex-end; gap:0.75rem; flex-wrap:wrap;">
                        <button type="button" class="pill-button ghost" data-close-damage>Cancelar</button>
                        <button class="pill-button" type="submit">Registrar dano</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (() => {
        const modal = document.getElementById('damageModal');
        const title = document.getElementById('damageModalTitle');
        const subtitle = document.getElementById('damageModalSubtitle');
        const lotGrid = document.getElementById('damageLotGrid');
        const lotIdField = document.getElementById('product_lot_id');
        const qtyField = document.getElementById('damaged_qty');

        function selectLot(button) {
            lotGrid.querySelectorAll('.damage-lot-option').forEach(item => item.classList.remove('active'));
            button.classList.add('active');
            lotIdField.value = button.dataset.lotId;
            qtyField.max = button.dataset.quantity;
            qtyField.placeholder = `Maximo ${button.dataset.quantity}`;
        }

        function openDamageModal(product) {
            title.textContent = `Registrar dano: ${product.name}`;
            subtitle.textContent = `SKU ${product.sku} - ${product.lots_count} lote(s) disponible(s)`;
            lotIdField.value = '';
            qtyField.value = '';
            lotGrid.innerHTML = (product.lots || []).map((lot) => `
                <button type="button" class="damage-lot-option" data-lot-id="${lot.id}" data-quantity="${lot.quantity}">
                    <strong>${lot.code}</strong>
                    <p style="margin:0.35rem 0 0;color:rgba(255,255,255,0.68);">${lot.quantity} uds disponibles</p>
                    <p style="margin:0.2rem 0 0;color:rgba(255,255,255,0.55);">Vence: ${lot.expires_at}</p>
                </button>
            `).join('');

            lotGrid.querySelectorAll('.damage-lot-option').forEach((button) => {
                button.addEventListener('click', () => selectLot(button));
            });

            const firstLot = lotGrid.querySelector('.damage-lot-option');
            if (firstLot) selectLot(firstLot);
            modal.classList.add('active');
        }

        document.querySelectorAll('.btn-damage-product').forEach((button) => {
            button.addEventListener('click', () => openDamageModal(JSON.parse(button.dataset.product)));
        });

        document.querySelectorAll('[data-close-damage]').forEach((button) => {
            button.addEventListener('click', () => modal.classList.remove('active'));
        });

        modal?.addEventListener('click', (event) => {
            if (event.target === modal) modal.classList.remove('active');
        });
    })();
</script>
@endpush
