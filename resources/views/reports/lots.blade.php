@extends('reports.layout')

@section('content')
    <div class="summary">
        <div class="summary-card">
            <strong>Productos</strong>
            <span>{{ $products->count() }}</span>
        </div>
        <div class="summary-card">
            <strong>Total lotes</strong>
            <span>{{ $totalLots }}</span>
        </div>
        <div class="summary-card">
            <strong>Busqueda</strong>
            <span>{{ $filters['search'] ?: 'Todas' }}</span>
        </div>
        <div class="summary-card">
            <strong>Producto</strong>
            <span>{{ $filters['product'] ?: 'Todos' }}</span>
        </div>
        <div class="summary-card">
            <strong>Bodega</strong>
            <span>{{ $filters['warehouse'] ?: 'Todas' }}</span>
        </div>
    </div>

    @if(!empty($filters['expires_at']))
        <div class="summary" style="margin-top:0.75rem;">
            <div class="summary-card">
                <strong>Vence el</strong>
                <span>{{ \Carbon\Carbon::parse($filters['expires_at'])->format('d/m/Y') }}</span>
            </div>
        </div>
    @endif

    @php
        $maxLots = max(collect($expiringTimeline)->pluck('count')->all() ?: [1]);
    @endphp

    <div class="chart-block">
        <p class="chart-title">Lotes por vencer en los proximos 4 meses</p>
        @foreach($expiringTimeline as $point)
            <div class="bar-row">
                <span class="bar-label">{{ $point['label'] }}</span>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ ($point['count'] / max($maxLots, 1)) * 100 }}%;"></div>
                </div>
                <span class="bar-value">{{ $point['count'] }} lote(s)</span>
            </div>
        @endforeach
    </div>

    @foreach($products as $product)
        <div class="chart-block" style="margin-top:1.4rem;">
            <p class="chart-title">{{ $product->name }} · {{ $product->sku }}</p>
            <div class="summary" style="margin-top:0;">
                <div class="summary-card">
                    <strong>Categoria</strong>
                    <span>{{ $product->category->name ?? 'Sin categoria' }}</span>
                </div>
                <div class="summary-card">
                    <strong>Stock actual</strong>
                    <span>{{ $product->current_stock }}</span>
                </div>
                <div class="summary-card">
                    <strong>Stock minimo</strong>
                    <span>{{ $product->min_quantity ?: 'No definido' }}</span>
                </div>
                <div class="summary-card">
                    <strong>Total lotes</strong>
                    <span>{{ $product->lots_count }}</span>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Stock</th>
                        <th>Bodega</th>
                        <th>Vence</th>
                        <th>Ultimo movimiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($product->history_rows as $row)
                        <tr>
                            <td>{{ $row['code'] }}</td>
                            <td>{{ $row['quantity'] }} uds</td>
                            <td>{{ $row['warehouse'] }}</td>
                            <td>{{ $row['expires_at'] }}</td>
                            <td>
                                {{ $row['last_movement'] }}
                                @if(!is_null($row['last_movement_qty']))
                                    ({{ $row['last_movement_qty'] > 0 ? '+' : '' }}{{ $row['last_movement_qty'] }})
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    @if($products->isEmpty())
        <div class="chart-block">
            <p style="margin:0;">Sin productos con lotes para los filtros aplicados.</p>
        </div>
    @endif
@endsection
