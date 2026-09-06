@extends('layouts.sidebar-vendedor')

@section('title', 'Cotizacion #' . $quotation->id . ' | Vendedor')
@section('page-title', 'Detalle de cotizacion')

@php
    $clientName = $quotation->company->name ?? $quotation->customer?->user?->name ?? 'Sin cliente';
    $clientCity = $quotation->company->city ?? $quotation->customer?->city ?? 'Ciudad no registrada';
    $clientNit = $quotation->company->nit ?? 'N/D';
    $totalUnits = $quotation->items->sum('quantity');
    $itemsCount = $quotation->items->count();
@endphp

@section('content')
<div class="vendor-quotations-page vendor-quotation-show-page">
    <section class="fit-users-header">
        <div class="fit-users-header-left">
            <div class="fit-header-icon"><i class="ri-file-text-line"></i></div>
            <div>
                <h1>Cotizacion #{{ $quotation->id }}</h1>
                <p>Detalle comercial de la proforma registrada por tu usuario.</p>
            </div>
        </div>
        <div class="fit-users-header-actions">
            <a href="{{ route($listRoute) }}" class="fit-outline-button compact">
                <i class="ri-arrow-left-line"></i>
                <span>Volver</span>
            </a>
            <a href="{{ route($pdfRoute, $quotation) }}" target="_blank" rel="noopener" class="fit-primary-button compact">
                <i class="ri-file-download-line"></i>
                <span>PDF</span>
            </a>
        </div>
    </section>

    <div class="vendor-quotation-show-layout">
        <main class="vendor-quotation-show-main">
            <section class="warehouse-panel">
                <div class="vendor-quotation-show-heading">
                    <div>
                        <span>Proforma comercial</span>
                        <h2>{{ $clientName }}</h2>
                        <p>{{ $saleTypeLabels[$quotation->sale_type] ?? $quotation->sale_type }} - {{ $clientCity }}</p>
                    </div>
                    <strong>Bs {{ number_format((float) $quotation->total_amount, 2) }}</strong>
                </div>

                <div class="vendor-quotation-info-grid">
                    <div>
                        <small>Cliente</small>
                        <strong>{{ $clientName }}</strong>
                    </div>
                    <div>
                        <small>Ciudad</small>
                        <strong>{{ $clientCity }}</strong>
                    </div>
                    <div>
                        <small>NIT</small>
                        <strong>{{ $clientNit }}</strong>
                    </div>
                    <div>
                        <small>Valido hasta</small>
                        <strong>{{ optional($quotation->valid_until)->format('d/m/Y') ?? 'N/D' }}</strong>
                    </div>
                    <div>
                        <small>Vendedor</small>
                        <strong>{{ $quotation->seller->name ?? 'N/D' }}</strong>
                    </div>
                    <div>
                        <small>Fecha</small>
                        <strong>{{ optional($quotation->created_at)->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>
            </section>

            <section class="warehouse-panel">
                <div class="fit-section-head">
                    <div>
                        <h2>Productos cotizados</h2>
                        <p>Precios, cantidades y subtotal por item.</p>
                    </div>
                    <span class="fit-section-badge green">{{ $itemsCount }} items</span>
                </div>
                <div class="fit-table-scroll">
                    <table class="fit-users-table fit-quotations-table">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio unitario</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quotation->items as $item)
                                <tr>
                                    <td><code class="fit-code">{{ $item->product->sku ?? 'N/D' }}</code></td>
                                    <td><strong>{{ $item->product->name ?? 'Producto' }}</strong></td>
                                    <td>{{ number_format((int) $item->quantity) }} uds</td>
                                    <td>Bs {{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="text-right"><strong>Bs {{ number_format((float) $item->subtotal, 2) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center;padding:1rem;">No hay productos en esta cotizacion.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if($quotation->notes)
                <section class="warehouse-panel">
                    <div class="fit-section-head">
                        <div>
                            <h2>Notas comerciales</h2>
                            <p>Observaciones registradas en la cotizacion.</p>
                        </div>
                    </div>
                    <p class="vendor-quotation-notes">{{ $quotation->notes }}</p>
                </section>
            @endif
        </main>

        <aside class="vendor-quotation-show-side">
            <section class="warehouse-panel">
                <h2>Resumen</h2>
                <div class="vendor-quotation-side-row">
                    <span>Total</span>
                    <strong>Bs {{ number_format((float) $quotation->total_amount, 2) }}</strong>
                </div>
                <div class="vendor-quotation-side-row">
                    <span>Productos</span>
                    <strong>{{ $itemsCount }}</strong>
                </div>
                <div class="vendor-quotation-side-row">
                    <span>Unidades</span>
                    <strong>{{ number_format((int) $totalUnits) }} uds</strong>
                </div>
                <div class="vendor-quotation-side-row">
                    <span>Validez</span>
                    <strong>{{ optional($quotation->valid_until)->format('d/m/Y') ?? 'N/D' }}</strong>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
