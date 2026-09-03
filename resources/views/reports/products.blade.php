@extends('reports.layout')

@section('content')
    <div class="summary">
        <div class="summary-card">
            <strong>Total productos</strong>
            <span>{{ $products->count() }}</span>
        </div>
        <div class="summary-card">
            <strong>Categoria</strong>
            <span>{{ $filters['category'] ?? 'Todas' }}</span>
        </div>
        <div class="summary-card">
            <strong>Estado</strong>
            <span>{{ $filters['status'] ?? 'Todos' }}</span>
        </div>
    </div>

    @php
        $active = $products->where('is_active', true)->count();
        $inactive = $products->where('is_active', false)->count();
        $byCategory = $products->groupBy(fn($p) => $p->category->name ?? 'Sin categoria')->map->count();
        $maxCat = max($byCategory->values()->all() ?: [1]);
        $totalProd = max($active + $inactive, 1);
    @endphp

    <div class="chart-block">
        <p class="chart-title">Activos vs Inactivos</p>
        <div class="bar-row">
            <span class="bar-label">Activos</span>
            <div class="bar-track"><div class="bar-fill" style="width: {{ ($active/$totalProd)*100 }}%;"></div></div>
            <span class="bar-value">{{ $active }}</span>
        </div>
        <div class="bar-row">
            <span class="bar-label">Inactivos</span>
            <div class="bar-track"><div class="bar-fill" style="width: {{ ($inactive/$totalProd)*100 }}%;"></div></div>
            <span class="bar-value">{{ $inactive }}</span>
        </div>
    </div>

    <div class="chart-block">
        <p class="chart-title">Productos por categoria</p>
        @foreach($byCategory as $name => $count)
            <div class="bar-row">
                <span class="bar-label">{{ $name }}</span>
                <div class="bar-track"><div class="bar-fill" style="width: {{ ($count / max($maxCat,1))*100 }}%;"></div></div>
                <span class="bar-value">{{ $count }}</span>
            </div>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Producto</th>
                <th>Categoria</th>
                <th>Precio publico</th>
                <th>Precio institucional</th>
                <th>Estado</th>
                <th>Stock minimo</th>
                <th>Stock maximo</th>
                <th>Stock total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? 'Sin categoria' }}</td>
                    <td>Bs {{ number_format($product->suggested_price_public, 2) }}</td>
                    <td>Bs {{ number_format($product->price_institutional, 2) }}</td>
                    <td>{{ $product->is_active ? 'Activo' : 'Inactivo' }}</td>
                    <td>{{ $product->min_quantity }}</td>
                    <td>{{ $product->max_quantity }}</td>
                    <td>{{ $product->inventory?->sum('quantity') ?? 0 }} uds</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
