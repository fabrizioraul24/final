@extends('layouts.sidebar')

@section('title', 'Traspasos internos | Pil Andina')
@section('page-title', 'Traspasos de productos')

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
        .transfer-products {
            display: grid;
            gap: 0.25rem;
        }
        .transfer-products span {
            color: rgba(255,255,255,0.82);
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
        .transfer-modal .table-wrapper {
            max-height: 320px;
            overflow: auto;
            border-radius: 1rem;
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

    <div class="stats-grid">
        <div class="card">
            <h3>Traspasos registrados</h3>
            <div class="value">{{ $stats['total'] }}</div>
            <span class="chip text-white/70"><i class="ri-stack-line"></i>Total historico</span>
        </div>
        <div class="card">
            <h3>Pendientes</h3>
            <div class="value">{{ $stats['pending'] }}</div>
            <span class="chip text-yellow-300"><i class="ri-time-line"></i>Por atender</span>
        </div>
        <div class="card">
            <h3>En transito</h3>
            <div class="value">{{ $stats['in_transit'] }}</div>
            <span class="chip text-blue-300"><i class="ri-truck-line"></i>Moviendose</span>
        </div>
        <div class="card">
            <h3>Recibidos</h3>
            <div class="value">{{ $stats['received'] }}</div>
            <span class="chip text-green-300"><i class="ri-checkbox-circle-line"></i>Confirmados</span>
        </div>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Reportes ejecutivos</h4>
            <a class="pill-button" target="_blank" rel="noopener" href="{{ route('dashboard.transfers.report') }}">
                <i class="ri-file-pdf-line mr-1"></i> Generar reporte PDF
            </a>
        </div>
        <p class="text-white/70">Descarga un resumen profesional listo para compartir con los responsables logisticos.</p>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Nuevo traspaso</h4>
        </div>
        <form method="POST" action="{{ route('dashboard.transfers.store') }}" id="transferForm">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label for="from_warehouse_id">Almacen origen</label>
                    <select id="from_warehouse_id" name="from_warehouse_id" class="select-light">
                        <option value="">Seleccionar Santa Cruz o Cochabamba</option>
                        @foreach($sourceWarehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('from_warehouse_id') == $warehouse->id)>{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                        @endforeach
                    </select>
                    @error('from_warehouse_id')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label>Almacen destino</label>
                    <input type="text" class="input-ghost" value="{{ $targetWarehouse?->name ?? 'Deposito La Paz' }} (LPZ)" readonly>
                    <small style="color:rgba(255,255,255,0.64);">Destino fijo para este sistema.</small>
                </div>
                <div class="form-group">
                    <label for="expected_date">Fecha estimada</label>
                    <input type="date" id="expected_date" name="expected_date" class="input-ghost" value="{{ old('expected_date') }}">
                    @error('expected_date')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="status">Estado inicial</label>
                    <select id="status" name="status" class="select-light">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', \App\Models\Transfer::STATUS_PENDING) === $status)>{{ $statusLabels[$status] }}</option>
                        @endforeach
                    </select>
                    @error('status')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="form-group" style="grid-column:1 / -1;">
                    <label for="notes">Notas generales</label>
                    <textarea id="notes" name="notes" class="input-ghost" rows="2" placeholder="Instrucciones para logistica">{{ old('notes') }}</textarea>
                    @error('notes')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="transfer-items-wrapper">
                <div class="chart-head" style="margin-top:1.2rem;">
                    <h4>Productos a traspasar</h4>
                    <button type="button" class="pill-button" id="addTransferItem" data-lookup-url="{{ route('dashboard.transfers.lookup') }}">
                        <i class="ri-add-line"></i>Agregar producto
                    </button>
                </div>
                <p class="text-white/70" style="margin-bottom:1rem;">Introduce el codigo (SKU) para rellenar automaticamente los datos y la cantidad disponible en el almacen de origen.</p>
                <div id="transferItems"></div>
                @error('items')<small style="color:#f87171">{{ $message }}</small>@enderror
                @error('items.*.product_id')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>

            <div style="margin-top:1.5rem; display:flex; justify-content:flex-end;">
                <button type="submit" class="pill-button">Guardar traspaso</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Traspasos recientes</h4>
            <span class="chip">{{ $transfers->total() }} registros · mayor a menor</span>
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
                                    {{ $statusLabels[$transfer->status] ?? ucfirst($transfer->status) }}
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
                            <td colspan="6" style="text-align:center; padding:1rem;">Sin traspasos registrados.</td>
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
                            <span class="status-pill {{ $statusSlug }}">{{ $statusLabels[$transfer->status] ?? ucfirst($transfer->status) }}</span>
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

                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>SKU</th>
                                    <th>Solicitado</th>
                                    <th>Recibido</th>
                                    <th>Danado</th>
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
                                        <td>{{ $item->notes ?? $item->receiving_note ?? 'Sin comentarios' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align:center;">Sin productos registrados.</td>
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
                    <a class="pill-button" target="_blank" rel="noopener" href="{{ route('dashboard.transfers.report.single', $transfer) }}">
                        Generar reporte PDF
                    </a>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
<script>
    (function() {
        const itemsContainer = document.getElementById('transferItems');
        const addItemButton = document.getElementById('addTransferItem');
        const lookupUrl = addItemButton.dataset.lookupUrl;
        const fromWarehouseSelect = document.getElementById('from_warehouse_id');
        let itemIndex = 0;

        function createRow(index) {
            const wrapper = document.createElement('div');
            wrapper.className = 'transfer-item-row';
            wrapper.dataset.index = index;
            wrapper.innerHTML = `
                <div class="form-grid">
                    <div class="form-group">
                        <label>cadigo (SKU)</label>
                        <input type="text" class="input-ghost sku-input" placeholder="Ej. 133" data-index="${index}">
                        <input type="hidden" name="items[${index}][product_id]" class="product-id-input" required>
                    </div>
                    <div class="form-group">
                        <label>Producto</label>
                        <input type="text" class="input-ghost product-name" placeholder="BuscA por cadigo" readonly>
                    </div>
                    <div class="form-group">
                        <label>Disponible en origen</label>
                        <input type="text" class="input-ghost available-qty" placeholder="0" readonly>
                    </div>
                    <div class="form-group">
                        <label>cantidad solicitada</label>
                        <input type="number" min="1" class="input-ghost qty-input" name="items[${index}][requested_qty]" required>
                    </div>
                    <div class="form-group" style="grid-column:1 / -1;">
                        <label>Notas</label>
                        <textarea class="input-ghost note-input" name="items[${index}][notes]" rows="1" placeholder="Para diferencias, lotes, etc."></textarea>
                    </div>
                </div>
                <button type="button" class="btn-danger remove-item">Quitar</button>
            `;
            return wrapper;
        }

        function addItemRow() {
            const row = createRow(itemIndex++);
            itemsContainer.appendChild(row);
        }

        function handleLookup(row, sku) {
            const productInput = row.querySelector('.product-id-input');
            const nameInput = row.querySelector('.product-name');
            const qtyavailable = row.querySelector('.available-qty');
            const qtyInput = row.querySelector('.qty-input');

            productInput.value = '';
            nameInput.value = 'Buscando...';
            qtyavailable.value = '';

            const params = new URLSearchParams({ sku });
            if (fromWarehouseSelect.value) {
                params.append('warehouse_id', fromWarehouseSelect.value);
            }

            fetch(`${lookupUrl}?${params.toString()}`)
                .then((response) => {
                    if (!response.ok) throw response;
                    return response.json();
                })
                .then((data) => {
                    productInput.value = data.product_id;
                    nameInput.value = `${data.name} (${data.sku})`;
                    qtyavailable.value = (data.available_quantity ?? 0) + ' uds';
                    if (!qtyInput.value) {
                        qtyInput.value = data.available_quantity && data.available_quantity > 0
                            ? data.available_quantity
                            : 1;
                    }
                })
                .catch(async (errorResponse) => {
                    let message = 'No pudimos encontrar el producto.';
                    if (errorResponse.json) {
                        const data = await errorResponse.json();
                        if (data?.message) message = data.message;
                    }
                    nameInput.value = message;
                    qtyavailable.value = '0';
                    productInput.value = '';
                    qtyInput.value = '';
                });
        }

        addItemButton.addEventListener('click', addItemRow);

        itemsContainer.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-item')) {
                event.target.closest('.transfer-item-row')?.remove();
            }
        });

        itemsContainer.addEventListener('blur', (event) => {
            if (event.target.classList.contains('sku-input')) {
                const sku = event.target.value.trim();
                if (!sku) return;
                const row = event.target.closest('.transfer-item-row');
                handleLookup(row, sku);
            }
        }, true);

        addItemRow();
    })();

    (() => {
        document.querySelectorAll('[data-open-transfer-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById(button.dataset.openTransferModal)?.classList.add('active');
            });
        });

        document.querySelectorAll('[data-close-transfer-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                button.closest('.transfer-modal')?.classList.remove('active');
            });
        });

        document.querySelectorAll('.transfer-modal').forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) modal.classList.remove('active');
            });
        });
    })();
</script>
@endpush


