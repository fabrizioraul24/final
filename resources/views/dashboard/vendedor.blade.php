@extends('layouts.sidebar-vendedor')

@section('title', 'Vendedor | Pil Andina')
@section('page-title', 'Dashboard Pil La Paz')

@section('content')
@php
    $calendarDate = now();
    $monthNames = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];
    $startOffset = ($calendarDate->copy()->startOfMonth()->dayOfWeek + 6) % 7;
    $daysInMonth = $calendarDate->daysInMonth;
    $maxSales = max(1, (float) collect($last7)->max('value'));
    $todaySales = (float) (collect($last7)->last()['value'] ?? 0);
@endphp

<div class="seller-dashboard seller-admin-board">
    <section class="seller-admin-top-grid">
        <article class="seller-admin-card seller-admin-metrics-card">
            <div class="seller-admin-card-head">
                <h3>Metricas Generales</h3>
                <span class="seller-admin-pill">Mes <i class="ri-arrow-down-s-line"></i></span>
            </div>

            <div class="seller-admin-kpi-row">
                <i class="ri-arrow-up-line seller-admin-blue"></i>
                <strong id="kpiSalesCount">{{ $countSales }}</strong>
            </div>
            <p>Ventas cerradas registradas en el mes actual</p>

            <div class="seller-admin-divider"></div>

            <div class="seller-admin-kpi-row">
                <i class="ri-arrow-down-line seller-admin-green"></i>
                <strong id="kpiPendingVisits">{{ $pendingVisits }}</strong>
            </div>
            <p>Visitas pendientes programadas desde hoy</p>
        </article>

        <article class="seller-admin-card seller-admin-calendar-card">
            <div class="seller-admin-calendar-head">
                <i class="ri-arrow-left-s-line"></i>
                <strong>{{ $monthNames[(int) $calendarDate->format('n')] }}</strong>
                <i class="ri-arrow-right-s-line"></i>
            </div>
            <div class="seller-admin-calendar-grid">
                @foreach(['LUN', 'MAR', 'MIE', 'JUE', 'VIE', 'SAB', 'DOM'] as $dayName)
                    <span class="seller-admin-day-name">{{ $dayName }}</span>
                @endforeach
                @for($blank = 0; $blank < $startOffset; $blank++)
                    <span></span>
                @endfor
                @for($day = 1; $day <= $daysInMonth; $day++)
                    <span class="{{ $day === (int) $calendarDate->format('j') ? 'is-today' : '' }}">{{ $day }}</span>
                @endfor
            </div>
        </article>

        <article class="seller-admin-card seller-admin-progress-card">
            <h3>Progreso General</h3>
            <div class="seller-admin-progress-list">
                <div class="seller-admin-progress-item">
                    <div><span>Ventas</span><strong id="targetPercentTextMetric">{{ $targetProgress }}%</strong></div>
                    <div class="seller-admin-track"><span id="sellerMetricProgressBar" style="width: {{ $targetProgress }}%"></span></div>
                </div>
                <div class="seller-admin-progress-item">
                    <div><span>Cotizaciones</span><strong id="conversionText">{{ $quotationConversion }}%</strong></div>
                    <div class="seller-admin-track"><span class="green" id="quotationProgressBar" style="width: {{ $quotationConversion }}%"></span></div>
                </div>
                <div class="seller-admin-progress-item">
                    <div><span>Clientes</span><strong id="kpiClientsCount">{{ $clientsCount }}</strong></div>
                    <div class="seller-admin-track"><span style="width: {{ min(100, $clientsCount * 5) }}%"></span></div>
                </div>
            </div>
        </article>

        <article class="seller-admin-card seller-admin-sales-card">
            <div class="seller-admin-search">
                <span>Buscar...</span>
                <i class="ri-search-line"></i>
            </div>
            <div>
                <h3>Ventas</h3>
                <p>Movimiento semanal registrado</p>
            </div>
            <div class="seller-admin-mini-chart" data-revenue-bars>
                @foreach($last7 as $day)
                    @php $height = max(8, min(100, (($day['value'] ?? 0) / $maxSales) * 100)); @endphp
                    <span style="height: {{ $height }}%"></span>
                @endforeach
            </div>
        </article>
    </section>

    <section class="seller-admin-main-grid">
        <article class="seller-admin-card seller-admin-weekly-card">
            <div class="seller-admin-card-head">
                <h3>Ventas semanales</h3>
                <span class="seller-admin-pill">Todo <i class="ri-arrow-down-s-line"></i></span>
            </div>
            <div class="seller-admin-line-visual" data-weekly-bars>
                @foreach($last7 as $day)
                    @php $height = max(10, min(96, (($day['value'] ?? 0) / $maxSales) * 96)); @endphp
                    <span style="height: {{ $height }}%"></span>
                @endforeach
            </div>
            <div class="seller-admin-axis">
                @foreach($last7 as $day)
                    <span>{{ $day['date'] }}</span>
                @endforeach
            </div>
            <div class="seller-admin-legend">
                <span><i></i> Actual</span>
                <span><i class="soft"></i> Anterior</span>
            </div>
        </article>

        <article class="seller-admin-card seller-admin-goal-card">
            <h3>Meta mensual</h3>
            <p>Avance de ventas del mes</p>
            <div class="seller-admin-ring" id="conversionProgress" style="--progress: {{ $targetProgress }}%;">
                <strong id="targetPercentText">{{ $targetProgress }}%</strong>
            </div>
            <p class="seller-admin-centered">Progreso calculado con ventas registradas en la base de datos</p>
            <a href="{{ route('dashboard.vendedor.sales') }}" class="seller-admin-primary-link">Ventas</a>
        </article>

        <article class="seller-admin-card seller-admin-income-card">
            <div class="seller-admin-card-head">
                <h3>Ingresos</h3>
                <div class="seller-admin-tabs">
                    <span>Hoy</span>
                    <span>Semana</span>
                    <strong>Mes</strong>
                    <span>Rango</span>
                </div>
            </div>
            <div class="seller-admin-income-row">
                <div>
                    <strong id="kpiAmountMonth">{{ number_format($amountMonth, 2) }}</strong>
                    <span>Este mes</span>
                </div>
                <div>
                    <strong id="kpiTodaySales">{{ number_format($todaySales, 2) }}</strong>
                    <span>Ventas del dia</span>
                </div>
            </div>
            <div class="seller-admin-income-chart" data-income-bars>
                @foreach($last7 as $day)
                    @php $height = max(8, min(100, (($day['value'] ?? 0) / $maxSales) * 100)); @endphp
                    <span style="height: {{ $height }}%"></span>
                @endforeach
            </div>
        </article>
    </section>

    <section class="seller-admin-bottom-grid">
        <article class="seller-admin-card seller-admin-categories-card">
            <h3>Categorias</h3>
            <div class="seller-admin-category-list">
                <div><span>Ventas</span><strong><i style="width: {{ min(100, $targetProgress) }}%"></i></strong></div>
                <div><span>Clientes</span><strong><i style="width: {{ min(100, $clientsCount * 5) }}%"></i></strong></div>
                <div><span>Visitas</span><strong><i class="green" style="width: {{ min(100, $pendingVisits * 10) }}%"></i></strong></div>
                <div><span>Cotiz.</span><strong><i style="width: {{ $quotationConversion }}%"></i></strong></div>
            </div>
        </article>

        <article class="seller-admin-card seller-admin-list-card">
            <div class="seller-admin-card-head">
                <h3>Actividad comercial</h3>
                <span class="seller-admin-pill">{{ $recentSales->count() }} ventas</span>
            </div>
            <div class="seller-admin-activity-grid">
                <div>
                    <h4>Ventas recientes</h4>
                    <div class="seller-admin-list" id="recentSalesContainer">
                        @forelse($recentSales as $sale)
                            <div class="seller-admin-list-row">
                                <span>{{ $sale->company->name ?? 'Venta minorista' }}</span>
                                <strong>Bs {{ number_format((float) $sale->total_amount, 2) }}</strong>
                            </div>
                        @empty
                            <p class="seller-admin-empty">No tienes ventas registradas.</p>
                        @endforelse
                    </div>
                </div>
                <div>
                    <h4>Agenda proxima</h4>
                    <div class="seller-admin-list" id="upcomingVisitsContainer">
                        @forelse($upcomingVisitsList as $visit)
                            <div class="seller-admin-list-row">
                                <span>{{ $visit->company->name ?? 'Cliente sin nombre' }}</span>
                                <strong>{{ optional($visit->visit_date)->format('d/m') }}</strong>
                            </div>
                        @empty
                            <p class="seller-admin-empty">No tienes visitas proximas.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </article>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        setInterval(fetchLiveStats, 15000);
    });

    function formatMoney(value) {
        return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function setText(id, value) {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    }

    function renderBars(selector, rows) {
        const container = document.querySelector(selector);
        if (!container || !Array.isArray(rows)) return;
        const maxValue = Math.max(1, ...rows.map((row) => Number(row.value || 0)));
        container.innerHTML = rows.map((row) => {
            const height = Math.max(8, Math.min(100, (Number(row.value || 0) / maxValue) * 100));
            return `<span style="height:${height}%"></span>`;
        }).join('');
    }

    function fetchLiveStats() {
        return fetch("{{ route('dashboard.vendedor.live-stats') }}")
            .then((response) => {
                if (!response.ok) throw new Error('Stats fetch failed');
                return response.json();
            })
            .then(updateDashboardUI)
            .catch((error) => console.error('Error polling live stats:', error));
    }

    function updateDashboardUI(data) {
        setText('kpiSalesCount', Number(data.countSales || 0).toLocaleString());
        setText('kpiPendingVisits', Number(data.pendingVisits || 0).toLocaleString());
        setText('kpiClientsCount', Number(data.clientsCount || 0).toLocaleString());
        setText('kpiAmountMonth', formatMoney(data.amountMonth));
        setText('kpiTodaySales', formatMoney(data.todaySales));
        setText('targetPercentText', `${data.targetProgress}%`);
        setText('targetPercentTextMetric', `${data.targetProgress}%`);
        setText('conversionText', `${data.quotationConversion}%`);

        document.getElementById('sellerMetricProgressBar')?.style.setProperty('width', `${data.targetProgress}%`);
        document.getElementById('quotationProgressBar')?.style.setProperty('width', `${data.quotationConversion}%`);
        document.getElementById('conversionProgress')?.style.setProperty('--progress', `${data.targetProgress}%`);

        renderBars('[data-revenue-bars]', data.last7);
        renderBars('[data-weekly-bars]', data.last7);
        renderBars('[data-income-bars]', data.last7);
    }
</script>
@endpush
