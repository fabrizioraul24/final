@php
    $primary = '#4e6baf';
    $accent = '#86acd4';
    $order = $order ?? null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo {{ $order->receipt_number }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
    <style>
        :root { --primary: {{ $primary }}; --accent: {{ $accent }}; }
        * { box-sizing: border-box; margin:0; padding:0; }
        @page { size: A4 portrait; margin: 18mm 16mm; }
        body { font-family:'Inter', Arial, sans-serif; background: radial-gradient(circle at 20% 20%, #e0e9ff, transparent 45%), radial-gradient(circle at 80% 10%, #d6f3ff, transparent 40%), linear-gradient(145deg,#eef2f7,#f9fbff); color:#0f172a; padding:30px; }
        .shell { max-width: 980px; margin:0 auto; background:#fff; border-radius:22px; border:1px solid rgba(15,23,42,0.06); box-shadow:0 32px 68px rgba(78,107,175,0.2); overflow:hidden; }
        .header { padding:24px 28px; background: linear-gradient(120deg, var(--primary), var(--accent)); color:#fff; display:flex; justify-content:space-between; align-items:center; gap:16px; }
        .title h2 { margin:0; letter-spacing:-0.01em; }
        .title p { margin:4px 0 0; opacity:0.94; }
        .chips { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
        .chip { padding:10px 14px; border-radius:14px; background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.28); font-weight:800; color:#fff; display:inline-flex; gap:6px; align-items:center; text-decoration:none; }
        .content { padding:24px 28px; display:grid; gap:18px; }
        .grid { display:grid; gap:16px; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
        .card { border:1px solid rgba(15,23,42,0.07); border-radius:18px; padding:18px; background:linear-gradient(135deg,#f8fbff,#f2f6ff); box-shadow:0 14px 36px rgba(15,23,42,0.08); }
        .card h3 { margin:0 0 10px; font-size:1.08rem; }
        .muted { color:#475569; margin:6px 0; line-height:1.8; }
        table { width:100%; border-collapse: collapse; margin-top:8px; }
        th, td { padding:11px 9px; text-align:left; }
        th { background:rgba(78,107,175,0.14); color:#0f172a; border-bottom:1px solid rgba(15,23,42,0.08); font-size:0.95rem; }
        td { border-bottom:1px solid rgba(15,23,42,0.05); }
        .totals { margin-top:12px; border-top:1px solid rgba(15,23,42,0.08); padding-top:12px; }
        .row { display:flex; justify-content:space-between; margin:4px 0; font-weight:700; }
        .row strong { font-size:1.02rem; }
        .status { padding:8px 12px; border-radius:12px; font-weight:800; display:inline-flex; align-items:center; gap:6px; }
        .status.pendiente { background:rgba(234,179,8,0.18); color:#854d0e; }
        .status.completado { background:rgba(34,197,94,0.18); color:#166534; }
        .foot-note { text-align:center; color:#475569; margin-top:6px; font-size:0.9rem; }
        .modal { position:fixed; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.55); z-index:30; padding:1rem; }
        .modal-box { background:#fff; border-radius:18px; padding:20px; max-width:460px; width:100%; box-shadow:0 22px 44px rgba(0,0,0,0.28); text-align:center; }
        .modal-box h3 { margin:0 0 8px; }
        .btn { cursor:pointer; border:none; border-radius:12px; padding:10px 14px; font-weight:800; }
        .btn-primary { background: linear-gradient(135deg, var(--primary), var(--accent)); color:#fff; }
        .btn-link { background:none; color:var(--primary); }
    </style>
</head>
<body>
    <div class="shell">
        <div class="header">
            <div class="title">
                <h2>Recibo #{{ $order->receipt_number }}</h2>
                <p>Fecha de emisión: {{ optional($order->issued_at)->format('Y-m-d H:i') }}</p>
            </div>
            <div class="chips">
                <a href="{{ route('dashboard.payment.receipt.download', $order->receipt_number) }}" class="chip"><i class="ri-download-2-line"></i> Descargar</a>
                <span class="chip"><i class="ri-user-3-line"></i> {{ optional($order->user)->name ?? 'Cliente' }}</span>
            </div>
        </div>
        <div class="content">
            <div class="grid">
                <div class="card">
                    <h3>Resumen de pago</h3>
                    <p class="muted">Método: {{ ucfirst($order->payment_method) }}</p>
                    <p class="muted">Estado: <span class="status {{ $order->payment_status }}">{{ ucfirst($order->payment_status) }}</span></p>
                    <p class="muted">Comprobante: {{ $order->receipt_number }}</p>
                </div>
                <div class="card">
                    <h3>Datos del cliente</h3>
                    <p class="muted">Nombre: {{ optional($order->user)->name ?? 'Cliente' }}</p>
                    <p class="muted">Email: {{ optional($order->user)->email ?? 'N/D' }}</p>
                </div>
            </div>

            <div class="card">
                <h3>Detalle de la compra</h3>
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
                                <td>Bs {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="totals">
                    <div class="row"><span>Subtotal</span><strong>Bs {{ number_format($order->subtotal, 2) }}</strong></div>
                    <div class="row"><span>Envío</span><strong>Bs {{ number_format($order->shipping, 2) }}</strong></div>
                    <div class="row" style="font-size:1.05rem;"><span>Total</span><strong>Bs {{ number_format($order->total, 2) }}</strong></div>
                </div>
            </div>
            <p class="foot-note">Gracias por tu compra. Guarda este comprobante como respaldo de tu pago.</p>
        </div>
    </div>

    @if(session('payment_success'))
        <div class="modal" id="successModal">
            <div class="modal-box">
                <h3>Pago realizado</h3>
                <p style="margin:0 0 10px;">Tu comprobante está listo.</p>
                <div style="display:flex; justify-content:center; gap:10px; flex-wrap:wrap;">
                    <button class="btn btn-primary" id="downloadNow"><i class="ri-download-2-line"></i> Descargar recibo</button>
                    <button class="btn btn-link" id="closeModal">Cerrar</button>
                </div>
            </div>
        </div>
        <script>
            const downloadUrl = "{{ route('dashboard.payment.receipt.download', $order->receipt_number) }}";
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('successModal');
                const closeBtn = document.getElementById('closeModal');
                const downloadBtn = document.getElementById('downloadNow');
                const auto = setTimeout(() => {
                    window.location = downloadUrl;
                }, 800);
                closeBtn.addEventListener('click', () => modal.style.display = 'none');
                downloadBtn.addEventListener('click', () => {
                    clearTimeout(auto);
                    window.location = downloadUrl;
                });
            });
        </script>
    @endif
</body>
</html>
