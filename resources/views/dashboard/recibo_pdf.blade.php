@php
    $primary = '#4e6baf';
    $accent = '#86acd4';
    $order = $order ?? null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; font-family:'Inter', Arial, sans-serif; }
        body { margin:0; padding:24px; color:#0f172a; font-size:14px; background:#f5f7fb; }
        .shell { width:100%; max-width:960px; margin:0 auto; background:#fff; border-radius:18px; border:1px solid #e2e8f0; box-shadow:0 20px 40px rgba(78,107,175,0.15); padding:20px; }
        .header { display:flex; justify-content:space-between; align-items:center; background: linear-gradient(135deg, {{ $primary }}, {{ $accent }}); color:#fff; padding:16px 18px; border-radius:14px; }
        .chip { padding:6px 10px; border-radius:10px; background:rgba(255,255,255,0.18); border:1px solid rgba(255,255,255,0.28); font-weight:700; display:inline-flex; align-items:center; gap:6px; }
        .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(240px,1fr)); gap:12px; margin-top:14px; }
        .card { background:#f8fbff; border:1px solid #e2e8f0; border-radius:14px; padding:12px; }
        .muted { color:#475569; margin:6px 0; line-height:1.6; }
        table { width:100%; border-collapse: collapse; margin-top:10px; }
        th, td { padding:8px; border-bottom:1px solid #e2e8f0; text-align:left; }
        th { background:#eef2f7; color:#0f172a; }
        .totals { margin-top:10px; }
        .row { display:flex; justify-content:space-between; margin:3px 0; }
        .status { padding:5px 9px; border-radius:9px; font-weight:700; }
        .pendiente { background:rgba(234,179,8,0.2); color:#854d0e; }
        .completado { background:rgba(34,197,94,0.2); color:#166534; }
    </style>
</head>
<body>
    <div class="shell">
        <div class="header">
            <div>
                <h2 style="margin:0;">Recibo #{{ $order->receipt_number }}</h2>
                <p style="margin:4px 0 0;">Fecha: {{ optional($order->issued_at)->format('Y-m-d H:i') }}</p>
            </div>
            <div class="chip">{{ optional($order->user)->name ?? 'Cliente' }}</div>
        </div>

        <div class="grid">
            <div class="card">
                <strong>Pago</strong>
                <p class="muted">Método: {{ ucfirst($order->payment_method) }}</p>
                <p class="muted">Estado: <span class="status {{ $order->payment_status }}">{{ ucfirst($order->payment_status) }}</span></p>
                <p class="muted">Comprobante: {{ $order->receipt_number }}</p>
            </div>
            <div class="card">
                <strong>Cliente</strong>
                <p class="muted">Nombre: {{ optional($order->user)->name ?? 'Cliente' }}</p>
                <p class="muted">Email: {{ optional($order->user)->email ?? 'N/D' }}</p>
            </div>
        </div>

        <div class="card" style="margin-top:12px;">
            <strong>Detalle</strong>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cant.</th>
                        <th>Precio unit.</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Bs {{ number_format($item->unit_price, 2) }}</td>
                            <td>Bs {{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="totals">
                <div class="row"><span>Subtotal</span><strong>Bs {{ number_format($order->subtotal, 2) }}</strong></div>
                <div class="row"><span>Envío</span><strong>Bs {{ number_format($order->shipping, 2) }}</strong></div>
                <div class="row"><span>Total</span><strong>Bs {{ number_format($order->total, 2) }}</strong></div>
            </div>
        </div>
    </div>
</body>
</html>
