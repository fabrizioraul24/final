@extends('reports.layout')

@section('content')
    @php
        $saleTypeLabels = [
            'empresa_institucional' => 'Empresa institucional',
            'tienda_barrio' => 'Tienda de barrio',
            'comprador_minorista' => 'Comprador minorista',
        ];
        $clientName = $quotation->company->name ?? $quotation->customer?->user?->name ?? 'Sin cliente';
        $clientCity = $quotation->company->city ?? $quotation->customer?->city ?? 'Ciudad no registrada';
        $clientNit = $quotation->company->nit ?? 'N/D';
        $itemsCount = $quotation->items->count();
        $unitsCount = $quotation->items->sum('quantity');
        $maxSubtotal = max($quotation->items->pluck('subtotal')->all() ?: [1]);
    @endphp

    <style>
        .quotation-report .summary { display: table; width: 100%; border-spacing: 0.45rem 0; table-layout: fixed; }
        .quotation-report .summary-card { display: table-cell; width: 33.333%; min-height: 58px; padding: 0.55rem 0.7rem; vertical-align: top; }
        .quotation-report .summary-card span { font-size: 0.98rem; }
        .summary-card.blue { border-left: 5px solid #4e6baf; }
        .summary-card.green { border-left: 5px solid #10b981; }
        .summary-card.cyan { border-left: 5px solid #0ea5e9; }
        .summary-card.amber { border-left: 5px solid #f59e0b; }
        .summary-card.rose { border-left: 5px solid #e11d48; }
        .report-note { margin: 0.2rem 0 0.9rem; color: #5f6a85; font-size: 0.84rem; }
        .quote-box { margin-top: 0.85rem; padding: 0.75rem 0.9rem; border: 1px solid #e1e4f2; border-radius: 0.75rem; background: #f8fbff; }
        .quote-box strong { display: block; margin-bottom: 0.24rem; color: #4e6baf; font-size: 0.72rem; text-transform: uppercase; }
        .quote-box p { margin: 0; color: #1c1c2d; font-size: 0.82rem; line-height: 1.5; }
        .bar-fill.green { background: #10b981; }
        .items-table { margin-top: 0.75rem; }
        .items-table th,
        .items-table td { font-size: 0.68rem; padding: 0.42rem; vertical-align: top; }
        .items-table th:nth-child(1) { width: 14%; }
        .items-table th:nth-child(2) { width: 36%; }
        .items-table th:nth-child(3) { width: 14%; }
        .items-table th:nth-child(4) { width: 18%; }
        .items-table th:nth-child(5) { width: 18%; }
        .product-name { color: #1c1c2d; font-weight: 700; }
        .product-sku { color: #4e6baf; font-weight: 700; }
        .total-row td { border-top: 2px solid #e1e4f2; border-bottom: 0; font-size: 0.78rem; font-weight: 700; }
    </style>

    <div class="quotation-report">
        <p class="report-note">
            Proforma comercial para {{ $clientName }}. Tipo: {{ $saleTypeLabels[$quotation->sale_type] ?? $quotation->sale_type }}.
        </p>

        <div class="summary">
            <div class="summary-card blue">
                <strong>Cliente</strong>
                <span>{{ $clientName }}</span>
            </div>
            <div class="summary-card cyan">
                <strong>Ciudad</strong>
                <span>{{ $clientCity }}</span>
            </div>
            <div class="summary-card amber">
                <strong>NIT</strong>
                <span>{{ $clientNit }}</span>
            </div>
        </div>

        <div class="summary">
            <div class="summary-card green">
                <strong>Total</strong>
                <span>Bs {{ number_format((float) $quotation->total_amount, 2) }}</span>
            </div>
            <div class="summary-card blue">
                <strong>Productos</strong>
                <span>{{ $itemsCount }}</span>
            </div>
            <div class="summary-card rose">
                <strong>Valido hasta</strong>
                <span>{{ optional($quotation->valid_until)->format('d/m/Y') ?? 'N/D' }}</span>
            </div>
        </div>

        <div class="chart-block">
            <p class="chart-title">Peso por producto cotizado</p>
            @forelse($quotation->items as $item)
                <div class="bar-row">
                    <span class="bar-label">{{ $item->product->sku ?? 'N/D' }}</span>
                    <div class="bar-track"><div class="bar-fill green" style="width: {{ (((float) $item->subtotal) / max($maxSubtotal, 1)) * 100 }}%;"></div></div>
                    <span class="bar-value">Bs {{ number_format((float) $item->subtotal, 2) }}</span>
                </div>
            @empty
                <p class="report-note">Sin productos registrados.</p>
            @endforelse
        </div>

        @if($quotation->notes)
            <div class="quote-box">
                <strong>Notas comerciales</strong>
                <p>{{ $quotation->notes }}</p>
            </div>
        @endif

        <h3 style="margin-top:1.3rem;">Detalle de productos</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotation->items as $item)
                    <tr>
                        <td><span class="product-sku">{{ $item->product->sku ?? 'N/D' }}</span></td>
                        <td><span class="product-name">{{ $item->product->name ?? 'Producto' }}</span></td>
                        <td>{{ number_format((float) $item->quantity, 0) }} uds</td>
                        <td>Bs {{ number_format((float) $item->unit_price, 2) }}</td>
                        <td><strong>Bs {{ number_format((float) $item->subtotal, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:1rem;">Sin productos registrados.</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="2">Total cotizado</td>
                    <td>{{ number_format((float) $unitsCount, 0) }} uds</td>
                    <td></td>
                    <td>Bs {{ number_format((float) $quotation->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
