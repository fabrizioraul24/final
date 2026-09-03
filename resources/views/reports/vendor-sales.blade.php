@extends('reports.layout')

@section('content')
    <div class="summary">
        <div class="summary-card">
            <strong>Vendedor</strong>
            <span>{{ $seller->name ?? 'Vendedor' }}</span>
        </div>
        <div class="summary-card">
            <strong>Periodo</strong>
            <span>{{ \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') }}</span>
        </div>
        <div class="summary-card">
            <strong>Ventas</strong>
            <span>{{ $totals['count'] }}</span>
        </div>
        <div class="summary-card">
            <strong>Monto total</strong>
            <span>Bs {{ number_format($totals['amount'], 2) }}</span>
        </div>
    </div>

    <div class="chart-block">
        <p class="chart-title">Actividad semanal (Bs facturados)</p>
        @php
            $chartTotals = $chart['totals'] ?? [];
            $chartLabels = $chart['labels'] ?? [];
            $maxTotal = max(1, collect($chartTotals)->max() ?? 1);
        @endphp
        @forelse($chartLabels as $index => $label)
            <div class="bar-row">
                <span class="bar-label">{{ $label }}</span>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ ($chartTotals[$index] ?? 0) / $maxTotal * 100 }}%;"></div>
                </div>
                <span class="bar-value">Bs {{ number_format($chartTotals[$index] ?? 0, 2) }}</span>
            </div>
        @empty
            <p style="margin:0;">Sin datos para mostrar en el gráfico.</p>
        @endforelse
    </div>

    <table style="margin-top:1.5rem;">
        <thead>
            <tr>
                <th>Día</th>
                <th>Ventas</th>
                <th>Total (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyBreakdown as $day)
                <tr>
                    <td>{{ $day['date'] }}</td>
                    <td>{{ $day['count'] }}</td>
                    <td>Bs {{ number_format($day['total'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align:center; padding:1rem;">Sin movimientos diarios en el período.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table style="margin-top:1.5rem;">
        <thead>
            <tr>
                <th style="width:60px;">ID</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th>Pago</th>
                <th>Fecha</th>
                <th style="width:120px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>#{{ $sale->id }}</td>
                    <td>
                        @if($sale->company)
                            {{ $sale->company->name }}
                        @elseif($sale->customer)
                            {{ $sale->customer->user->name ?? 'Cliente' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $statusLabels[$sale->status] ?? ucfirst($sale->status) }}</td>
                    <td>{{ $paymentLabels[$sale->payment_method] ?? 'Sin método' }}</td>
                    <td>{{ optional($sale->created_at)->format('d/m/Y H:i') }}</td>
                    <td>Bs {{ number_format($sale->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:1rem;">No se encontraron ventas para este periodo.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
