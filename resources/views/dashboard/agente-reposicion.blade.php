@extends('layouts.sidebar')

@section('title', 'Agente de Reposicion | Pil Andina')
@section('page-title', 'Agente de Reposicion')

@section('content')
    <style>
        .agent-hero {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
            align-items: center;
            padding: 1.25rem 1.4rem;
            border-radius: 1.3rem;
            background: radial-gradient(circle at 10% 20%, rgba(99,102,241,0.25), transparent 30%), radial-gradient(circle at 80% 0%, rgba(14,165,233,0.2), transparent 30%), linear-gradient(135deg, #0f172a, #0b1223);
            box-shadow: 0 20px 45px rgba(2,6,23,0.4);
            margin-bottom: 1rem;
        }
        .agent-toolbar { display:flex; justify-content:flex-end; gap:0.55rem; align-items:center; flex-wrap:wrap; }
        .agent-search-island {
            margin-bottom: 1rem;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.045);
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            font-weight: 800;
            border: 1px solid rgba(255,255,255,0.18);
        }
        .status-pill.online { color: #bbf7d0; background: rgba(34,197,94,0.14); border-color: rgba(74,222,128,0.35); }
        .status-pill.offline { color: #fecdd3; background: rgba(239,68,68,0.15); border-color: rgba(248,113,113,0.35); }
        .agent-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .agent-card-label { color: rgba(255,255,255,0.68); margin: 0.25rem 0 0; }
        .agent-section { margin-top: 1rem; }
        .section-kicker { display:block; color:rgba(255,255,255,0.58); font-size:0.88rem; margin-top:0.15rem; }
        .urgent-row { background: rgba(239,68,68,0.12); }
        .decision-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.6rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            color: #e5e7eb;
            font-weight: 700;
        }
        .decision-chip.urgent { color:#fecdd3; background:rgba(239,68,68,0.16); border-color:rgba(248,113,113,0.35); }
        .decision-chip.warning { color:#fde68a; background:rgba(251,191,36,0.14); border-color:rgba(251,191,36,0.35); }
        .decision-chip.expired { color:#e9d5ff; background:rgba(147,51,234,0.18); border-color:rgba(192,132,252,0.4); }
        .decision-chip.normal { color:#bbf7d0; background:rgba(34,197,94,0.14); border-color:rgba(74,222,128,0.35); }
        .inline-actions { display:grid; gap:0.45rem; min-width:260px; }
        .mini-form { display:flex; gap:0.4rem; align-items:center; flex-wrap:wrap; }
        .mini-form input { min-width:160px; }
        .alert-columns { display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:0.8rem; }
        .alert-item {
            padding: 0.85rem;
            border-radius: 1rem;
            background: rgba(255,255,255,0.045);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .alert-product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 1rem;
        }
        .alert-product-card {
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.045);
            padding: 1rem;
            min-height: 170px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.85rem;
            position: relative;
            overflow: hidden;
        }
        .alert-product-card::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 5px;
            background: rgba(148,163,184,0.7);
        }
        .alert-product-card.critical {
            border-color: rgba(248,113,113,0.38);
            background: rgba(239,68,68,0.1);
        }
        .alert-product-card.critical::before { background: #ef4444; }
        .alert-product-card.expired {
            border-color: rgba(192,132,252,0.42);
            background: rgba(147,51,234,0.12);
        }
        .alert-product-card.expired::before { background: #a855f7; }
        .alert-product-card.warning {
            border-color: rgba(251,191,36,0.38);
            background: rgba(251,191,36,0.09);
        }
        .alert-product-card.warning::before { background: #f59e0b; }
        .alert-product-card.normal {
            border-color: rgba(74,222,128,0.32);
            background: rgba(34,197,94,0.08);
        }
        .alert-product-card.normal::before { background: #22c55e; }
        .alert-card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }
        .alert-card-head h4 {
            margin: 0;
        }
        .alert-product-title {
            display:flex;
            gap:0.75rem;
            align-items:center;
            min-width:0;
        }
        .alert-product-title img {
            width:46px;
            height:46px;
            border-radius:8px;
            object-fit:cover;
            border:1px solid rgba(255,255,255,0.12);
            background:rgba(255,255,255,0.06);
            flex:0 0 auto;
        }
        .alert-product-title h4 {
            overflow-wrap:anywhere;
        }
        .alert-card-list {
            display: grid;
            gap: 0.55rem;
        }
        .alert-card-item {
            border-radius: 8px;
            padding: 0.75rem 0.85rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .alert-card-item strong {
            display: block;
            margin-bottom: 0.25rem;
        }
        .alert-card-item p {
            margin: 0;
            color: rgba(255,255,255,0.72);
        }
        .alert-card-actions {
            display:flex;
            justify-content:flex-end;
        }
        .agent-lot-list {
            display:grid;
            gap:0.65rem;
        }
        .agent-lot-row {
            display:grid;
            grid-template-columns:minmax(0, 1fr) auto;
            gap:1rem;
            align-items:center;
            padding:0.8rem 0.9rem;
            border-radius:8px;
            border:1px solid rgba(255,255,255,0.08);
            background:rgba(255,255,255,0.045);
        }
        .agent-lot-row.expired { border-color:rgba(192,132,252,0.4); background:rgba(147,51,234,0.12); }
        .agent-lot-row.critical { border-color:rgba(248,113,113,0.38); background:rgba(239,68,68,0.1); }
        .agent-lot-row.warning { border-color:rgba(251,191,36,0.38); background:rgba(251,191,36,0.09); }
        .agent-lot-row.normal { border-color:rgba(74,222,128,0.32); background:rgba(34,197,94,0.08); }
        .agent-lot-row strong { display:block; }
        .agent-lot-row p { margin:0.2rem 0 0; color:rgba(255,255,255,0.72); }
        .alert-product {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.65rem;
            padding: 0.65rem 0;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .alert-product:first-of-type { border-top:0; padding-top:0.35rem; }
        .alert-product strong { display:block; color:#fff; }
        .alert-product p { margin:0.2rem 0 0; color:rgba(255,255,255,0.68); }
        .metric-row, .reason-metrics { display:flex; gap:0.35rem; flex-wrap:wrap; }
        .metric-row { justify-content:flex-end; }
        .metric-chip {
            display:inline-flex;
            align-items:center;
            gap:0.25rem;
            padding:0.25rem 0.45rem;
            border-radius:999px;
            background:rgba(255,255,255,0.08);
            color:rgba(255,255,255,0.84);
            font-size:0.82rem;
            font-weight:700;
            white-space:nowrap;
        }
        .metric-chip.danger { color:#fecdd3; background:rgba(239,68,68,0.16); }
        .metric-chip.warn { color:#fde68a; background:rgba(251,191,36,0.14); }
        .reason-summary { display:grid; gap:0.4rem; min-width:260px; }
        .reason-summary p { margin:0; color:rgba(255,255,255,0.78); }
        .reason-summary strong { color:#fff; }
        .agent-pagination { margin-top:1rem; }
        .agent-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: rgba(6, 11, 25, 0.65);
            backdrop-filter: blur(6px);
            z-index: 80;
        }
        .agent-modal.active { display:flex; }
        .agent-modal .modal-card {
            width: min(980px, 95vw);
            max-height: 90vh;
            background: linear-gradient(140deg, #0f172a, #132347);
            border-radius: 1.5rem;
            color: #fff;
            box-shadow: 0 25px 60px rgba(2,6,23,0.65), inset 0 1px 1px rgba(255,255,255,0.08);
            padding: 1.5rem 1.8rem;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .agent-modal .modal-header {
            display:flex;
            justify-content:space-between;
            gap:1rem;
            align-items:center;
            border-bottom:1px solid rgba(255,255,255,0.08);
            padding-bottom:0.75rem;
        }
        .agent-modal .modal-body { padding-top:1rem; overflow-y:auto; }
        .agent-modal .summary {
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(170px, 1fr));
            gap:0.85rem;
            margin-bottom:1rem;
        }
        .agent-modal .summary-card {
            background:rgba(255,255,255,0.05);
            border:1px solid rgba(255,255,255,0.08);
            border-radius:1rem;
            padding:0.85rem 1rem;
        }
        .agent-modal .summary-card strong {
            display:block;
            color:rgba(255,255,255,0.64);
            font-size:0.78rem;
            margin-bottom:0.35rem;
        }
        .agent-detail-section {
            margin:1rem 0;
            padding:1rem;
            border:1px solid rgba(255,255,255,0.08);
            border-radius:1rem;
            background:rgba(255,255,255,0.04);
        }
        .agent-bars { display:grid; gap:0.75rem; }
        .agent-bar-row { display:grid; gap:0.35rem; }
        .agent-bar-head { display:flex; justify-content:space-between; gap:1rem; color:rgba(255,255,255,0.78); font-weight:700; }
        .agent-bar-track { height:12px; border-radius:999px; background:rgba(255,255,255,0.08); overflow:hidden; }
        .agent-bar-fill { height:100%; border-radius:999px; background:linear-gradient(90deg, #566d30, #7b814f); }
        .agent-bar-fill.warn { background:linear-gradient(90deg, #7b814f, #b9be96); }
        .agent-bar-fill.danger { background:linear-gradient(90deg, #b9be96, #e0e3c7); }
        .agent-decision-actions {
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));
            gap:0.85rem;
        }
        .agent-decision-card {
            border:1px solid rgba(255,255,255,0.08);
            border-radius:1rem;
            padding:1rem;
            background:rgba(255,255,255,0.045);
        }
        .agent-decision-card h4 { margin:0 0 0.75rem; }
        .agent-decision-card form { display:grid; gap:0.65rem; }
    </style>

    <div class="agent-hero">
        <div>
            <h2 style="margin:0; color:#fff;">Reposicion inteligente</h2>
            <p style="margin:0.25rem 0 0; color:rgba(255,255,255,0.75);">Evaluaciones del agente para anticipar faltantes y aprobar traspasos con control humano.</p>
            @if($error)
                <p style="margin:0.55rem 0 0; color:#fecdd3;">{{ $error }}</p>
            @endif
        </div>
        <div class="agent-toolbar">
            <span class="status-pill {{ $agentOnline ? 'online' : 'offline' }}">
                <i class="{{ $agentOnline ? 'ri-checkbox-circle-line' : 'ri-close-circle-line' }}"></i>
                {{ $agentOnline ? 'Agente en linea' : 'Agente sin conexion' }}
            </span>
            <span class="agent-auto-chip"><i class="ri-refresh-line"></i> Monitoreo automatico</span>
            <span class="chip text-white/70">Ultima ejecucion: {{ optional($lastRunAt)->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    @if(session('status'))
        <div class="card" style="border:1px solid rgba(74,222,128,0.35); margin-bottom:1rem;">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="card" style="border:1px solid rgba(248,113,113,0.35); margin-bottom:1rem; color:#fecdd3;">{{ session('error') }}</div>
    @endif

    <div class="card agent-search-island">
        <div class="chart-head">
            <div>
                <h4>Buscar productos evaluados</h4>
                <span class="section-kicker">Filtra evaluaciones, alertas, solicitudes pendientes e historial por nombre, SKU o categoria.</span>
            </div>
            <a class="pill-button" target="_blank" rel="noopener" data-live-report-link
               href="{{ route('admin.agent.replenishment.report', ['search' => $search, 'category_id' => $categoryId]) }}">
                <i class="ri-file-pdf-line"></i> Reporte PDF
            </a>
        </div>
        <form method="GET" action="{{ route('admin.agent.replenishment') }}" class="form-grid" style="margin-top:1rem;" data-live-search-form>
            <div class="form-group">
                <label for="agent_search">Producto, SKU o categoria</label>
                <input type="text" id="agent_search" name="search" class="input-ghost" value="{{ $search }}" placeholder="Ej. Leche, YOG-001, Lacteos" data-live-search-input>
            </div>
            <div class="form-group">
                <label for="agent_category_id">Categoria</label>
                <select id="agent_category_id" name="category_id" class="select-light">
                    <option value="">Todas</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <button type="submit" class="pill-button">Buscar</button>
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <a href="{{ route('admin.agent.replenishment') }}" class="clean-link">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="agent-grid">
        <div class="card">
            <h3>Productos evaluados</h3>
            <div class="value">{{ $forecastsTotal }}</div>
            <p class="agent-card-label">Analisis de demanda para los proximos 7 dias.</p>
        </div>
        <div class="card">
            <h3>Alertas de stock bajo</h3>
            <div class="value">{{ count($alerts['low_stock'] ?? []) }}</div>
            <p class="agent-card-label">Productos que pueden quedar por debajo del nivel seguro.</p>
        </div>
        <div class="card">
            <h3>Lotes vencidos o por vencer</h3>
            <div class="value">{{ count($alerts['expiring'] ?? []) }}</div>
            <p class="agent-card-label">Inventario vencido o con fecha de vencimiento cercana.</p>
        </div>
        <div class="card">
            <h3>Solicitudes por revisar</h3>
            <div class="value">{{ $pendingRequestsTotal }}</div>
            <p class="agent-card-label">Recomendaciones esperando aprobacion o rechazo.</p>
        </div>
    </div>

    @foreach($alertProductCards as $productAlert)
        <div class="agent-modal" id="alertProductModal-{{ $productAlert['id'] }}">
            <div class="modal-card">
                <div class="modal-header">
                    <div class="alert-product-title">
                        <img src="{{ $productAlert['image'] }}" alt="{{ $productAlert['name'] }}">
                        <div>
                            <h3 style="margin:0;">{{ $productAlert['name'] }}</h3>
                            <span class="section-kicker">Problemas detectados y lotes relacionados.</span>
                        </div>
                    </div>
                    <button type="button" class="btn-secondary" data-close-agent-modal>Cerrar</button>
                </div>
                <div class="modal-body">
                    <div class="summary">
                        <div class="summary-card">
                            <strong>SKU</strong>
                            {{ $productAlert['sku'] ?? 'N/D' }}
                        </div>
                        <div class="summary-card">
                            <strong>Categoria</strong>
                            {{ $productAlert['category'] }}
                        </div>
                        <div class="summary-card">
                            <strong>Estado</strong>
                            {{ $productAlert['severity_label'] }}
                        </div>
                    </div>

                    <div class="agent-detail-section">
                        <h4 style="margin:0 0 0.75rem;">Problemas detectados</h4>
                        <div class="alert-card-list">
                            @foreach($productAlert['problems'] as $problem)
                                <div class="alert-card-item">
                                    <strong>{{ $problem['label'] }}</strong>
                                    <p>{{ $problem['message'] }}</p>
                                    @if(!empty($problem['meta']))
                                        <div class="metric-row" style="justify-content:flex-start; margin-top:0.55rem;">
                                            @foreach($problem['meta'] as $label => $value)
                                                <span class="metric-chip {{ str_contains(strtolower($label), 'faltante') ? 'danger' : (str_contains(strtolower($label), 'minimo') ? 'warn' : '') }}">
                                                    {{ $label }}: {{ $value }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="agent-detail-section">
                        <h4 style="margin:0 0 0.75rem;">Lotes del producto</h4>
                        <div class="agent-lot-list">
                            @forelse($productAlert['lots'] as $lot)
                                <div class="agent-lot-row {{ $lot['status'] }}">
                                    <div>
                                        <strong>{{ $lot['label'] }} - {{ $lot['code'] }}</strong>
                                        <p>{{ $lot['message'] }}</p>
                                    </div>
                                    <div class="metric-row">
                                        <span class="metric-chip">Cantidad: {{ $lot['quantity'] }} uds</span>
                                        <span class="metric-chip {{ $lot['status'] === 'expired' ? 'danger' : ($lot['status'] === 'warning' ? 'warn' : '') }}">
                                            Vence: {{ $lot['expires_at'] }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="alert-card-item">
                                    <strong>Sin lotes activos</strong>
                                    <p>No hay lotes con cantidad disponible para este producto.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="card agent-section">
        <div class="chart-head">
            <div>
                <h4>Evaluaciones de reposicion</h4>
                <span class="section-kicker">Demanda, stock disponible y decision recomendada por producto.</span>
            </div>
            <span class="chip text-white/70">{{ $forecasts->total() }} registros</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Demanda 7 dias</th>
                        <th>Stock actual</th>
                        <th>Traspasos previstos</th>
                        <th>Stock final estimado</th>
                        <th>Stock minimo</th>
                        <th>Decision</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forecasts as $item)
                        @php $urgent = strtolower((string) ($item['priority'] ?? '')) === 'urgente'; @endphp
                        <tr class="{{ $urgent ? 'urgent-row' : '' }}">
                            <td>{{ $item['name'] }}</td>
                            <td>{{ number_format($item['forecast_7_days'], 0) }} uds</td>
                            <td>{{ $item['stock'] }} uds</td>
                            <td>{{ $item['in_transit'] }} uds</td>
                            <td>
                                @if($item['result'] < 0)
                                    Faltan {{ number_format(abs($item['result']), 0) }} uds
                                @else
                                    {{ number_format($item['result'], 0) }} uds
                                @endif
                            </td>
                            <td>{{ $item['safety_threshold'] }} uds</td>
                            <td>
                                <span class="decision-chip {{ $urgent ? 'urgent' : '' }}">
                                    <i class="{{ $urgent ? 'ri-alarm-warning-line' : 'ri-lightbulb-flash-line' }}"></i>
                                    {{ $item['decision'] }}{{ $urgent ? ' - Urgente' : '' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;">Sin evaluaciones del agente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="agent-pagination">{{ $forecasts->links() }}</div>
    </div>

    <div class="card agent-section">
        <div class="chart-head">
            <div>
                <h4>Solicitudes pendientes del agente</h4>
                <span class="section-kicker">Aprobacion humana antes de crear o confirmar el traspaso operativo.</span>
            </div>
            <span class="chip text-white/70">{{ $pendingRequests->total() }} pendientes</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad solicitada</th>
                        <th>Prioridad</th>
                        <th>Motivo resumido</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $request)
                        @php
                            $urgent = strtolower((string) $request->priority) === 'urgente';
                            $parsedReason = null;
                            if ($request->reason && preg_match('/Stock\s+(-?\d+)\s+\+\s+traspasos\s+7d\s+(-?\d+)\s+-\s+demanda\s+proyectada\s+7d\s+(-?\d+)\s+=\s+(-?\d+);\s+cae\s+bajo\s+umbral\s+(-?\d+)/i', $request->reason, $matches)) {
                                $parsedReason = [
                                    'stock' => (int) $matches[1],
                                    'transfers' => (int) $matches[2],
                                    'demand' => (int) $matches[3],
                                    'result' => (int) $matches[4],
                                    'threshold' => (int) $matches[5],
                                ];
                            }
                            $reasonText = str_replace(
                                ['CREATE_TRANSFER_REQUEST', 'low_stock', 'expiring', 'post_peak_drop'],
                                ['Crear solicitud de traspaso', 'stock bajo', 'por vencer', 'baja despues de pico'],
                                (string) $request->reason
                            );
                        @endphp
                        <tr class="{{ $urgent ? 'urgent-row' : '' }}">
                            <td>{{ $request->product?->name ?? 'Producto '.$request->product_id }}</td>
                            <td>{{ $request->requested_qty }} uds</td>
                            <td><span class="decision-chip {{ $urgent ? 'urgent' : '' }}">{{ $request->priority ?? 'Normal' }}</span></td>
                            <td style="max-width:460px;">
                                @if($parsedReason)
                                    <div class="reason-summary">
                                        <p>
                                            <strong>Reposicion necesaria.</strong>
                                            @if($parsedReason['result'] < 0)
                                                Faltan <strong>{{ abs($parsedReason['result']) }} uds</strong> para completar la demanda prevista y mantener el stock minimo.
                                            @else
                                                Quedarian <strong>{{ $parsedReason['result'] }} uds</strong>, por debajo del stock minimo.
                                            @endif
                                        </p>
                                        <div class="reason-metrics">
                                            <span class="metric-chip">Stock: {{ $parsedReason['stock'] }} uds</span>
                                            <span class="metric-chip warn">Stock minimo: {{ $parsedReason['threshold'] }} uds</span>
                                        </div>
                                    </div>
                                @else
                                    {{ $reasonText ?: 'El agente recomienda revisar este producto.' }}
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn-secondary" data-open-agent-modal="agentRequestModal-{{ $request->id }}">
                                    Ver detalles
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;">No hay solicitudes pendientes del agente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="agent-pagination">{{ $pendingRequests->links() }}</div>
    </div>

    @foreach($pendingRequests as $request)
        @php
            $urgent = strtolower((string) $request->priority) === 'urgente';
            $parsedReason = null;
            if ($request->reason && preg_match('/Stock\s+(-?\d+)\s+\+\s+traspasos\s+7d\s+(-?\d+)\s+-\s+demanda\s+proyectada\s+7d\s+(-?\d+)\s+=\s+(-?\d+);\s+cae\s+bajo\s+umbral\s+(-?\d+)/i', $request->reason, $matches)) {
                $parsedReason = [
                    'stock' => (int) $matches[1],
                    'transfers' => (int) $matches[2],
                    'demand' => (int) $matches[3],
                    'result' => (int) $matches[4],
                    'threshold' => (int) $matches[5],
                ];
            }
            $stock = $parsedReason['stock'] ?? 0;
            $transfers = $parsedReason['transfers'] ?? 0;
            $demand = $parsedReason['demand'] ?? 0;
            $result = $parsedReason['result'] ?? null;
            $threshold = $parsedReason['threshold'] ?? 0;
            $missing = $result !== null && $result < 0 ? abs($result) : 0;
            $scale = max($stock, $transfers, $demand, $threshold, $missing, 1);
            $stockPct = min(100, round(($stock / $scale) * 100));
            $transferPct = min(100, round(($transfers / $scale) * 100));
            $demandPct = min(100, round(($demand / $scale) * 100));
            $thresholdPct = min(100, round(($threshold / $scale) * 100));
            $missingPct = min(100, round(($missing / $scale) * 100));
        @endphp
        <div class="agent-modal" id="agentRequestModal-{{ $request->id }}">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3 style="margin:0;">Solicitud de traspaso #{{ $request->id }}</h3>
                        <p style="margin:0.35rem 0 0;color:rgba(255,255,255,0.7);">
                            {{ $request->product?->name ?? 'Producto '.$request->product_id }}
                        </p>
                    </div>
                    <button type="button" class="close-button" data-close-agent-modal>&times;</button>
                </div>
                <div class="modal-body">
                    <div class="summary">
                        <div class="summary-card">
                            <strong>Cantidad solicitada</strong>
                            <span>{{ $request->requested_qty }} uds</span>
                        </div>
                        <div class="summary-card">
                            <strong>Prioridad</strong>
                            <span class="decision-chip {{ $urgent ? 'urgent' : '' }}">{{ $request->priority ?? 'Normal' }}</span>
                        </div>
                        <div class="summary-card">
                            <strong>Estado</strong>
                            <span>{{ $request->status }}</span>
                        </div>
                        <div class="summary-card">
                            <strong>Creada</strong>
                            <span>{{ optional($request->created_at)->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    <div class="agent-detail-section">
                        <h4 style="margin:0 0 0.75rem;">Detalle de reposicion</h4>
                        @if($parsedReason)
                            <p style="margin:0 0 1rem;color:rgba(255,255,255,0.78);">
                                <strong>Reposicion necesaria.</strong>
                                @if($missing > 0)
                                    Faltan <strong>{{ $missing }} unidades</strong> para completar la demanda prevista de 7 dias y mantener el stock minimo.
                                @else
                                    Despues de cubrir la demanda prevista quedarian <strong>{{ $result }} uds</strong>, por debajo del stock minimo.
                                @endif
                            </p>
                            <div class="agent-bars">
                                <div class="agent-bar-row">
                                    <div class="agent-bar-head"><span>Stock actual</span><span>{{ $stock }} uds</span></div>
                                    <div class="agent-bar-track"><div class="agent-bar-fill" style="width:{{ $stockPct }}%;"></div></div>
                                </div>
                                <div class="agent-bar-row">
                                    <div class="agent-bar-head"><span>Traspasos previstos</span><span>{{ $transfers }} uds</span></div>
                                    <div class="agent-bar-track"><div class="agent-bar-fill" style="width:{{ $transferPct }}%;"></div></div>
                                </div>
                                <div class="agent-bar-row">
                                    <div class="agent-bar-head"><span>Demanda 7 dias</span><span>{{ $demand }} uds</span></div>
                                    <div class="agent-bar-track"><div class="agent-bar-fill warn" style="width:{{ $demandPct }}%;"></div></div>
                                </div>
                                <div class="agent-bar-row">
                                    <div class="agent-bar-head"><span>Stock minimo</span><span>{{ $threshold }} uds</span></div>
                                    <div class="agent-bar-track"><div class="agent-bar-fill warn" style="width:{{ $thresholdPct }}%;"></div></div>
                                </div>
                                @if($missing > 0)
                                    <div class="agent-bar-row">
                                        <div class="agent-bar-head"><span>Unidades faltantes</span><span>{{ $missing }} uds</span></div>
                                        <div class="agent-bar-track"><div class="agent-bar-fill danger" style="width:{{ $missingPct }}%;"></div></div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p style="margin:0;color:rgba(255,255,255,0.78);">{{ $request->reason ?: 'El agente recomienda revisar este producto.' }}</p>
                        @endif
                    </div>

                    <div class="agent-detail-section">
                        <h4 style="margin:0 0 0.75rem;">Decision humana</h4>
                        <div class="agent-decision-actions">
                            <div class="agent-decision-card">
                                <h4>Aprobar traspaso</h4>
                                <form method="POST" action="{{ route('admin.agent.replenishment.approve', $request) }}">
                                    @csrf
                                    <input type="text" name="decision_reason" class="input-ghost" placeholder="Motivo de aprobacion">
                                    <button type="submit" class="pill-button">Aprobar traspaso</button>
                                </form>
                            </div>
                            <div class="agent-decision-card">
                                <h4>Rechazar solicitud</h4>
                                <form method="POST" action="{{ route('admin.agent.replenishment.reject', $request) }}">
                                    @csrf
                                    <input type="text" name="decision_reason" class="input-ghost" placeholder="Motivo de rechazo">
                                    <button type="submit" class="pill-button ghost">Rechazar traspaso</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="card agent-section">
        <div class="chart-head">
            <div>
                <h4>Alertas por producto</h4>
                <span class="section-kicker">Revisa stock bajo, lotes por vencer y lotes vencidos por producto.</span>
            </div>
            <span class="chip text-white/70">Verde normal - Amarillo menor a 5 meses - Rojo menor a 2 meses - Morado vencido</span>
        </div>
        <div class="alert-product-grid">
            @forelse($alertProductCards as $productAlert)
                <div class="alert-product-card {{ $productAlert['severity'] }}">
                    <div class="alert-card-head">
                        <div class="alert-product-title">
                            <img src="{{ $productAlert['image'] }}" alt="{{ $productAlert['name'] }}">
                            <div>
                                <h4>{{ $productAlert['name'] }}</h4>
                                <span class="section-kicker">SKU: {{ $productAlert['sku'] ?? 'N/D' }} - {{ $productAlert['category'] }}</span>
                            </div>
                        </div>
                        <span class="decision-chip {{ $productAlert['severity'] === 'critical' ? 'urgent' : $productAlert['severity'] }}">
                            {{ $productAlert['severity_label'] }}
                        </span>
                    </div>

                    <div class="metric-row" style="justify-content:flex-start;">
                        @foreach($productAlert['metrics'] as $metricLabel => $metricValue)
                            <span class="metric-chip {{ str_contains(strtolower($metricLabel), 'faltante') ? 'danger' : (str_contains(strtolower($metricLabel), 'minimo') ? 'warn' : '') }}">
                                {{ $metricLabel }}: {{ $metricValue }}
                            </span>
                        @endforeach
                    </div>

                    <div class="alert-card-list">
                        @foreach(collect($productAlert['problems'])->take(2) as $item)
                            <div class="alert-card-item">
                                <strong>{{ $item['label'] }}</strong>
                                <p>{{ $item['message'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="alert-card-actions">
                        <button type="button" class="btn-secondary" data-open-alert-modal="alertProductModal-{{ $productAlert['id'] }}">
                            Detalles
                        </button>
                    </div>
                </div>
            @empty
                <div class="alert-product-card">
                    <h4 style="margin:0;">Sin alertas operativas</h4>
                    <p style="margin:0;color:rgba(255,255,255,0.7);">No hay productos criticos en este momento.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="card agent-section">
        <div class="chart-head">
            <div>
                <h4>Historial de decisiones</h4>
                <span class="section-kicker">Solicitudes creadas, aprobadas o rechazadas por el flujo del agente.</span>
            </div>
            <span class="chip text-white/70">{{ $recentRequests->total() }} registros</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Estado</th>
                        <th>Decision humana</th>
                        <th>Traspaso relacionado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRequests as $request)
                        <tr>
                            <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $request->product?->name ?? 'Producto '.$request->product_id }}</td>
                            <td>{{ $request->requested_qty }} uds</td>
                            <td>{{ $request->status }}</td>
                            <td>{{ $request->approved_by ? 'Aprobado por usuario' : ($request->rejected_by ? 'Rechazado por usuario' : 'Pendiente de revision') }}</td>
                            <td>
                                @if($request->transfer)
                                    Traspaso #{{ $request->transfer->id }}
                                @else
                                    N/D
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;">Sin historial de solicitudes del agente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="agent-pagination">{{ $recentRequests->links() }}</div>
    </div>
@endsection

@push('scripts')
<script>
    (() => {
        document.querySelectorAll('[data-open-agent-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById(button.dataset.openAgentModal)?.classList.add('active');
            });
        });

        document.querySelectorAll('[data-open-alert-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById(button.dataset.openAlertModal)?.classList.add('active');
            });
        });

        document.querySelectorAll('[data-close-agent-modal]').forEach((button) => {
            button.addEventListener('click', () => button.closest('.agent-modal')?.classList.remove('active'));
        });

        document.querySelectorAll('.agent-modal').forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) modal.classList.remove('active');
            });
        });
    })();
</script>
@endpush
