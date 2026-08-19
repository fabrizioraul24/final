@extends('reports.layout')

@section('content')
    <div class="summary">
        <div class="summary-card">
            <strong>Estado del agente</strong>
            <span>{{ $agentOnline ? 'En linea' : 'Sin conexion' }}</span>
        </div>
        <div class="summary-card">
            <strong>Filtro</strong>
            <span>{{ $filters['search'] ?? 'Todos' }}</span>
        </div>
        <div class="summary-card">
            <strong>Categoria</strong>
            <span>{{ $filters['category'] ?? 'Todas' }}</span>
        </div>
        <div class="summary-card">
            <strong>Productos evaluados</strong>
            <span>{{ $forecasts->count() }}</span>
        </div>
        <div class="summary-card">
            <strong>Solicitudes pendientes</strong>
            <span>{{ $pendingRequests->count() }}</span>
        </div>
    </div>

    @if($error)
        <div class="chart-block">
            <p class="chart-title">Observacion</p>
            <p style="margin:0;color:#991b1b;">{{ $error }}</p>
        </div>
    @endif

    @php
        $criticalAlerts = $alertProductCards->whereIn('severity', ['critical', 'expired'])->count();
        $warningAlerts = $alertProductCards->where('severity', 'warning')->count();
        $lowStock = count($alerts['low_stock'] ?? []);
        $expiring = count($alerts['expiring'] ?? []);
        $maxSummary = max($forecasts->count(), $criticalAlerts, $warningAlerts, $lowStock, $expiring, 1);
    @endphp

    <div class="chart-block">
        <p class="chart-title">Resumen operativo</p>
        <div class="bar-row">
            <span class="bar-label">Evaluados</span>
            <div class="bar-track"><div class="bar-fill" style="width: {{ ($forecasts->count() / $maxSummary) * 100 }}%;"></div></div>
            <span class="bar-value">{{ $forecasts->count() }}</span>
        </div>
        <div class="bar-row">
            <span class="bar-label">Criticos</span>
            <div class="bar-track"><div class="bar-fill" style="width: {{ ($criticalAlerts / $maxSummary) * 100 }}%;"></div></div>
            <span class="bar-value">{{ $criticalAlerts }}</span>
        </div>
        <div class="bar-row">
            <span class="bar-label">Advertencias</span>
            <div class="bar-track"><div class="bar-fill" style="width: {{ ($warningAlerts / $maxSummary) * 100 }}%;"></div></div>
            <span class="bar-value">{{ $warningAlerts }}</span>
        </div>
        <div class="bar-row">
            <span class="bar-label">Stock bajo</span>
            <div class="bar-track"><div class="bar-fill" style="width: {{ ($lowStock / $maxSummary) * 100 }}%;"></div></div>
            <span class="bar-value">{{ $lowStock }}</span>
        </div>
        <div class="bar-row">
            <span class="bar-label">Por vencer</span>
            <div class="bar-track"><div class="bar-fill" style="width: {{ ($expiring / $maxSummary) * 100 }}%;"></div></div>
            <span class="bar-value">{{ $expiring }}</span>
        </div>
    </div>

    <h3 style="margin-top:1.5rem;">Evaluaciones de reposicion</h3>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>SKU</th>
                <th>Demanda 7 dias</th>
                <th>Stock</th>
                <th>Traspasos</th>
                <th>Resultado</th>
                <th>Decision</th>
            </tr>
        </thead>
        <tbody>
            @forelse($forecasts as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['sku'] ?? 'N/D' }}</td>
                    <td>{{ number_format($item['forecast_7_days'], 0) }} uds</td>
                    <td>{{ $item['stock'] }} uds</td>
                    <td>{{ $item['in_transit'] }} uds</td>
                    <td>{{ number_format($item['result'], 0) }} uds</td>
                    <td>{{ $item['decision'] }}{{ $item['priority'] ? ' - '.$item['priority'] : '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Sin evaluaciones para el filtro seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="margin-top:1.5rem;">Alertas por producto</h3>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>SKU</th>
                <th>Categoria</th>
                <th>Estado</th>
                <th>Problemas detectados</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alertProductCards as $card)
                <tr>
                    <td>{{ $card['name'] }}</td>
                    <td>{{ $card['sku'] ?? 'N/D' }}</td>
                    <td>{{ $card['category'] }}</td>
                    <td>{{ $card['severity_label'] }}</td>
                    <td>
                        @foreach(collect($card['problems'])->take(3) as $problem)
                            <div><strong>{{ $problem['label'] }}:</strong> {{ $problem['message'] }}</div>
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Sin alertas operativas para el filtro seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="margin-top:1.5rem;">Solicitudes pendientes</h3>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Prioridad</th>
                <th>Motivo</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendingRequests as $request)
                <tr>
                    <td>{{ $request->product?->name ?? 'Producto '.$request->product_id }}</td>
                    <td>{{ $request->requested_qty }} uds</td>
                    <td>{{ $request->priority ?? 'Normal' }}</td>
                    <td>{{ $request->reason ?: 'Sin motivo registrado.' }}</td>
                    <td>{{ optional($request->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Sin solicitudes pendientes para el filtro seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="margin-top:1.5rem;">Historial reciente</h3>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Estado</th>
                <th>Decision humana</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentRequests as $request)
                <tr>
                    <td>{{ optional($request->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $request->product?->name ?? 'Producto '.$request->product_id }}</td>
                    <td>{{ $request->requested_qty }} uds</td>
                    <td>{{ $request->status }}</td>
                    <td>{{ $request->approved_by ? 'Aprobado' : ($request->rejected_by ? 'Rechazado' : 'Pendiente') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Sin historial reciente para el filtro seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
