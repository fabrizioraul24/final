@extends('layouts.sidebar-almacen')

@section('title', 'Inventario de bodega | Pil Andina')
@section('page-title', 'Inventario de bodega')

@php
    $activeWarehouse = $warehouses->firstWhere('id', $filters['warehouse_id']);
@endphp

@section('content')
<div class="warehouse-inventory-page">
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    <section class="fit-users-header">
        <div class="fit-users-header-left">
            <div class="fit-header-icon"><i class="ri-box-3-line"></i></div>
            <div>
                <h1>Inventario por Lotes</h1>
                <p>Consulta stock fisico, vencimientos y movimientos de la bodega asignada.</p>
            </div>
        </div>
        <div class="warehouse-inventory-branch">
            <span>Bodega activa</span>
            <strong>{{ $activeWarehouse?->name ?? 'La Paz' }}</strong>
        </div>
    </section>

    <section class="fit-metric-grid warehouse-metric-grid">
        <div class="fit-metric-card orange">
            <span><small>Productos con stock</small><strong>{{ $stats['products'] }}</strong><em>Con lotes activos</em></span>
            <span class="fit-metric-icon"><i class="ri-stack-line"></i></span>
        </div>
        <div class="fit-metric-card blue">
            <span><small>Lotes registrados</small><strong>{{ $stats['lots'] }}</strong><em>Inventario fisico</em></span>
            <span class="fit-metric-icon"><i class="ri-barcode-line"></i></span>
        </div>
        <div class="fit-metric-card indigo">
            <span><small>Stock total</small><strong>{{ number_format((int) $stats['stock']) }}</strong><em>Unidades disponibles</em></span>
            <span class="fit-metric-icon"><i class="ri-dropbox-line"></i></span>
        </div>
        <div class="fit-metric-card rose">
            <span><small>Alertas</small><strong>{{ $stats['expiring'] + $stats['critical'] }}</strong><em>Vencimiento o minimo</em></span>
            <span class="fit-metric-icon"><i class="ri-alarm-warning-line"></i></span>
        </div>
    </section>

    <section class="fit-filter-card warehouse-inventory-filter-card">
        <form method="GET" action="{{ route('dashboard.almacen.lots') }}" class="fit-filter-form warehouse-inventory-filter" data-live-search-form>
            <label class="fit-search-control" for="filter_search">
                <i class="ri-search-line"></i>
                <input type="search" id="filter_search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar producto, SKU o descripcion..." data-live-search-input>
            </label>
            <label class="fit-select-control" for="filter_product_id">
                <i class="ri-price-tag-3-line"></i>
                <select id="filter_product_id" name="product_id">
                    <option value="">Todos los productos</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(($filters['product_id'] ?? null) == $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="fit-select-control" for="filter_warehouse_id">
                <i class="ri-archive-drawer-line"></i>
                <select id="filter_warehouse_id" name="warehouse_id">
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(($filters['warehouse_id'] ?? null) == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="fit-search-control" for="filter_expires_at">
                <i class="ri-calendar-line"></i>
                <input type="date" id="filter_expires_at" name="expires_at" value="{{ $filters['expires_at'] ?? '' }}">
            </label>
            <button class="fit-primary-button compact" type="submit"><i class="ri-search-line"></i><span>Filtrar</span></button>
            @if(($filters['search'] ?? null) || ($filters['product_id'] ?? null) || ($filters['expires_at'] ?? null))
                <a href="{{ route('dashboard.almacen.lots') }}" class="fit-clear-button">Limpiar filtros</a>
            @endif
        </form>
    </section>

    <section class="warehouse-inventory-grid">
        @forelse($productsWithLots as $product)
            @php
                $currentStock = (int) ($product->current_stock ?? 0);
                $minimumStock = (int) ($product->min_quantity ?? 0);
                $isCritical = $minimumStock > 0 && $currentStock <= $minimumStock;
                $nextExpiry = $product->next_expiry ? \Carbon\Carbon::parse($product->next_expiry)->format('d/m/Y') : 'Sin fecha';
                $expiryIsNear = $product->next_expiry && \Carbon\Carbon::parse($product->next_expiry)->between(now(), now()->addDays(30));
            @endphp
            <article class="warehouse-inventory-card {{ $isCritical ? 'is-critical' : '' }}">
                <div class="warehouse-inventory-product">
                    <img src="{{ $product->getImageUrl() }}" alt="{{ $product->name }}">
                    <div>
                        <h2>{{ $product->name }}</h2>
                        <p>{{ $product->category->name ?? 'Sin categoria' }}</p>
                        <div class="warehouse-inventory-tags">
                            <span>SKU: {{ $product->sku }}</span>
                            <span>{{ $product->lots_count }} lote{{ $product->lots_count === 1 ? '' : 's' }}</span>
                            @if($expiryIsNear)<span class="danger">Vence pronto</span>@endif
                        </div>
                    </div>
                </div>

                <div class="warehouse-inventory-stats">
                    <div><span>Stock actual</span><strong>{{ number_format($currentStock) }}</strong></div>
                    <div><span>Stock minimo</span><strong>{{ $minimumStock ?: 'N/D' }}</strong></div>
                    <div><span>Proximo venc.</span><strong>{{ $nextExpiry }}</strong></div>
                </div>

                @if($isCritical)
                    <div class="warehouse-inventory-alert">
                        <i class="ri-error-warning-line"></i>
                        <span>Stock bajo el minimo de seguridad.</span>
                    </div>
                @endif

                <div class="warehouse-inventory-actions">
                    <a href="{{ route('dashboard.almacen.lots.show', ['product' => $product, 'warehouse_id' => $filters['warehouse_id'] ?? null]) }}" class="fit-primary-button">
                        <i class="ri-eye-line"></i>
                        <span>Ver detalle</span>
                    </a>
                </div>
            </article>
        @empty
            <div class="warehouse-empty-state warehouse-inventory-empty">
                <i class="ri-inbox-line"></i>
                <span>No encontramos productos con lotes para esos filtros.</span>
            </div>
        @endforelse
    </section>

    <div class="warehouse-pagination">
        {{ $productsWithLots->links() }}
    </div>
</div>
@endsection
