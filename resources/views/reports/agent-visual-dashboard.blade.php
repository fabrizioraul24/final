@extends('reports.layout')

@section('content')
    @php
        $current = $insights['current'] ?? [];
        $previous = $insights['previous'] ?? [];
        $deltas = $insights['deltas'] ?? [];
        $series = collect($insights['series'] ?? [])->slice(-8)->values();
        $detailSeries = $series->slice(-4)->values();
        $topProducts = collect($insights['top_products'] ?? [])->take(4)->values();
        $maxSales = max($series->max('sales_qty') ?: 1, 1);
        $maxTransfers = max($series->max('transfer_requested') ?: 1, 1);
        $maxWape = max($series->max('avg_wape_percent') ?: 1, 1);
        $maxTopProduct = max($topProducts->max('qty') ?: 1, 1);
        $deltaText = fn ($key) => (($deltas[$key]['percent'] ?? 0) > 0 ? '+' : '').number_format((float) ($deltas[$key]['percent'] ?? 0), 1).'%';
    @endphp

    <style>
        .visual-note { margin: 0.4rem 0 0; color: #5f6a85; font-size: 0.86rem; }
        .summary-card.blue { border-left: 5px solid #4e6baf; }
        .summary-card.green { border-left: 5px solid #10b981; }
        .summary-card.cyan { border-left: 5px solid #0ea5e9; }
        .summary-card.rose { border-left: 5px solid #e11d48; }
        .summary-card { min-height: 62px; padding: 0.65rem 0.75rem; }
        .summary-card span { font-size: 1rem; }
        .summary-card em { display: block; margin-top: 0.2rem; color: #5f6a85; font-size: 0.72rem; font-style: normal; font-weight: 700; }
        .comparison-table { margin-top: 0.8rem; border-collapse: collapse; }
        .comparison-table td { padding: 0.34rem 0.35rem; border: 0; font-size: 0.72rem; vertical-align: middle; }
        .comparison-table .period { width: 3rem; color: #5f6a85; font-weight: 700; }
        .comparison-table .value { width: 4.5rem; color: #1c1c2d; font-weight: 700; text-align: right; white-space: nowrap; }
        .compact-track { height: 8px; overflow: hidden; border-radius: 999px; background: #edf1f7; }
        .compact-fill { height: 8px; border-radius: inherit; }
        .compact-fill.sales { background: #4e6baf; }
        .compact-fill.transfers { background: #f59e0b; }
        .legend { margin-top: 0.75rem; color: #5f6a85; font-size: 0.78rem; }
        .legend span { display: inline-block; margin-right: 1rem; }
        .legend i { display: inline-block; width: 10px; height: 10px; margin-right: 0.3rem; border-radius: 50%; vertical-align: middle; }
        .legend .sales { background: #4e6baf; }
        .legend .transfers { background: #f59e0b; }
        .two-columns { width: 100%; margin-top: 1.2rem; }
        .two-columns > div { display: block; width: 100%; }
        .two-columns > div + div { margin-top: 0.8rem; }
        .mini-row { margin: 0.55rem 0; }
        .mini-row-head { width: 100%; margin-bottom: 0.22rem; color: #5f6a85; font-size: 0.78rem; }
        .mini-row-head span { display: inline-block; width: 68%; }
        .mini-row-head strong { display: inline-block; width: 30%; text-align: right; color: #1c1c2d; }
        .mini-track { height: 11px; overflow: hidden; border-radius: 999px; background: #edf1f7; }
        .mini-fill { height: 100%; border-radius: inherit; background: #4e6baf; }
        .mini-fill.green { background: #10b981; }
        .mini-fill.cyan { background: #0ea5e9; }
        .mini-fill.amber { background: #f59e0b; }
        .mini-fill.rose { background: #e11d48; }
        .legend .wape { background: #e11d48; }
        .legend .product { background: #10b981; }
        .period-table td, .period-table th { font-size: 0.64rem; padding: 0.34rem; }
    </style>

    <p class="visual-note">
        Comparacion {{ ($insights['granularity'] ?? 'week') === 'month' ? 'mensual' : 'semanal' }}
        para {{ $insights['product_name'] ?? 'General' }}:
        {{ $previous['label'] ?? 'periodo anterior' }} contra {{ $current['label'] ?? 'periodo actual' }}.
    </p>

    <div class="summary">
        <div class="summary-card blue">
            <strong>Ventas</strong>
            <span>{{ number_format((float) ($current['sales_qty'] ?? 0), 0) }} uds</span>
            <em>{{ $deltaText('sales_qty') }} vs anterior</em>
        </div>
        <div class="summary-card green">
            <strong>Facturacion</strong>
            <span>Bs {{ number_format((float) ($current['sales_amount'] ?? 0), 0) }}</span>
            <em>{{ $deltaText('sales_amount') }} vs anterior</em>
        </div>
        <div class="summary-card cyan">
            <strong>Traspasos</strong>
            <span>{{ number_format((float) ($current['transfer_requested'] ?? 0), 0) }} uds</span>
            <em>{{ $deltaText('transfer_requested') }} vs anterior</em>
        </div>
        <div class="summary-card rose">
            <strong>WAPE</strong>
            <span>{{ isset($current['avg_wape_percent']) ? number_format((float) $current['avg_wape_percent'], 1).'%' : 'N/D' }}</span>
            <em>{{ $deltaText('avg_wape_percent') }} vs anterior</em>
        </div>
    </div>

    <div class="chart-block">
        <p class="chart-title">Ventas vs traspasos por periodo</p>
        <div class="legend">
            <span><i class="sales"></i>Azul: ventas reales del periodo</span>
            <span><i class="transfers"></i>Amarillo: traspasos solicitados del periodo</span>
        </div>
        <table class="comparison-table">
            <tbody>
                @forelse($series as $item)
                    <tr>
                        <td class="period" rowspan="2">{{ $item['label'] }}</td>
                        <td>Ventas</td>
                        <td><div class="compact-track"><div class="compact-fill sales" style="width: {{ max(3, (($item['sales_qty'] ?? 0) / $maxSales) * 100) }}%;"></div></div></td>
                        <td class="value">{{ number_format((float) ($item['sales_qty'] ?? 0), 0) }} uds</td>
                    </tr>
                    <tr>
                        <td>Traspasos</td>
                        <td><div class="compact-track"><div class="compact-fill transfers" style="width: {{ max(3, (($item['transfer_requested'] ?? 0) / $maxTransfers) * 100) }}%;"></div></div></td>
                        <td class="value">{{ number_format((float) ($item['transfer_requested'] ?? 0), 0) }} uds</td>
                    </tr>
                @empty
                    <tr><td>Sin periodos para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="two-columns">
        <div>
            <div class="chart-block">
                <p class="chart-title">Error WAPE</p>
                <div class="legend">
                    <span><i class="wape"></i>Rojo: porcentaje de error WAPE; mientras mas alto, peor fue la prediccion</span>
                </div>
                @forelse($series as $item)
                    <div class="mini-row">
                        <div class="mini-row-head">
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['avg_wape_percent'] === null ? 'N/D' : number_format((float) $item['avg_wape_percent'], 1).'%' }}</strong>
                        </div>
                        <div class="mini-track">
                            <div class="mini-fill rose" style="width: {{ $item['avg_wape_percent'] === null ? 0 : min(100, (($item['avg_wape_percent'] / $maxWape) * 100)) }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="visual-note">Sin datos de error para mostrar.</p>
                @endforelse
            </div>
        </div>
        <div>
            <div class="chart-block">
                <p class="chart-title">Productos fuertes</p>
                <div class="legend">
                    <span><i class="product"></i>Verde: volumen vendido por producto; barra mas larga significa mayor venta</span>
                </div>
                @forelse($topProducts as $index => $product)
                    <div class="mini-row">
                        <div class="mini-row-head">
                            <span>{{ $product['name'] }}</span>
                            <strong>{{ number_format((float) $product['qty'], 0) }} uds</strong>
                        </div>
                        <div class="mini-track">
                            <div class="mini-fill {{ $index % 3 === 0 ? 'green' : ($index % 3 === 1 ? 'cyan' : 'amber') }}" style="width: {{ max(4, min(100, (($product['qty'] ?? 0) / $maxTopProduct) * 100)) }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="visual-note">Sin productos para mostrar.</p>
                @endforelse
            </div>
        </div>
    </div>

    <h3 style="margin-top:1rem;">Detalle de periodos visibles</h3>
    <table class="period-table">
        <thead>
            <tr>
                <th>Periodo</th>
                <th>Ventas</th>
                <th>Facturacion</th>
                <th>Solicitados</th>
                <th>Recibidos</th>
                <th>WAPE</th>
                <th>Factores</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detailSeries as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td>{{ number_format((float) ($item['sales_qty'] ?? 0), 0) }} uds</td>
                    <td>Bs {{ number_format((float) ($item['sales_amount'] ?? 0), 0) }}</td>
                    <td>{{ number_format((float) ($item['transfer_requested'] ?? 0), 0) }} uds</td>
                    <td>{{ number_format((float) ($item['transfer_received'] ?? 0), 0) }} uds</td>
                    <td>{{ $item['avg_wape_percent'] === null ? 'N/D' : number_format((float) $item['avg_wape_percent'], 1).'%' }}</td>
                    <td>{{ number_format((float) ($item['changed_factors'] ?? 0), 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Sin periodos para mostrar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
