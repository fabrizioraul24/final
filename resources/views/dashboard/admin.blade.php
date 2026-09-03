@extends('layouts.sidebar')

@section('title', 'Dashboard Administrador | Pil Andina')
@section('page-title', 'Radar Ejecutivo')

@section('content')
    <style>
        .exec-dashboard {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .exec-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .exec-stat-card {
            min-height: 178px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .exec-stat-card::after {
            content: '';
            position: absolute;
            inset: auto -32px -42px auto;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(134, 172, 212, 0.18), transparent 70%);
            pointer-events: none;
        }

        .exec-stat-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .exec-stat-label h3 {
            margin: 0;
            font-size: 1rem;
            color: inherit;
        }

        .exec-stat-value {
            margin-top: 0.65rem;
            font-size: clamp(2.05rem, 3.6vw, 2.8rem);
            line-height: 1;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .exec-stat-card .chip {
            width: fit-content;
            margin-top: 1rem;
        }

        .exec-chart-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .exec-chart-card {
            min-height: 348px;
            display: flex;
            flex-direction: column;
            padding: 1.45rem;
        }

        .exec-chart-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.9rem;
        }

        .exec-chart-head h4 {
            margin: 0 0 0.2rem;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.92);
        }

        .exec-chart-head p {
            margin: 0;
            font-size: 0.83rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .exec-chart-wrap {
            position: relative;
            width: 100%;
            height: 238px;
            margin-top: auto;
        }

        .exec-chart-wrap canvas {
            width: 100% !important;
            height: 100% !important;
        }

        @media (max-width: 1180px) {
            .exec-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .exec-chart-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .exec-stats-grid {
                grid-template-columns: 1fr;
            }

            .exec-stat-card {
                min-height: 160px;
            }
        }
    </style>

    <div class="exec-dashboard">
        <div class="exec-stats-grid">
            <article class="card exec-stat-card">
                <div>
                    <div class="exec-stat-label">
                        <h3>Ventas del dia</h3>
                        <i class="ri-line-chart-line" style="font-size:1.2rem;color:rgba(255,255,255,0.55);"></i>
                    </div>
                    <div class="exec-stat-value">Bs {{ number_format($kpis['sales_today'], 2) }}</div>
                </div>
                <span class="chip text-green-300"><i class="ri-arrow-up-line"></i> Actualizado hoy</span>
            </article>

            <article class="card exec-stat-card">
                <div>
                    <div class="exec-stat-label">
                        <h3>Clientes registrados</h3>
                        <i class="ri-community-line" style="font-size:1.2rem;color:rgba(255,255,255,0.55);"></i>
                    </div>
                    <div class="exec-stat-value">{{ $kpis['customers'] }}</div>
                </div>
                <span class="chip text-white/70"><i class="ri-building-4-line"></i> Empresas + tiendas</span>
            </article>

            <article class="card exec-stat-card">
                <div>
                    <div class="exec-stat-label">
                        <h3>Productos activos</h3>
                        <i class="ri-shopping-bag-3-line" style="font-size:1.2rem;color:rgba(255,255,255,0.55);"></i>
                    </div>
                    <div class="exec-stat-value">{{ $kpis['products_active'] }}</div>
                </div>
                <span class="chip text-white/70"><i class="ri-shopping-bag-line"></i> Catalogo disponible</span>
            </article>

            <article class="card exec-stat-card">
                <div>
                    <div class="exec-stat-label">
                        <h3>Traspasos abiertos</h3>
                        <i class="ri-shuffle-line" style="font-size:1.2rem;color:rgba(255,255,255,0.55);"></i>
                    </div>
                    <div class="exec-stat-value">{{ $kpis['transfers_active'] }}</div>
                </div>
                <span class="chip"><i class="ri-loop-right-line"></i> Pendientes o en transito</span>
            </article>
        </div>

        <div class="exec-chart-grid">
            <article class="card exec-chart-card">
                <div class="exec-chart-head">
                    <div>
                        <h4>Ventas ultimos 7 dias</h4>
                        <p>Lectura diaria del pulso comercial.</p>
                    </div>
                    <span class="chip">Serie diaria</span>
                </div>
                <div class="exec-chart-wrap">
                    <canvas id="salesChart"></canvas>
                </div>
            </article>

            <article class="card exec-chart-card">
                <div class="exec-chart-head">
                    <div>
                        <h4>Mix por categoria</h4>
                        <p>Que familias sostienen el catalogo.</p>
                    </div>
                    <span class="chip">Top categorias</span>
                </div>
                <div class="exec-chart-wrap">
                    <canvas id="categoryChart"></canvas>
                </div>
            </article>

            <article class="card exec-chart-card">
                <div class="exec-chart-head">
                    <div>
                        <h4>Estado de traspasos</h4>
                        <p>Resumen operativo del flujo interno.</p>
                    </div>
                    <span class="chip">Resumen</span>
                </div>
                <div class="exec-chart-wrap">
                    <canvas id="transferChart"></canvas>
                </div>
            </article>

            <article class="card exec-chart-card">
                <div class="exec-chart-head">
                    <div>
                        <h4>Usuarios por rol</h4>
                        <p>Distribucion actual del sistema.</p>
                    </div>
                    <span class="chip">Distribucion</span>
                </div>
                <div class="exec-chart-wrap">
                    <canvas id="roleChart"></canvas>
                </div>
            </article>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const colors = {
        primary: '#566d30',
        primaryLight: '#7b814f',
        accent: '#b9be96',
        green: '#f6f6f3',
        red: '#e0e3c7',
        yellow: '#f6f6f3'
    };

    const salesCtx = document.getElementById('salesChart')?.getContext('2d');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: @json($salesSeries['labels']),
                datasets: [{
                    label: 'Ventas (Bs)',
                    data: @json($salesSeries['data']),
                    borderColor: colors.primaryLight,
                    backgroundColor: 'rgba(185,190,150,0.22)',
                    tension: 0.34,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,0.07)' }, ticks: { color: 'rgba(255,255,255,0.8)' } },
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: 'rgba(255,255,255,0.8)' }, beginAtZero: true }
                }
            }
        });
    }

    const categoryCtx = document.getElementById('categoryChart')?.getContext('2d');
    if (categoryCtx) {
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: @json($categoryMix['labels']),
                datasets: [{
                    label: 'Productos',
                    data: @json($categoryMix['data']),
                    backgroundColor: [colors.primary, colors.primaryLight, colors.accent, colors.red, colors.yellow],
                    borderRadius: 10,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: 'rgba(255,255,255,0.8)' }, grid: { display: false } },
                    y: { ticks: { color: 'rgba(255,255,255,0.8)' }, grid: { color: 'rgba(255,255,255,0.05)' }, beginAtZero: true }
                }
            }
        });
    }

    const transferCtx = document.getElementById('transferChart')?.getContext('2d');
    if (transferCtx) {
        new Chart(transferCtx, {
            type: 'doughnut',
            data: {
                labels: @json($transferStatuses['labels']),
                datasets: [{
                    data: @json($transferStatuses['data']),
                    backgroundColor: [colors.primary, colors.primaryLight, colors.accent, colors.red, colors.yellow],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '64%',
                plugins: { legend: { position: 'bottom', labels: { color: 'rgba(255,255,255,0.8)', padding: 12, boxWidth: 10 } } }
            }
        });
    }

    const roleCtx = document.getElementById('roleChart')?.getContext('2d');
    if (roleCtx) {
        new Chart(roleCtx, {
            type: 'bar',
            data: {
                labels: @json($roleMix['labels']),
                datasets: [{
                    label: 'Usuarios',
                    data: @json($roleMix['data']),
                    backgroundColor: colors.accent,
                    borderRadius: 10,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: 'rgba(255,255,255,0.8)' }, grid: { display: false } },
                    y: { ticks: { color: 'rgba(255,255,255,0.8)' }, grid: { color: 'rgba(255,255,255,0.05)' }, beginAtZero: true }
                }
            }
        });
    }
</script>
@endpush
