@extends('layouts.sidebar')

@section('title', 'Predicciones | Pil Andina')
@section('page-title', 'Predicciones FastAPI')

@section('content')
    <div class="card">
        <div class="chart-head">
            <h4>Predicciones del servicio FastAPI</h4>
            @if($generatedAt)
                <span class="chip text-white/70">Generado: {{ \Carbon\Carbon::parse($generatedAt)->format('d/m/Y H:i') }}</span>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Pronostico</h4>
            <span class="chip text-white/70">{{ count($forecast ?? []) }} productos</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Pronostico</th>
                        <th>Tendencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forecast ?? [] as $item)
                        <tr>
                            <td>{{ $item['product_id'] ?? '-' }}</td>
                            <td>{{ $item['name'] ?? 'Producto' }}</td>
                            <td>{{ $item['forecast'] ?? 0 }} uds</td>
                            <td>{{ ucfirst($item['trend'] ?? 'sin datos') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;">Sin datos de pronostico.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Recomendaciones de restock</h4>
            <span class="chip text-white/70">{{ count($restock ?? []) }} productos</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Sugerido</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($restock ?? [] as $item)
                        <tr>
                            <td>{{ $item['name'] ?? 'Producto' }}</td>
                            <td>{{ $item['suggested_qty'] ?? 0 }} uds</td>
                            <td>{{ $item['reason'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align:center;">Sin recomendaciones.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Alertas</h4>
        </div>
        <div class="alert-grid">
            <div class="summary-card" style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25);">
                <strong>Stock bajo</strong>
                <ul style="margin:0.3rem 0 0; padding-left:1rem; color:rgba(255,255,255,0.9);">
                    @forelse($alerts['low_stock'] ?? [] as $alert)
                        <li>{{ $alert['name'] ?? 'Producto' }} — Stock {{ $alert['stock'] ?? 0 }} vs demanda {{ $alert['forecast'] ?? 0 }}</li>
                    @empty
                        <li>Sin alertas.</li>
                    @endforelse
                </ul>
            </div>
            <div class="summary-card" style="background:rgba(251,191,36,0.08); border:1px solid rgba(251,191,36,0.25);">
                <strong>Por vencer</strong>
                <ul style="margin:0.3rem 0 0; padding-left:1rem; color:rgba(255,255,255,0.9);">
                    @forelse($alerts['expiring'] ?? [] as $alert)
                        <li>{{ $alert['name'] ?? 'Producto' }} — {{ $alert['expires_in_days'] ?? 0 }} dias ({{ $alert['stock'] ?? 0 }} uds)</li>
                    @empty
                        <li>Sin lotes criticos.</li>
                    @endforelse
                </ul>
            </div>
            <div class="summary-card" style="background:rgba(59,130,246,0.08); border:1px solid rgba(59,130,246,0.25);">
                <strong>Sobre stock</strong>
                <ul style="margin:0.3rem 0 0; padding-left:1rem; color:rgba(255,255,255,0.9);">
                    @forelse($alerts['overstock'] ?? [] as $alert)
                        <li>{{ $alert['name'] ?? 'Producto' }} — Stock {{ $alert['stock'] ?? 0 }} vs demanda {{ $alert['forecast'] ?? 0 }}</li>
                    @empty
                        <li>Sin sobre stock.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
