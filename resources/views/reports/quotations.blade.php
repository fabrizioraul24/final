@extends('reports.layout')

@section('content')
    <div class="summary">
        <div class="summary-card">
            <strong>Cliente</strong>
            <span>
                @if($quotation->company)
                    {{ $quotation->company->name }}
                @elseif($quotation->customer)
                    {{ $quotation->customer->user->name ?? 'Cliente' }}
                @else
                    -
                @endif
            </span>
        </div>
        <div class="summary-card">
            <strong>Valido hasta</strong>
            <span>{{ optional($quotation->valid_until)->format('d/m/Y') }}</span>
        </div>
        <div class="summary-card">
            <strong>Total</strong>
            <span>Bs {{ number_format($quotation->total_amount, 2) }}</span>
        </div>
    </div>

    @if($quotation->notes)
        <p style="margin-top:1rem;"><strong>Notas:</strong> {{ $quotation->notes }}</p>
    @endif

    <table>
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
            @foreach($quotation->items as $item)
                <tr>
                    <td>{{ $item->product->sku ?? '-' }}</td>
                    <td>{{ $item->product->name ?? 'Producto' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>Bs {{ number_format($item->unit_price, 2) }}</td>
                    <td>Bs {{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

