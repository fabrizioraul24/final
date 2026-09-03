@extends('layouts.sidebar-almacen')

@section('title', 'Pedidos de almacen | Pil Andina')
@section('page-title', 'Pedidos de almacen')

@php
    $statusLabels = [
        'sin_entregar' => 'Sin entregar',
        'entregado' => 'Entregado',
    ];
@endphp

@section('content')
<div class="warehouse-orders-page">
    @if(session('status'))
        <div class="warehouse-sync-card">
            <div class="warehouse-sync-status">
                <span class="warehouse-live-dot"></span>
                <strong>{{ session('status') }}</strong>
            </div>
        </div>
    @endif

    <section class="fit-users-header">
        <div class="fit-users-header-left">
            <div class="fit-header-icon"><i class="ri-truck-line"></i></div>
            <div>
                <h1>Pedidos para Despacho</h1>
                <p>Prepara, revisa y confirma los pedidos asignados a almacen.</p>
            </div>
        </div>
        <a href="{{ route('dashboard.almacen') }}" class="fit-outline-button compact">
            <i class="ri-dashboard-line"></i>
            <span>Dashboard</span>
        </a>
    </section>

    <section class="fit-metric-grid warehouse-metric-grid">
        <a href="{{ route('dashboard.almacen.receptions') }}" class="fit-metric-card orange">
            <span><small>Total pedidos</small><strong>{{ $stats['total'] }}</strong><em>Registro operativo</em></span>
            <span class="fit-metric-icon"><i class="ri-file-list-3-line"></i></span>
        </a>
        <a href="{{ route('dashboard.almacen.receptions', ['status' => 'sin_entregar']) }}" class="fit-metric-card blue">
            <span><small>Pendientes</small><strong>{{ $stats['pending'] }}</strong><em>Por preparar</em></span>
            <span class="fit-metric-icon"><i class="ri-time-line"></i></span>
        </a>
        <a href="{{ route('dashboard.almacen.receptions', ['status' => 'entregado']) }}" class="fit-metric-card indigo">
            <span><small>Entregados</small><strong>{{ $stats['delivered'] }}</strong><em>Despacho cerrado</em></span>
            <span class="fit-metric-icon"><i class="ri-checkbox-circle-line"></i></span>
        </a>
        <div class="fit-metric-card rose">
            <span><small>Ingresados hoy</small><strong>{{ $stats['today'] }}</strong><em>Actividad diaria</em></span>
            <span class="fit-metric-icon"><i class="ri-calendar-check-line"></i></span>
        </div>
    </section>

    <section class="fit-filter-card warehouse-orders-filter-card">
        <form method="GET" action="{{ route('dashboard.almacen.receptions') }}" class="fit-filter-form warehouse-orders-filter">
            <label class="fit-select-control" for="status">
                <i class="ri-filter-3-line"></i>
                <select id="status" name="status">
                    <option value="">Todos los estados</option>
                    @foreach($statuses as $statusOption)
                        <option value="{{ $statusOption }}" @selected(($filters['status'] ?? null) === $statusOption)>
                            {{ $statusLabels[$statusOption] ?? ucfirst(str_replace('_', ' ', $statusOption)) }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button class="fit-primary-button compact" type="submit">
                <i class="ri-search-line"></i>
                <span>Filtrar</span>
            </button>
            @if($filters['status'] ?? null)
                <a href="{{ route('dashboard.almacen.receptions') }}" class="fit-clear-button">Limpiar filtros</a>
            @endif
        </form>
    </section>

    <section class="warehouse-panel warehouse-orders-panel">
        <div class="warehouse-panel-head">
            <div>
                <span class="fit-section-badge orange">Despacho</span>
                <h2>Pedidos registrados</h2>
            </div>
            <span class="warehouse-panel-count">{{ $sales->total() }} registros</span>
        </div>

        <div class="fit-table-scroll">
            <table class="fit-users-table warehouse-orders-table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Destino</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        @php
                            $clientName = $sale->company->name ?? $sale->customer?->user?->name ?? 'Cliente';
                            $clientType = $sale->company ? 'Empresa' : 'Minorista';
                        @endphp
                        <tr>
                            <td>
                                <strong>#{{ $sale->id }}</strong>
                                <span>{{ optional($sale->created_at)->format('d/m/Y H:i') }}</span>
                            </td>
                            <td>
                                <strong>{{ $clientName }}</strong>
                                <span>{{ $clientType }}{{ $sale->company?->nit ? ' - NIT: ' . $sale->company->nit : '' }}</span>
                            </td>
                            <td>
                                <strong>{{ $sale->delivery_city ?? $sale->warehouse?->city ?? 'La Paz' }}</strong>
                                <span>{{ $sale->delivery_address ?? 'Retiro en planta' }}</span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('dashboard.almacen.receptions.status', $sale) }}" class="warehouse-orders-status-form">
                                    @csrf
                                    <label class="fit-select-control compact" for="status-{{ $sale->id }}">
                                        <select id="status-{{ $sale->id }}" name="status" onchange="this.form.submit()">
                                            @foreach($statuses as $statusOption)
                                                <option value="{{ $statusOption }}" @selected($sale->status === $statusOption)>
                                                    {{ $statusLabels[$statusOption] ?? ucfirst(str_replace('_', ' ', $statusOption)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <strong>Bs {{ number_format((float) $sale->total_amount, 2) }}</strong>
                                <span>{{ $sale->items->count() }} item{{ $sale->items->count() === 1 ? '' : 's' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('dashboard.almacen.receptions.show', $sale) }}" class="fit-action-button success" title="Ver detalle">
                                    <i class="ri-eye-line"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="warehouse-empty-cell">Sin pedidos para preparar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="warehouse-pagination">
            {{ $sales->links() }}
        </div>
    </section>
</div>
@endsection
