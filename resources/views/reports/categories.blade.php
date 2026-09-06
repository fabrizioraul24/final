@extends('reports.layout')

@section('content')
    @php
        $active = $categories->whereNull('deleted_at')->count();
        $inactive = $categories->whereNotNull('deleted_at')->count();
        $withProducts = $categories->where('products_count', '>', 0)->count();
        $withoutProducts = $categories->where('products_count', 0)->count();
        $total = max($active + $inactive, 1);
        $totalProducts = $categories->sum('products_count');
        $maxProducts = max($categories->max('products_count') ?: 1, 1);
    @endphp

    <style>
        .categories-report .summary { display: table; width: 100%; border-spacing: 0.45rem 0; table-layout: fixed; }
        .categories-report .summary-card { display: table-cell; width: 33.333%; min-height: 58px; padding: 0.55rem 0.7rem; vertical-align: top; }
        .categories-report .summary-card span { font-size: 0.98rem; }
        .summary-card.blue { border-left: 5px solid #4e6baf; }
        .summary-card.green { border-left: 5px solid #10b981; }
        .summary-card.cyan { border-left: 5px solid #0ea5e9; }
        .summary-card.amber { border-left: 5px solid #f59e0b; }
        .summary-card.rose { border-left: 5px solid #e11d48; }
        .bar-fill.green { background: #10b981; }
        .bar-fill.rose { background: #e11d48; }
        .bar-fill.cyan { background: #0ea5e9; }
        .category-table { margin-top: 0.75rem; }
        .category-table th,
        .category-table td { font-size: 0.68rem; padding: 0.42rem; vertical-align: top; }
        .category-name { color: #1c1c2d; font-weight: 700; }
        .category-description { display: block; margin-top: 0.12rem; color: #5f6a85; font-size: 0.58rem; }
        .status-pill { display: inline-block; min-width: 68px; padding: 0.18rem 0.45rem; border-radius: 999px; font-size: 0.62rem; font-weight: 700; text-align: center; }
        .status-pill.active { background: #dcfce7; color: #047857; }
        .status-pill.inactive { background: #ffe4e6; color: #be123c; }
        .product-count { color: #4e6baf; font-weight: 700; }
    </style>

    <div class="categories-report">
        <div class="summary">
            <div class="summary-card blue">
                <strong>Total categorias</strong>
                <span>{{ $categories->count() }}</span>
            </div>
            <div class="summary-card green">
                <strong>Activas</strong>
                <span>{{ $active }}</span>
            </div>
            <div class="summary-card rose">
                <strong>Desactivadas</strong>
                <span>{{ $inactive }}</span>
            </div>
        </div>

        <div class="summary">
            <div class="summary-card cyan">
                <strong>Con productos</strong>
                <span>{{ $withProducts }}</span>
            </div>
            <div class="summary-card amber">
                <strong>Sin productos</strong>
                <span>{{ $withoutProducts }}</span>
            </div>
            <div class="summary-card blue">
                <strong>Productos asociados</strong>
                <span>{{ $totalProducts }}</span>
            </div>
        </div>

        <div class="chart-block">
            <p class="chart-title">Estado de categorias</p>
            <div class="bar-row">
                <span class="bar-label">Activas</span>
                <div class="bar-track"><div class="bar-fill green" style="width: {{ ($active / $total) * 100 }}%;"></div></div>
                <span class="bar-value">{{ $active }}</span>
            </div>
            <div class="bar-row">
                <span class="bar-label">Desactivadas</span>
                <div class="bar-track"><div class="bar-fill rose" style="width: {{ ($inactive / $total) * 100 }}%;"></div></div>
                <span class="bar-value">{{ $inactive }}</span>
            </div>
        </div>

        <div class="chart-block">
            <p class="chart-title">Productos por categoria</p>
            @forelse($categories->sortByDesc('products_count')->take(8) as $category)
                <div class="bar-row">
                    <span class="bar-label">{{ $category->name }}</span>
                    <div class="bar-track"><div class="bar-fill cyan" style="width: {{ (($category->products_count ?? 0) / $maxProducts) * 100 }}%;"></div></div>
                    <span class="bar-value">{{ $category->products_count }}</span>
                </div>
            @empty
                <p style="margin:0;color:#5f6a85;">Sin categorias para mostrar.</p>
            @endforelse
        </div>

        <h3 style="margin-top:1.3rem;">Detalle de categorias</h3>
        <table class="category-table">
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th>Productos</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>
                            <span class="category-name">{{ $category->name }}</span>
                            <span class="category-description">{{ $category->description ?: 'Sin descripcion' }}</span>
                        </td>
                        <td><span class="product-count">{{ $category->products_count }}</span></td>
                        <td>
                            <span class="status-pill {{ is_null($category->deleted_at) ? 'active' : 'inactive' }}">
                                {{ is_null($category->deleted_at) ? 'Activa' : 'Desactivada' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Sin categorias para el filtro seleccionado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
