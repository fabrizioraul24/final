@extends('reports.layout')

@section('content')
    @php
        $periodStart = \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y');
        $periodEnd = \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y');
        $delivered = $sales->where('status', 'entregado')->count();
        $pending = $sales->where('status', 'sin_entregar')->count();
        $cancelled = $sales->where('status', 'cancelado')->count();
        $unitsCount = $sales->sum(fn($sale) => $sale->items->sum('quantity'));
        $average = $totals['count'] > 0 ? $totals['amount'] / $totals['count'] : 0;
        $maxDaily = max($dailyBreakdown->pluck('total')->all() ?: [1]);
    @endphp

    <style>
        .vendor-sales-report .summary { display: table; width: 100%; border-spacing: 0.45rem 0; table-layout: fixed; }
        .vendor-sales-report .summary-card { display: table-cell; width: 33.333%; min-height: 58px; padding: 0.55rem 0.7rem; vertical-align: top; }
        .vendor-sales-report .summary-card span { font-size: 0.98rem; }
        .summary-card.blue { border-left: 5px solid #4e6baf; }
        .summary-card.green { border-left: 5px solid #10b981; }
        .summary-card.cyan { border-left: 5px solid #0ea5e9; }
        .summary-card.amber { border-left: 5px solid #f59e0b; }
        .summary-card.rose { border-left: 5px solid #e11d48; }
        .report-note { margin: 0.2rem 0 0.9rem; color: #5f6a85; font-size: 0.84rem; }
        .bar-fill.green { background: #10b981; }
        .bar-fill.blue { background: #4e6baf; }
        .bar-fill.amber { background: #f59e0b; }
        .bar-fill.rose { background: #e11d48; }
        .sales-table { margin-top: 0.75rem; }
        .sales-table th,
        .sales-table td { font-size: 0.65rem; padding: 0.38rem 0.42rem; vertical-align: top; }
        .sales-table th:nth-child(1) { width: 8%; }
        .sales-table th:nth-child(2) { width: 27%; }
        .sales-table th:nth-child(3) { width: 15%; }
        .sales-table th:nth-child(4) { width: 15%; }
        .sales-table th:nth-child(5) { width: 15%; }
        .sales-table th:nth-child(6) { width: 9%; }
        .sales-table th:nth-child(7) { width: 11%; }
        .client-name { color: #1c1c2d; font-weight: 700; }
        .client-meta { display: block; margin-top: 0.12rem; color: #5f6a85; font-size: 0.58rem; }
        .status-pill { display: inline-block; min-width: 70px; padding: 0.18rem 0.45rem; border-radius: 999px; font-size: 0.58rem; font-weight: 700; text-align: center; }
        .status-pill.delivered { background: #dcfce7; color: #047857; }
        .status-pill.pending { background: #fef3c7; color: #b45309; }
        .status-pill.cancelled { background: #ffe4e6; color: #be123c; }
        .status-pill.default { background: #e0ecff; color: #4e6baf; }
    </style>

    <div class="vendor-sales-report">
        <p class="report-note">
            Vendedor: {{ $seller->name ?? 'Vendedor' }}. Periodo {{ $periodStart }} - {{ $periodEnd }}.
        </p>

        <div class="summary">
            <div class="summary-card blue">
                <strong>Ventas</strong>
                <span>{{ $totals['count'] }}</span>
            </div>
            <div class="summary-card green">
                <strong>Monto total</strong>
                <span>Bs {{ number_format((float) $totals['amount'], 2) }}</span>
            </div>
            <div class="summary-card cyan">
                <strong>Ticket promedio</strong>
                <span>Bs {{ number_format((float) $average, 2) }}</span>
            </div>
        </div>

        <div class="summary">
            <div class="summary-card amber">
                <strong>Pendientes</strong>
                <span>{{ $pending }}</span>
            </div>
            <div class="summary-card green">
                <strong>Entregadas</strong>
                <span>{{ $delivered }}</span>
            </div>
            <div class="summary-card rose">
                <strong>Unidades</strong>
                <span>{{ number_format((float) $unitsCount, 0) }}</span>
            </div>
        </div>

        <div class="chart-block">
            <p class="chart-title">Ventas por dia</p>
            @forelse($dailyBreakdown as $day)
                <div class="bar-row">
                    <span class="bar-label">{{ $day['date'] }}</span>
                    <div class="bar-track"><div class="bar-fill blue" style="width: {{ (($day['total'] ?? 0) / max($maxDaily, 1)) * 100 }}%;"></div></div>
                    <span class="bar-value">Bs {{ number_format((float) ($day['total'] ?? 0), 2) }}</span>
                </div>
            @empty
                <p class="report-note">Sin movimientos diarios para este periodo.</p>
            @endforelse
        </div>

        <div class="chart-block">
            <p class="chart-title">Estado de ventas</p>
            @php $stateTotal = max($sales->count(), 1); @endphp
            <div class="bar-row">
                <span class="bar-label">Entregadas</span>
                <div class="bar-track"><div class="bar-fill green" style="width: {{ ($delivered / $stateTotal) * 100 }}%;"></div></div>
                <span class="bar-value">{{ $delivered }}</span>
            </div>
            <div class="bar-row">
                <span class="bar-label">Pendientes</span>
                <div class="bar-track"><div class="bar-fill amber" style="width: {{ ($pending / $stateTotal) * 100 }}%;"></div></div>
                <span class="bar-value">{{ $pending }}</span>
            </div>
            @if($cancelled > 0)
                <div class="bar-row">
                    <span class="bar-label">Canceladas</span>
                    <div class="bar-track"><div class="bar-fill rose" style="width: {{ ($cancelled / $stateTotal) * 100 }}%;"></div></div>
                    <span class="bar-value">{{ $cancelled }}</span>
                </div>
            @endif
        </div>

        <h3 style="margin-top:1.3rem;">Detalle de ventas</h3>
        <table class="sales-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th>Pago</th>
                    <th>Fecha</th>
                    <th>Items</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    @php
                        $statusClass = match ($sale->status) {
                            'entregado' => 'delivered',
                            'sin_entregar' => 'pending',
                            'cancelado' => 'cancelled',
                            default => 'default',
                        };
                        $client = $sale->company->name ?? $sale->customer?->user?->name ?? 'Sin cliente';
                        $city = $sale->company->city ?? $sale->customer?->city ?? 'Ciudad no registrada';
                    @endphp
                    <tr>
                        <td>#{{ $sale->id }}</td>
                        <td>
                            <span class="client-name">{{ $client }}</span>
                            <span class="client-meta">{{ $city }}</span>
                        </td>
                        <td><span class="status-pill {{ $statusClass }}">{{ $statusLabels[$sale->status] ?? ucfirst(str_replace('_', ' ', $sale->status)) }}</span></td>
                        <td>{{ $paymentLabels[$sale->payment_method] ?? 'Sin metodo' }}</td>
                        <td>{{ optional($sale->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->items->count() }} / {{ number_format((float) $sale->items->sum('quantity'), 0) }} uds</td>
                        <td><strong>Bs {{ number_format((float) $sale->total_amount, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:1rem;">No se encontraron ventas para este periodo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
