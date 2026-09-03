@extends('layouts.sidebar-almacen')

@section('title', 'Registrar dano | Pil Andina')
@section('page-title', 'Registrar dano')

@section('content')
<div class="warehouse-damages-page warehouse-damage-create-page">
    @if(session('status'))
        <div class="warehouse-sync-card">
            <div class="warehouse-sync-status">
                <span class="warehouse-live-dot"></span>
                <strong>{{ session('status') }}</strong>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="warehouse-inventory-alert">
            <i class="ri-error-warning-line"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <section class="warehouse-lot-detail-hero">
        <div class="warehouse-lot-detail-hero-main">
            <a href="{{ route('dashboard.almacen.damages') }}" class="fit-outline-button compact warehouse-lot-detail-back">
                <i class="ri-arrow-left-line"></i>
                <span>Volver</span>
            </a>
            <div class="warehouse-order-detail-title">
                <span class="fit-section-badge orange">Control de incidencia</span>
                <h1>Registrar dano por lote</h1>
                <p>Busca el lote afectado, registra la cantidad danada y deja la observacion correspondiente.</p>
            </div>
        </div>
        <div class="warehouse-inventory-branch">
            <span>Bodega activa</span>
            <strong>{{ $targetWarehouse?->name ?? 'La Paz' }}</strong>
        </div>
    </section>

    <section class="fit-metric-grid warehouse-metric-grid">
        <div class="fit-metric-card orange">
            <span><small>Lotes disponibles</small><strong>{{ $stats['lots'] }}</strong><em>Con stock positivo</em></span>
            <span class="fit-metric-icon"><i class="ri-barcode-line"></i></span>
        </div>
        <div class="fit-metric-card blue">
            <span><small>Stock disponible</small><strong>{{ number_format((int) $stats['stock']) }}</strong><em>Unidades en bodega</em></span>
            <span class="fit-metric-icon"><i class="ri-dropbox-line"></i></span>
        </div>
        <div class="fit-metric-card rose">
            <span><small>Vencen pronto</small><strong>{{ $stats['expiring'] }}</strong><em>Dentro de 30 dias</em></span>
            <span class="fit-metric-icon"><i class="ri-alarm-warning-line"></i></span>
        </div>
    </section>

    <section class="fit-filter-card warehouse-damages-filter-card">
        <form method="GET" action="{{ route('dashboard.almacen.damages.create') }}" class="fit-filter-form warehouse-damages-filter" data-live-search-form>
            <label class="fit-search-control" for="damage_lot_search">
                <i class="ri-search-line"></i>
                <input type="search" id="damage_lot_search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar lote, producto, SKU o descripcion..." data-live-search-input>
            </label>
            <label class="fit-select-control" for="damage_product_id">
                <i class="ri-price-tag-3-line"></i>
                <select id="damage_product_id" name="product_id">
                    <option value="">Todos los productos</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(($filters['product_id'] ?? null) == $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
            </label>
            <button class="fit-primary-button compact" type="submit">
                <i class="ri-search-line"></i>
                <span>Buscar</span>
            </button>
            @if(($filters['search'] ?? null) || ($filters['product_id'] ?? null))
                <a href="{{ route('dashboard.almacen.damages.create') }}" class="fit-clear-button">Limpiar filtros</a>
            @endif
        </form>
    </section>

    <section class="warehouse-panel warehouse-damage-lots-panel">
        <div class="warehouse-panel-head">
            <div>
                <span class="fit-section-badge orange">Historial de lotes</span>
                <h2>Lotes disponibles para registrar dano</h2>
            </div>
            <span class="warehouse-panel-count">{{ $lots->total() }} registros</span>
        </div>

        <div class="fit-table-scroll">
            <table class="fit-users-table warehouse-orders-table warehouse-damage-lots-table">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Producto</th>
                        <th>Stock</th>
                        <th>Vence</th>
                        <th>Registro de dano</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lots as $lot)
                        <tr>
                            <td>
                                <code class="fit-code">{{ $lot->lote_code ?: 'Sin codigo' }}</code>
                                <span>{{ $lot->warehouse?->name ?? 'Almacen' }}</span>
                            </td>
                            <td>
                                <strong>{{ $lot->product?->name ?? 'Producto' }}</strong>
                                <span>SKU: {{ $lot->product?->sku ?? 'N/D' }} - {{ $lot->product?->category?->name ?? 'Sin categoria' }}</span>
                            </td>
                            <td>
                                <strong>{{ number_format((int) $lot->quantity) }}</strong>
                                <span>Unidades disponibles</span>
                            </td>
                            <td>
                                <strong>{{ optional($lot->expires_at)->format('d/m/Y') ?? 'Sin fecha' }}</strong>
                                <span>Fecha de vencimiento</span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('dashboard.almacen.damages.store') }}" class="warehouse-damage-inline-form">
                                    @csrf
                                    <input type="hidden" name="product_lot_id" value="{{ $lot->id }}">
                                    <label>
                                        <span>Cantidad</span>
                                        <input type="number" name="damaged_qty" min="1" max="{{ (int) $lot->quantity }}" value="{{ old('product_lot_id') == $lot->id ? old('damaged_qty') : '' }}" placeholder="Max. {{ (int) $lot->quantity }}" required>
                                    </label>
                                    <label>
                                        <span>Comentario</span>
                                        <input type="text" name="comment" value="{{ old('product_lot_id') == $lot->id ? old('comment') : '' }}" placeholder="Motivo o detalle">
                                    </label>
                                    <button class="fit-primary-button compact" type="submit">
                                        <i class="ri-save-3-line"></i>
                                        <span>Registrar</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="warehouse-empty-cell">No encontramos lotes activos para esos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="warehouse-pagination">
            {{ $lots->links() }}
        </div>
    </section>
</div>
@endsection
