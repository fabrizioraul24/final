@extends('reports.layout')

@section('content')
    @php
        $criticalAlerts = $alertProductCards->whereIn('severity', ['critical', 'expired'])->count();
        $warningAlerts = $alertProductCards->where('severity', 'warning')->count();
        $lowStock = count($alerts['low_stock'] ?? []);
        $expiring = count($alerts['expiring'] ?? []);
        $evaluated = $forecasts->count();
        $pending = $pendingRequests->count();
        $history = $recentRequests->count();
        $avgForecast = $evaluated > 0 ? $forecasts->avg('forecast_7_days') : 0;
        $criticalForecasts = $forecasts->filter(fn ($item) => (float) ($item['result'] ?? 0) < 0)->count();
        $maxSummary = max($evaluated, $criticalAlerts, $warningAlerts, $lowStock, $expiring, $pending, 1);
        $lastRunLabel = $lastRunAt ? \Carbon\Carbon::parse($lastRunAt)->format('d/m/Y H:i') : 'Sin registro';
    @endphp

    <style>
        .agent-report .summary { display: table; width: 100%; border-spacing: 0.45rem 0; table-layout: fixed; }
        .agent-report .summary-card { display: table-cell; width: 33.333%; min-height: 58px; padding: 0.55rem 0.7rem; vertical-align: top; }
        .agent-report .summary-card span { font-size: 0.98rem; }
        .summary-card.blue { border-left: 5px solid #4e6baf; }
        .summary-card.green { border-left: 5px solid #10b981; }
        .summary-card.cyan { border-left: 5px solid #0ea5e9; }
        .summary-card.amber { border-left: 5px solid #f59e0b; }
        .summary-card.rose { border-left: 5px solid #e11d48; }
        .summary-card.indigo { border-left: 5px solid #6366f1; }
        .report-note { margin: 0.2rem 0 0.9rem; color: #5f6a85; font-size: 0.84rem; }
        .bar-fill.blue { background: #4e6baf; }
        .bar-fill.green { background: #10b981; }
        .bar-fill.cyan { background: #0ea5e9; }
        .bar-fill.amber { background: #f59e0b; }
        .bar-fill.rose { background: #e11d48; }
        .agent-report .chart-block { padding: 0.72rem; margin-top: 0.8rem; }
        .agent-report .bar-row { margin: 0.28rem 0; font-size: 0.72rem; }
        .agent-report .bar-track { height: 9px; }
        .agent-report .bar-fill { height: 9px; }
        .agent-table { margin-top: 0.7rem; }
        .agent-table th,
        .agent-table td { font-size: 0.62rem; padding: 0.34rem 0.38rem; vertical-align: top; }
        .agent-table th:nth-child(1) { width: 27%; }
        .agent-table th:nth-child(2) { width: 11%; }
        .agent-table th:nth-child(3) { width: 13%; }
        .agent-table th:nth-child(4) { width: 12%; }
        .agent-table th:nth-child(5) { width: 12%; }
        .agent-table th:nth-child(6) { width: 12%; }
        .agent-table th:nth-child(7) { width: 13%; }
        .product-name { display: block; color: #1c1c2d; font-weight: 700; }
        .product-meta { display: block; margin-top: 0.1rem; color: #5f6a85; font-size: 0.56rem; }
        .status-pill { display: inline-block; min-width: 66px; padding: 0.16rem 0.42rem; border-radius: 999px; font-size: 0.58rem; font-weight: 700; text-align: center; }
        .status-pill.ok { background: #dcfce7; color: #047857; }
        .status-pill.warn { background: #fef3c7; color: #b45309; }
        .status-pill.danger { background: #ffe4e6; color: #be123c; }
        .status-pill.info { background: #e0f2fe; color: #0369a1; }
        .problem-line { display: block; margin-bottom: 0.16rem; color: #334155; }
        .problem-line strong { color: #1c1c2d; }
    </style>

    <div class="agent-report">
        <p class="report-note">
            Reporte operativo del agente inteligente. Filtro: {{ $filters['search'] ?? 'Todos' }}.
            Categoria: {{ $filters['category'] ?? 'Todas' }}. Ultima revision: {{ $lastRunLabel }}.
        </p>

        @if($error)
            <div class="chart-block">
                <p class="chart-title">Observacion del agente</p>
                <p style="margin:0;color:#991b1b;">{{ $error }}</p>
            </div>
        @endif

        <div class="summary">
            <div class="summary-card blue">
                <strong>Productos evaluados</strong>
                <span>{{ number_format($evaluated, 0) }}</span>
            </div>
            <div class="summary-card rose">
                <strong>Alertas criticas</strong>
                <span>{{ number_format($criticalAlerts, 0) }}</span>
            </div>
            <div class="summary-card amber">
                <strong>Solicitudes pendientes</strong>
                <span>{{ number_format($pending, 0) }}</span>
            </div>
        </div>

        <div class="summary">
            <div class="summary-card green">
                <strong>Demanda promedio 7 dias</strong>
                <span>{{ number_format((float) $avgForecast, 0) }} uds</span>
            </div>
            <div class="summary-card cyan">
                <strong>Lotes por vencer</strong>
                <span>{{ number_format($expiring, 0) }}</span>
            </div>
            <div class="summary-card indigo">
                <strong>Historial reciente</strong>
                <span>{{ number_format($history, 0) }}</span>
            </div>
        </div>

        <div class="chart-block">
            <p class="chart-title">Pulso operativo del agente</p>
            @foreach([
                ['label' => 'Productos evaluados', 'value' => $evaluated, 'class' => 'blue'],
                ['label' => 'Resultado negativo', 'value' => $criticalForecasts, 'class' => 'rose'],
                ['label' => 'Stock bajo', 'value' => $lowStock, 'class' => 'amber'],
                ['label' => 'Lotes por vencer', 'value' => $expiring, 'class' => 'cyan'],
                ['label' => 'Solicitudes pendientes', 'value' => $pending, 'class' => 'green'],
            ] as $row)
                <div class="bar-row">
                    <span class="bar-label">{{ $row['label'] }}</span>
                    <div class="bar-track"><div class="bar-fill {{ $row['class'] }}" style="width: {{ max(2, ($row['value'] / $maxSummary) * 100) }}%;"></div></div>
                    <span class="bar-value">{{ number_format($row['value'], 0) }}</span>
                </div>
            @endforeach
        </div>

        <h3 style="margin-top:1.25rem;">Evaluaciones de reposicion</h3>
        <table class="agent-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>SKU</th>
                    <th>Demanda 7d</th>
                    <th>Stock</th>
                    <th>Programado</th>
                    <th>Resultado</th>
                    <th>Estado operativo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($forecasts->take(28) as $item)
                    @php
                        $level = $item['decision_level'] ?? 'optimal';
                        $decisionClass = $level === 'critical' ? 'danger' : ($level === 'preventive' ? 'warn' : 'ok');
                    @endphp
                    <tr>
                        <td>
                            <span class="product-name">{{ $item['name'] }}</span>
                            <span class="product-meta">{{ $item['category'] ?? 'Sin categoria' }}</span>
                        </td>
                        <td>{{ $item['sku'] ?? 'N/D' }}</td>
                        <td>{{ number_format((float) ($item['forecast_7_days'] ?? 0), 0) }} uds</td>
                        <td>{{ number_format((float) ($item['stock'] ?? 0), 0) }} uds</td>
                        <td>{{ number_format((float) ($item['in_transit'] ?? 0), 0) }} uds</td>
                        <td>{{ number_format((float) ($item['result'] ?? 0), 0) }} uds</td>
                        <td><span class="status-pill {{ $decisionClass }}">{{ $item['decision'] ?? 'Optimo' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7">Sin evaluaciones para el filtro seleccionado.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h3 style="margin-top:1.25rem;">Alertas por producto</h3>
        <table class="agent-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>SKU</th>
                    <th>Categoria</th>
                    <th>Estado</th>
                    <th colspan="3">Problemas detectados</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alertProductCards->take(18) as $card)
                    @php
                        $severityClass = in_array($card['severity'] ?? '', ['critical', 'expired'], true) ? 'danger' : (($card['severity'] ?? '') === 'warning' ? 'warn' : 'info');
                    @endphp
                    <tr>
                        <td><span class="product-name">{{ $card['name'] }}</span></td>
                        <td>{{ $card['sku'] ?? 'N/D' }}</td>
                        <td>{{ $card['category'] }}</td>
                        <td><span class="status-pill {{ $severityClass }}">{{ $card['severity_label'] }}</span></td>
                        <td colspan="3">
                            @foreach(collect($card['problems'])->take(2) as $problem)
                                <span class="problem-line"><strong>{{ $problem['label'] }}:</strong> {{ $problem['message'] }}</span>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">Sin alertas operativas para el filtro seleccionado.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h3 style="margin-top:1.25rem;">Solicitudes e historial</h3>
        <table class="agent-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Reposicion sugerida</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th colspan="2">Motivo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingRequests->merge($recentRequests)->take(24) as $request)
                    @php
                        $statusClass = $request->status === 'approved' ? 'ok' : ($request->status === 'rejected' ? 'danger' : 'warn');
                    @endphp
                    <tr>
                        <td>{{ optional($request->created_at)->format('d/m/Y H:i') }}</td>
                        <td><span class="product-name">{{ $request->product?->name ?? 'Producto '.$request->product_id }}</span></td>
                        <td>{{ number_format((float) $request->requested_qty, 0) }} uds</td>
                        <td>{{ $request->priority ?? 'Normal' }}</td>
                        <td><span class="status-pill {{ $statusClass }}">{{ ucfirst($request->status) }}</span></td>
                        <td colspan="2">{{ $request->reason ?: 'Solicitud generada por el agente inteligente.' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">Sin solicitudes del agente para el filtro seleccionado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
