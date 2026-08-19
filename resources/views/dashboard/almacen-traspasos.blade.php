@extends('layouts.sidebar-almacen')

@section('title', 'Traspasos | Pil Andina')
@section('page-title', 'Recepcion de traspasos')

@section('content')
    <style>
        .source-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.6rem;
            border-radius: 999px;
            background: rgba(14,165,233,0.14);
            color: #bae6fd;
            border: 1px solid rgba(56,189,248,0.35);
            font-weight: 800;
            margin-bottom: 0.35rem;
        }
        .transfer-detail {
            margin: 0.2rem 0 0;
            color: rgba(255,255,255,0.64);
            font-size: 0.88rem;
        }
        .transfer-modal {
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
        .transfer-modal.active {
            display: flex;
        }
        .transfer-modal .modal-card {
            width: min(980px, 95vw);
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
        .transfer-modal .modal-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding-bottom: 0.75rem;
        }
        .transfer-modal .modal-body {
            padding-top: 1rem;
            overflow-y: auto;
        }
        .transfer-modal .modal-footer {
            padding-top: 1rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .transfer-modal .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.85rem;
            margin-bottom: 1rem;
        }
        .transfer-modal .summary-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 1rem;
            padding: 0.85rem 1rem;
        }
        .transfer-modal .summary-card strong {
            display: block;
            color: rgba(255,255,255,0.64);
            font-size: 0.78rem;
            margin-bottom: 0.35rem;
        }
        .transfer-modal .summary-card span {
            font-weight: 700;
        }
        .transfer-modal .detail-section {
            margin: 1rem 0;
            padding: 1rem;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 1rem;
            background: rgba(255,255,255,0.04);
        }
        .transfer-modal .detail-section h4 {
            margin: 0 0 0.55rem;
        }
        .transfer-modal .table-wrapper {
            max-height: 320px;
            overflow: auto;
            border-radius: 1rem;
        }
        .transfer-modal table.data-table th,
        .transfer-modal table.data-table td {
            line-height: 1.6;
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
        }
    </style>

    @php
        $statusLabels = [
            \App\Models\Transfer::STATUS_PENDING => 'Pendiente',
            \App\Models\Transfer::STATUS_IN_TRANSIT => 'En transito',
            \App\Models\Transfer::STATUS_RECEIVED => 'Recibido',
        ];
    @endphp

    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    <div class="transfer-modal" id="transferErrorModal">
        <div class="modal-card" style="max-width:520px;">
            <div class="modal-header">
                <h3 style="margin:0;">Atencion</h3>
                <button type="button" class="close-button" data-close-error>&times;</button>
            </div>
            <div class="modal-body">
                <p id="transferErrorMessage" style="margin:0;">No puedes ingresar esa cantidad porque se excede el stock maximo del producto.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="pill-button" data-close-error>Entendido</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Filtrar traspasos</h4>
        </div>
        <form method="GET" action="{{ route('dashboard.almacen.transfers') }}" class="form-grid">
            <div class="form-group">
                <label for="status">Estado</label>
                <select id="status" name="status" class="select-light">
                    <option value="">Todos</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <button type="submit" class="pill-button">Aplicar</button>
                <a href="{{ route('dashboard.almacen.transfers') }}" class="clean-link">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Traspasos asignados</h4>
            <span class="chip text-white/70">{{ $transfers->total() }} registros</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Estado</th>
                        <th>Origen de solicitud</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                        @php
                            $statusSlug = \Illuminate\Support\Str::slug($transfer->status, '_');
                            $agentRequest = $transfer->agentTransferRequest;
                        @endphp
                        <tr>
                            <td>#{{ $transfer->id }}</td>
                            <td>{{ $transfer->fromWarehouse->name ?? 'No definido' }}</td>
                            <td>{{ $transfer->toWarehouse->name ?? 'N/A' }}</td>
                            <td>
                                <span class="status-pill {{ $statusSlug }}">
                                    {{ $statusLabels[$transfer->status] ?? ucfirst(str_replace('_', ' ', $transfer->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($agentRequest)
                                    <span class="source-chip"><i class="ri-robot-2-line"></i>Sugerencia de agente inteligente</span>
                                @else
                                    <span class="chip text-white/70">Registro manual</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn-secondary" data-open-transfer-modal="transferModal-{{ $transfer->id }}">
                                    Ver detalles
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:1.5rem;">Sin traspasos pendientes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $transfers->links() }}
        </div>
    </div>

    @foreach($transfers as $transfer)
        @php
            $statusSlug = \Illuminate\Support\Str::slug($transfer->status, '_');
            $agentRequest = $transfer->agentTransferRequest;
        @endphp
        <div class="transfer-modal" id="transferModal-{{ $transfer->id }}">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3 style="margin:0;">Traspaso #{{ $transfer->id }}</h3>
                        <p style="margin:0.35rem 0 0;color:rgba(255,255,255,0.7);">
                            {{ $transfer->fromWarehouse->name ?? 'Sin origen' }} -> {{ $transfer->toWarehouse->name ?? 'Sin destino' }}
                        </p>
                    </div>
                    <button type="button" class="close-button" data-close-transfer-modal>&times;</button>
                </div>
                <div class="modal-body">
                    <div class="summary">
                        <div class="summary-card">
                            <strong>Estado</strong>
                            <span class="status-pill {{ $statusSlug }}">{{ $statusLabels[$transfer->status] ?? ucfirst(str_replace('_', ' ', $transfer->status)) }}</span>
                        </div>
                        <div class="summary-card">
                            <strong>Fecha estimada</strong>
                            <span>{{ optional($transfer->expected_date)->format('d/m/Y') ?? 'Sin fecha' }}</span>
                        </div>
                        <div class="summary-card">
                            <strong>Solicitado por</strong>
                            <span>{{ $agentRequest ? 'Agente inteligente' : ($transfer->requestedByUser->name ?? 'Usuario Pil') }}</span>
                            <p class="transfer-detail">{{ optional($transfer->created_at)->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($agentRequest)
                            <div class="summary-card">
                                <strong>Aprobado por</strong>
                                <span>{{ $transfer->approvedByUser?->name ?? 'Usuario' }}</span>
                                <p class="transfer-detail">{{ optional($agentRequest->approved_at)->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                            </div>
                        @endif
                        <div class="summary-card">
                            <strong>Productos</strong>
                            <span>{{ $transfer->items->count() }} item(s)</span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4>Origen de solicitud</h4>
                        @if($agentRequest)
                            <span class="source-chip"><i class="ri-robot-2-line"></i>Sugerencia de agente inteligente</span>
                            <p class="transfer-detail">Solicitud creada: {{ optional($agentRequest->created_at)->format('d/m/Y H:i') }}</p>
                            <p class="transfer-detail">Aprobado por: {{ $transfer->approvedByUser?->name ?? 'Usuario' }}</p>
                            @if($agentRequest->priority)
                                <p class="transfer-detail">Prioridad: {{ $agentRequest->priority }}</p>
                            @endif
                            @if($agentRequest->reason)
                                <p class="transfer-detail">Motivo: {{ $agentRequest->reason }}</p>
                            @endif
                        @else
                            <span class="chip text-white/70">Registro manual</span>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('dashboard.almacen.transfers.status', $transfer) }}" class="detail-section form-grid" style="grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); gap:0.75rem; align-items:end;">
                        @csrf
                        <div class="form-group">
                            <label for="status-{{ $transfer->id }}">Cambiar estado</label>
                            <select id="status-{{ $transfer->id }}" name="status" class="select-light">
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" @selected($transfer->status === $status)>{{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="align-self:flex-end;">
                            <button type="submit" class="pill-button ghost">Actualizar estado</button>
                        </div>
                    </form>

                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>SKU</th>
                                    <th>Solicitado</th>
                                    <th>Recibido</th>
                                    <th>Danado</th>
                                    <th>Lote generado</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transfer->items as $item)
                                    <tr>
                                        <td><strong>{{ $item->product?->name ?? 'Producto '.$item->product_id }}</strong></td>
                                        <td>{{ $item->product?->sku ?? 'N/D' }}</td>
                                        <td>{{ $item->requested_qty }} uds</td>
                                        <td>{{ $item->received_qty ?? 0 }} uds</td>
                                        <td>{{ $item->damaged_qty ?? 0 }} uds</td>
                                        <td>
                                            @if($item->generatedLot)
                                                Creado #{{ $item->generatedLot->id }}
                                                <p style="margin:0;color:rgba(255,255,255,0.6);">Codigo: {{ $item->generatedLot->lote_code }}</p>
                                            @elseif($item->lot_code)
                                                Creado: {{ $item->lot_code }}
                                            @else
                                                Pendiente
                                            @endif
                                            @if($item->receiving_expires_at)
                                                <p style="margin:0;color:rgba(255,255,255,0.6);">Vence: {{ optional($item->receiving_expires_at)->format('d/m/Y') }}</p>
                                            @endif
                                        </td>
                                        <td>{{ $item->receiving_note ?? $item->notes ?? 'Sin comentarios' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align:center;">Sin productos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="detail-section">
                        <h4>Notas generales</h4>
                        <p style="margin:0;color:rgba(255,255,255,0.74);">{{ $transfer->notes ?: 'Sin notas registradas.' }}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="pill-button ghost" data-close-transfer-modal>Cerrar</button>
                    <a href="{{ route('dashboard.transfers.report.single', $transfer) }}" target="_blank" rel="noopener" class="pill-button">Descargar PDF</a>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
<script>
    (() => {
        document.querySelectorAll('[data-open-transfer-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById(button.dataset.openTransferModal)?.classList.add('active');
            });
        });

        document.querySelectorAll('[data-close-transfer-modal]').forEach((button) => {
            button.addEventListener('click', () => button.closest('.transfer-modal')?.classList.remove('active'));
        });

        document.querySelectorAll('.transfer-modal').forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) modal.classList.remove('active');
            });
        });

        @if(session('modal_error'))
            const transferErrorModal = document.getElementById('transferErrorModal');
            const transferErrorMessage = document.getElementById('transferErrorMessage');
            transferErrorMessage.textContent = @json(session('modal_error'));
            transferErrorModal.classList.add('active');

            transferErrorModal.querySelectorAll('[data-close-error]').forEach(btn => {
                btn.addEventListener('click', () => transferErrorModal.classList.remove('active'));
            });
        @endif
    })();
</script>
@endpush
