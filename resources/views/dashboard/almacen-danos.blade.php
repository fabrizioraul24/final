@extends('layouts.sidebar-almacen')

@section('title', 'Registro de danos | Pil Andina')
@section('page-title', 'Registro de danos')

@section('content')
<div class="warehouse-damages-page">
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

    <section class="fit-users-header">
        <div class="fit-users-header-left">
            <div class="fit-header-icon"><i class="ri-alert-line"></i></div>
            <div>
                <h1>Control de Danos</h1>
                <p>Registra incidencias por lote y descuenta unidades afectadas del inventario fisico.</p>
            </div>
        </div>
        <div class="warehouse-inventory-branch">
            <span>Bodega activa</span>
            <strong>{{ $targetWarehouse?->name ?? 'La Paz' }}</strong>
        </div>
        <a href="{{ route('dashboard.almacen.damages.create') }}" class="fit-primary-button compact">
            <i class="ri-add-line"></i>
            <span>Registrar dano</span>
        </a>
    </section>

    <section class="fit-metric-grid warehouse-metric-grid">
        <div class="fit-metric-card orange">
            <span><small>Reportes</small><strong>{{ $stats['reports'] }}</strong><em>Incidencias registradas</em></span>
            <span class="fit-metric-icon"><i class="ri-flag-2-line"></i></span>
        </div>
        <div class="fit-metric-card rose">
            <span><small>Unidades afectadas</small><strong>{{ number_format((int) $stats['units']) }}</strong><em>Retiradas de stock</em></span>
            <span class="fit-metric-icon"><i class="ri-close-circle-line"></i></span>
        </div>
        <div class="fit-metric-card blue">
            <span><small>Productos afectados</small><strong>{{ $stats['products'] }}</strong><em>Con historial de dano</em></span>
            <span class="fit-metric-icon"><i class="ri-box-3-line"></i></span>
        </div>
        <div class="fit-metric-card indigo">
            <span><small>Hoy</small><strong>{{ $stats['today'] }}</strong><em>Reportes del dia</em></span>
            <span class="fit-metric-icon"><i class="ri-calendar-check-line"></i></span>
        </div>
    </section>

    <section class="fit-filter-card warehouse-damages-filter-card">
        <form method="GET" action="{{ route('dashboard.almacen.damages') }}" class="fit-filter-form warehouse-damages-filter" data-live-search-form>
            <label class="fit-search-control" for="damage_search">
                <i class="ri-search-line"></i>
                <input type="search" id="damage_search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar producto, SKU o descripcion..." data-live-search-input>
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
                <span>Filtrar</span>
            </button>
            @if(($filters['search'] ?? null) || ($filters['product_id'] ?? null))
                <a href="{{ route('dashboard.almacen.damages') }}" class="fit-clear-button">Limpiar filtros</a>
            @endif
        </form>
    </section>

    <section class="warehouse-inventory-grid warehouse-damages-product-grid">
        @forelse($productsWithLots as $product)
            <article class="warehouse-inventory-card warehouse-damage-product-card">
                <div class="warehouse-inventory-product">
                    <img src="{{ $product->getImageUrl() }}" alt="{{ $product->name }}">
                    <div>
                        <h2>{{ $product->name }}</h2>
                        <p>{{ $product->category->name ?? 'Sin categoria' }}</p>
                        <div class="warehouse-inventory-tags">
                            <span>SKU: {{ $product->sku }}</span>
                            <span>{{ $product->lots_count }} lote{{ $product->lots_count === 1 ? '' : 's' }}</span>
                            <span>{{ number_format((int) $product->current_stock) }} uds</span>
                        </div>
                    </div>
                </div>

                <div class="warehouse-inventory-stats">
                    <div><span>Stock actual</span><strong>{{ number_format((int) $product->current_stock) }}</strong></div>
                    <div><span>Lotes activos</span><strong>{{ $product->lots_count }}</strong></div>
                    <div><span>Ubicacion</span><strong>{{ $targetWarehouse?->city ?? 'La Paz' }}</strong></div>
                </div>

                <div class="warehouse-inventory-actions">
                    <a href="{{ route('dashboard.almacen.damages.create', ['product_id' => $product->id]) }}" class="fit-primary-button">
                        <i class="ri-error-warning-line"></i>
                        <span>Registrar dano</span>
                    </a>
                </div>
            </article>
        @empty
            <div class="warehouse-empty-state warehouse-inventory-empty">
                <i class="ri-inbox-line"></i>
                <span>No encontramos productos con lotes activos.</span>
            </div>
        @endforelse
    </section>

    <div class="warehouse-pagination">
        {{ $productsWithLots->links() }}
    </div>

    <section class="warehouse-panel warehouse-damages-history-panel">
        <div class="warehouse-panel-head">
            <div>
                <span class="fit-section-badge orange">Historial</span>
                <h2>Incidencias registradas</h2>
            </div>
            <span class="warehouse-panel-count">{{ $reports->total() }} registros</span>
        </div>

        <div class="fit-table-scroll">
            <table class="fit-users-table warehouse-orders-table">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Almacen</th>
                        <th>Reportado por</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>
                                <code class="fit-code">{{ $report->lot->lote_code ?? 'Sin codigo' }}</code>
                            </td>
                            <td>
                                <strong>{{ $report->product->name ?? 'Producto' }}</strong>
                                <span>SKU: {{ $report->product->sku ?? 'N/D' }}</span>
                            </td>
                            <td>
                                <strong>{{ number_format((int) $report->damaged_qty) }}</strong>
                                <span>Unidades</span>
                            </td>
                            <td>
                                <strong>{{ $report->warehouse->name ?? 'Almacen' }}</strong>
                                <span>{{ $report->warehouse->city ?? 'La Paz' }}</span>
                            </td>
                            <td>
                                <strong>{{ $report->reporter->name ?? 'Sistema' }}</strong>
                                <span>Responsable</span>
                            </td>
                            <td>
                                <strong>{{ optional($report->created_at)->format('d/m/Y') }}</strong>
                                <span>{{ optional($report->created_at)->format('H:i') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="warehouse-empty-cell">Sin incidencias registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="warehouse-pagination">
            {{ $reports->links() }}
        </div>
    </section>
</div>
@endsection
