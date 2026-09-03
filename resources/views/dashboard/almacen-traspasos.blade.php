@extends('layouts.sidebar-almacen')

@section('title', 'Traspasos de almacen | Pil Andina')
@section('page-title', 'Traspasos de almacen')

@php
    $statusLabels = [
        \App\Models\Transfer::STATUS_PENDING => 'Pendiente',
        \App\Models\Transfer::STATUS_IN_TRANSIT => 'En transito',
        \App\Models\Transfer::STATUS_RECEIVED => 'Recibido',
    ];
@endphp

@section('content')
<div class="warehouse-transfers-page">
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

    <section class="fit-users-header">
        <div class="fit-users-header-left">
            <div class="fit-header-icon"><i class="ri-swap-box-line"></i></div>
            <div>
                <h1>Recepcion de Traspasos</h1>
                <p>Controla movimientos entrantes, origen de solicitud y productos recibidos por bodega.</p>
            </div>
        </div>
        <div class="warehouse-inventory-branch">
            <span>Bodega destino</span>
            <strong>{{ $targetWarehouse?->name ?? 'La Paz' }}</strong>
        </div>
    </section>

    <section class="fit-metric-grid warehouse-metric-grid">
        <a href="{{ route('dashboard.almacen.transfers') }}" class="fit-metric-card orange">
            <span><small>Total traspasos</small><strong>{{ $stats['total'] }}</strong><em>Asignados a almacen</em></span>
            <span class="fit-metric-icon"><i class="ri-route-line"></i></span>
        </a>
        <a href="{{ route('dashboard.almacen.transfers', ['status' => \App\Models\Transfer::STATUS_PENDING]) }}" class="fit-metric-card rose">
            <span><small>Pendientes</small><strong>{{ $stats['pending'] }}</strong><em>Por revisar</em></span>
            <span class="fit-metric-icon"><i class="ri-time-line"></i></span>
        </a>
        <a href="{{ route('dashboard.almacen.transfers', ['status' => \App\Models\Transfer::STATUS_IN_TRANSIT]) }}" class="fit-metric-card blue">
            <span><small>En transito</small><strong>{{ $stats['in_transit'] }}</strong><em>Camino a bodega</em></span>
            <span class="fit-metric-icon"><i class="ri-truck-line"></i></span>
        </a>
        <a href="{{ route('dashboard.almacen.transfers', ['status' => \App\Models\Transfer::STATUS_RECEIVED]) }}" class="fit-metric-card indigo">
            <span><small>Recibidos</small><strong>{{ $stats['received'] }}</strong><em>Inventario actualizado</em></span>
            <span class="fit-metric-icon"><i class="ri-checkbox-circle-line"></i></span>
        </a>
    </section>

    <section class="fit-filter-card warehouse-orders-filter-card">
        <form method="GET" action="{{ route('dashboard.almacen.transfers') }}" class="fit-filter-form warehouse-orders-filter">
            <label class="fit-select-control" for="status">
                <i class="ri-filter-3-line"></i>
                <select id="status" name="status">
                    <option value="">Todos los estados</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>
                            {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="fit-primary-button compact">
                <i class="ri-search-line"></i>
                <span>Filtrar</span>
            </button>
            @if($filters['status'] ?? null)
                <a href="{{ route('dashboard.almacen.transfers') }}" class="fit-clear-button">Limpiar filtros</a>
            @endif
        </form>
    </section>

    <section class="warehouse-panel warehouse-transfers-panel">
        <div class="warehouse-panel-head">
            <div>
                <span class="fit-section-badge orange">Movimientos</span>
                <h2>Traspasos asignados</h2>
            </div>
            <span class="warehouse-panel-count">{{ $transfers->total() }} registros</span>
        </div>

        <div class="fit-table-scroll">
            <table class="fit-users-table warehouse-orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Estado</th>
                        <th>Solicitud</th>
                        <th>Productos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                        @php
                            $agentRequest = $transfer->agentTransferRequest;
                            $statusSlug = \Illuminate\Support\Str::slug($transfer->status, '_');
                        @endphp
                        <tr>
                            <td>
                                <strong>#{{ $transfer->id }}</strong>
                                <span>{{ optional($transfer->created_at)->format('d/m/Y H:i') }}</span>
                            </td>
                            <td>
                                <strong>{{ $transfer->fromWarehouse->name ?? 'Sin origen' }}</strong>
                                <span>{{ $transfer->fromWarehouse->city ?? 'Origen' }}</span>
                            </td>
                            <td>
                                <strong>{{ $transfer->toWarehouse->name ?? 'Sin destino' }}</strong>
                                <span>{{ $transfer->toWarehouse->city ?? 'Destino' }}</span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('dashboard.almacen.transfers.status', $transfer) }}" class="warehouse-orders-status-form">
                                    @csrf
                                    <label class="fit-select-control compact" for="status-{{ $transfer->id }}">
                                        <select id="status-{{ $transfer->id }}" name="status" onchange="this.form.submit()">
                                            @foreach($statuses as $status)
                                                <option value="{{ $status }}" @selected($transfer->status === $status)>
                                                    {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                </form>
                            </td>
                            <td>
                                @if($agentRequest)
                                    <span class="warehouse-source-pill ai"><i class="ri-robot-2-line"></i> Agente IA</span>
                                @else
                                    <span class="warehouse-source-pill manual"><i class="ri-user-settings-line"></i> Manual</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $transfer->items->count() }}</strong>
                                <span>item{{ $transfer->items->count() === 1 ? '' : 's' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('dashboard.almacen.transfers.show', $transfer) }}" class="fit-action-button success" title="Ver detalle">
                                    <i class="ri-eye-line"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="warehouse-empty-cell">Sin traspasos asignados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="warehouse-pagination">
            {{ $transfers->links() }}
        </div>
    </section>
</div>
@endsection
