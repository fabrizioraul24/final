@extends('reports.layout')

@section('content')
    <div class="summary">
        <div class="summary-card">
            <strong>Total categorias</strong>
            <span>{{ $categories->count() }}</span>
        </div>
        <div class="summary-card">
            <strong>Con productos</strong>
            <span>{{ $categories->where('products_count', '>', 0)->count() }}</span>
        </div>
    </div>

    @php
        $active = $categories->whereNull('deleted_at')->count();
        $inactive = $categories->whereNotNull('deleted_at')->count();
        $total = max($active + $inactive, 1);
    @endphp

    <div class="chart-block">
        <p class="chart-title">Activas vs Desactivadas</p>
        <div class="bar-row">
            <span class="bar-label">Activas</span>
            <div class="bar-track"><div class="bar-fill" style="width: {{ ($active/$total)*100 }}%;"></div></div>
            <span class="bar-value">{{ $active }}</span>
        </div>
        <div class="bar-row">
            <span class="bar-label">Desactivadas</span>
            <div class="bar-track"><div class="bar-fill" style="width: {{ ($inactive/$total)*100 }}%;"></div></div>
            <span class="bar-value">{{ $inactive }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripcion</th>
                <th>Productos asociados</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->description ?? 'Sin descripcion' }}</td>
                    <td>{{ $category->products_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

