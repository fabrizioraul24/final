@extends('layouts.sidebar-almacen')

@section('title', 'Detalle de pedido | Pil Andina')
@section('page-title', 'Detalle de pedido')

@php
    $statusLabels = [
        'sin_entregar' => 'Sin entregar',
        'entregado' => 'Entregado',
    ];
    $clientName = $sale->company->name ?? $sale->customer?->user?->name ?? 'Cliente';
    $clientType = $sale->company ? 'Empresa' : 'Minorista';
    $mapAddress = $sale->delivery_address
        ?? $sale->company?->address
        ?? $sale->customer?->delivery_address
        ?? null;
    $mapQuery = trim(($mapAddress ? $mapAddress . ', ' : '') . ($sale->delivery_city ?? $sale->warehouse?->city ?? 'La Paz') . ', Bolivia');
    $mapUrl = $sale->company?->google_maps_url ?: 'https://www.google.com/maps/search/?api=1&query=' . urlencode($mapQuery);
    $mapEmbedUrl = 'https://www.google.com/maps?q=' . urlencode($mapQuery) . '&output=embed';
@endphp

@section('content')
<div class="warehouse-orders-page warehouse-order-detail-page">
    <section class="warehouse-lot-detail-hero warehouse-order-print-hide">
        <div class="warehouse-lot-detail-hero-main">
            <a href="{{ route('dashboard.almacen.receptions') }}" class="fit-outline-button compact warehouse-lot-detail-back">
                <i class="ri-arrow-left-line"></i>
                <span>Volver</span>
            </a>
            <div class="warehouse-order-detail-title">
                <span class="fit-section-badge orange">Pedido #{{ $sale->id }}</span>
                <h1>{{ $clientName }}</h1>
                <p>{{ $sale->delivery_address ?? 'Retiro en planta' }} - {{ $sale->delivery_city ?? $sale->warehouse?->city ?? 'La Paz' }}</p>
            </div>
        </div>
        <div class="warehouse-order-detail-actions">
            <button type="button" class="fit-outline-button compact" onclick="window.print()">
                <i class="ri-printer-line"></i>
                <span>Imprimir</span>
            </button>
        </div>
    </section>

    <section class="warehouse-inventory-stats warehouse-lot-detail-stats">
        <div>
            <span>Estado</span>
            <strong>{{ $statusLabels[$sale->status] ?? ucfirst(str_replace('_', ' ', $sale->status)) }}</strong>
        </div>
        <div>
            <span>Productos</span>
            <strong>{{ $sale->items->count() }}</strong>
        </div>
        <div>
            <span>Total pedido</span>
            <strong>Bs {{ number_format((float) $sale->total_amount, 2) }}</strong>
        </div>
        <div>
            <span>Fecha</span>
            <strong>{{ optional($sale->created_at)->format('d/m/Y') }}</strong>
        </div>
    </section>

    <div class="warehouse-order-detail-layout">
        <section class="warehouse-panel warehouse-order-products-panel">
            <div class="warehouse-panel-head">
                <div>
                    <span class="fit-section-badge orange">Preparacion</span>
                    <h2>Productos del pedido</h2>
                </div>
                <form method="POST" action="{{ route('dashboard.almacen.receptions.status', $sale) }}" class="warehouse-orders-status-form warehouse-order-print-hide">
                    @csrf
                    <label class="fit-select-control compact" for="detail_status">
                        <select id="detail_status" name="status" onchange="this.form.submit()">
                            @foreach($statuses as $statusOption)
                                <option value="{{ $statusOption }}" @selected($sale->status === $statusOption)>
                                    {{ $statusLabels[$statusOption] ?? ucfirst(str_replace('_', ' ', $statusOption)) }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </form>
            </div>

            <div class="fit-table-scroll">
                <table class="fit-users-table warehouse-orders-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Lote sugerido</th>
                            <th>Disponible</th>
                            <th>Vencimiento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                            @php $lot = $suggestions[$item->id] ?? null; @endphp
                            <tr>
                                <td>
                                    <strong>{{ $item->product->name ?? 'Producto' }}</strong>
                                    <span>SKU: {{ $item->product->sku ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <strong>{{ number_format((int) $item->quantity) }}</strong>
                                    <span>Solicitado</span>
                                </td>
                                <td>
                                    @if($lot)
                                        <code class="fit-code">{{ $lot->lote_code ?? 'Sin codigo' }}</code>
                                        <span>{{ $lot->warehouse?->name ?? $sale->warehouse?->name ?? 'Bodega' }}</span>
                                    @else
                                        <span class="warehouse-status-pill danger">Sin lote disponible</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ number_format((int) ($lot?->quantity ?? 0)) }}</strong>
                                    <span>Unidades</span>
                                </td>
                                <td>
                                    <span>{{ optional($lot?->expires_at)->format('d/m/Y') ?? 'N/A' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="warehouse-lot-detail-side">
            <div class="warehouse-lot-panel">
                <h4>Datos del cliente</h4>
                <div class="warehouse-lot-summary-list">
                    <div><span>Nombre</span><strong>{{ $clientName }}</strong></div>
                    <div><span>Tipo</span><strong>{{ $clientType }}</strong></div>
                    <div><span>NIT</span><strong>{{ $sale->company?->nit ?? 'N/A' }}</strong></div>
                    <div><span>Vendedor</span><strong>{{ $sale->seller?->name ?? 'Sistema' }}</strong></div>
                </div>
            </div>

            <div class="warehouse-lot-panel">
                <h4>Entrega</h4>
                <div class="warehouse-lot-summary-list">
                    <div><span>Ciudad</span><strong>{{ $sale->delivery_city ?? $sale->warehouse?->city ?? 'La Paz' }}</strong></div>
                    <div><span>Bodega</span><strong>{{ $sale->warehouse?->name ?? 'Sin bodega' }}</strong></div>
                    <div><span>Direccion</span><strong>{{ $sale->delivery_address ?? 'Retiro en planta' }}</strong></div>
                    <div><span>Pago</span><strong>{{ ucfirst(str_replace('_', ' ', $sale->payment_method ?? 'No registrado')) }}</strong></div>
                </div>
            </div>

            <div class="warehouse-lot-panel warehouse-order-map-panel">
                <div class="warehouse-order-map-head">
                    <h4>Ubicacion de entrega</h4>
                    <a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="fit-outline-button compact">
                        <i class="ri-map-pin-line"></i>
                        <span>Abrir mapa</span>
                    </a>
                </div>
                <div class="warehouse-order-map-frame">
                    <iframe src="{{ $mapEmbedUrl }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Mapa de entrega del pedido #{{ $sale->id }}"></iframe>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
