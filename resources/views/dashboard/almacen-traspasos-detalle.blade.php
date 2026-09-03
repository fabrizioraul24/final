@extends('layouts.sidebar-almacen')

@section('title', 'Detalle de traspaso | Pil Andina')
@section('page-title', 'Detalle de traspaso')

@php
    $statusLabels = [
        \App\Models\Transfer::STATUS_PENDING => 'Pendiente',
        \App\Models\Transfer::STATUS_IN_TRANSIT => 'En transito',
        \App\Models\Transfer::STATUS_RECEIVED => 'Recibido',
    ];
    $agentRequest = $transfer->agentTransferRequest;
@endphp

@section('content')
<div class="warehouse-transfers-page warehouse-transfer-detail-page">
    @if(session('status'))
        <div class="warehouse-sync-card">
            <div class="warehouse-sync-status">
                <span class="warehouse-live-dot"></span>
                <strong>{{ session('status') }}</strong>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="warehouse-inventory-alert">
            <i class="ri-error-warning-line"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <section class="warehouse-lot-detail-hero">
        <div class="warehouse-lot-detail-hero-main">
            <a href="{{ route('dashboard.almacen.transfers') }}" class="fit-outline-button compact warehouse-lot-detail-back">
                <i class="ri-arrow-left-line"></i>
                <span>Volver</span>
            </a>
            <div class="warehouse-order-detail-title">
                <span class="fit-section-badge orange">Traspaso #{{ $transfer->id }}</span>
                <h1>{{ $transfer->fromWarehouse->name ?? 'Sin origen' }} a {{ $transfer->toWarehouse->name ?? 'Sin destino' }}</h1>
                <p>Fecha estimada: {{ optional($transfer->expected_date)->format('d/m/Y') ?? 'Sin fecha' }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('dashboard.almacen.transfers.status', $transfer) }}" class="warehouse-orders-status-form warehouse-transfer-status-top">
            @csrf
            <label class="fit-select-control compact" for="transfer_status">
                <select id="transfer_status" name="status" onchange="this.form.submit()">
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected($transfer->status === $status)>
                            {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </label>
        </form>
    </section>

    <section class="warehouse-inventory-stats warehouse-lot-detail-stats">
        <div><span>Estado</span><strong>{{ $statusLabels[$transfer->status] ?? ucfirst(str_replace('_', ' ', $transfer->status)) }}</strong></div>
        <div><span>Productos</span><strong>{{ $transfer->items->count() }}</strong></div>
        <div><span>Recibido por</span><strong>{{ $transfer->received_by ? 'Registrado' : 'Pendiente' }}</strong></div>
        <div><span>Fecha recibido</span><strong>{{ optional($transfer->received_date)->format('d/m/Y') ?? 'Pendiente' }}</strong></div>
    </section>

    <div class="warehouse-transfer-detail-layout">
        <section class="warehouse-panel warehouse-transfer-items-panel">
            <div class="warehouse-panel-head">
                <div>
                    <span class="fit-section-badge orange">Recepcion</span>
                    <h2>Productos transferidos</h2>
                </div>
                @if($agentRequest)
                    <span class="warehouse-source-pill ai"><i class="ri-robot-2-line"></i> Sugerido por IA</span>
                @else
                    <span class="warehouse-source-pill manual"><i class="ri-user-settings-line"></i> Registro manual</span>
                @endif
            </div>

            <div class="warehouse-transfer-item-list">
                @forelse($transfer->items as $item)
                    <article class="warehouse-transfer-item-card">
                        <div class="warehouse-transfer-item-head">
                            <div>
                                <h3>{{ $item->product?->name ?? 'Producto '.$item->product_id }}</h3>
                                <p>SKU: {{ $item->product?->sku ?? 'N/D' }}</p>
                            </div>
                            <span>{{ number_format((int) $item->requested_qty) }} uds solicitadas</span>
                        </div>

                        <form method="POST" action="{{ route('dashboard.almacen.transfers.items.update', $item) }}" class="warehouse-transfer-item-form">
                            @csrf
                            <label>
                                <span>Recibido</span>
                                <input type="number" name="received_qty" min="0" value="{{ old('received_qty', $item->received_qty ?? $item->requested_qty) }}">
                            </label>
                            <label>
                                <span>Danado</span>
                                <input type="number" name="damaged_qty" min="0" value="{{ old('damaged_qty', $item->damaged_qty ?? 0) }}">
                            </label>
                            <label>
                                <span>Codigo lote</span>
                                <input type="text" name="lot_code" value="{{ old('lot_code', $item->lot_code) }}" placeholder="Automatico">
                            </label>
                            <label>
                                <span>Vencimiento</span>
                                <input type="date" name="receiving_expires_at" value="{{ old('receiving_expires_at', optional($item->receiving_expires_at)->format('Y-m-d')) }}">
                            </label>
                            <label class="wide">
                                <span>Nota</span>
                                <input type="text" name="receiving_note" value="{{ old('receiving_note', $item->receiving_note) }}" placeholder="Observaciones de recepcion">
                            </label>
                            <button class="fit-primary-button compact" type="submit">
                                <i class="ri-save-3-line"></i>
                                <span>Guardar item</span>
                            </button>
                        </form>

                        <div class="warehouse-transfer-generated-lot">
                            @if($item->generatedLot)
                                <span>Lote creado</span>
                                <strong>{{ $item->generatedLot->lote_code }}</strong>
                                <small>{{ number_format((int) $item->generatedLot->quantity) }} uds disponibles</small>
                            @elseif($item->lot_code)
                                <span>Lote registrado</span>
                                <strong>{{ $item->lot_code }}</strong>
                                <small>Pendiente de asociacion final</small>
                            @else
                                <span>Sin lote generado</span>
                                <strong>Pendiente</strong>
                                <small>Se genera al registrar recepcion valida</small>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="warehouse-empty-cell">Sin productos registrados.</div>
                @endforelse
            </div>
        </section>

        <aside class="warehouse-lot-detail-side">
            <div class="warehouse-lot-panel">
                <h4>Resumen</h4>
                <div class="warehouse-lot-summary-list">
                    <div><span>Origen</span><strong>{{ $transfer->fromWarehouse->name ?? 'Sin origen' }}</strong></div>
                    <div><span>Destino</span><strong>{{ $transfer->toWarehouse->name ?? 'Sin destino' }}</strong></div>
                    <div><span>Solicitado por</span><strong>{{ $agentRequest ? 'Agente IA' : ($transfer->requestedByUser->name ?? 'Usuario PIL') }}</strong></div>
                    <div><span>Aprobado por</span><strong>{{ $transfer->approvedByUser?->name ?? 'Pendiente' }}</strong></div>
                </div>
            </div>

            <div class="warehouse-lot-panel">
                <h4>Origen de solicitud</h4>
                @if($agentRequest)
                    <div class="warehouse-transfer-origin">
                        <span class="warehouse-source-pill ai"><i class="ri-robot-2-line"></i> Sugerencia de agente inteligente</span>
                        <p>Solicitud creada: {{ optional($agentRequest->created_at)->format('d/m/Y H:i') }}</p>
                        @if($agentRequest->priority)<p>Prioridad: {{ $agentRequest->priority }}</p>@endif
                        @if($agentRequest->reason)<p>Motivo: {{ $agentRequest->reason }}</p>@endif
                    </div>
                @else
                    <div class="warehouse-transfer-origin">
                        <span class="warehouse-source-pill manual"><i class="ri-user-settings-line"></i> Registro manual</span>
                        <p>Creado por: {{ $transfer->requestedByUser->name ?? 'Usuario PIL' }}</p>
                    </div>
                @endif
            </div>

            <div class="warehouse-lot-panel">
                <h4>Notas generales</h4>
                <p>{{ $transfer->notes ?: 'Sin notas registradas.' }}</p>
            </div>
        </aside>
    </div>
</div>
@endsection
