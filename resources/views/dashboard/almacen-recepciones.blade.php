@extends('layouts.sidebar-almacen')

@section('title', 'Recepciones | Pil Andina')
@section('page-title', 'Preparación y despacho')

@section('content')
    <style>
        .warehouse-modal {
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
        .warehouse-modal.active {
            display: flex;
        }
        .warehouse-modal .modal-card {
            width: min(960px, 95vw);
            max-height: 90vh;
            background: linear-gradient(140deg, #0f172a, #132347);
            border-radius: 1.5rem;
            color: #fff;
            box-shadow:
                0 25px 60px rgba(2, 6, 23, 0.65),
                inset 0 1px 1px rgba(255,255,255,0.08);
            padding: 1.5rem 1.8rem;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .warehouse-modal .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding-bottom: 0.75rem;
        }
        .warehouse-modal .modal-body {
            padding-top: 1rem;
            overflow-y: auto;
        }
        .warehouse-modal .modal-footer {
            padding-top: 1rem;
            display: flex;
            justify-content: flex-end;
        }
        .warehouse-modal .summary-card {
            background: rgba(255,255,255,0.05);
        }
        .warehouse-modal .table-wrapper {
            max-height: 320px;
            overflow: auto;
            border-radius: 1rem;
        }
    </style>
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    <div class="card">
        <div class="chart-head">
            <h4>Filtrar pedidos</h4>
        </div>
        <form method="GET" action="{{ route('dashboard.almacen.receptions') }}" class="form-grid">
            <div class="form-group">
                <label for="status">Estado</label>
                <select id="status" name="status" class="select-light">
                    <option value="">Todos</option>
                    @foreach($statuses as $statusOption)
                        <option value="{{ $statusOption }}" @selected(($filters['status'] ?? null) === $statusOption)>{{ ucfirst(str_replace('_', ' ', $statusOption)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <button class="pill-button" type="submit">Aplicar</button>
                <a href="{{ route('dashboard.almacen.receptions') }}" class="clean-link">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Pedidos por preparar</h4>
            <span class="chip text-white/70">{{ $sales->total() }} registros</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th># Pedido</th>
                        <th>Cliente</th>
                        <th>Destino</th>
                        <th>Estado</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>#{{ $sale->id }}</td>
                            <td>
                                @if($sale->company)
                                    <strong>{{ $sale->company->name }}</strong><br>
                                    <small>NIT: {{ $sale->company->nit }}</small>
                                @elseif($sale->customer)
                                    <strong>{{ $sale->customer->user->name ?? 'Comprador' }}</strong><br>
                                    <small>Minorista</small>
                                @endif
                            </td>
                            <td>
                                {{ $sale->delivery_address ?? 'Retiro en planta' }}<br>
                                <small>{{ $sale->delivery_city ?? $sale->warehouse->city ?? '' }}</small>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('dashboard.almacen.receptions.status', $sale) }}">
                                    @csrf
                                    <select name="status" class="select-light" style="min-width:160px;">
                                        @foreach($statuses as $statusOption)
                                            <option value="{{ $statusOption }}" @selected($sale->status === $statusOption)>{{ ucfirst(str_replace('_', ' ', $statusOption)) }}</option>
                                        @endforeach
                                    </select>
                                    <button class="pill-button ghost" type="submit">Actualizar</button>
                                </form>
                            </td>
                            <td>
                                <button type="button" class="pill-button" data-open-modal="saleModal-{{ $sale->id }}">Ver productos</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;padding:1.5rem;">Sin pedidos para preparar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $sales->links() }}
        </div>
    </div>
    @foreach($sales as $sale)
        <div class="warehouse-modal" id="saleModal-{{ $sale->id }}">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3>Pedido #{{ $sale->id }}</h3>
                        <p style="margin:0;color:rgba(255,255,255,0.7);">
                            {{ $sale->delivery_address ?? 'Retiro en planta' }} — {{ $sale->delivery_city ?? $sale->warehouse->city ?? '' }}
                        </p>
                    </div>
                    <button type="button" class="close-button" data-close-modal>&times;</button>
                </div>
                <div class="modal-body">
                    <div class="summary modal-summary">
                        <div class="summary-card">
                            <strong>Cliente</strong>
                            <span>{{ $sale->company->name ?? $sale->customer->user->name ?? 'Cliente' }}</span>
                        </div>
                        <div class="summary-card">
                            <strong>Estado</strong>
                            <span>{{ ucfirst(str_replace('_', ' ', $sale->status)) }}</span>
                        </div>
                        <div class="summary-card">
                            <strong>Productos</strong>
                            <span>{{ $sale->items->count() }}</span>
                        </div>
                    </div>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Lote sugerido</th>
                                    <th>Disponible</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->items as $item)
                                    @php $lot = $suggestions[$item->id] ?? null; @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product->name ?? 'Producto' }}</strong>
                                            <p style="margin:0;color:rgba(255,255,255,0.6);">SKU: {{ $item->product->sku ?? 'N/A' }}</p>
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>
                                            @if($lot)
                                                <div>Código: {{ $lot->lote_code ?? 'Sin código' }}</div>
                                                <small>Vence: {{ optional($lot->expires_at)->format('d/m/Y') ?? 'N/A' }}</small>
                                            @else
                                                <span class="chip text-red-300">Sin lotes disponibles</span>
                                            @endif
                                        </td>
                                        <td>{{ $lot?->quantity ?? 0 }} uds</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('dashboard.vendedor.sales.report') }}?sale_id={{ $sale->id }}" target="_blank" rel="noopener" class="pill-button">Generar reporte</a>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
<script>
    (() => {
        document.querySelectorAll('[data-open-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.dataset.openModal);
                target?.classList.add('active');
            });
        });

        document.querySelectorAll('[data-close-modal]').forEach((button) => {
            button.addEventListener('click', () => button.closest('.warehouse-modal')?.classList.remove('active'));
        });

        document.querySelectorAll('.warehouse-modal').forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) modal.classList.remove('active');
            });
        });
    })();
</script>
@endpush
