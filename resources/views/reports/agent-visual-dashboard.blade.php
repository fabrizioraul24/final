@extends('reports.layout')

@section('content')
    @php
        $current = $insights['current'] ?? [];
        $previous = $insights['previous'] ?? [];
        $deltas = $insights['deltas'] ?? [];
        $series = collect($insights['series'] ?? [])->slice(-10)->values();
        $detailSeries = $series->slice(-6)->values();
        $dailyWape = collect($insights['daily_wape'] ?? []);
        $topProducts = collect($insights['top_products'] ?? [])->take(6)->values();
        $maxSales = max((float) ($series->max('sales_qty') ?: 1), 1);
        $maxTransfers = max((float) ($series->max('transfer_requested') ?: 1), 1);
        $maxWape = max((float) ($series->max('avg_wape_percent') ?: 1), (float) ($dailyWape->max('wape') ?: 1), 1);
        $maxTopProduct = max((float) ($topProducts->max('qty') ?: 1), 1);
        $deltaText = fn ($key) => (($deltas[$key]['percent'] ?? 0) > 0 ? '+' : '').number_format((float) ($deltas[$key]['percent'] ?? 0), 1).'%';
    @endphp

    <style>
        .visual-agent-report .summary { display: table; width: 100%; border-spacing: 0.45rem 0; table-layout: fixed; }
        .visual-agent-report .summary-card { display: table-cell; width: 25%; min-height: 58px; padding: 0.55rem 0.7rem; vertical-align: top; }
        .visual-agent-report .summary-card span { font-size: 0.96rem; }
        .summary-card.blue { border-left: 5px solid #4e6baf; }
        .summary-card.green { border-left: 5px solid #10b981; }
        .summary-card.cyan { border-left: 5px solid #0ea5e9; }
        .summary-card.rose { border-left: 5px solid #e11d48; }
        .summary-card.amber { border-left: 5px solid #f59e0b; }
        .summary-card em { display: block; margin-top: 0.18rem; color: #5f6a85; font-size: 0.68rem; font-style: normal; font-weight: 700; }
        .visual-note { margin: 0.2rem 0 0.9rem; color: #5f6a85; font-size: 0.84rem; }
        .visual-agent-report .chart-block { padding: 0.72rem; margin-top: 0.8rem; }
        .split { display: table; width: 100%; border-spacing: 0.55rem 0; table-layout: fixed; }
        .split-cell { display: table-cell; width: 50%; vertical-align: top; }
        .mini-row { margin: 0.38rem 0; }
        .mini-row-head { width: 100%; margin-bottom: 0.18rem; color: #5f6a85; font-size: 0.68rem; }
        .mini-row-head span { display: inline-block; width: 64%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .mini-row-head strong { display: inline-block; width: 34%; text-align: right; color: #1c1c2d; }
        .mini-track { height: 9px; overflow: hidden; border-radius: 999px; background: #edf1f7; }
        .mini-fill { height: 100%; border-radius: inherit; }
        .mini-fill.blue { background: #4e6baf; }
        .mini-fill.green { background: #10b981; }
        .mini-fill.cyan { background: #0ea5e9; }
        .mini-fill.amber { background: #f59e0b; }
        .mini-fill.rose { background: #e11d48; }
        .legend { margin-top: 0.42rem; color: #5f6a85; font-size: 0.66rem; }
        .legend span { display: inline-block; margin-right: 0.7rem; }
        .legend i { display: inline-block; width: 9px; height: 9px; margin-right: 0.25rem; border-radius: 50%; vertical-align: middle; }
        .legend .blue { background: #4e6baf; }
        .legend .green { background: #10b981; }
        .legend .amber { background: #f59e0b; }
        .legend .rose { background: #e11d48; }
        .period-table { margin-top: 0.75rem; }
        .period-table th,
        .period-table td { font-size: 0.6rem; padding: 0.32rem; vertical-align: top; }
    </style>

    <div class="visual-agent-report">
        <p class="visual-note">
            Dashboard comparativo {{ ($insights['granularity'] ?? 'week') === 'month' ? 'mensual' : 'semanal' }}
            para {{ $insights['product_name'] ?? 'General' }}. Periodo actual: {{ $current['label'] ?? 'N/D' }}.
        </p>

        <div class="summary">
            <div class="summary-card blue">
                <strong>Ventas</strong>
                <span>{{ number_format((float) ($current['sales_qty'] ?? 0), 0) }} uds</span>
                <em>{{ $deltaText('sales_qty') }} vs anterior</em>
            </div>
            <div class="summary-card green">
                <strong>Ingresos</strong>
                <span>Bs {{ number_format((float) ($current['sales_amount'] ?? 0), 0) }}</span>
                <em>{{ $deltaText('sales_amount') }} vs anterior</em>
            </div>
            <div class="summary-card cyan">
                <strong>Traspasos</strong>
                <span>{{ number_format((float) ($current['transfer_requested'] ?? 0), 0) }} uds</span>
                <em>{{ $deltaText('transfer_requested') }} vs anterior</em>
            </div>
            <div class="summary-card rose">
                <strong>Error WAPE</strong>
                <span>{{ isset($current['avg_wape_percent']) ? number_format((float) $current['avg_wape_percent'], 1).'%' : 'N/D' }}</span>
                <em>{{ $deltaText('avg_wape_percent') }} vs anterior</em>
            </div>
        </div>

        <div class="chart-block">
            <p class="chart-title">Ventas y traspasos por periodo</p>
            <div class="legend">
                <span><i class="blue"></i>Ventas reales</span>
                <span><i class="amber"></i>Traspasos solicitados</span>
            </div>
            @forelse($series as $item)
                <div class="mini-row">
                    <div class="mini-row-head">
                        <span>{{ $item['label'] }}</span>
                        <strong>{{ number_format((float) ($item['sales_qty'] ?? 0), 0) }} uds</strong>
                    </div>
                    <div class="mini-track"><div class="mini-fill blue" style="width: {{ max(2, (($item['sales_qty'] ?? 0) / $maxSales) * 100) }}%;"></div></div>
                </div>
                <div class="mini-row">
                    <div class="mini-row-head">
                        <span>Traspasos {{ $item['label'] }}</span>
                        <strong>{{ number_format((float) ($item['transfer_requested'] ?? 0), 0) }} uds</strong>
                    </div>
                    <div class="mini-track"><div class="mini-fill amber" style="width: {{ max(2, (($item['transfer_requested'] ?? 0) / $maxTransfers) * 100) }}%;"></div></div>
                </div>
            @empty
                <p class="visual-note">Sin periodos para mostrar.</p>
            @endforelse
        </div>

        <div class="split">
            <div class="split-cell">
                <div class="chart-block">
                    <p class="chart-title">Error WAPE por dia</p>
                    <p class="visual-note">Eje X: dias de la semana. Eje Y: porcentaje de error WAPE.</p>
                    @forelse($dailyWape as $item)
                        <div class="mini-row">
                            <div class="mini-row-head">
                                <span>{{ $item['day'] }} - {{ $item['date'] }}</span>
                                <strong>{{ number_format((float) ($item['wape'] ?? 0), 1) }}%</strong>
                            </div>
                            <div class="mini-track"><div class="mini-fill rose" style="width: {{ max(2, (($item['wape'] ?? 0) / $maxWape) * 100) }}%;"></div></div>
                        </div>
                    @empty
                        <p class="visual-note">Sin datos diarios de error.</p>
                    @endforelse
                </div>
            </div>
            <div class="split-cell">
                <div class="chart-block">
                    <p class="chart-title">Productos con mayor volumen</p>
                    <p class="visual-note">Productos que mas peso tuvieron en la demanda del periodo.</p>
                    @forelse($topProducts as $index => $product)
                        <div class="mini-row">
                            <div class="mini-row-head">
                                <span>{{ $product['name'] }}</span>
                                <strong>{{ number_format((float) ($product['qty'] ?? 0), 0) }} uds</strong>
                            </div>
                            <div class="mini-track">
                                <div class="mini-fill {{ $index % 3 === 0 ? 'green' : ($index % 3 === 1 ? 'cyan' : 'amber') }}" style="width: {{ max(3, (($product['qty'] ?? 0) / $maxTopProduct) * 100) }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="visual-note">Sin productos para mostrar.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <h3 style="margin-top:1rem;">Detalle comparativo</h3>
        <table class="period-table">
            <thead>
                <tr>
                    <th>Periodo</th>
                    <th>Ventas</th>
                    <th>Ingresos</th>
                    <th>Solicitado</th>
                    <th>Recibido</th>
                    <th>WAPE</th>
                    <th>Ajustes</th>
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
                    <tr><td colspan="7">Sin periodos para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
