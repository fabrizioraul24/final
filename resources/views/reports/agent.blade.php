@extends('reports.layout')

@section('content')
    <div class="summary">
        <div class="summary-card">
            <strong>Fecha</strong>
            <span>{{ $generatedAt->format('d/m/Y H:i') }}</span>
        </div>
        <div class="summary-card">
            <strong>Restock sugeridos</strong>
            <span>{{ count($data['restock'] ?? []) }}</span>
        </div>
        <div class="summary-card">
            <strong>Alertas de stock</strong>
            <span>{{ count($data['alerts']['low_stock'] ?? []) }}</span>
        </div>
        <div class="summary-card">
            <strong>Lotes por vencer</strong>
            <span>{{ count($data['alerts']['expiring'] ?? []) }}</span>
        </div>
    </div>

    <h3 style="margin-top:1.5rem;">Predicción de demanda</h3>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Demanda semanal</th>
                <th>Tendencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['forecast'] ?? [] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['forecast'] }} uds</td>
                    <td>{{ ucfirst($item['trend']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Sin datos de pronóstico.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="margin-top:1.5rem;">Recomendaciones de restock</h3>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad sugerida</th>
                <th>Justificación</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['restock'] ?? [] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['suggested_qty'] }} uds</td>
                    <td>{{ $item['reason'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No se requieren compras adicionales.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="margin-top:1.5rem;">Alertas</h3>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Producto</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['alerts']['low_stock'] ?? [] as $alert)
                <tr>
                    <td>Stock bajo</td>
                    <td>{{ $alert['name'] }}</td>
                    <td>Inventario {{ $alert['stock'] }} vs demanda {{ $alert['forecast'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Sin alertas de bajo stock.</td>
                </tr>
            @endforelse
            @forelse($data['alerts']['expiring'] ?? [] as $alert)
                <tr>
                    <td>Por vencer</td>
                    <td>{{ $alert['name'] }}</td>
                    <td>{{ $alert['expires_in_days'] }} días restantes, {{ $alert['stock'] }} uds</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Sin lotes por vencer.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="margin-top:1.5rem;">Top pronósticos y restock</h3>
    <div class="summary" style="margin-top:0.5rem;">
        <div class="summary-card" style="flex:1;">
            <strong>Top pronóstico</strong>
            @foreach($charts['forecast'] ?? [] as $item)
                <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-top:0.2rem;">
                    <span>{{ $item['name'] }}</span>
                    <span>{{ $item['forecast'] }} uds</span>
                </div>
            @endforeach
        </div>
        <div class="summary-card" style="flex:1;">
            <strong>Top restock</strong>
            @foreach($charts['restock'] ?? [] as $item)
                <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-top:0.2rem;">
                    <span>{{ $item['name'] }}</span>
                    <span>{{ $item['suggested_qty'] }} uds</span>
                </div>
            @endforeach
        </div>
    </div>
@endsection
