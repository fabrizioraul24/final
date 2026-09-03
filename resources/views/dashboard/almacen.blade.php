@extends('layouts.sidebar-almacen')

@section('title', 'Dashboard almacen | Pil Andina')
@section('page-title', 'Dashboard de almacen')

@php
    $avgCapacity = collect($capacityChart['data'] ?? [])->count()
        ? round(collect($capacityChart['data'])->avg(), 1)
        : 0;
    $statusLabels = [
        'pendiente' => 'Pendiente',
        'en_transito' => 'En transito',
        'recibido' => 'Recibido',
        'cancelado' => 'Cancelado',
    ];
@endphp

@section('content')
<div class="warehouse-dashboard-page">
    <section class="fit-users-header warehouse-hero">
        <div class="fit-users-header-left">
            <div class="fit-header-icon"><i class="ri-archive-drawer-line"></i></div>
            <div>
                <h1>Control Operativo de Almacen</h1>
                <p>Supervisa stock, vencimientos, traspasos y pedidos pendientes sin salir del rol almacen.</p>
            </div>
        </div>
        <div class="warehouse-clock-card">
            <strong id="warehouseClock">00:00:00</strong>
            <span id="warehouseDate">Cargando fecha...</span>
        </div>
    </section>

    <section class="warehouse-shortcut-grid">
        <a href="{{ route('dashboard.almacen.lots') }}" class="warehouse-shortcut-card orange">
            <span class="warehouse-shortcut-icon"><i class="ri-box-3-line"></i></span>
            <strong>Inventario por lotes</strong>
            <small>Revisar stock, vencimientos y existencias.</small>
        </a>
        <a href="{{ route('dashboard.almacen.transfers') }}" class="warehouse-shortcut-card blue">
            <span class="warehouse-shortcut-icon"><i class="ri-swap-box-line"></i></span>
            <strong>Traspasos</strong>
            <small>Gestionar envios internos entre almacenes.</small>
        </a>
        <a href="{{ route('dashboard.almacen.receptions') }}" class="warehouse-shortcut-card amber">
            <span class="warehouse-shortcut-icon"><i class="ri-truck-line"></i></span>
            <strong>Pedidos en bodega</strong>
            <small>Validar entregas y salidas pendientes.</small>
        </a>
        <a href="{{ route('dashboard.almacen.damages') }}" class="warehouse-shortcut-card rose">
            <span class="warehouse-shortcut-icon"><i class="ri-alert-line"></i></span>
            <strong>Registro de danos</strong>
            <small>Registrar mermas, roturas y ajustes.</small>
        </a>
    </section>

    <section class="fit-metric-grid warehouse-metric-grid">
        <div class="fit-metric-card orange">
            <span><small>Stock Disponible</small><strong id="warehouseStock">{{ number_format($stats['stock']) }}</strong><em>Unidades fisicas</em></span>
            <span class="fit-metric-icon"><i class="ri-dropbox-line"></i></span>
        </div>
        <div class="fit-metric-card amber">
            <span><small>Pedidos Pendientes</small><strong id="warehousePendingOrders">{{ $stats['pending_orders'] }}</strong><em>Sin entregar</em></span>
            <span class="fit-metric-icon"><i class="ri-timer-2-line"></i></span>
        </div>
        <div class="fit-metric-card blue">
            <span><small>Traspasos Hoy</small><strong id="warehouseTransfersToday">{{ $stats['transfers_today'] }}</strong><em>Operaciones registradas</em></span>
            <span class="fit-metric-icon"><i class="ri-shuffle-line"></i></span>
        </div>
        <div class="fit-metric-card rose">
            <span><small>Caducidad Proxima</small><strong id="warehouseExpiringLots">{{ $stats['expiring_lots'] }}</strong><em>Lotes en 30 dias</em></span>
            <span class="fit-metric-icon"><i class="ri-alarm-warning-line"></i></span>
        </div>
    </section>

    <section class="warehouse-main-grid">
        <article class="warehouse-panel warehouse-capacity-panel">
            <div class="fit-section-head">
                <div>
                    <h2>Ocupacion global</h2>
                    <p>Promedio de capacidad usada en los almacenes registrados.</p>
                </div>
                <span class="fit-section-badge orange" id="warehouseCapacityBadge">{{ $avgCapacity }}%</span>
            </div>
            <div class="warehouse-capacity-progress">
                <div id="warehouseCapacityBar" style="width: {{ $avgCapacity }}%"></div>
            </div>
            <div class="warehouse-capacity-meta">
                <span>Stock total: <strong id="warehouseStockSummary">{{ number_format($stats['stock']) }} uds</strong></span>
                <span>Alertas: <strong id="warehouseExpiringSummary">{{ $stats['expiring_lots'] }} lotes</strong></span>
            </div>
        </article>

        <article class="warehouse-panel warehouse-flow-panel">
            <div class="fit-section-head">
                <div>
                    <h2>Flujo logistico</h2>
                    <p>Indicadores operativos del dia.</p>
                </div>
            </div>
            <div class="warehouse-flow-grid">
                <div><span>Traspasos hoy</span><strong id="warehouseTransfersSummary">{{ $stats['transfers_today'] }}</strong></div>
                <div><span>Pedidos en cola</span><strong id="warehousePendingSummary">{{ $stats['pending_orders'] }}</strong></div>
            </div>
        </article>
    </section>

    <section class="warehouse-alert-grid">
        <article class="warehouse-panel">
            <div class="fit-section-head">
                <div>
                    <h2>Caducidad proxima</h2>
                    <p>Lotes con vencimiento dentro de los siguientes 30 dias.</p>
                </div>
                <a href="{{ route('dashboard.almacen.lots') }}" class="fit-outline-button compact"><i class="ri-arrow-right-line"></i><span>Ver lotes</span></a>
            </div>
            <div class="warehouse-list" id="warehouseExpiringList">
                @forelse($expiringLotsList as $lot)
                    <div class="warehouse-list-row">
                        <div>
                            <strong>{{ $lot->product->name ?? 'Producto' }}</strong>
                            <span>{{ $lot->warehouse->name ?? 'Sin almacen' }} - Vence: {{ optional($lot->expires_at)->format('d/m/Y') }}</span>
                        </div>
                        <em>{{ $lot->quantity }} uds</em>
                    </div>
                @empty
                    <div class="warehouse-empty-state"><i class="ri-checkbox-circle-line"></i><span>No hay lotes con vencimiento proximo.</span></div>
                @endforelse
            </div>
        </article>

        <article class="warehouse-panel">
            <div class="fit-section-head">
                <div>
                    <h2>Stock bajo</h2>
                    <p>Lotes por debajo del minimo de seguridad.</p>
                </div>
                <a href="{{ route('dashboard.almacen.lots') }}" class="fit-outline-button compact"><i class="ri-arrow-right-line"></i><span>Ver inventario</span></a>
            </div>
            <div class="warehouse-list" id="warehouseCriticalList">
                @forelse($criticalLotsList as $lot)
                    @php
                        $percentLeft = max(0, min(100, round(($lot->quantity / max(1, $lot->safety_threshold)) * 100)));
                    @endphp
                    <div class="warehouse-list-row warning">
                        <div>
                            <strong>{{ $lot->product->name ?? 'Producto' }}</strong>
                            <span>SKU: {{ $lot->product->sku ?? 'N/D' }} - {{ $lot->warehouse->name ?? 'Sin almacen' }}</span>
                            <small><span style="width: {{ $percentLeft }}%"></span></small>
                        </div>
                        <em>{{ $lot->quantity }} / {{ $lot->safety_threshold }}</em>
                    </div>
                @empty
                    <div class="warehouse-empty-state"><i class="ri-checkbox-circle-line"></i><span>Todos los lotes tienen stock seguro.</span></div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="warehouse-chart-grid">
        <article class="warehouse-panel">
            <div class="fit-section-head">
                <div>
                    <h2>Ocupacion por almacen</h2>
                    <p>Porcentaje usado contra capacidad maxima.</p>
                </div>
            </div>
            <div class="warehouse-chart-wrap"><canvas id="warehouseCapacityChart"></canvas></div>
        </article>
        <article class="warehouse-panel">
            <div class="fit-section-head">
                <div>
                    <h2>Traspasos ultimos 7 dias</h2>
                    <p>Movimiento diario consolidado.</p>
                </div>
            </div>
            <div class="warehouse-chart-wrap"><canvas id="warehouseTransferChart"></canvas></div>
        </article>
    </section>

    <section class="fit-section warehouse-transfers-section">
        <div class="fit-section-head">
            <div>
                <h2>Ultimos traspasos</h2>
                <p>Control reciente de operaciones internas entre almacenes.</p>
            </div>
            <a href="{{ route('dashboard.almacen.transfers') }}" class="fit-outline-button compact"><i class="ri-arrow-right-line"></i><span>Ver todos</span></a>
        </div>
        <div class="fit-table-card">
            <div class="fit-table-scroll">
                <table class="fit-users-table warehouse-transfer-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Estado</th>
                            <th>Fecha estimada</th>
                            <th class="text-right">Accion</th>
                        </tr>
                    </thead>
                    <tbody id="warehouseTransfersBody">
                        @forelse($recentTransfers as $transfer)
                            @php
                                $status = $transfer->status;
                                $statusClass = $status === 'recibido' ? 'active' : ($status === 'en_transito' ? 'pending' : 'canceled');
                            @endphp
                            <tr>
                                <td><code class="fit-code">#{{ $transfer->id }}</code></td>
                                <td><span class="fit-muted-text">{{ $transfer->fromWarehouse->name ?? 'Sin origen' }}</span></td>
                                <td><span class="fit-muted-text">{{ $transfer->toWarehouse->name ?? 'Sin destino' }}</span></td>
                                <td><span class="fit-transfer-status {{ $statusClass }}"><span></span>{{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</span></td>
                                <td><span class="fit-muted-text">{{ optional($transfer->expected_date)->format('d/m/Y') ?? 'Sin fecha' }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('dashboard.almacen.transfers') }}" class="fit-action-button success" title="Ver detalle"><i class="ri-eye-line"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center;padding:1rem;">No hay traspasos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
(() => {
    let capacityChart = null;
    let transferChart = null;
    const statusLabels = @json($statusLabels);

    function formatNumber(value) {
        return Number(value || 0).toLocaleString('es-BO');
    }

    function updateClock() {
        const now = new Date();
        const clock = document.getElementById('warehouseClock');
        const date = document.getElementById('warehouseDate');
        if (clock) clock.textContent = now.toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
        if (date) date.textContent = now.toLocaleDateString('es-BO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }

    function average(values) {
        if (!values.length) return 0;
        return Math.round((values.reduce((sum, value) => sum + Number(value || 0), 0) / values.length) * 10) / 10;
    }

    function statusClass(status) {
        if (status === 'recibido') return 'active';
        if (status === 'en_transito') return 'pending';
        return 'canceled';
    }

    function renderLots(containerId, lots, type) {
        const container = document.getElementById(containerId);
        if (!container) return;

        if (!lots.length) {
            container.innerHTML = `<div class="warehouse-empty-state"><i class="ri-checkbox-circle-line"></i><span>${type === 'critical' ? 'Todos los lotes tienen stock seguro.' : 'No hay lotes con vencimiento proximo.'}</span></div>`;
            return;
        }

        container.innerHTML = lots.map((lot) => {
            if (type === 'critical') {
                const ratio = Math.max(0, Math.min(100, Math.round((Number(lot.quantity || 0) / Math.max(1, Number(lot.safety_threshold || 1))) * 100)));
                return `
                    <div class="warehouse-list-row warning">
                        <div>
                            <strong>${lot.product_name}</strong>
                            <span>SKU: ${lot.sku || 'N/D'} - ${lot.warehouse_name}</span>
                            <small><span style="width:${ratio}%"></span></small>
                        </div>
                        <em>${lot.quantity} / ${lot.safety_threshold}</em>
                    </div>
                `;
            }

            return `
                <div class="warehouse-list-row">
                    <div>
                        <strong>${lot.product_name}</strong>
                        <span>${lot.warehouse_name} - Vence: ${lot.expires_at_formatted}</span>
                    </div>
                    <em>${lot.quantity} uds</em>
                </div>
            `;
        }).join('');
    }

    function renderTransfers(transfers) {
        const tbody = document.getElementById('warehouseTransfersBody');
        if (!tbody) return;

        if (!transfers.length) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:1rem;">No hay traspasos registrados.</td></tr>';
            return;
        }

        tbody.innerHTML = transfers.map((transfer) => `
            <tr>
                <td><code class="fit-code">#${transfer.id}</code></td>
                <td><span class="fit-muted-text">${transfer.from_warehouse}</span></td>
                <td><span class="fit-muted-text">${transfer.to_warehouse}</span></td>
                <td><span class="fit-transfer-status ${statusClass(transfer.status)}"><span></span>${statusLabels[transfer.status] || transfer.status_label}</span></td>
                <td><span class="fit-muted-text">${transfer.expected_date}</span></td>
                <td class="text-right"><a href="{{ route('dashboard.almacen.transfers') }}" class="fit-action-button success" title="Ver detalle"><i class="ri-eye-line"></i></a></td>
            </tr>
        `).join('');
    }

    function initCharts(data) {
        if (typeof Chart === 'undefined') return;
        const textColor = document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#64748b';
        const gridColor = document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.08)' : 'rgba(148,163,184,0.25)';

        const capacityCtx = document.getElementById('warehouseCapacityChart');
        if (capacityCtx) {
            capacityChart = new Chart(capacityCtx, {
                type: 'bar',
                data: {
                    labels: data.capacityLabels,
                    datasets: [{
                        label: 'Ocupacion %',
                        data: data.capacityData,
                        backgroundColor: 'rgba(113,135,173,0.68)',
                        borderRadius: 14,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: textColor }, grid: { display: false } },
                        y: { beginAtZero: true, max: 100, ticks: { color: textColor, callback: (value) => `${value}%` }, grid: { color: gridColor } }
                    }
                }
            });
        }

        const transferCtx = document.getElementById('warehouseTransferChart');
        if (transferCtx) {
            transferChart = new Chart(transferCtx, {
                type: 'line',
                data: {
                    labels: data.transferLabels,
                    datasets: [{
                        label: 'Traspasos',
                        data: data.transferData,
                        borderColor: '#0b4fc1',
                        backgroundColor: 'rgba(11,79,193,0.12)',
                        borderWidth: 3,
                        pointRadius: 4,
                        tension: 0.42,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: textColor, boxWidth: 12, usePointStyle: true } } },
                    scales: {
                        x: { ticks: { color: textColor }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { color: textColor, precision: 0 }, grid: { color: gridColor } }
                    }
                }
            });
        }
    }

    function updateDashboard(data) {
        document.getElementById('warehouseStock').textContent = formatNumber(data.stats.stock);
        document.getElementById('warehousePendingOrders').textContent = data.stats.pending_orders;
        document.getElementById('warehouseTransfersToday').textContent = data.stats.transfers_today;
        document.getElementById('warehouseExpiringLots').textContent = data.stats.expiring_lots;
        document.getElementById('warehouseStockSummary').textContent = `${formatNumber(data.stats.stock)} uds`;
        document.getElementById('warehouseExpiringSummary').textContent = `${data.stats.expiring_lots} lotes`;
        document.getElementById('warehouseTransfersSummary').textContent = data.stats.transfers_today;
        document.getElementById('warehousePendingSummary').textContent = data.stats.pending_orders;

        const avg = average(data.capacityChart.data || []);
        document.getElementById('warehouseCapacityBadge').textContent = `${avg}%`;
        document.getElementById('warehouseCapacityBar').style.width = `${avg}%`;

        renderLots('warehouseExpiringList', data.expiringLotsList || [], 'expiring');
        renderLots('warehouseCriticalList', data.criticalLotsList || [], 'critical');
        renderTransfers(data.recentTransfers || []);

        if (capacityChart) {
            capacityChart.data.labels = data.capacityChart.labels;
            capacityChart.data.datasets[0].data = data.capacityChart.data;
            capacityChart.update('none');
        }
        if (transferChart) {
            transferChart.data.labels = data.transferSeries.labels;
            transferChart.data.datasets[0].data = data.transferSeries.data;
            transferChart.update('none');
        }
    }

    function fetchLiveStats() {
        return fetch("{{ route('dashboard.almacen.live-stats') }}")
            .then((response) => {
                if (!response.ok) throw new Error('No se pudo sincronizar');
                return response.json();
            })
            .then((data) => updateDashboard(data))
            .catch(() => {});
    }

    updateClock();
    setInterval(updateClock, 1000);
    initCharts({
        capacityLabels: @json($capacityChart['labels'] ?? []),
        capacityData: @json($capacityChart['data'] ?? []),
        transferLabels: @json($transferSeries['labels'] ?? []),
        transferData: @json($transferSeries['data'] ?? []),
    });
    setInterval(fetchLiveStats, 15000);
})();
</script>
@endpush
