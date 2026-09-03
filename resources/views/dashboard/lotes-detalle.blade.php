@extends('layouts.sidebar')

@section('title', 'Detalle de lotes | Pil Andina')
@section('page-title', 'Detalle de Lotes')

@php
    $currentStock = (int) ($product->current_stock ?? 0);
    $minimumStock = (int) ($product->min_quantity ?? 0);
    $lotsCount = (int) ($product->lots_count ?? 0);
    $isCritical = $minimumStock > 0 && $currentStock <= $minimumStock;
    $nextExpiry = $product->next_expiry ? \Carbon\Carbon::parse($product->next_expiry)->format('d/m/Y') : 'Sin fecha';
@endphp

@section('content')
    <div class="fit-users-page fit-lots-page">
        @if(session('status'))
            <div class="fit-filter-card">
                <span class="fit-status active"><span></span> {{ session('status') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="fit-filter-card">
                <span class="fit-status inactive"><span></span> {{ $errors->first() }}</span>
            </div>
        @endif

        <section class="fit-users-header">
            <div class="fit-users-header-left">
                <span class="fit-product-image fit-lot-product-image large">
                    <img src="{{ $product->getImageUrl() }}" alt="{{ $product->name }}">
                </span>
                <div>
                    <a href="{{ route('dashboard.lots') }}" class="fit-clear-button" style="display:inline-flex; margin-bottom:0.8rem;">
                        <i class="ri-arrow-left-line"></i> Volver a productos
                    </a>
                    <h1>{{ $product->name }}</h1>
                    <p>SKU: {{ $product->sku }} · {{ $product->category->name ?? 'Sin categoria' }} · {{ $warehouse?->name ?? 'La Paz' }}</p>
                </div>
            </div>
        </section>

        <section class="fit-metric-grid">
            <div class="fit-metric-card indigo">
                <span><small>Stock actual</small><strong>{{ number_format($currentStock) }}</strong><em>Unidades en lotes</em></span>
                <span class="fit-metric-icon"><i class="ri-box-3-line"></i></span>
            </div>
            <div class="fit-metric-card green">
                <span><small>Total lotes</small><strong>{{ number_format($lotsCount) }}</strong><em>Registros del producto</em></span>
                <span class="fit-metric-icon"><i class="ri-stack-line"></i></span>
            </div>
            <div class="fit-metric-card amber">
                <span><small>Stock minimo</small><strong>{{ $minimumStock ?: 'N/D' }}</strong><em>Umbral configurado</em></span>
                <span class="fit-metric-icon"><i class="ri-alarm-warning-line"></i></span>
            </div>
            <div class="fit-metric-card rose">
                <span><small>Proximo vencimiento</small><strong>{{ $nextExpiry }}</strong><em>Orden FEFO</em></span>
                <span class="fit-metric-icon"><i class="ri-calendar-close-line"></i></span>
            </div>
        </section>

        @if($isCritical)
            <section class="fit-lot-alert">
                <i class="ri-error-warning-line"></i>
                <div>
                    <strong>Necesita reabastecimiento</strong>
                    <span>El stock actual alcanzo o bajo del minimo configurado.</span>
                </div>
            </section>
        @endif

        <section class="fit-filter-card">
            <form method="GET" action="{{ route('dashboard.lots.show', $product) }}" class="fit-lot-filter-form">
                <label class="fit-search-control" for="search">
                    <i class="ri-search-line"></i>
                    <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar codigo de lote...">
                </label>

                <label class="fit-select-control" for="warehouse_id">
                    <i class="ri-store-2-line"></i>
                    <select id="warehouse_id" name="warehouse_id">
                        <option value="">Todas las bodegas</option>
                        @foreach($warehouses as $item)
                            <option value="{{ $item->id }}" @selected(($filters['warehouse_id'] ?? null) == $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="fit-search-control" for="expires_at">
                    <i class="ri-calendar-line"></i>
                    <input id="expires_at" type="date" name="expires_at" value="{{ $filters['expires_at'] ?? '' }}">
                </label>

                <label class="fit-select-control" for="scope">
                    <i class="ri-filter-3-line"></i>
                    <select id="scope" name="scope">
                        <option value="">Todos los lotes</option>
                        <option value="expiring" @selected(($filters['scope'] ?? null) === 'expiring')>Vencen en 30 dias</option>
                    </select>
                </label>

                <button type="submit" class="fit-primary-button compact">
                    <i class="ri-search-line"></i> Buscar
                </button>

                <a href="{{ route('dashboard.lots.show', $product) }}" class="fit-clear-button">Limpiar</a>
            </form>
        </section>

        <div class="fit-lot-detail-grid">
            <section class="fit-lot-panel">
                <div class="fit-section-head">
                    <div>
                        <h2>Lotes del producto</h2>
                        <p>{{ $lots->total() }} registros encontrados. La tabla esta paginada para manejar inventarios grandes.</p>
                    </div>
                    <span class="fit-section-badge green">{{ $lots->perPage() }} por pagina</span>
                </div>

                <div class="fit-table-card">
                    <div class="fit-table-scroll">
                        <table class="fit-users-table fit-lot-history-table">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Stock</th>
                                    <th>Bodega</th>
                                    <th>Vence</th>
                                    <th>Ultimo mov.</th>
                                    <th>Usuario</th>
                                    <th class="text-right">Accion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lots as $lot)
                                    <tr>
                                        <td><code class="fit-code">{{ $lot['code'] }}</code></td>
                                        <td><strong>{{ number_format($lot['quantity']) }}</strong></td>
                                        <td><span class="fit-muted-text">{{ $lot['warehouse'] }}</span></td>
                                        <td><span class="fit-muted-text">{{ $lot['expires_at'] }}</span></td>
                                        <td>
                                            <span class="fit-muted-text">
                                                {{ $lot['last_movement'] }}
                                                @if($lot['last_movement_qty'] !== null)
                                                    {{ (int) $lot['last_movement_qty'] > 0 ? '+' : '' }}{{ $lot['last_movement_qty'] }}
                                                @endif
                                            </span>
                                        </td>
                                        <td><span class="fit-muted-text">{{ $lot['last_movement_user'] }}</span></td>
                                        <td class="text-right">
                                            <button
                                                type="button"
                                                class="fit-action-button warning"
                                                data-edit-lot
                                                data-action="{{ $lot['action'] }}"
                                                data-code="{{ $lot['code'] === 'Sin codigo' ? '' : e($lot['code']) }}"
                                                data-quantity="{{ $lot['quantity'] }}"
                                                data-expires="{{ $lot['raw_expires_at'] }}"
                                                title="Editar lote"
                                            >
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="fit-muted-text" style="text-align:center; padding:1.4rem;">No encontramos lotes para estos filtros.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="margin-top:1rem;">
                    {{ $lots->links() }}
                </div>
            </section>

            <aside class="fit-lot-panel">
                <h4>Movimientos recientes</h4>
                <div class="fit-lot-movement-list">
                    @forelse($movementHistory as $item)
                        <div class="fit-lot-movement-item">
                            <strong>{{ $item['type'] }} {{ $item['quantity'] > 0 ? '+' : '' }}{{ $item['quantity'] }}</strong>
                            <span>Lote: {{ $item['lot_code'] }}</span>
                            <p>{{ $item['note'] }}</p>
                            <time>{{ $item['user'] }} · {{ $item['date'] }}</time>
                        </div>
                    @empty
                        <p class="fit-lot-empty">Sin movimientos recientes.</p>
                    @endforelse
                </div>
            </aside>
        </div>
    </div>

    <div class="modal" id="lotEditModal" style="display:none;">
        <div class="modal-content fit-modal-content" style="max-width:520px;">
            <div class="modal-header">
                <h3>Editar lote</h3>
                <button class="close-button" type="button" data-close-edit>&times;</button>
            </div>
            <form method="POST" id="lotEditForm" class="fit-register-form">
                @csrf
                <div class="fit-form-grid">
                    <div class="fit-form-field span-2">
                        <label for="edit_lote_code">Codigo de lote</label>
                        <input id="edit_lote_code" type="text" name="lote_code">
                    </div>
                    <div class="fit-form-field">
                        <label for="edit_expires_at">Fecha de expiracion *</label>
                        <input id="edit_expires_at" type="date" name="expires_at" required>
                    </div>
                    <div class="fit-form-field">
                        <label for="edit_quantity">Cantidad total *</label>
                        <input id="edit_quantity" type="number" name="quantity" required>
                    </div>
                </div>
                <div class="fit-modal-footer">
                    <button type="button" class="fit-outline-button" data-close-edit>Cancelar</button>
                    <button type="submit" class="fit-primary-button">
                        <i class="ri-save-3-line"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('lotEditModal');
            const form = document.getElementById('lotEditForm');
            const codeInput = document.getElementById('edit_lote_code');
            const quantityInput = document.getElementById('edit_quantity');
            const expiresInput = document.getElementById('edit_expires_at');

            document.querySelectorAll('[data-edit-lot]').forEach((button) => {
                button.addEventListener('click', () => {
                    form.action = button.dataset.action || '#';
                    codeInput.value = button.dataset.code || '';
                    quantityInput.value = button.dataset.quantity || 0;
                    expiresInput.value = button.dataset.expires || '';
                    modal.style.display = 'flex';
                });
            });

            document.querySelectorAll('[data-close-edit]').forEach((button) => {
                button.addEventListener('click', () => {
                    modal.style.display = 'none';
                });
            });
        });
    </script>
@endsection
