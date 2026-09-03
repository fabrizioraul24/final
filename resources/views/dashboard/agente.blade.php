@extends('layouts.sidebar')

@section('title', 'Agente Inteligente | Pil Andina')
@section('page-title', 'Agente Inteligente')

@section('content')
    <style>
        .chart-card {
            background: linear-gradient(135deg, rgba(15,23,42,0.95), rgba(23,34,59,0.85));
            border-radius: 1.2rem;
            padding: 1rem;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.04), 0 20px 40px rgba(2,6,23,0.35);
            min-height: 280px;
        }
        .chart-shell {
            background: rgba(9,14,30,0.9);
            border-radius: 1rem;
            padding: 0.75rem;
            height: calc(100% - 2rem);
        }
        .hero {
            display: grid;
            grid-template-columns: repeat(auto-fit,minmax(260px,1fr));
            gap: 1rem;
            align-items: center;
            padding: 1.2rem 1.4rem;
            border-radius: 1.3rem;
            background: radial-gradient(circle at 10% 20%, rgba(99,102,241,0.25), transparent 30%), radial-gradient(circle at 80% 0%, rgba(14,165,233,0.2), transparent 30%), linear-gradient(135deg, #0f172a, #0b1223);
            box-shadow: 0 20px 45px rgba(2,6,23,0.4);
        }
        .capacity-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            background: linear-gradient(120deg, rgba(251,191,36,0.12), rgba(239,68,68,0.12));
            color: #fde68a;
            border: 1px solid rgba(250,204,21,0.35);
            animation: pulseGlow 2.6s ease-in-out infinite;
        }
        @keyframes pulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(250,204,21,0.2); }
            50% { box-shadow: 0 0 0 12px rgba(250,204,21,0.02); }
            100% { box-shadow: 0 0 0 0 rgba(250,204,21,0.15); }
        }
        .spark-card {
            background: linear-gradient(140deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
            border: 1px solid rgba(255,255,255,0.04);
            border-radius: 1.1rem;
            padding: 0.9rem;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 0.7rem;
            align-items: center;
        }
        .alert-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit,minmax(260px,1fr));
            gap: 0.8rem;
        }
        .trend-pill { display:inline-flex; align-items:center; gap:0.4rem; padding:0.35rem 0.7rem; border-radius:999px; font-weight:700; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#e5e7eb; }
        .trend-pill.up { background:rgba(34,197,94,0.15); border-color:rgba(74,222,128,0.45); color:#bbf7d0; }
        .trend-pill.down { background:rgba(239,68,68,0.16); border-color:rgba(248,113,113,0.4); color:#fecdd3; }
        .trend-pill.steady { background:rgba(99,102,241,0.12); border-color:rgba(129,140,248,0.35); color:#c7d2fe; }
        .badge-grid { display:flex; gap:0.5rem; flex-wrap:wrap; margin:0.3rem 0; }
        .ai-modal-wide .modal-content { width:90vw; max-width:1250px; max-height:82vh; }
        .ai-modal-body { display:grid; grid-template-columns:1.4fr 1fr; gap:1.1rem; align-items:start; flex-wrap:wrap; max-height:62vh; overflow-y:auto; }
        @media (max-width: 960px) { .ai-modal-body { grid-template-columns:1fr; max-height:none; } }
        .ai-card { background: linear-gradient(135deg, rgba(16,23,42,0.92), rgba(25,31,54,0.9)); border:1px solid rgba(255,255,255,0.08); border-radius:1.1rem; padding:1rem; box-shadow: 0 15px 35px rgba(0,0,0,0.4); }
        .ai-highlight { background: linear-gradient(120deg, rgba(78,107,175,0.3), rgba(14,165,233,0.15)); border:1px solid rgba(134,172,212,0.45); border-radius:1rem; padding:1rem; box-shadow: inset 0 1px 0 rgba(255,255,255,0.08); }
        .ai-chart-mini { background: rgba(9,14,30,0.9); border:1px solid rgba(255,255,255,0.06); border-radius:1rem; padding:1rem; }
        .ai-chart-mini canvas { width:100%; min-height:220px; }
        .modal-content.enhanced { animation: riseIn 0.4s ease; }
        @keyframes riseIn { from { opacity:0; transform: translateY(20px) scale(0.97); } to { opacity:1; transform: translateY(0) scale(1); } }
    </style>
    <div class="hero">
        <div>
            <h2 style="margin:0; color:#fff;">Resumen del agente inteligente</h2>
            <p style="margin:0.25rem 0 0; color:rgba(255,255,255,0.75);">Prediccion con limites de capacidad y alertas mejoradas.</p>
            @if(!empty($data['capacity_alerts']))
                <div style="margin-top:0.6rem;">
                    <span class="capacity-badge"><i class="ri-rocket-2-line"></i> Sugerencia IA: aumenta capacidad</span>
                </div>
            @endif
        </div>
        <div style="display:flex; gap:0.5rem; justify-content:flex-end; flex-wrap:wrap;">
            <button class="pill-button ghost" onclick="document.getElementById('chartsSection').scrollIntoView({behavior:'smooth'})">
                <i class="ri-line-chart-line"></i>Ver grÃ¡ficos
            </button>
            <a href="{{ route('dashboard.agent.report') }}" class="pill-button" target="_blank" rel="noopener">
                <i class="ri-file-text-line"></i>Descargar reporte
            </a>
        </div>
    </div>
    <div class="stats-grid">
        <div class="card">
            <h3>Restock sugeridos</h3>
            <div class="value">{{ $stats['restock'] }}</div>
            <span class="chip text-white/70"><i class="ri-lightbulb-flash-line"></i> Ã“rdenes recomendadas</span>
        </div>
        <div class="card">
            <h3>Alertas de stock</h3>
            <div class="value">{{ $stats['alerts_low'] }}</div>
            <span class="chip text-red-300"><i class="ri-error-warning-line"></i>Bajo inventario</span>
        </div>
        <div class="card">
            <h3>Lotes por vencer</h3>
            <div class="value">{{ $stats['alerts_expiring'] }}</div>
            <span class="chip text-yellow-200"><i class="ri-timer-line"></i>30 dÃ­as</span>
        </div>
        <div class="card">
            <h3>Sugerencias de capacidad</h3>
            <div class="value">{{ $stats['capacity'] }}</div>
            <span class="chip text-white/70"><i class="ri-rocket-2-line"></i>Aumentar limite</span>
        </div>
    </div>

    <div class="charts-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:1rem; margin-top:1.5rem;" id="chartsSection">
        <div class="card chart-placeholder">
            <div class="chart-head">
                <h4>Top productos (pronostico)</h4>
                <span class="chip">Demanda semanal</span>
            </div>
            <canvas id="forecastChart" style="max-height:260px;"></canvas>
        </div>

        <div class="card chart-placeholder">
            <div class="chart-head">
                <h4>Distribucion de restock</h4>
                <span class="chip">Sugerencias</span>
            </div>
            <canvas id="restockChart" style="max-height:260px;"></canvas>
        </div>
    </div>

    <div class="card" style="margin-top:1rem;">
        <div class="chart-head">
            <h4>Predicciones IA por producto</h4>
            <span class="chip text-white/70">{{ count($data['forecast'] ?? []) }} registros</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Pronostico</th>
                        <th>Tendencia</th>
                        <th>Stock</th>
                        <th>Ventas (6 sem)</th>
                        <th>Ventas (6 mes)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['forecast'] ?? [] as $item)
                        @php
                            $series = $salesSeries[$item['product_id'] ?? 0] ?? ['weekly' => ['labels' => [], 'data' => [], 'recent_total' => 0], 'monthly' => ['labels' => [], 'data' => [], 'recent_total' => 0]];
                            $stock = $stockTotals[$item['product_id'] ?? 0] ?? 0;
                            $trendClass = $item['trend'] === 'alza' ? 'up' : ($item['trend'] === 'baja' ? 'down' : 'steady');
                            $restockItem = $restockByProduct[$item['product_id'] ?? 0] ?? ($restockByName[strtolower($item['name'] ?? '')] ?? []);
                        @endphp
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['forecast'] }} uds</td>
                            <td><span class="trend-pill {{ $trendClass }}"><i class="ri-line-chart-line"></i>{{ ucfirst($item['trend'] ?? 'sin datos') }}</span></td>
                            <td>{{ $stock }} uds</td>
                            <td>{{ $series['weekly']['recent_total'] }} uds</td>
                            <td>{{ $series['monthly']['recent_total'] }} uds</td>
                            <td>
                                <button
                                    type="button"
                                    class="pill-button ghost btn-ai-product"
                                    data-product-name="{{ $item['name'] }}"
                                    data-product-forecast="{{ $item['forecast'] }}"
                                    data-product-trend="{{ $item['trend'] ?? 'sin datos' }}"
                                    data-product-stock="{{ $stock }}"
                                    data-weekly-labels='@json($series['weekly']['labels'])'
                                    data-weekly-values='@json($series['weekly']['data'])'
                                    data-weekly-total="{{ $series['weekly']['recent_total'] }}"
                                    data-monthly-labels='@json($series['monthly']['labels'])'
                                    data-monthly-values='@json($series['monthly']['data'])'
                                    data-monthly-total="{{ $series['monthly']['recent_total'] }}"
                                    data-forecast-history='@json($item['history'] ?? [])'
                                    data-capacity-flag="{{ !empty($item['capacity_flag']) ? '1' : '0' }}"
                                    data-capacity-note="{{ $item['capacity_note'] ?? '' }}"
                                    data-max="{{ $item['max_quantity'] ?? '' }}"
                                    data-min="{{ $item['min_quantity'] ?? '' }}"
                                    data-expiring-lots='@json($expiringLots[$item['product_id'] ?? 0]['lots'] ?? [])'
                                    data-restock='@json($restockItem)'
                                >
                                    Detalles
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;">Sin datos de Prediccion.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(!empty($data['capacity_alerts']))
        <div class="card" style="margin-top:1rem;">
            <div class="chart-head">
                <h4>Capacidad al limite</h4>
                <span class="chip text-yellow-200">{{ count($data['capacity_alerts']) }} productos</span>
            </div>
            <div class="alert-grid">
                @foreach($data['capacity_alerts'] as $cap)
                    <div class="summary-card" style="background:rgba(251,191,36,0.06); border:1px solid rgba(251,191,36,0.25);">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:0.5rem;">
                            <div>
                                <strong>{{ $cap['name'] }}</strong>
                                <p style="margin:0.15rem 0; colimite: {{ $cap['max_quantity'] ?? 'N/D' }} uds</p>
                                <p style="margin:0; color:#fcd34d;">{{ $cap['note'] ?? 'Sugerencia: aumentar capacidad' }}</p>
                            </div>
                            <span class="capacity-badge"><i class="ri-flashlight-line"></i> IA</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @php
        $sparkProducts = collect($data['forecast'] ?? [])->take(8);
    @endphp
    <div class="card">
        <div class="chart-head">
            <h4>Demanda por producto</h4>
            <span class="chip text-white/70">Series de las Ãºltimas semanas</span>
        </div>
        <div style="display:grid; grid-template-columns: repeat(auto-fit,minmax(280px,1fr)); gap:0.8rem;">
            @foreach($sparkProducts as $idx => $item)
                <div class="spark-card">
                    <div>
                        <strong>{{ $item['name'] }}</strong>
                        <p style="margin:0.3rem 0 0; color:rgba(255,255,255,0.8);">Demanda prevista: {{ $item['forecast'] }} uds</p>
                        @if(!empty($item['capacity_flag']))
                            <span class="capacity-badge" style="margin-top:0.4rem;"><i class="ri-alert-line"></i> limite alcanzado</span>
                        @else
                            <span class="chip text-white/60" style="margin-top:0.4rem; display:inline-block;">{{ ucfirst($item['trend'] ?? 'estable') }}</span>
                        @endif
                    </div>
                    <canvas id="spark-{{ $idx }}" data-history='@json($item['history'] ?? [])'></canvas>
                </div>
            @endforeach
        </div>
    </div>
    <div class="dashboard-grid" style="display:grid; grid-template-columns: repeat(auto-fit,minmax(320px,1fr)); gap:1rem;">
    </div>

    <div class="modal ai-modal-wide" id="aiProductModal">
        <div class="modal-content enhanced">
            <div class="modal-header">
                <h3>Detalle de Prediccion</h3>
                <button class="close-button" type="button" id="closeAiProduct">&times;</button>
            </div>
            <div class="ai-modal-body">
                <div class="ai-card">
                    <p class="text-white/70" style="margin:0;">Producto</p>
                    <h2 id="aiProductName" style="margin:0.2rem 0 0.6rem;"></h2>
                    <div class="badge-grid">
                        <span class="trend-pill steady" id="aiTrendBadge"><i class="ri-line-chart-line"></i><span id="aiTrend"></span></span>
                        <span class="chip">Pronostico: <strong id="aiForecastValue">N/D</strong> uds</span>
                        <span class="chip">Stock: <strong id="aiStockValue">0</strong> uds</span>
                        <span class="chip">Min: <strong id="aiMinValue">N/D</strong></span>
                        <span class="chip">Max: <strong id="aiMaxValue">N/D</strong></span>
                    </div>
                    <div class="ai-highlight" style="margin-top:0.6rem;">
                        <p class="text-white/70" style="margin:0;">Nota IA</p>
                        <p id="aiCapacityNote" style="margin:0.25rem 0 0; color:rgba(255,255,255,0.85);">Prediccion generada con historico reciente y topes de stock.</p>
                        <div class="badge-grid" style="margin-top:0.4rem;">
                            <span class="chip" id="aiWeeklySummary">Ventas 6 sem: <strong id="aiWeeklyTotal">0</strong> uds</span>
                            <span class="chip" id="aiMonthlySummary">Ventas 6 mes: <strong id="aiMonthlyTotal">0</strong> uds</span>
                        </div>
                    </div>
                    <div class="ai-highlight" style="margin-top:0.6rem;">
                        <p class="text-white/70" style="margin:0;">Lotes por vencer</p>
                        <ul id="aiLotsList" style="margin:0.35rem 0 0; padding-left:1rem; color:rgba(255,255,255,0.9);">
                            <li style="color:rgba(255,255,255,0.7);">Sin lotes proximos.</li>
                        </ul>
                    </div>
                    <div class="ai-highlight" style="margin-top:0.6rem;">
                        <p class="text-white/70" style="margin:0;">Recomendacion de restock</p>
                        <div id="aiRestockBlock" style="margin-top:0.35rem;">
                            <p style="margin:0; color:rgba(255,255,255,0.7);">Sin sugerencia.</p>
                        </div>
                    </div>
                </div>
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    <div class="ai-chart-mini">
                        <div class="chart-head" style="margin-bottom:0.25rem;">
                            <h4 style="margin:0;">Ventas por semana</h4>
                            <span class="chip text-white/70">Serie reciente</span>
                        </div>
                        <canvas id="aiWeeklyChart"></canvas>
                    </div>
                    <div class="ai-chart-mini">
                        <div class="chart-head" style="margin-bottom:0.25rem;">
                            <h4 style="margin:0;">Ventas por mes</h4>
                            <span class="chip text-white/70">Ãšltimos meses</span>
                        </div>
                        <canvas id="aiMonthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
    const forecastCtx = document.getElementById('forecastChart');
    const restockCtx = document.getElementById('restockChart');
    const forecastLabels = @json($charts['forecast']['labels'] ?? []);
    const forecastData = @json($charts['forecast']['data'] ?? []);
    const restockLabels = @json($charts['restock']['labels'] ?? []);
    const restockData = @json($charts['restock']['data'] ?? []);

    if (forecastCtx && forecastLabels.length) {
        new Chart(forecastCtx, {
            type: 'bar',
            data: {
                labels: forecastLabels,
                datasets: [{
                    label: 'Demanda semanal (uds)',
                    data: forecastData,
                    backgroundColor: 'rgba(86,109,48,0.8)',
                    borderColor: '#566d30',
                    borderWidth: 1,
                    borderRadius: 12,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#fff' }, grid: { display: false } },
                    y: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.08)' } }
                }
            }
        });
    }

    if (restockCtx && restockLabels.length) {
        new Chart(restockCtx, {
            type: 'doughnut',
            data: {
                labels: restockLabels,
                datasets: [{
                    data: restockData,
                    backgroundColor: ['#566d30', '#7b814f', '#b9be96', '#e0e3c7', '#f6f6f3'],
                }]
            },
            options: {
                plugins: {
                    legend: { labels: { color: '#fff' } }
                }
            }
        });
    }

    // Sparklines por producto
    document.querySelectorAll('[id^="spark-"]').forEach((canvas) => {
        const history = JSON.parse(canvas.dataset.history || '[]');
        if (!history.length) return;
        new Chart(canvas, {
            type: 'line',
            data: {
                labels: history.map((_, idx) => `S${idx + 1}`),
                datasets: [{
                    data: history,
                    borderColor: '#b9be96',
                    backgroundColor: 'rgba(185,190,150,0.15)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 0,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { x: { display: false }, y: { display: false } },
                elements: { line: { borderWidth: 2 } },
            }
        });
    });

    const aiModal = document.getElementById('aiProductModal');
    const aiClose = document.getElementById('closeAiProduct');
    const aiWeeklyCanvas = document.getElementById('aiWeeklyChart');
    const aiMonthlyCanvas = document.getElementById('aiMonthlyChart');
    let aiWeeklyChart = null;
    let aiMonthlyChart = null;

    const parseDataset = (value, fallback) => {
        try {
            return JSON.parse(value || '');
        } catch (e) {
            return fallback;
        }
    };

    const renderBar = (canvas, labels, data, color, instanceRef) => {
        if (!canvas) return null;
        if (instanceRef) {
            instanceRef.destroy();
        }
        return new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: color,
                    borderRadius: 10,
                    borderSkipped: false,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#f6f6f3' }, grid: { display: false } },
                    y: { ticks: { color: 'rgba(255,255,255,0.85)' }, grid: { color: 'rgba(255,255,255,0.08)' }, beginAtZero: true }
                }
            }
        });
    };

    const applyTrendBadge = (trend) => {
        const badge = document.getElementById('aiTrendBadge');
        badge.classList.remove('up', 'down', 'steady');
        let trendClass = 'steady';
        if (trend === 'alza') trendClass = 'up';
        if (trend === 'baja') trendClass = 'down';
        badge.classList.add(trendClass);
        document.getElementById('aiTrend').textContent = trend ? trend.replace('_',' ') : 'sin datos';
    };

    const openAiModal = (button) => {
        document.getElementById('aiProductName').textContent = button.dataset.productName || '';
        document.getElementById('aiForecastValue').textContent = button.dataset.productForecast || 'N/D';
        document.getElementById('aiStockValue').textContent = button.dataset.productStock || '0';
        document.getElementById('aiMinValue').textContent = button.dataset.min || 'N/D';
        document.getElementById('aiMaxValue').textContent = button.dataset.max || 'N/D';
        const weeklyTotal = button.dataset.weeklyTotal || '0';
        const monthlyTotal = button.dataset.monthlyTotal || '0';
        document.getElementById('aiWeeklyTotal').textContent = weeklyTotal;
        document.getElementById('aiMonthlyTotal').textContent = monthlyTotal;
        document.getElementById('aiCapacityNote').textContent = button.dataset.capacityNote || 'Prediccion generada con historico reciente.';
        applyTrendBadge(button.dataset.productTrend || 'sin datos');

        const weeklyLabels = parseDataset(button.dataset.weeklyLabels, []);
        const weeklyValues = parseDataset(button.dataset.weeklyValues, []);
        const monthlyLabels = parseDataset(button.dataset.monthlyLabels, []);
        const monthlyValues = parseDataset(button.dataset.monthlyValues, []);

        aiWeeklyChart = renderBar(aiWeeklyCanvas, weeklyLabels, weeklyValues, 'rgba(86,109,48,0.75)', aiWeeklyChart);
        aiMonthlyChart = renderBar(aiMonthlyCanvas, monthlyLabels, monthlyValues, 'rgba(224,227,199,0.86)', aiMonthlyChart);

        const lotsList = document.getElementById('aiLotsList');
        lotsList.innerHTML = '';
        const lots = parseDataset(button.dataset.expiringLots, []);
        if (!lots.length) {
            lotsList.innerHTML = '<li style="color:rgba(255,255,255,0.7);">Sin lotes proximos.</li>';
        } else {
            lots.forEach((lot) => {
                const li = document.createElement('li');
                li.textContent = `Lote ${lot.code} — ${lot.expires_in_days} dias (${lot.quantity} uds${lot.warehouse ? ' · ' + lot.warehouse : ''})`;
                lotsList.appendChild(li);
            });
        }

        const restockBlock = document.getElementById('aiRestockBlock');
        restockBlock.innerHTML = '';
        const restock = parseDataset(button.dataset.restock, null);
        if (!restock || !restock.suggested_qty) {
            restockBlock.innerHTML = '<p style="margin:0; color:rgba(255,255,255,0.7);">Sin sugerencia.</p>';
        } else {
            restockBlock.innerHTML = `
                <p style="margin:0;">Cantidad sugerida: <strong>${restock.suggested_qty} uds</strong></p>
                <p style="margin:0.2rem 0 0; color:rgba(255,255,255,0.8);">${restock.reason || ''}</p>
            `;
        }

        aiModal.classList.add('active');
    };

    document.querySelectorAll('.btn-ai-product').forEach((button) => {
        button.addEventListener('click', () => openAiModal(button));
    });

    const closeAiModal = () => aiModal.classList.remove('active');
    aiClose?.addEventListener('click', closeAiModal);
    window.addEventListener('click', (event) => {
        if (event.target === aiModal) closeAiModal();
    });
})();
</script>
@endpush
