@extends('reports.layout')

@section('content')
    <div class="summary">
        <div class="summary-card">
            <strong>Total traspasos</strong>
            <span>{{ $transfers->count() }}</span>
        </div>
        <div class="summary-card">
            <strong>En transito</strong>
            <span>{{ $transfers->where('status', \App\Models\Transfer::STATUS_IN_TRANSIT)->count() }}</span>
        </div>
        <div class="summary-card">
            <strong>Recibidos</strong>
            <span>{{ $transfers->where('status', \App\Models\Transfer::STATUS_RECEIVED)->count() }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Estado</th>
                <th>Fecha estimada</th>
                <th>Productos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfers as $transfer)
                <tr>
                    <td>#{{ $transfer->id }}</td>
                    <td>{{ $transfer->fromWarehouse->name ?? 'No definido' }}</td>
                    <td>{{ $transfer->toWarehouse->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $transfer->status)) }}</td>
                    <td>{{ optional($transfer->expected_date)->format('d/m/Y') ?? 'Sin fecha' }}</td>
                    <td>
                        @foreach($transfer->items as $item)
                            <div>
                                <strong>SKU:</strong> {{ $item->product->sku ?? 'N/A' }} |
                                <strong>Cantidad:</strong> {{ $item->requested_qty }} uds
                            </div>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
