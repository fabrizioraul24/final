@extends('reports.layout')

@section('content')
    @php
        $pending = $transfers->where('status', \App\Models\Transfer::STATUS_PENDING)->count();
        $inTransit = $transfers->where('status', \App\Models\Transfer::STATUS_IN_TRANSIT)->count();
        $received = $transfers->where('status', \App\Models\Transfer::STATUS_RECEIVED)->count();
        $total = max($transfers->count(), 1);
        $itemsCount = $transfers->sum(fn($transfer) => $transfer->items->count());
        $requestedQty = $transfers->sum(fn($transfer) => $transfer->items->sum('requested_qty'));
        $receivedQty = $transfers->sum(fn($transfer) => $transfer->items->sum('received_qty'));
    @endphp

    <style>
        .transfers-report .summary { display: table; width: 100%; border-spacing: 0.45rem 0; table-layout: fixed; }
        .transfers-report .summary-card { display: table-cell; width: 33.333%; min-height: 58px; padding: 0.55rem 0.7rem; vertical-align: top; }
        .transfers-report .summary-card span { font-size: 0.98rem; }
        .summary-card.blue { border-left: 5px solid #4e6baf; }
        .summary-card.green { border-left: 5px solid #10b981; }
        .summary-card.cyan { border-left: 5px solid #0ea5e9; }
        .summary-card.amber { border-left: 5px solid #f59e0b; }
        .summary-card.rose { border-left: 5px solid #e11d48; }
        .bar-fill.green { background: #10b981; }
        .bar-fill.blue { background: #4e6baf; }
        .bar-fill.cyan { background: #0ea5e9; }
        .bar-fill.amber { background: #f59e0b; }
        .transfer-table { margin-top: 0.75rem; }
        .transfer-table th,
        .transfer-table td { font-size: 0.66rem; padding: 0.4rem; vertical-align: top; }
        .status-pill { display: inline-block; min-width: 72px; padding: 0.18rem 0.45rem; border-radius: 999px; font-size: 0.6rem; font-weight: 700; text-align: center; }
        .status-pill.pending { background: #fef3c7; color: #b45309; }
        .status-pill.in-transit { background: #e0f2fe; color: #0369a1; }
        .status-pill.received { background: #dcfce7; color: #047857; }
        .warehouse-line { display: block; color: #1c1c2d; font-weight: 700; }
        .warehouse-sub { display: block; margin-top: 0.12rem; color: #5f6a85; font-size: 0.58rem; }
        .items-list { margin: 0; padding: 0; list-style: none; }
        .items-list li { margin-bottom: 0.16rem; }
        .items-list strong { color: #4e6baf; }
        .report-note { margin: 0.2rem 0 0.9rem; color: #5f6a85; font-size: 0.82rem; }
    </style>

    <div class="transfers-report">
        <p class="report-note">Resumen operativo de traspasos internos y productos solicitados.</p>

        <div class="summary">
            <div class="summary-card blue">
                <strong>Total traspasos</strong>
                <span>{{ $transfers->count() }}</span>
            </div>
            <div class="summary-card amber">
                <strong>Pendientes</strong>
                <span>{{ $pending }}</span>
            </div>
            <div class="summary-card cyan">
                <strong>En transito</strong>
                <span>{{ $inTransit }}</span>
            </div>
        </div>

        <div class="summary">
            <div class="summary-card green">
                <strong>Recibidos</strong>
                <span>{{ $received }}</span>
            </div>
            <div class="summary-card blue">
                <strong>Items</strong>
                <span>{{ $itemsCount }}</span>
            </div>
            <div class="summary-card green">
                <strong>Unidades solicitadas</strong>
                <span>{{ number_format((float) $requestedQty, 0) }}</span>
            </div>
        </div>

        <div class="chart-block">
            <p class="chart-title">Estado de traspasos</p>
            <div class="bar-row">
                <span class="bar-label">Pendientes</span>
                <div class="bar-track"><div class="bar-fill amber" style="width: {{ ($pending / $total) * 100 }}%;"></div></div>
                <span class="bar-value">{{ $pending }}</span>
            </div>
            <div class="bar-row">
                <span class="bar-label">En transito</span>
                <div class="bar-track"><div class="bar-fill cyan" style="width: {{ ($inTransit / $total) * 100 }}%;"></div></div>
                <span class="bar-value">{{ $inTransit }}</span>
            </div>
            <div class="bar-row">
                <span class="bar-label">Recibidos</span>
                <div class="bar-track"><div class="bar-fill green" style="width: {{ ($received / $total) * 100 }}%;"></div></div>
                <span class="bar-value">{{ $received }}</span>
            </div>
        </div>

        <div class="chart-block">
            <p class="chart-title">Unidades solicitadas vs recibidas</p>
            @php $maxQty = max($requestedQty, $receivedQty, 1); @endphp
            <div class="bar-row">
                <span class="bar-label">Solicitadas</span>
                <div class="bar-track"><div class="bar-fill blue" style="width: {{ ($requestedQty / $maxQty) * 100 }}%;"></div></div>
                <span class="bar-value">{{ number_format((float) $requestedQty, 0) }}</span>
            </div>
            <div class="bar-row">
                <span class="bar-label">Recibidas</span>
                <div class="bar-track"><div class="bar-fill green" style="width: {{ ($receivedQty / $maxQty) * 100 }}%;"></div></div>
                <span class="bar-value">{{ number_format((float) $receivedQty, 0) }}</span>
            </div>
        </div>

        <h3 style="margin-top:1.3rem;">Detalle de traspasos</h3>
        <table class="transfer-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ruta</th>
                    <th>Estado</th>
                    <th>Fecha estimada</th>
                    <th>Productos</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $transfer)
                    @php
                        $statusClass = match ($transfer->status) {
                            \App\Models\Transfer::STATUS_PENDING => 'pending',
                            \App\Models\Transfer::STATUS_IN_TRANSIT => 'in-transit',
                            \App\Models\Transfer::STATUS_RECEIVED => 'received',
                            default => 'pending',
                        };
                    @endphp
                    <tr>
                        <td>#{{ $transfer->id }}</td>
                        <td>
                            <span class="warehouse-line">{{ $transfer->fromWarehouse->name ?? 'No definido' }}</span>
                            <span class="warehouse-sub">Destino: {{ $transfer->toWarehouse->name ?? 'N/A' }}</span>
                        </td>
                        <td><span class="status-pill {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $transfer->status)) }}</span></td>
                        <td>{{ optional($transfer->expected_date)->format('d/m/Y') ?? 'Sin fecha' }}</td>
                        <td>
                            <ul class="items-list">
                                @foreach($transfer->items->take(4) as $item)
                                    <li><strong>{{ $item->product->sku ?? 'N/A' }}</strong> - {{ $item->requested_qty }} uds</li>
                                @endforeach
                                @if($transfer->items->count() > 4)
                                    <li>+{{ $transfer->items->count() - 4 }} productos mas</li>
                                @endif
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Sin traspasos para el filtro seleccionado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
