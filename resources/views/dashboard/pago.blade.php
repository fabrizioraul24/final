@php
    use Illuminate\Support\Facades\Auth;

    $primary = '#4e6baf';
    $accent = '#86acd4';
    $qrImage = asset('storage/images/QR.jpeg');
    $mockItems = $cartItems ?? [
        ['name' => 'Leche Entera PIL 1L', 'qty' => 2, 'price' => 8.50],
        ['name' => 'Yogurt Frutilla 1L', 'qty' => 1, 'price' => 12.00],
    ];
    $items = $cartItems ?? $mockItems;
    $subtotal = collect($items)->sum(fn($i) => ($i['qty'] ?? 0) * ($i['price'] ?? 0));
    $shipping = $shipping ?? 0;
    $total = $subtotal + $shipping;
    $recsFromCart = collect($items)->pluck('name')->take(5);
    $downloadUrl = isset($receiptNumber) && $receiptNumber ? route('dashboard.payment.receipt.download', $receiptNumber) : null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasarela de Pago | PIL Andina</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

        :root {
            --brand-primary: #0056b3;
            --brand-secondary: #00a8e8;
            --brand-accent: #ff6b6b;
            --bg-main: #f8fbff;
            --surface: rgba(255, 255, 255, 0.85);
            --surface-glass: rgba(255, 255, 255, 0.6);
            --text-main: #1a2b4b;
            --text-muted: #64748b;
            --grad-primary: linear-gradient(135deg, #0056b3 0%, #00a8e8 100%);
            --grad-surface: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            --shadow-sm: 0 4px 12px rgba(0, 86, 179, 0.05);
            --shadow-md: 0 12px 34px rgba(0, 86, 179, 0.1);
            --shadow-lg: 0 24px 68px rgba(0, 86, 179, 0.15);
            --radius-sm: 12px;
            --radius-md: 20px;
            --radius-lg: 32px;
            --primary: {{ $primary }};
            --primary-deep: #003a7a;
            --primary-soft: {{ $accent }};
            --ink: #1e293b;
            --muted: #64748b;
            --paper: #f1f5f9;
            --card: rgba(255, 255, 255, 0.95);
            --line: rgba(226, 232, 240, 0.8);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow: 0 20px 50px rgba(0, 0, 0, 0.05);
        }

        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
            -webkit-font-smoothing: antialiased;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: 
                radial-gradient(circle at 10% 10%, rgba(0, 86, 179, 0.05) 0%, transparent 30%),
                radial-gradient(circle at 90% 90%, rgba(0, 168, 232, 0.05) 0%, transparent 30%),
                var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
            line-height: 1.5;
            padding: 24px 16px 80px;
        }

        .page-shell {
            max-width: 1280px;
            margin: 0 auto;
        }

        /* --- Navigation --- */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            background: var(--surface-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 32px;
        }

        .back-btn, .btn-base {
            padding: 12px 20px;
            border-radius: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .ghost-btn,
        .solid-btn,
        .confirm-btn {
            border: none;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.8);
            color: var(--text-main);
            border: 1px solid var(--line);
        }

        .back-btn:hover { background: white; transform: translateX(-4px); box-shadow: var(--shadow-sm); }

        .ghost-btn {
            background: rgba(255, 255, 255, 0.8);
            color: var(--text-main);
            border: 1px solid var(--line);
        }

        .solid-btn,
        .confirm-btn {
            background: var(--grad-primary);
            color: white;
            box-shadow: 0 12px 24px rgba(0, 86, 179, 0.18);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-badge {
            width: 44px;
            height: 44px;
            background: var(--grad-primary);
            border-radius: 12px;
            display: grid;
            place-items: center;
            color: white;
            font-size: 1.25rem;
            box-shadow: 0 8px 16px rgba(0, 86, 179, 0.2);
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: var(--grad-surface);
            border: 1px solid var(--line);
            border-radius: 16px;
        }

        /* --- Layout --- */
        .layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 32px;
        }

        .surface {
            background: white;
            padding: 40px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--line);
            box-shadow: var(--shadow-sm);
        }

        /* --- Hero/Summary Intro --- */
        .hero {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 24px;
            margin-bottom: 40px;
        }

        .hero-card {
            background: #f8fafc;
            padding: 32px;
            border-radius: var(--radius-md);
            border: 1px solid var(--line);
            position: relative;
            overflow: hidden;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 999px;
            background: rgba(0, 86, 179, 0.08);
            color: var(--brand-primary);
            font-size: 0.82rem;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .hero-card h1 { font-size: 2.5rem; font-weight: 800; line-height: 1.1; margin-bottom: 16px; }

        .mini-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: white;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .support-card {
            background: linear-gradient(135deg, #1a2b4b 0%, #003a7a 100%);
            padding: 24px;
            border-radius: var(--radius-md);
            color: white;
            box-shadow: var(--shadow-md);
        }

        /* --- Payment Options --- */
        .section-intro { margin-bottom: 24px; }
        .section-intro h2 { font-size: 1.5rem; font-weight: 800; }

        .method-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .pay-option {
            background: white;
            padding: 24px;
            border-radius: 20px;
            border: 2px solid var(--line);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .pay-option input { display: none; }

        .pay-option.active {
            border-color: var(--brand-primary);
            background: rgba(0, 86, 179, 0.02);
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .method-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .method-icon {
            width: 48px;
            height: 48px;
            background: #f1f5f9;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 1.5rem;
            color: var(--brand-primary);
            transition: all 0.3s ease;
        }

        .pay-option.active .method-icon { background: var(--grad-primary); color: white; }

        .method-check {
            width: 24px;
            height: 24px;
            border: 2px solid var(--line);
            border-radius: 50%;
            display: grid;
            place-items: center;
            color: white;
            font-size: 0.8rem;
        }

        .pay-option.active .method-check { background: var(--brand-primary); border-color: var(--brand-primary); }

        .method-title { display: block; font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; }

        /* --- Payment Panels --- */
        .payment-panel {
            display: none;
            padding: 32px;
            background: #f8fafc;
            border-radius: var(--radius-md);
            border: 1px solid var(--line);
            animation: slideUp 0.4s ease;
        }

        .payment-panel.active { display: block; }

        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .qr-layout { display: flex; gap: 32px; align-items: center; }

        .qr-frame {
            padding: 16px;
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--line);
        }

        .qr-frame img { width: 180px; height: 180px; border-radius: 12px; }

        .amount-card {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            background: var(--brand-primary);
            color: white;
            border-radius: 14px;
            font-weight: 800;
            font-size: 1.25rem;
            box-shadow: 0 8px 16px rgba(0, 86, 179, 0.2);
            margin-bottom: 20px;
        }

        .input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }

        .input {
            width: 100%;
            padding: 14px 18px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: white;
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
        }

        .insight-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-top: 24px;
        }

        .insight-card {
            border-radius: 24px;
            padding: 18px;
            background: linear-gradient(180deg, #ffffff, #f7fbff);
            border: 1px solid rgba(78, 107, 175, 0.12);
        }

        .insight-card ul {
            margin: 10px 0 0 18px;
            color: #42607d;
            line-height: 1.6;
        }

        .summary-panel {
            background: white;
            border: 1px solid var(--line);
            box-shadow: var(--shadow-sm);
            border-radius: 34px;
            padding: 24px;
            position: sticky;
            top: 18px;
            height: fit-content;
        }

        .summary-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
        }

        .summary-count {
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(78, 107, 175, 0.08);
            color: var(--primary-deep);
            font-size: 0.82rem;
            font-weight: 800;
        }

        .summary-items {
            display: grid;
            gap: 12px;
            margin-bottom: 16px;
            max-height: 360px;
            overflow-y: auto;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 14px;
            border-radius: 22px;
            background: linear-gradient(180deg, #ffffff, #f7fbff);
            border: 1px solid rgba(78, 107, 175, 0.1);
        }

        .summary-row,
        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .summary-row {
            padding: 8px 0;
            color: #49647f;
        }

        .summary-total {
            margin: 10px 0 6px;
            padding-top: 14px;
            border-top: 1px solid rgba(78, 107, 175, 0.12);
        }

        .summary-total strong:last-child {
            font-size: 1.6rem;
            color: var(--primary-deep);
        }

        .btn,
        .confirm-btn {
            width: 100%;
            padding: 15px 18px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary-deep), var(--primary-soft));
            box-shadow: 0 16px 28px rgba(78, 107, 175, 0.22);
        }

        .btn:hover,
        .confirm-btn:hover,
        .back-btn:hover,
        .solid-btn:hover,
        .ghost-btn:hover {
            transform: translateY(-1px);
        }

        .summary-note {
            text-align: center;
            font-size: 0.88rem;
            margin-top: 12px;
            line-height: 1.6;
        }

        .modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(6px);
            z-index: 40;
            padding: 18px;
        }

        .modal-box,
        .modal-content {
            max-width: 420px;
            width: 100%;
            background: white;
            border: 1px solid var(--line);
            box-shadow: var(--shadow-lg);
            border-radius: 28px;
            padding: 24px;
            text-align: center;
        }

        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        @media (max-width: 1080px) {
            .layout,
            .hero {
                grid-template-columns: 1fr;
            }

            .method-grid,
            .insight-grid {
                grid-template-columns: 1fr;
            }

            .summary-panel {
                position: static;
            }
        }

        @media (max-width: 760px) {
            .topbar,
            .surface,
            .summary-panel,
            .hero-card,
            .support-card,
            .payment-panel {
                border-radius: 24px;
            }

            .topbar {
                flex-direction: column;
                align-items: stretch;
            }

            .qr-layout {
                flex-direction: column;
                align-items: flex-start;
            }

            .input-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="topbar">
            <a href="{{ route('dashboard.comprador') }}" class="back-btn"><i class="ri-arrow-left-s-line"></i> Regresar a la Tienda</a>

            <div class="brand">
                <div class="brand-badge">
                    <i class="ri-shield-check-fill"></i>
                </div>
                <div style="text-align:left;">
                    <small style="display:block; color:var(--text-muted); font-size:0.75rem; font-weight:700; text-transform:uppercase;">Pasarela Segura</small>
                    <strong style="font-size:1.1rem; font-weight:800;">Finalizar Compra</strong>
                </div>
            </div>

            <div class="user-pill">
                <i class="ri-user-smile-fill" style="color:var(--brand-primary); font-size:1.2rem;"></i>
                <div>
                    <strong style="display:block; font-size:0.9rem;">{{ Auth::user()->name ?? 'Cliente PIL' }}</strong>
                    <small style="color:var(--text-muted); font-size:0.7rem; font-weight:600;">TRANSACCIÓN PROTEGIDA</small>
                </div>
            </div>
        </div>

        <div class="layout">
            <section class="surface">
                <div class="hero">
                    <div class="hero-card">
                        <span class="eyebrow"><i class="ri-lock-password-line"></i> Pago Blindado</span>
                        <h1>Tu frescura está a un paso de distancia.</h1>
                        <p>Confirma tus detalles de pago y asegura tu pedido. Hemos simplificado el proceso para que sea tan natural como elegir tus productos favoritos.</p>
                        
                        <div style="display:flex; gap:12px; margin-top:24px;">
                            <span class="mini-pill" style="background:white; color:var(--text-main);"><i class="ri-customer-service-2-line"></i> Soporte 24/7</span>
                            <span class="mini-pill" style="background:white; color:var(--text-main);"><i class="ri-refund-2-line"></i> Garantía PIL</span>
                        </div>
                    </div>

                    <aside class="support-card">
                        <strong style="display:block; font-size:1.25rem; font-weight:800; margin-bottom:12px;">Seguridad Total</strong>
                        <p style="font-size:0.9rem; opacity:0.9; line-height:1.6; margin-bottom:20px;">Utilizamos estándares de encriptación bancaria para proteger cada bit de tu información.</p>
                        
                        <div style="display:grid; gap:12px;">
                            <div style="display:flex; align-items:center; gap:10px; font-size:0.85rem; font-weight:600;">
                                <i class="ri-checkbox-circle-fill" style="color:var(--brand-secondary);"></i> Verificación en tiempo real
                            </div>
                            <div style="display:flex; align-items:center; gap:10px; font-size:0.85rem; font-weight:600;">
                                <i class="ri-checkbox-circle-fill" style="color:var(--brand-secondary);"></i> Sin cargos ocultos
                            </div>
                            <div style="display:flex; align-items:center; gap:10px; font-size:0.85rem; font-weight:600;">
                                <i class="ri-checkbox-circle-fill" style="color:var(--brand-secondary);"></i> Comprobante instantáneo
                            </div>
                        </div>
                    </aside>
                </div>

        @if(!empty($paymentSuccess) && $paymentSuccess && $downloadUrl)
            <div class="modal active" id="successModal">
                <div class="modal-content">
                    <div style="width:80px; height:80px; background:rgba(16, 185, 129, 0.1); color:var(--success); border-radius:50%; display:grid; place-items:center; margin:0 auto 24px; font-size:2.5rem;">
                        <i class="ri-checkbox-circle-fill"></i>
                    </div>
                    <h2 style="font-size:1.75rem; font-weight:800; margin-bottom:12px;">¡Pago Exitoso!</h2>
                    <p style="color:var(--text-muted); line-height:1.6; margin-bottom:32px;">Gracias por tu compra. Tu recibo se está generando y se descargará automáticamente.</p>
                    <div style="display:grid; gap:12px;">
                        <button class="solid-btn btn-base" id="downloadNow" type="button" style="justify-content:center; padding:16px;">
                            <i class="ri-download-cloud-2-line"></i> Descargar Recibo Ahora
                        </button>
                        <button class="ghost-btn btn-base" id="closeModal" type="button" style="justify-content:center;">Cerrar Ventana</button>
                    </div>
                </div>
            </div>
        @endif

        <form id="paymentForm" method="POST" action="{{ route('dashboard.payment.process') }}" style="display:none;">
            @csrf
            <input type="hidden" name="payment_method" id="paymentMethodInput" value="qr">
            <input type="hidden" name="cart" value='@json($items)'>
        </form>

                <div class="section-intro">
                    <h2>Método de Pago</h2>
                    <p style="color:var(--text-muted);">Elige la opción que mejor se adapte a ti. Todas son 100% seguras.</p>
                </div>

                <div class="method-grid">
                    <label class="pay-option active" data-method="qr">
                        <input type="radio" name="method" value="qr" checked>
                        <div class="method-top">
                            <div class="method-icon"><i class="ri-qr-code-line"></i></div>
                            <div class="method-check"><i class="ri-check-line"></i></div>
                        </div>
                        <strong class="method-title">Transferencia QR</strong>
                        <p style="color:var(--text-muted); font-size:0.85rem;">Pago instantáneo desde cualquier banca móvil.</p>
                    </label>

                    <label class="pay-option" data-method="efectivo">
                        <input type="radio" name="method" value="efectivo">
                        <div class="method-top">
                            <div class="method-icon"><i class="ri-money-dollar-circle-line"></i></div>
                            <div class="method-check"><i class="ri-check-line"></i></div>
                        </div>
                        <strong class="method-title">Pago en Efectivo</strong>
                        <p style="color:var(--text-muted); font-size:0.85rem;">Cancela al momento de recibir tus productos.</p>
                    </label>

                    <label class="pay-option" data-method="tarjeta">
                        <input type="radio" name="method" value="tarjeta">
                        <div class="method-top">
                            <div class="method-icon"><i class="ri-bank-card-2-line"></i></div>
                            <div class="method-check"><i class="ri-check-line"></i></div>
                        </div>
                        <strong class="method-title">Tarjeta Débito/Crédito</strong>
                        <p style="color:var(--text-muted); font-size:0.85rem;">Procesamiento seguro con respaldo bancario.</p>
                    </label>
                </div>

                <div id="panel-qr" class="payment-panel active">
                    <div class="qr-layout">
                        <div class="qr-frame">
                            <img src="{{ $qrImage }}" alt="Código QR PIL">
                        </div>
                        <div style="flex:1;">
                            <h3 style="font-weight:800; margin-bottom:8px;">Escanea y Paga</h3>
                            <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.6; margin-bottom:20px;">Utiliza la aplicación de tu banco para escanear el código. Asegúrate de que el monto coincida exactamente con el total de tu pedido.</p>
                            <div class="amount-card">
                                <i class="ri-wallet-3-line"></i> Total: Bs {{ number_format($total, 2) }}
                            </div>
                            <div style="display:flex; gap:8px;">
                                <span class="mini-pill" style="background:#f1f5f9; color:var(--brand-primary);"><i class="ri-smartphone-line"></i> Simple</span>
                                <span class="mini-pill" style="background:#f1f5f9; color:var(--brand-primary);"><i class="ri-flashlight-line"></i> Rápido</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="panel-efectivo" class="payment-panel">
                    <div style="display:flex; gap:24px; align-items:center;">
                        <div style="width:60px; height:60px; background:rgba(16, 185, 129, 0.1); color:var(--success); border-radius:16px; display:grid; place-items:center; font-size:1.5rem;">
                            <i class="ri-hand-coin-line"></i>
                        </div>
                        <div>
                            <h3 style="font-weight:800; margin-bottom:8px;">Pago al Entregar</h3>
                            <p style="color:var(--text-muted); line-height:1.6;">Prepara el monto exacto para agilizar la entrega. Nuestro personal de logística validará el pago en tu domicilio.</p>
                        </div>
                    </div>
                </div>

                <div id="panel-tarjeta" class="payment-panel">
                    <h3 style="font-weight:800; margin-bottom:20px;">Datos de la Tarjeta</h3>
                    <div class="input-grid">
                        <div>
                            <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:6px; color:var(--text-muted);">TITULAR DE LA TARJETA</label>
                            <input class="input" type="text" placeholder="Como aparece en el plástico">
                        </div>
                        <div>
                            <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:6px; color:var(--text-muted);">NÚMERO DE TARJETA</label>
                            <input class="input" type="text" placeholder="0000 0000 0000 0000" inputmode="numeric">
                        </div>
                    </div>
                    <div class="input-grid" style="grid-template-columns: 1fr 1fr 1fr;">
                        <div>
                            <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:6px; color:var(--text-muted);">EXPIRACIÓN</label>
                            <input class="input" type="text" placeholder="MM/AA">
                        </div>
                        <div>
                            <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:6px; color:var(--text-muted);">CVV</label>
                            <input class="input" type="password" placeholder="***">
                        </div>
                        <div style="display:flex; align-items:flex-end;">
                             <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/2560px-Visa_Inc._logo.svg.png" style="height:20px; margin-right:12px; opacity:0.6;">
                             <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/1280px-Mastercard-logo.svg.png" style="height:20px; opacity:0.6;">
                        </div>
                    </div>
                </div>

                <div class="insight-grid">
                    <div class="insight-card">
                        <strong>Productos nuevos</strong>
                        <p class="muted" style="margin-top:8px;">Mantiene viva la experiencia incluso en el checkout.</p>
                    </div>

                    <div class="insight-card">
                        <strong>Recomendaciones desde tu carrito</strong>
                        <p class="muted" style="margin-top:8px;">Una forma ligera de recordar afinidades de compra.</p>
                        @if($recsFromCart->count())
                            <ul>
                                @foreach($recsFromCart as $rec)
                                    <li>{{ $rec }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </section>

            <aside class="summary-panel">
                <div class="summary-head">
                    <div>
                        <h2 style="font-weight:800; margin-bottom:4px;">Tu Pedido</h2>
                        <p style="color:var(--text-muted); font-size:0.85rem;">Resumen detallado de la compra.</p>
                    </div>
                    <span class="result-pill">{{ count($items) }} Ítems</span>
                </div>

                <div class="summary-items">
                    @foreach($items as $item)
                        <article class="summary-item">
                            <div style="flex:1;">
                                <strong style="display:block; font-size:0.95rem;">{{ $item['name'] ?? 'Producto' }}</strong>
                                <span style="color:var(--text-muted); font-size:0.8rem; font-weight:600;">Cant: {{ $item['qty'] ?? 0 }} • BS {{ number_format($item['price'], 2) }}</span>
                            </div>
                            <strong style="color:var(--text-main);">Bs {{ number_format(($item['qty'] ?? 0) * ($item['price'] ?? 0), 2) }}</strong>
                        </article>
                    @endforeach
                </div>

                <div style="display:grid; gap:12px;">
                    <div style="display:flex; justify-content:space-between; color:var(--text-muted); font-weight:600; font-size:0.9rem;">
                        <span>Subtotal</span>
                        <span>Bs {{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; color:var(--text-muted); font-weight:600; font-size:0.9rem;">
                        <span>Envío Estándar</span>
                        <span style="color:var(--success);">Gratis</span>
                    </div>
                    <div class="summary-total">
                        <strong style="font-size:1.1rem; color:var(--text-muted);">TOTAL</strong>
                        <strong>Bs {{ number_format($total, 2) }}</strong>
                    </div>
                </div>

                <button class="confirm-btn" type="button" id="confirmPayment">
                    <i class="ri-checkbox-circle-line"></i> Confirmar y Pagar
                </button>
                <p style="text-align:center; font-size:0.75rem; color:var(--text-muted); margin-top:20px; line-height:1.4;">
                    Al confirmar, aceptas nuestros <a href="#" style="color:var(--brand-primary); font-weight:700;">Términos de Servicio</a> y la política de privacidad.
                </p>
            </aside>
        </div>
    </div>

    <script>
        const options = document.querySelectorAll('.pay-option');
        const panels = {
            qr: document.getElementById('panel-qr'),
            efectivo: document.getElementById('panel-efectivo'),
            tarjeta: document.getElementById('panel-tarjeta'),
        };

        options.forEach(opt => {
            opt.addEventListener('click', () => {
                const value = opt.dataset.method;
                options.forEach(o => o.classList.remove('active'));
                opt.classList.add('active');

                Object.entries(panels).forEach(([key, panel]) => {
                    panel.classList.toggle('active', key === value);
                });

                opt.querySelector('input').checked = true;
                document.getElementById('paymentMethodInput').value = value;
            });
        });

        document.getElementById('confirmPayment').addEventListener('click', () => {
            document.getElementById('paymentForm').submit();
        });

        @if(!empty($paymentSuccess) && $paymentSuccess && $downloadUrl)
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('successModal');
                const closeBtn = document.getElementById('closeModal');
                const downloadBtn = document.getElementById('downloadNow');
                const url = "{{ $downloadUrl }}";
                const auto = setTimeout(() => window.location = url, 800);

                closeBtn?.addEventListener('click', () => {
                    modal.style.display = 'none';
                });

                downloadBtn?.addEventListener('click', () => {
                    clearTimeout(auto);
                    window.location = url;
                });
            });
        @endif
    </script>
</body>
</html>
