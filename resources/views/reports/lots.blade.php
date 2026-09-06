@extends('reports.layout')

@section('content')
    @php
        $totalStock = $products->sum(fn($product) => (int) ($product->current_stock ?? 0));
        $lowStock = $products->filter(fn($product) => (int) ($product->current_stock ?? 0) <= (int) ($product->min_quantity ?? 0))->count();
        $maxLots = max(collect($expiringTimeline)->pluck('count')->all() ?: [1]);
        $visibleProducts = $products->take(10);
    @endphp

    <style>
        .lots-report .summary { display: table; width: 100%; border-spacing: 0.45rem 0; table-layout: fixed; }
        .lots-report .summary-card { display: table-cell; width: 33.333%; min-height: 58px; padding: 0.55rem 0.7rem; vertical-align: top; }
        .lots-report .summary-card span { font-size: 0.98rem; }
        .summary-card.blue { border-left: 5px solid #4e6baf; }
        .summary-card.green { border-left: 5px solid #10b981; }
        .summary-card.cyan { border-left: 5px solid #0ea5e9; }
        .summary-card.amber { border-left: 5px solid #f59e0b; }
        .summary-card.rose { border-left: 5px solid #e11d48; }
        .bar-fill.green { background: #10b981; }
        .bar-fill.cyan { background: #0ea5e9; }
        .bar-fill.amber { background: #f59e0b; }
        .bar-fill.rose { background: #e11d48; }
        .report-note { margin: 0.2rem 0 0.9rem; color: #5f6a85; font-size: 0.82rem; }
        .product-lot-block { margin-top: 0.8rem; border: 1px solid #e1e4f2; border-radius: 0.75rem; padding: 0.72rem; }
        .product-lot-head { margin-bottom: 0.45rem; }
        .product-lot-head strong { color: #1c1c2d; font-size: 0.86rem; }
        .product-lot-head span { display: block; margin-top: 0.12rem; color: #5f6a85; font-size: 0.62rem; }
        .lot-kpis { margin-bottom: 0.5rem; }
        .lot-kpis span { display: inline-block; margin-right: 0.45rem; padding: 0.18rem 0.46rem; border-radius: 999px; background: #eef2fb; color: #4e6baf; font-size: 0.62rem; font-weight: 700; }
        .lot-kpis .warning { background: #fef3c7; color: #b45309; }
        .lot-table { margin-top: 0.45rem; }
        .lot-table th,
        .lot-table td { font-size: 0.64rem; padding: 0.34rem 0.38rem; vertical-align: top; }
    </style>

    <div class="lots-report">
        <p class="report-note">
            Filtros: busqueda {{ $filters['search'] ?: 'Todas' }}, producto {{ $filters['product'] ?: 'Todos' }},
            bodega {{ $filters['warehouse'] ?: 'Todas' }}.
        </p>

        <div class="summary">
            <div class="summary-card blue">
                <strong>Productos</strong>
                <span>{{ $products->count() }}</span>
            </div>
            <div class="summary-card green">
                <strong>Total lotes</strong>
                <span>{{ $totalLots }}</span>
            </div>
            <div class="summary-card amber">
                <strong>Stock total</strong>
                <span>{{ number_format($totalStock, 0) }} uds</span>
            </div>
        </div>

        <div class="summary">
            <div class="summary-card rose">
                <strong>Stock bajo</strong>
                <span>{{ $lowStock }}</span>
            </div>
            <div class="summary-card cyan">
                <strong>Producto filtro</strong>
                <span>{{ $filters['product'] ?: 'Todos' }}</span>
            </div>
            <div class="summary-card blue">
                <strong>Bodega filtro</strong>
                <span>{{ $filters['warehouse'] ?: 'Todas' }}</span>
            </div>
        </div>

        @if(!empty($filters['expires_at']))
            <div class="chart-block">
                <p class="chart-title">Fecha exacta filtrada</p>
                <p class="report-note">Vence el {{ \Carbon\Carbon::parse($filters['expires_at'])->format('d/m/Y') }}</p>
            </div>
        @endif

        <div class="chart-block">
            <p class="chart-title">Lotes por vencer en los proximos 4 meses</p>
            @forelse($expiringTimeline as $point)
                <div class="bar-row">
                    <span class="bar-label">{{ $point['label'] }}</span>
                    <div class="bar-track">
                        <div class="bar-fill amber" style="width: {{ ($point['count'] / max($maxLots, 1)) * 100 }}%;"></div>
                    </div>
                    <span class="bar-value">{{ $point['count'] }}</span>
                </div>
            @empty
                <p class="report-note">Sin vencimientos proximos.</p>
            @endforelse
        </div>

        @forelse($visibleProducts as $product)
            <div class="product-lot-block">
                <div class="product-lot-head">
                    <strong>{{ $product->name }}</strong>
                    <span>SKU {{ $product->sku }} - {{ $product->category->name ?? 'Sin categoria' }}</span>
                </div>
                <div class="lot-kpis">
                    <span>Stock {{ number_format((float) $product->current_stock, 0) }} uds</span>
                    <span class="{{ (int) $product->current_stock <= (int) $product->min_quantity ? 'warning' : '' }}">Minimo {{ $product->min_quantity ?: 'N/D' }}</span>
                    <span>Lotes {{ $product->lots_count }}</span>
                </div>

                <table class="lot-table">
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
                        @forelse(collect($product->history_rows)->take(5) as $row)
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
                        @empty
                            <tr>
                                <td colspan="5">Sin lotes registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @empty
            <div class="chart-block">
                <p style="margin:0;color:#5f6a85;">Sin productos con lotes para los filtros aplicados.</p>
            </div>
        @endforelse
    </div>
@endsection
