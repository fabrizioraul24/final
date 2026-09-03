@extends('layouts.sidebar-vendedor')

@section('title', 'Registro personal de ventas | Vendedor')
@section('page-title', 'Registro personal de ventas')

@php
    $statusLabels = $statusLabels ?? [
        'sin_entregar' => 'Sin entregar',
        'entregado' => 'Entregado',
    ];
    $paymentLabels = $paymentLabels ?? [
        'efectivo' => 'Efectivo',
        'qr' => 'QR',
        'tarjeta_debito' => 'Tarjeta de debito',
    ];
    $activeMetric = $filters['status'] ?: 'all';
@endphp

@section('content')
<div class="vendor-sales-log-page">
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    <section class="fit-users-header">
        <div class="fit-users-header-left">
            <div class="fit-header-icon"><i class="ri-bar-chart-2-line"></i></div>
            <div>
                <h1>Registro Personal de Ventas</h1>
                <p>Historial, facturacion y estados de las ventas registradas por tu usuario.</p>
            </div>
        </div>
        <div class="fit-users-header-actions">
            <a href="{{ route('dashboard.vendedor.sales') }}" class="fit-outline-button">
                <i class="ri-arrow-left-line"></i>
                <span>Ventas</span>
            </a>
            <a href="{{ route('dashboard.vendedor.sales.create') }}" class="fit-primary-button">
                <i class="ri-add-box-line"></i>
                <span>Nueva venta</span>
            </a>
        </div>
    </section>

    <section class="fit-metric-grid">
        <a class="fit-metric-card indigo {{ $activeMetric === 'all' ? 'active' : '' }}" href="{{ route('dashboard.vendedor.sales.log') }}">
            <span><small>Ventas del periodo</small><strong>{{ $sales->total() }}</strong><em>Segun filtros actuales</em></span>
            <span class="fit-metric-icon"><i class="ri-shopping-cart-2-line"></i></span>
        </a>
        <div class="fit-metric-card blue">
            <span><small>Ventas de hoy</small><strong>{{ $stats['today_count'] }}</strong><em>Bs {{ number_format((float) $stats['today_total'], 2) }}</em></span>
            <span class="fit-metric-icon"><i class="ri-calendar-check-line"></i></span>
        </div>
        <div class="fit-metric-card green">
            <span><small>Total mensual</small><strong>Bs {{ number_format((float) $stats['month_total'], 2) }}</strong><em>Mes actual</em></span>
            <span class="fit-metric-icon"><i class="ri-money-dollar-circle-line"></i></span>
        </div>
        <a class="fit-metric-card amber {{ $activeMetric === 'sin_entregar' ? 'active' : '' }}" href="{{ route('dashboard.vendedor.sales.log', ['status' => 'sin_entregar', 'start_date' => $filters['start_date'], 'end_date' => $filters['end_date']]) }}">
            <span><small>Pendientes</small><strong>{{ $stats['pending_count'] }}</strong><em>Sin entregar</em></span>
            <span class="fit-metric-icon"><i class="ri-time-line"></i></span>
        </a>
    </section>

    <section class="vendor-sales-log-layout">
        <article class="vendor-sales-log-chart-card">
            <div class="fit-section-head">
                <div>
                    <h2>Actividad semanal</h2>
                    <p>Monto facturado y cantidad de ventas de los ultimos 7 dias.</p>
                </div>
                <span class="fit-section-badge green">Semana</span>
            </div>
            <div class="vendor-sales-log-chart-wrap">
                <canvas id="vendorSalesChart" height="120"></canvas>
            </div>
        </article>

        <article class="vendor-sales-log-report-card">
            <span>Reporte</span>
            <h2>PDF personal</h2>
            <p>Descarga un reporte con el mismo rango y estado filtrado.</p>
            <form method="GET" action="{{ route($reportRoute) }}">
                <input type="hidden" name="start_date" value="{{ $filters['start_date'] }}">
                <input type="hidden" name="end_date" value="{{ $filters['end_date'] }}">
                <input type="hidden" name="status" value="{{ $filters['status'] }}">
                <button class="fit-primary-button" type="submit">
                    <i class="ri-file-download-line"></i>
                    <span>Generar PDF</span>
                </button>
            </form>
        </article>
    </section>

    <section class="fit-filter-card">
        <form method="GET" action="{{ route('dashboard.vendedor.sales.log') }}" class="fit-filter-form vendor-sales-log-filter">
            <label class="fit-search-control" for="filter_start">
                <i class="ri-calendar-line"></i>
                <input type="date" id="filter_start" name="start_date" value="{{ $filters['start_date'] }}">
            </label>
            <label class="fit-search-control" for="filter_end">
                <i class="ri-calendar-check-line"></i>
                <input type="date" id="filter_end" name="end_date" value="{{ $filters['end_date'] }}">
            </label>
            <label class="fit-select-control" for="filter_status">
                <i class="ri-checkbox-circle-line"></i>
                <select id="filter_status" name="status">
                    <option value="">Todos los estados</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button class="fit-primary-button compact" type="submit"><i class="ri-search-line"></i><span>Filtrar</span></button>
            <a class="fit-clear-button" href="{{ route('dashboard.vendedor.sales.log') }}">Limpiar filtros</a>
        </form>
    </section>

    <section class="fit-section">
        <div class="fit-section-head">
            <div>
                <h2>Ventas registradas</h2>
                <p>Listado personal ordenado por fecha. No incluye ventas de admin ni de otros vendedores.</p>
            </div>
            <span class="fit-section-badge green">{{ $sales->total() }} registros</span>
        </div>

        <div class="fit-table-card">
            <div class="fit-table-scroll">
                <table class="fit-users-table fit-sales-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Estado</th>
                            <th>Pago</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            @php
                                $clientName = $sale->company->name ?? $sale->customer->user->name ?? 'Sin cliente';
                                $clientCity = $sale->company->city ?? $sale->customer->city ?? 'Ciudad no registrada';
                            @endphp
                            <tr>
                                <td><code class="fit-code fit-sale-id">#{{ $sale->id }}</code></td>
                                <td>
                                    <div class="fit-user-cell fit-sale-client">
                                        <span class="fit-sale-client-icon"><i class="{{ $sale->company ? 'ri-building-4-line' : 'ri-user-smile-line' }}"></i></span>
                                        <div><strong>{{ $clientName }}</strong><small>{{ $clientCity }}</small></div>
                                    </div>
                                </td>
                                <td><span class="fit-transfer-status {{ $sale->status === 'entregado' ? 'active' : 'pending' }}"><span></span> {{ $statusLabels[$sale->status] ?? ucfirst($sale->status) }}</span></td>
                                <td><span class="fit-sale-payment">{{ $paymentLabels[$sale->payment_method] ?? 'Sin metodo' }}</span></td>
                                <td><strong class="fit-sale-amount">Bs {{ number_format((float) $sale->total_amount, 2) }}</strong></td>
                                <td><span class="fit-muted-text">{{ optional($sale->created_at)->format('d/m/Y H:i') }}</span></td>
                                <td class="text-right">
                                    <div class="fit-row-actions">
                                        <button type="button"
                                                class="fit-action-button warning btn-sale-update"
                                                data-update-url="{{ route($updateRoute, $sale) }}"
                                                data-status="{{ $sale->status }}"
                                                title="Actualizar estado">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align:center; padding:1rem;">No se registraron ventas en este periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top:1rem;">
            {{ $sales->appends($filters)->links() }}
        </div>
    </section>

    @include('dashboard.partials.sale-status-modal', ['statusLabels' => $statusLabels, 'paymentLabels' => $paymentLabels])
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
(() => {
    const ctx = document.getElementById('vendorSalesChart');
    if (!ctx || typeof Chart === 'undefined') return;

    const labels = @json($chart['labels']);
    const totals = @json($chart['totals']);
    const counts = @json($chart['counts']);
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#e2e8f0' : '#64748b';
    const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(148,163,184,0.25)';

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Monto (Bs)',
                    data: totals,
                    backgroundColor: 'rgba(34,197,94,0.58)',
                    borderRadius: 14,
                    yAxisID: 'y',
                },
                {
                    label: 'Ventas',
                    data: counts,
                    type: 'line',
                    borderColor: '#0b4fc1',
                    backgroundColor: '#0b4fc1',
                    borderWidth: 3,
                    pointRadius: 4,
                    tension: 0.42,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: textColor },
                    grid: { color: gridColor },
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    ticks: { color: textColor, precision: 0 },
                    grid: { display: false },
                },
                x: {
                    ticks: { color: textColor },
                    grid: { display: false },
                }
            },
            plugins: {
                legend: {
                    labels: { color: textColor, boxWidth: 12, usePointStyle: true }
                }
            }
        }
    });
})();
</script>
@endpush
