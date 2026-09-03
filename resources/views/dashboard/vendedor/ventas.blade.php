@extends('layouts.sidebar-vendedor')

@section('title', 'Ventas | Vendedor')
@section('page-title', 'Ventas del vendedor')

@php
    $saleTypeLabels = [
        'empresa_institucional' => 'Empresa institucional',
        'tienda_barrio' => 'Tienda de barrio',
        'comprador_minorista' => 'Comprador minorista',
    ];
    $statusLabels = [
        'sin_entregar' => 'Sin entregar',
        'entregado' => 'Entregado',
    ];
    $activeMetric = $filters['status'] ?: 'all';
@endphp

@section('content')
<div class="fit-users-page fit-sales-page vendor-sales-page">
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    <section class="fit-users-header">
        <div class="fit-users-header-left">
            <div class="fit-header-icon"><i class="ri-shopping-cart-2-line"></i></div>
            <div>
                <h1>Ventas del Vendedor</h1>
                <p>Consulta, filtra y actualiza solo las ventas asociadas a tu usuario.</p>
            </div>
        </div>
        <div class="fit-users-header-actions">
            <a href="{{ route($createRoute) }}" class="fit-primary-button">
                <i class="ri-add-box-line"></i>
                <span>Crear Venta</span>
            </a>
            <a href="{{ route('dashboard.vendedor.sales.log') }}" class="fit-outline-button">
                <i class="ri-bar-chart-2-line"></i>
                <span>Registro</span>
            </a>
        </div>
    </section>

    <section class="fit-metric-grid">
        <a class="fit-metric-card indigo {{ $activeMetric === 'all' ? 'active' : '' }}" href="{{ route($listRoute) }}">
            <span><small>Ventas Total</small><strong>{{ $stats['count'] }}</strong><em>Ver todas</em></span>
            <span class="fit-metric-icon"><i class="ri-shopping-cart-2-line"></i></span>
        </a>
        <a class="fit-metric-card amber {{ $activeMetric === 'sin_entregar' ? 'active' : '' }}" href="{{ route($listRoute, ['status' => 'sin_entregar', 'sale_type' => $filters['sale_type'], 'search' => $filters['search']]) }}">
            <span><small>Sin Entregar</small><strong>{{ $stats['pending'] }}</strong><em>Pendientes</em></span>
            <span class="fit-metric-icon"><i class="ri-time-line"></i></span>
        </a>
        <a class="fit-metric-card green {{ $activeMetric === 'entregado' ? 'active' : '' }}" href="{{ route($listRoute, ['status' => 'entregado', 'sale_type' => $filters['sale_type'], 'search' => $filters['search']]) }}">
            <span><small>Entregadas</small><strong>{{ $stats['delivered'] }}</strong><em>Completadas</em></span>
            <span class="fit-metric-icon"><i class="ri-checkbox-circle-line"></i></span>
        </a>
        <div class="fit-metric-card blue">
            <span><small>Monto Total</small><strong>Bs {{ number_format($stats['total_amount'], 2) }}</strong><em>Historico personal</em></span>
            <span class="fit-metric-icon"><i class="ri-money-dollar-circle-line"></i></span>
        </div>
    </section>

    <section class="fit-filter-card">
        <form method="GET" action="{{ route($listRoute) }}" class="fit-filter-form fit-sale-filter-form">
            <label class="fit-search-control" for="search">
                <i class="ri-search-line"></i>
                <input type="search" id="search" name="search" value="{{ $filters['search'] }}" placeholder="Buscar ID o cliente...">
            </label>
            <label class="fit-select-control" for="sale_type_filter">
                <i class="ri-price-tag-3-line"></i>
                <select id="sale_type_filter" name="sale_type">
                    <option value="">Todos los tipos</option>
                    @foreach($saleTypeLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['sale_type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="fit-select-control" for="status_filter">
                <i class="ri-checkbox-circle-line"></i>
                <select id="status_filter" name="status">
                    <option value="">Todos los estados</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="fit-primary-button compact"><i class="ri-search-line"></i><span>Buscar</span></button>
            @if($filters['search'] || $filters['sale_type'] || $filters['status'])
                <a href="{{ route($listRoute) }}" class="fit-clear-button">Limpiar Filtros</a>
            @endif
        </form>
    </section>

    <section class="fit-section">
        <div class="fit-section-head">
            <div>
                <h2>Ventas Recientes</h2>
                <p>Registros ordenados del mas reciente al mas antiguo. No muestra ventas de admin ni de otros vendedores.</p>
            </div>
            <span class="fit-section-badge green">{{ $sales->total() }} registros</span>
        </div>

        <div class="fit-table-card">
            <div class="fit-table-scroll">
                <table class="fit-users-table fit-sales-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Pago</th>
                            <th>Monto</th>
                            <th>Almacen</th>
                            <th>Fecha</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            @php
                                $clientName = $sale->company->name ?? $sale->customer->user->name ?? 'Sin cliente';
                                $clientCity = $sale->company->city ?? $sale->customer->city ?? 'Ciudad no registrada';
                                $itemsPayload = $sale->items->map(fn ($item) => [
                                    'product' => $item->product->name ?? 'Producto',
                                    'sku' => $item->product->sku ?? '',
                                    'qty' => (int) $item->quantity,
                                    'price' => (float) $item->unit_price,
                                    'subtotal' => (float) $item->subtotal,
                                ])->values();
                            @endphp
                            <tr>
                                <td><code class="fit-code fit-sale-id">#{{ $sale->id }}</code></td>
                                <td>
                                    <div class="fit-user-cell fit-sale-client">
                                        <span class="fit-sale-client-icon"><i class="{{ $sale->company ? 'ri-building-4-line' : 'ri-user-smile-line' }}"></i></span>
                                        <div><strong>{{ $clientName }}</strong><small>{{ $clientCity }}</small></div>
                                    </div>
                                </td>
                                <td><span class="fit-role-badge default"><i class="ri-price-tag-3-line"></i> {{ $saleTypeLabels[$sale->sale_type] ?? $sale->sale_type }}</span></td>
                                <td><span class="fit-transfer-status {{ $sale->status === 'entregado' ? 'active' : 'pending' }}"><span></span> {{ $statusLabels[$sale->status] ?? ucfirst($sale->status) }}</span></td>
                                <td><span class="fit-sale-payment">{{ $paymentLabels[$sale->payment_method] ?? 'Sin metodo' }}</span></td>
                                <td><strong class="fit-sale-amount">Bs {{ number_format((float) $sale->total_amount, 2) }}</strong></td>
                                <td><span class="fit-muted-text">{{ $sale->warehouse->name ?? '-' }}</span></td>
                                <td><span class="fit-muted-text">{{ optional($sale->created_at)->format('d/m/Y H:i') }}</span></td>
                                <td class="text-right">
                                    <div class="fit-row-actions">
                                        <button type="button" class="fit-action-button success btn-vendor-sale-detail" title="Ver detalles"
                                            data-sale-id="{{ $sale->id }}"
                                            data-customer="{{ $clientName }}"
                                            data-type="{{ $saleTypeLabels[$sale->sale_type] ?? $sale->sale_type }}"
                                            data-status="{{ $statusLabels[$sale->status] ?? $sale->status }}"
                                            data-payment="{{ $paymentLabels[$sale->payment_method] ?? 'Sin metodo' }}"
                                            data-warehouse="{{ $sale->warehouse->name ?? 'Sin almacen' }}"
                                            data-date="{{ optional($sale->created_at)->format('d/m/Y H:i') }}"
                                            data-total="Bs {{ number_format((float) $sale->total_amount, 2) }}"
                                            data-items='@json($itemsPayload)'>
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        <button type="button" class="fit-action-button warning btn-sale-update" title="Actualizar estado" data-update-url="{{ route($updateRoute, $sale) }}" data-status="{{ $sale->status }}">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" style="text-align:center;padding:1rem;">No hay ventas registradas para este vendedor.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top:1rem;">{{ $sales->appends($filters)->links() }}</div>
    </section>

    <div class="modal" id="vendorSaleDetailModal">
        <div class="modal-content fit-modal-content fit-sale-report-modal" style="max-width:920px;">
            <div class="modal-header">
                <h3>Reporte de venta</h3>
                <div class="modal-header-actions">
                    <button type="button" class="fit-outline-button compact print-hide" data-print-vendor-sale><i class="ri-printer-line"></i><span>Imprimir reporte</span></button>
                    <button class="close-button" type="button" data-close-vendor-sale-detail>&times;</button>
                </div>
            </div>
            <div id="vendorSaleReportHead"></div>
            <div class="fit-transfer-summary fit-sale-summary fit-sale-report-summary" id="vendorSaleSummary"></div>
            <div class="fit-sale-detail-items" id="vendorSaleItems"></div>
            <div class="fit-modal-footer">
                <button type="button" class="fit-primary-button print-hide" data-print-vendor-sale><i class="ri-printer-line"></i><span>Imprimir reporte</span></button>
                <button type="button" class="fit-outline-button" data-close-vendor-sale-detail>Cerrar</button>
            </div>
        </div>
    </div>

    @include('dashboard.partials.sale-status-modal', ['statusLabels' => $statusLabels, 'paymentLabels' => $paymentLabels])
</div>
@endsection

@push('scripts')
<script>
(() => {
    const detailModal = document.getElementById('vendorSaleDetailModal');
    const detailHead = document.getElementById('vendorSaleReportHead');
    const detailSummary = document.getElementById('vendorSaleSummary');
    const detailItems = document.getElementById('vendorSaleItems');

    function closeDetail() {
        detailModal?.classList.remove('active');
    }

    document.querySelectorAll('.btn-vendor-sale-detail').forEach((button) => {
        button.addEventListener('click', () => {
            let items = [];
            try {
                items = JSON.parse(button.dataset.items || '[]');
            } catch (error) {
                items = [];
            }

            detailHead.innerHTML = `
                <div class="fit-sale-report-heading">
                    <div><span>Reporte oficial</span><h4>Venta #${button.dataset.saleId}</h4><p>${button.dataset.date || 'Modulo vendedor'}</p></div>
                    <strong>${button.dataset.total}</strong>
                </div>
            `;
            detailSummary.innerHTML = `
                <div><span>Cliente</span><strong>${button.dataset.customer}</strong></div>
                <div><span>Tipo</span><strong>${button.dataset.type}</strong></div>
                <div><span>Estado</span><strong>${button.dataset.status}</strong></div>
                <div><span>Pago</span><strong>${button.dataset.payment}</strong></div>
                <div><span>Almacen</span><strong>${button.dataset.warehouse}</strong></div>
            `;
            detailItems.innerHTML = items.length
                ? items.map((item) => `<div><strong>${item.product}</strong><span>${item.qty} uds x Bs ${Number(item.price || 0).toFixed(2)} = Bs ${Number(item.subtotal || 0).toFixed(2)}</span></div>`).join('')
                : '<p class="fit-muted-text">Sin productos registrados.</p>';
            detailModal?.classList.add('active');
        });
    });

    document.querySelectorAll('[data-close-vendor-sale-detail]').forEach((button) => button.addEventListener('click', closeDetail));
    document.querySelectorAll('[data-print-vendor-sale]').forEach((button) => button.addEventListener('click', () => window.print()));
    detailModal?.addEventListener('click', (event) => {
        if (event.target === detailModal) closeDetail();
    });
})();
</script>
@endpush
