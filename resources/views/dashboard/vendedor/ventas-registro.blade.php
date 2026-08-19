@extends('layouts.sidebar-vendedor')

@section('title', 'Registro de ventas | Pil Andina')
@section('page-title', 'Registro personal de ventas')

@php
    $statusLabels = $statusLabels ?? [
        'sin_entregar' => 'Sin entregar',
        'entregado' => 'Entregado',
    ];
    $paymentLabels = $paymentLabels ?? [
        'efectivo' => 'Efectivo',
        'qr' => 'QR',
        'tarjeta_debito' => 'Tarjeta de débito',
    ];
@endphp

@section('content')
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    <div class="stats-grid">
        <div class="card">
            <h3>Ventas de hoy</h3>
            <div class="value">{{ $stats['today_count'] }}</div>
            <span class="chip text-white/70">Bs {{ number_format($stats['today_total'], 2) }} facturados</span>
        </div>
        <div class="card">
            <h3>Total mensual</h3>
            <div class="value">Bs {{ number_format($stats['month_total'], 2) }}</div>
            <span class="chip text-white/70">Acumulado del mes</span>
        </div>
        <div class="card">
            <h3>Descargar reporte</h3>
            <div class="value">{{ $sales->total() }} ventas</div>
            <form method="GET" action="{{ route($reportRoute) }}" style="margin-top:0.75rem;">
                <input type="hidden" name="start_date" value="{{ $filters['start_date'] }}">
                <input type="hidden" name="end_date" value="{{ $filters['end_date'] }}">
                <input type="hidden" name="status" value="{{ $filters['status'] }}">
                <button class="pill-button" type="submit"><i class="ri-file-download-line"></i> Generar PDF</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Actividad semanal</h4>
            <span class="chip text-white/70">Últimos 7 días</span>
        </div>
        <canvas id="vendorSalesChart" height="120"></canvas>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Ventas registradas</h4>
            <span class="chip">{{ $sales->total() }} registros</span>
        </div>

        <form method="GET" action="{{ route('dashboard.vendedor.sales.log') }}" class="form-grid" style="margin-bottom:1.5rem;">
            <div class="form-group">
                <label for="filter_start">Desde</label>
                <input type="date" id="filter_start" name="start_date" value="{{ $filters['start_date'] }}" class="input-ghost">
            </div>
            <div class="form-group">
                <label for="filter_end">Hasta</label>
                <input type="date" id="filter_end" name="end_date" value="{{ $filters['end_date'] }}" class="input-ghost">
            </div>
            <div class="form-group">
                <label for="filter_status">Estado</label>
                <select id="filter_status" name="status" class="select-light">
                    <option value="">Todos</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <button class="pill-button" type="submit">Filtrar</button>
                <a class="clean-link" href="{{ route('dashboard.vendedor.sales.log') }}">Limpiar</a>
            </div>
        </form>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Pago</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>#{{ $sale->id }}</td>
                            <td>
                                @if($sale->company)
                                    <strong>{{ $sale->company->name }}</strong><br>
                                    <small>{{ $sale->company->city }}</small>
                                @elseif($sale->customer)
                                    <strong>{{ $sale->customer->user->name ?? 'Cliente' }}</strong><br>
                                    <small>{{ $sale->customer->city }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="status-pill {{ \Illuminate\Support\Str::slug($sale->status, '_') }}">
                                    {{ $statusLabels[$sale->status] ?? ucfirst($sale->status) }}
                                </span>
                            </td>
                            <td>{{ $paymentLabels[$sale->payment_method] ?? 'Sin método' }}</td>
                            <td>Bs {{ number_format($sale->total_amount, 2) }}</td>
                            <td>{{ optional($sale->created_at)->format('d/m/Y H:i') }}</td>
                            <td style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                                <button type="button"
                                        class="btn-secondary btn-sale-update"
                                        data-update-url="{{ route($updateRoute, $sale) }}"
                                        data-status="{{ $sale->status }}">
                                    Actualizar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:1rem;">No se registraron ventas en este periodo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">
            {{ $sales->appends($filters)->links() }}
        </div>
    </div>

    @include('dashboard.partials.sale-status-modal', ['statusLabels' => $statusLabels, 'paymentLabels' => $paymentLabels])
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
(() => {
    const ctx = document.getElementById('vendorSalesChart');
    if (!ctx) return;

    const labels = @json($chart['labels']);
    const totals = @json($chart['totals']);
    const counts = @json($chart['counts']);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Monto (Bs)',
                    data: totals,
                    backgroundColor: 'rgba(86,109,48,0.6)',
                    borderRadius: 12,
                },
                {
                    label: 'Ventas',
                    data: counts,
                    type: 'line',
                    borderColor: '#e0e3c7',
                    backgroundColor: '#e0e3c7',
                    tension: 0.4,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.08)' },
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    ticks: { color: '#fbbf24' },
                    grid: { display: false },
                },
                x: {
                    ticks: { color: '#fff' },
                    grid: { display: false },
                }
            },
            plugins: {
                legend: {
                    labels: { color: '#fff' }
                }
            }
        }
    });
})();
</script>
@endpush
