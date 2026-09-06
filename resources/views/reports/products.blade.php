@extends('reports.layout')

@section('content')
    @php
        $active = $products->where('is_active', true)->count();
        $inactive = $products->where('is_active', false)->count();
        $byCategory = $products->groupBy(fn($p) => $p->category->name ?? 'Sin categoria')->map->count()->sortDesc();
        $maxCat = max($byCategory->values()->all() ?: [1]);
        $totalProd = max($active + $inactive, 1);
        $totalStock = $products->sum(fn($product) => $product->inventory?->sum('quantity') ?? 0);
        $lowStock = $products->filter(fn($product) => ($product->inventory?->sum('quantity') ?? 0) <= (int) $product->min_quantity)->count();
    @endphp

    <style>
        .products-report .summary {
            display: table;
            width: 100%;
            border-spacing: 0.45rem 0;
            table-layout: fixed;
        }
        .products-report .summary-card {
            display: table-cell;
            width: 33.333%;
            min-height: 58px;
            padding: 0.55rem 0.7rem;
            vertical-align: top;
        }
        .products-report .summary-card span { font-size: 0.98rem; }
        .summary-card.blue { border-left: 5px solid #4e6baf; }
        .summary-card.green { border-left: 5px solid #10b981; }
        .summary-card.rose { border-left: 5px solid #e11d48; }
        .summary-card.amber { border-left: 5px solid #f59e0b; }
        .report-note { margin: 0.2rem 0 0.9rem; color: #5f6a85; font-size: 0.86rem; }
        .status-pill { display: inline-block; min-width: 58px; padding: 0.18rem 0.45rem; border-radius: 999px; font-size: 0.68rem; font-weight: 700; text-align: center; }
        .status-pill.active { background: #dcfce7; color: #047857; }
        .status-pill.inactive { background: #ffe4e6; color: #be123c; }
        .bar-fill.green { background: #10b981; }
        .bar-fill.rose { background: #e11d48; }
        .bar-fill.amber { background: #f59e0b; }
        .bar-fill.cyan { background: #0ea5e9; }
        .products-report .chart-block { padding: 0.75rem; margin-top: 0.8rem; }
        .products-report .bar-row { margin: 0.28rem 0; font-size: 0.74rem; }
        .products-report .bar-track { height: 9px; }
        .products-report .bar-fill { height: 9px; }
        .catalog-table { margin-top: 0.65rem; }
        .catalog-table th,
        .catalog-table td { font-size: 0.64rem; padding: 0.34rem 0.4rem; vertical-align: top; }
        .catalog-table th:nth-child(1) { width: 13%; }
        .catalog-table th:nth-child(2) { width: 31%; }
        .catalog-table th:nth-child(3) { width: 18%; }
        .catalog-table th:nth-child(4) { width: 15%; }
        .catalog-table th:nth-child(5) { width: 14%; }
        .catalog-table th:nth-child(6) { width: 9%; }
        .product-name { color: #1c1c2d; font-weight: 700; }
        .product-meta { display: block; margin-top: 0.12rem; color: #5f6a85; font-size: 0.58rem; }
        .stock-line { display: block; color: #1c1c2d; font-weight: 700; }
        .stock-range { display: block; margin-top: 0.1rem; color: #5f6a85; font-size: 0.58rem; }
        .price-line { display: block; white-space: nowrap; }
        .price-line + .price-line { margin-top: 0.16rem; color: #5f6a85; }
    </style>

    <div class="products-report">
        <p class="report-note">
            Catalogo filtrado por categoria {{ $filters['category'] ?? 'Todas' }} y estado {{ $filters['status'] ?? 'Todos' }}.
        </p>

        <div class="summary">
            <div class="summary-card blue">
                <strong>Total productos</strong>
                <span>{{ $products->count() }}</span>
            </div>
            <div class="summary-card green">
                <strong>Productos activos</strong>
                <span>{{ $active }}</span>
            </div>
            <div class="summary-card rose">
                <strong>Stock bajo</strong>
                <span>{{ $lowStock }}</span>
            </div>
        </div>

        <div class="summary">
            <div class="summary-card amber">
                <strong>Stock total</strong>
                <span>{{ number_format((float) $totalStock, 0) }} uds</span>
            </div>
            <div class="summary-card blue">
                <strong>Categoria</strong>
                <span>{{ $filters['category'] ?? 'Todas' }}</span>
            </div>
            <div class="summary-card green">
                <strong>Estado</strong>
                <span>{{ $filters['status'] ?? 'Todos' }}</span>
            </div>
        </div>

        <div class="chart-block">
            <p class="chart-title">Estado del catalogo</p>
            <div class="bar-row">
                <span class="bar-label">Activos</span>
                <div class="bar-track"><div class="bar-fill green" style="width: {{ ($active / $totalProd) * 100 }}%;"></div></div>
                <span class="bar-value">{{ $active }}</span>
            </div>
            <div class="bar-row">
                <span class="bar-label">Inactivos</span>
                <div class="bar-track"><div class="bar-fill rose" style="width: {{ ($inactive / $totalProd) * 100 }}%;"></div></div>
                <span class="bar-value">{{ $inactive }}</span>
            </div>
            <div class="bar-row">
                <span class="bar-label">Stock bajo</span>
                <div class="bar-track"><div class="bar-fill amber" style="width: {{ ($lowStock / $totalProd) * 100 }}%;"></div></div>
                <span class="bar-value">{{ $lowStock }}</span>
            </div>
        </div>

        <div class="chart-block">
            <p class="chart-title">Productos por categoria</p>
            @forelse($byCategory->take(6) as $name => $count)
                <div class="bar-row">
                    <span class="bar-label">{{ $name }}</span>
                    <div class="bar-track"><div class="bar-fill cyan" style="width: {{ ($count / max($maxCat, 1)) * 100 }}%;"></div></div>
                    <span class="bar-value">{{ $count }}</span>
                </div>
            @empty
                <p class="report-note">Sin categorias para mostrar.</p>
            @endforelse
        </div>

        <h3 style="margin-top:1.4rem;">Detalle del catalogo</h3>
        <table class="catalog-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Producto</th>
                    <th>Categoria</th>
                    <th>Precios</th>
                    <th>Stock</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    @php
                        $stock = (int) ($product->inventory?->sum('quantity') ?? 0);
                        $isLow = $stock <= (int) $product->min_quantity;
                    @endphp
                    <tr>
                        <td>{{ $product->sku }}</td>
                        <td>
                            <span class="product-name">{{ $product->name }}</span>
                            <span class="product-meta">Min {{ $product->min_quantity }} uds / Max {{ $product->max_quantity }} uds</span>
                        </td>
                        <td>{{ $product->category->name ?? 'Sin categoria' }}</td>
                        <td>
                            <span class="price-line">Publico: Bs {{ number_format($product->suggested_price_public, 2) }}</span>
                            <span class="price-line">Inst.: Bs {{ number_format($product->price_institutional, 2) }}</span>
                        </td>
                        <td>
                            <span class="stock-line">{{ number_format($stock, 0) }} uds</span>
                            <span class="stock-range">{{ $isLow ? 'Revisar minimo' : 'En rango' }}</span>
                        </td>
                        <td>
                            <span class="status-pill {{ $product->is_active ? 'active' : 'inactive' }}">{{ $product->is_active ? 'Activo' : 'Inactivo' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Sin productos para el filtro seleccionado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
