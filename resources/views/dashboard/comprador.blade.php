@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Str;

    $primary = '#4e6baf';
    $accent = '#86acd4';
    $user = Auth::user();
    $availableProducts = $products->where('available_qty', '>', 0)->count();
    $soldOutProducts = $products->where('available_qty', '<=', 0)->count();
    $totalUnits = $products->sum('available_qty');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprador | PIL Andina</title>
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
                radial-gradient(circle at 0% 0%, rgba(0, 86, 179, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 100% 0%, rgba(0, 168, 232, 0.08) 0%, transparent 40%),
                var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
            line-height: 1.5;
            overflow-x: hidden;
        }

        .page-shell {
            max-width: 1440px;
            margin: 0 auto;
            padding: 24px 32px 80px;
        }

        /* --- Header & Navigation --- */
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
            margin-bottom: 40px;
            position: sticky;
            top: 24px;
            z-index: 100;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .topbar:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .brand-block {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-badge {
            width: 48px;
            height: 48px;
            background: var(--grad-primary);
            border-radius: 14px;
            display: grid;
            place-items: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 8px 16px rgba(0, 86, 179, 0.25);
        }

        .brand-copy small {
            display: block;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .brand-copy strong {
            display: block;
            font-size: 1.25rem;
            font-weight: 800;
            background: var(--grad-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        /* --- Buttons --- */
        .btn-base {
            padding: 12px 20px;
            border-radius: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .ghost-btn {
            background: transparent;
            color: var(--text-main);
            border: 1px solid var(--line);
        }

        .ghost-btn:hover {
            background: rgba(0, 86, 179, 0.05);
            border-color: var(--brand-primary);
            color: var(--brand-primary);
            transform: translateY(-2px);
        }

        .solid-btn {
            background: var(--grad-primary);
            color: white;
            box-shadow: 0 8px 20px rgba(0, 86, 179, 0.2);
        }

        .solid-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 86, 179, 0.3);
            filter: brightness(1.1);
        }

        .cart-btn {
            background: var(--text-main);
            color: white;
            position: relative;
        }

        .cart-btn:hover {
            background: var(--brand-primary);
            transform: translateY(-2px) scale(1.02);
        }

        #cartBadge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: var(--brand-accent);
            color: white;
            min-width: 22px;
            height: 22px;
            border-radius: 11px;
            font-size: 0.7rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(255, 107, 107, 0.3);
            border: 2px solid var(--text-main);
        }

        .meta-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: white;
            border: 1px solid var(--line);
            border-radius: 16px;
        }

        .meta-chip i { color: var(--brand-primary); font-size: 1.2rem; }

        /* --- Hero Section --- */
        .hero {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 24px;
            margin-bottom: 48px;
        }

        .hero-main {
            background: white;
            padding: 48px;
            border-radius: var(--radius-lg);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--line);
            box-shadow: var(--shadow-sm);
        }

        .hero-main::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(0, 168, 232, 0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(0, 86, 179, 0.08);
            color: var(--brand-primary);
            border-radius: 100px;
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 24px;
        }

        .hero-main h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            letter-spacing: -0.02em;
            color: var(--text-main);
        }

        .hero-main p {
            color: var(--text-muted);
            font-size: 1.1rem;
            max-width: 60ch;
            margin-bottom: 32px;
        }

        .hero-search {
            display: flex;
            gap: 12px;
            background: #f1f5f9;
            padding: 8px;
            border-radius: 18px;
            margin-bottom: 32px;
            max-width: 700px;
        }

        .search-box {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 0 16px;
            gap: 12px;
            background: white;
            border-radius: 14px;
            box-shadow: var(--shadow-sm);
        }

        .search-box i { color: var(--text-muted); }

        .search-box input {
            width: 100%;
            border: none;
            padding: 14px 0;
            font-family: inherit;
            font-size: 1rem;
            outline: none;
            background: transparent;
        }

        .sort-box {
            width: 180px;
            border: none;
            background: white;
            padding: 0 16px;
            border-radius: 14px;
            font-family: inherit;
            font-weight: 600;
            color: var(--text-main);
            outline: none;
            box-shadow: var(--shadow-sm);
            cursor: pointer;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .stat-card {
            padding: 20px;
            background: white;
            border-radius: 18px;
            border: 1px solid var(--line);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: var(--brand-primary);
            box-shadow: var(--shadow-md);
        }

        .stat-card span {
            display: block;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .stat-card strong {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-main);
        }

        .hero-side {
            background: linear-gradient(180deg, #1a2b4b 0%, #003a7a 100%);
            border-radius: var(--radius-lg);
            padding: 32px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .hero-side::before {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(0, 168, 232, 0.2) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hero-side h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 24px;
            line-height: 1.3;
        }

        .hero-list {
            list-style: none;
            display: grid;
            gap: 16px;
            margin-bottom: 32px;
        }

        .hero-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
        }

        .hero-list i {
            color: var(--brand-secondary);
            font-size: 1.25rem;
        }

        .mini-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 600;
            backdrop-filter: blur(4px);
        }

        /* --- Category Filters --- */
        .panel {
            background: white;
            padding: 24px;
            border-radius: var(--radius-md);
            border: 1px solid var(--line);
            margin-bottom: 32px;
            box-shadow: var(--shadow-sm);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .panel-header h3 { font-size: 1.25rem; font-weight: 800; }

        .category-scroller {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 8px;
            scrollbar-width: none;
        }

        .category-scroller::-webkit-scrollbar { display: none; }

        .filter-button {
            padding: 10px 20px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: white;
            color: var(--text-main);
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
        }

        .filter-button:hover {
            border-color: var(--brand-primary);
            color: var(--brand-primary);
            background: rgba(0, 86, 179, 0.02);
        }

        .filter-button.active {
            background: var(--grad-primary);
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(0, 86, 179, 0.2);
        }

        .result-pill {
            padding: 6px 14px;
            background: #f1f5f9;
            border-radius: 100px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--brand-primary);
        }

        /* --- Product Grid & Cards --- */
        .catalog-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 16px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        .product-card {
            background: white;
            border-radius: var(--radius-md);
            padding: 16px;
            border: 1px solid var(--line);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-lg);
            border-color: var(--brand-primary);
        }

        .product-media {
            height: 240px;
            background: #f8fafc;
            border-radius: 16px;
            margin-bottom: 16px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .product-media img {
            width: 85%;
            height: 85%;
            object-fit: contain;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-media img {
            transform: scale(1.1) rotate(2deg);
        }

        .tag {
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .tag-category { background: rgba(0, 86, 179, 0.1); color: var(--brand-primary); }
        .tag-stock { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .tag-empty { background: rgba(239, 68, 68, 0.1); color: var(--danger); }

        .product-card h3 {
            font-size: 1.15rem;
            font-weight: 700;
            margin: 12px 0 6px;
            color: var(--text-main);
        }

        .product-card p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 16px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.8em;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: auto;
            margin-bottom: 16px;
        }

        .price-block span {
            display: block;
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .price-block strong {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--brand-primary);
        }

        .qty-row {
            display: grid;
            grid-template-columns: 80px 1fr;
            gap: 8px;
        }

        .qty-input {
            background: #f1f5f9;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 800;
            text-align: center;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }

        .qty-input:focus { background: #e2e8f0; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); }

        .add-cart {
            background: var(--grad-primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .add-cart:hover { filter: brightness(1.1); transform: scale(1.02); }

        .soldout-box {
            background: #fef2f2;
            color: var(--danger);
            padding: 12px;
            border-radius: 12px;
            font-weight: 700;
            text-align: center;
            font-size: 0.85rem;
            border: 1px dashed rgba(239, 68, 68, 0.2);
        }

        /* --- Drawer (Cart) --- */
        .drawer-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(8px);
            z-index: 1000;
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .drawer {
            position: fixed;
            top: 0;
            right: 0;
            height: 100%;
            width: 480px;
            background: white;
            box-shadow: -20px 0 60px rgba(0, 0, 0, 0.1);
            z-index: 1001;
            transform: translateX(100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            padding: 32px;
        }

        .drawer.active { transform: translateX(0); }

        .drawer-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .drawer-header h2 { font-size: 1.5rem; font-weight: 800; }

        .drawer-content {
            flex: 1;
            overflow-y: auto;
            margin: 0 -8px;
            padding: 0 8px;
        }

        .drawer-item {
            display: flex;
            gap: 16px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 18px;
            margin-bottom: 12px;
            border: 1px solid var(--line);
        }

        .drawer-item img { width: 80px; height: 80px; object-fit: contain; background: white; border-radius: 12px; padding: 8px; }

        .drawer-item h4 { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }

        .drawer-footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 2px solid #f1f5f9;
        }

        .drawer-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .drawer-total strong { font-size: 2rem; color: var(--brand-primary); letter-spacing: -0.02em; }

        /* --- Modals --- */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .modal.active { opacity: 1; pointer-events: all; }

        .modal-content {
            background: white;
            border-radius: var(--radius-lg);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            transform: scale(0.9);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center;
        }

        .modal.active .modal-content { transform: scale(1); }

        /* --- Animations --- */
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        .hidden-card { display: none !important; }

        /* --- Responsive --- */
        @media (max-width: 1200px) {
            .hero { grid-template-columns: 1fr; }
            .hero-side { display: none; }
        }

        @media (max-width: 768px) {
            .page-shell { padding: 16px; }
            .hero-main h1 { font-size: 2.5rem; }
            .hero-stats { grid-template-columns: 1fr; }
            .topbar { flex-direction: column; gap: 16px; align-items: stretch; border-radius: var(--radius-md); }
            .topbar-actions { width: 100%; justify-content: stretch; }
            .topbar-actions > * { flex: 1 1 100%; }
            .catalog-head,
            .panel-header { flex-direction: column; align-items: flex-start; }
            .drawer { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <nav class="topbar">
            <div class="brand-block">
                <div class="brand-badge">
                    <i class="ri-shopping-basket-2-fill"></i>
                </div>
                <div class="brand-copy">
                    <small>Tienda Directa</small>
                    <strong>PIL Andina Marketplace</strong>
                </div>
            </div>

            <div class="topbar-actions">
                <button class="ghost-btn btn-base" type="button" id="historyBtn">
                    <i class="ri-time-line"></i> Historial
                </button>
                <div class="meta-chip">
                    <i class="ri-user-smile-line"></i>
                    <div>
                        <strong style="display:block; font-size:0.9rem;">{{ $user->name ?? 'Usuario PIL' }}</strong>
                        <small style="color:var(--text-muted); font-size:0.7rem; font-weight:600; text-transform:uppercase;">{{ optional($user->role)->name ?? 'Comprador' }}</small>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="ghost-btn btn-base">Cerrar sesión</button>
                </form>
                <button class="cart-btn btn-base" id="openCart" type="button">
                    <i class="ri-shopping-cart-2-fill"></i> Mi Carrito
                    <span id="cartBadge" style="display:none;">0</span>
                </button>
            </div>
        </nav>

        <section class="hero">
            <div class="hero-main">
                <span class="eyebrow"><i class="ri-sparkling-2-line"></i> Experiencia de compra renovada</span>
                <h1>Calidad que nace en el campo y llega a tu hogar.</h1>
                <p>Nuestra nueva tienda está diseñada para que encuentres la frescura de PIL con solo un clic. Navegación fluida, pagos seguros y la confianza de siempre en una interfaz moderna.</p>

                <div class="hero-search">
                    <label class="search-box">
                        <i class="ri-search-2-line"></i>
                        <input type="search" id="productSearch" placeholder="¿Qué buscas hoy? Leche, yogurt, quesos...">
                    </label>
                    <select id="sortProducts" class="sort-box">
                        <option value="name-asc">Ordenar: A-Z</option>
                        <option value="price-asc">Precio: Menor</option>
                        <option value="price-desc">Precio: Mayor</option>
                        <option value="stock-desc">Disponibilidad</option>
                    </select>
                </div>

                <div class="hero-stats">
                    <div class="stat-card">
                        <span>Catálogo Activo</span>
                        <strong>{{ $availableProducts }} Prod.</strong>
                    </div>
                    <div class="stat-card">
                        <span>Unidades Listas</span>
                        <strong>{{ number_format($totalUnits) }}</strong>
                    </div>
                    <div class="stat-card">
                        <span>Stock Crítico</span>
                        <strong>{{ $soldOutProducts }} Agotados</strong>
                    </div>
                </div>
            </div>

            <aside class="hero-side">
                <div>
                    <span class="mini-pill" style="margin-bottom:16px;">Venta Segura</span>
                    <h2>Eficiencia en cada pedido.</h2>
                    <ul class="hero-list">
                        <li><i class="ri-checkbox-circle-fill"></i> Stock sincronizado en tiempo real.</li>
                        <li><i class="ri-checkbox-circle-fill"></i> Interfaz intuitiva y optimizada.</li>
                        <li><i class="ri-checkbox-circle-fill"></i> Soporte directo post-venta.</li>
                    </ul>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    <span class="mini-pill"><i class="ri-shield-flash-line"></i> Encriptado</span>
                    <span class="mini-pill"><i class="ri-truck-line"></i> Logística PIL</span>
                </div>
            </aside>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h3>Explorar Categorías</h3>
                    <p style="color:var(--text-muted); font-size:0.95rem;">Selecciona una línea de productos para filtrar el catálogo.</p>
                </div>
                <span class="result-pill">{{ $products->count() }} Productos disponibles</span>
            </div>

            <div class="category-scroller">
                <button class="filter-button active" data-filter="all" type="button">Todos los productos</button>
                @foreach($categories as $cat)
                    <button class="filter-button" data-filter="cat-{{ $cat['id'] }}" type="button">
                        {{ $cat['name'] }} <span style="opacity:0.6; margin-left:6px; font-weight:400;">{{ $cat['count'] }}</span>
                    </button>
                @endforeach
            </div>
        </section>

        <section>
            <div class="catalog-head">
                <div>
                    <h3 style="font-size:1.5rem; font-weight:800;">Catálogo de Frescura</h3>
                    <p style="color:var(--text-muted);">Los mejores productos PIL directo a tu carrito.</p>
                </div>
                <span id="resultsCount" class="result-pill">Mostrando {{ $products->count() }} resultados</span>
            </div>

            <div class="product-grid" id="productGrid">
                @foreach($products as $product)
                    @php
                        $imgPath = $product->image_path;
                        $imgUrl = $imgPath ? \Illuminate\Support\Facades\Storage::url($imgPath) : asset('storage/images/logo.png');
                    @endphp
                    <article
                        class="product-card addable"
                        data-id="{{ $product->id }}"
                        data-category="cat-{{ $product->category->id ?? 0 }}"
                        data-name="{{ strtolower($product->name) }}"
                        data-title="{{ $product->name }}"
                        data-description="{{ strtolower($product->description ?? '') }}"
                        data-price="{{ $product->price_for_buyer }}"
                        data-stock="{{ $product->available_qty }}"
                        data-available="{{ $product->available_qty }}"
                        data-img="{{ $imgUrl }}"
                    >
                        <div class="product-media">
                            <img src="{{ $imgUrl }}" alt="{{ $product->name }}" loading="lazy">
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                            <span class="tag tag-category">{{ $product->category->name ?? 'PIL' }}</span>
                            @if($product->available_qty > 0)
                                <span class="tag tag-stock"><i class="ri-checkbox-circle-line"></i> En Stock</span>
                            @else
                                <span class="tag tag-empty"><i class="ri-close-circle-line"></i> Agotado</span>
                            @endif
                        </div>

                        <h3>{{ $product->name }}</h3>
                        <p>{{ Str::limit($product->description ?? 'Calidad garantizada PIL Andina.', 90) }}</p>

                        <div class="product-meta">
                            <div class="price-block">
                                <span>Precio Sugerido</span>
                                <strong>Bs {{ number_format($product->price_for_buyer, 2) }}</strong>
                            </div>
                            @if($product->nearest_expire)
                                <div style="text-align:right;">
                                    <span style="display:block; font-size:0.7rem; color:var(--text-muted); font-weight:600;">Vencimiento</span>
                                    <span style="font-size:0.75rem; font-weight:700; color:var(--brand-primary);">{{ $product->nearest_expire }}</span>
                                </div>
                            @endif
                        </div>

                        @if($product->available_qty > 0)
                            <div class="qty-row">
                                <input type="number" min="1" value="1" class="qty-input">
                                <button class="add-cart" type="button">
                                    <i class="ri-shopping-cart-2-line"></i> Agregar
                                </button>
                            </div>
                        @else
                            <div class="soldout-box">
                                <i class="ri-notification-3-line"></i> Próximamente disponible
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    </div>

    <div class="drawer-backdrop" id="cartBackdrop"></div>

    <aside class="drawer" id="cartDrawer">
        <div class="drawer-header">
            <div>
                <h2>Tu Carrito</h2>
                <span style="color:var(--text-muted); font-size:0.85rem; font-weight:600;">Revisa tus productos antes de finalizar.</span>
            </div>
            <button class="ghost-btn btn-base" id="closeCart" type="button"><i class="ri-close-line"></i></button>
        </div>

        <div class="drawer-content" id="cartItems"></div>

        <div class="drawer-footer">
            <div class="drawer-total">
                <span style="color:var(--text-muted); font-weight:700;">Total Estimado</span>
                <strong id="cartTotal">Bs 0.00</strong>
            </div>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:24px; text-align:center;">Impuestos y cargos de envío se calcularán en el pago.</p>
            
            <div style="display:grid; gap:12px;">
                <button class="solid-btn btn-base" id="finalizePurchase" type="button" style="justify-content:center; padding:16px;">
                    <i class="ri-bank-card-line"></i> Proceder al Pago
                </button>
                <button class="ghost-btn btn-base" id="clearCart" type="button" style="justify-content:center;">
                    <i class="ri-delete-bin-line"></i> Vaciar Carrito
                </button>
            </div>
        </div>
    </aside>

    <div class="modal" id="stockModal">
        <div class="modal-content">
            <div style="width:64px; height:64px; background:#fef2f2; color:var(--danger); border-radius:50%; display:grid; place-items:center; margin:0 auto 24px; font-size:2rem;">
                <i class="ri-error-warning-line"></i>
            </div>
            <h3 style="font-size:1.5rem; font-weight:800; margin-bottom:12px;">Stock Insuficiente</h3>
            <p id="stockModalMsg" style="color:var(--text-muted); line-height:1.6; margin-bottom:24px;"></p>
            <button class="solid-btn btn-base" id="closeModal" type="button" style="width:100%; justify-content:center;">Entendido</button>
        </div>
    </div>

    @if(isset($history) && $history->count())
        <div class="modal" id="historyModal">
            <div class="modal-content" style="max-width:800px; text-align:left;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
                    <div>
                        <h2 style="font-size:1.75rem; font-weight:800;">Historial de Pedidos</h2>
                        <p style="color:var(--text-muted);">Tus compras anteriores y estados actuales.</p>
                    </div>
                    <button class="ghost-btn btn-base" id="closeHistory" type="button"><i class="ri-close-line"></i></button>
                </div>

                <div class="history-stack" style="max-height:60vh; overflow-y:auto; padding-right:12px;">
                    @foreach($history as $h)
                        <article class="history-item" style="border:1px solid var(--line); border-radius:18px; padding:20px; margin-bottom:16px; background:#f8fafc;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                                <div>
                                    <strong style="font-size:1.1rem; color:var(--brand-primary);">Ref: #{{ $h->receipt_number }}</strong>
                                    <p style="color:var(--text-muted); font-size:0.85rem; font-weight:600; margin-top:4px;">
                                        Emitido: {{ optional($h->issued_at)->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <div style="text-align:right;">
                                    <span class="result-pill" style="display:inline-block; margin-bottom:8px;">{{ ucfirst($h->payment_status) }}</span>
                                    <strong style="display:block; font-size:1.2rem;">Bs {{ number_format($h->total, 2) }}</strong>
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
                                @foreach($h->items as $item)
                                    @php
                                        $pi = $item->product;
                                        $pImg = $pi && $pi->image_path ? \Illuminate\Support\Facades\Storage::url($pi->image_path) : asset('storage/images/logo.png');
                                    @endphp
                                    <div style="display:flex; gap:12px; align-items:center; background:white; padding:10px; border-radius:12px; border:1px solid rgba(0,0,0,0.03);">
                                        <img src="{{ $pImg }}" alt="{{ $item->product_name }}" style="width:40px; height:40px; object-fit:contain;">
                                        <div>
                                            <strong style="display:block; font-size:0.85rem;">{{ $item->product_name }}</strong>
                                            <span style="font-size:0.75rem; color:var(--text-muted);">Cant: {{ $item->quantity }} • Bs {{ number_format($item->unit_price, 2) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <script>
        const filterButtons = document.querySelectorAll('.filter-button');
        const productGrid = document.getElementById('productGrid');
        const productCards = Array.from(document.querySelectorAll('.addable'));
        const productSearch = document.getElementById('productSearch');
        const sortProducts = document.getElementById('sortProducts');
        const resultsCount = document.getElementById('resultsCount');
        let activeFilter = 'all';

        function applyCatalogFilters() {
            const query = (productSearch?.value || '').trim().toLowerCase();
            const sortedCards = [...productCards].sort((a, b) => {
                const sortMode = sortProducts?.value || 'name-asc';
                const nameA = a.dataset.title.toLowerCase();
                const nameB = b.dataset.title.toLowerCase();
                const priceA = parseFloat(a.dataset.price || '0');
                const priceB = parseFloat(b.dataset.price || '0');
                const stockA = parseInt(a.dataset.stock || '0', 10);
                const stockB = parseInt(b.dataset.stock || '0', 10);

                if (sortMode === 'price-asc') return priceA - priceB;
                if (sortMode === 'price-desc') return priceB - priceA;
                if (sortMode === 'stock-desc') return stockB - stockA;
                return nameA.localeCompare(nameB);
            });

            let visible = 0;

            sortedCards.forEach(card => {
                const matchesCategory = activeFilter === 'all' || card.dataset.category === activeFilter;
                const searchable = `${card.dataset.name} ${card.dataset.description}`;
                const matchesQuery = !query || searchable.includes(query);
                const shouldShow = matchesCategory && matchesQuery;

                card.classList.toggle('hidden-card', !shouldShow);
                if (shouldShow) {
                    productGrid.appendChild(card);
                    visible++;
                }
            });

            resultsCount.textContent = `${visible} resultado${visible === 1 ? '' : 's'}`;
        }

        filterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                activeFilter = btn.dataset.filter;
                filterButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                applyCatalogFilters();
            });
        });

        productSearch?.addEventListener('input', applyCatalogFilters);
        sortProducts?.addEventListener('change', applyCatalogFilters);

        const cart = new Map();
        const cartBadge = document.getElementById('cartBadge');
        const cartItems = document.getElementById('cartItems');
        const cartTotal = document.getElementById('cartTotal');
        const cartDrawer = document.getElementById('cartDrawer');
        const cartBackdrop = document.getElementById('cartBackdrop');
        const stockModal = document.getElementById('stockModal');
        const stockModalMsg = document.getElementById('stockModalMsg');

        function openCart() {
            cartDrawer.classList.add('active');
            cartBackdrop.style.display = 'block';
        }

        function closeCart() {
            cartDrawer.classList.remove('active');
            cartBackdrop.style.display = 'none';
        }

        document.getElementById('openCart').addEventListener('click', openCart);
        document.getElementById('closeCart').addEventListener('click', closeCart);
        cartBackdrop.addEventListener('click', closeCart);

        function updateBadge() {
            let totalQty = 0;
            cart.forEach(item => totalQty += item.qty);
            cartBadge.textContent = totalQty;
            cartBadge.style.display = totalQty > 0 ? 'inline-flex' : 'none';
        }

        function renderCart() {
            cartItems.innerHTML = '';
            let total = 0;

            cart.forEach(item => {
                total += item.qty * item.price;

                const row = document.createElement('div');
                row.className = 'drawer-item';
                row.innerHTML = `
                    <img src="${item.img}" alt="${item.name}">
                    <div style="flex:1;">
                        <strong>${item.name}</strong>
                        <p style="margin:4px 0; color:#64748b;">Bs ${item.price.toFixed(2)}</p>
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <input type="number" min="1" max="${item.available}" value="${item.qty}">
                            <span style="color:#4b647b; font-size:0.84rem;">de ${item.available} disponibles</span>
                        </div>
                    </div>
                    <button class="trash-btn" type="button" title="Quitar" data-remove="${item.id}">
                        <i class="ri-delete-bin-6-line"></i>
                    </button>
                `;

                const qtyInput = row.querySelector('input');
                qtyInput.addEventListener('change', (e) => {
                    let val = parseInt(e.target.value, 10) || 1;
                    if (val > item.available) {
                        showStockModal(item.available);
                        val = item.available;
                    }
                    cart.set(item.id, { ...item, qty: val });
                    updateBadge();
                    renderCart();
                });

                row.querySelector('[data-remove]').addEventListener('click', () => {
                    cart.delete(item.id);
                    updateBadge();
                    renderCart();
                });

                cartItems.appendChild(row);
            });

            cartTotal.textContent = 'Bs ' + total.toFixed(2);

            if (cart.size === 0) {
                cartItems.innerHTML = `
                    <div style="padding:18px; border-radius:22px; background:rgba(255,255,255,0.8); border:1px dashed rgba(78,107,175,0.16); color:#64748b; text-align:center; line-height:1.6;">
                        Tu carrito esta vacio.<br>
                        Agrega productos desde el catalogo para empezar la compra.
                    </div>
                `;
            }
        }

        function showStockModal(max) {
            stockModalMsg.textContent = `No hay unidades suficientes. Maximo disponible: ${max}.`;
            stockModal.style.display = 'flex';
        }

        document.getElementById('closeModal').addEventListener('click', () => {
            stockModal.style.display = 'none';
        });

        document.querySelectorAll('.addable').forEach(card => {
            const addBtn = card.querySelector('.add-cart');
            const qtyInput = card.querySelector('.qty-input');

            if (!addBtn || !qtyInput) return;

            const available = parseInt(card.dataset.available, 10) || 0;
            if (available <= 0) {
                addBtn.disabled = true;
                qtyInput.disabled = true;
                return;
            }

            addBtn.addEventListener('click', () => {
                const availableNow = parseInt(card.dataset.available, 10) || 0;
                let qty = parseInt(qtyInput.value, 10) || 1;

                if (qty > availableNow) {
                    showStockModal(availableNow);
                    qtyInput.value = availableNow > 0 ? availableNow : 1;
                    return;
                }

                const id = card.dataset.id;
                const existing = cart.get(id) || {
                    id,
                    name: card.dataset.title,
                    price: parseFloat(card.dataset.price),
                    available: availableNow,
                    img: card.dataset.img,
                    qty: 0,
                };

                const newQty = Math.min(existing.qty + qty, availableNow);
                if (newQty < existing.qty + qty) {
                    showStockModal(availableNow);
                }

                cart.set(id, { ...existing, qty: newQty, available: availableNow });
                updateBadge();
                renderCart();
                openCart();
            });
        });

        document.getElementById('clearCart').addEventListener('click', () => {
            cart.clear();
            updateBadge();
            renderCart();
        });

        const checkoutForm = document.createElement('form');
        checkoutForm.method = 'POST';
        checkoutForm.action = "{{ route('dashboard.payment') }}";
        checkoutForm.style.display = 'none';
        checkoutForm.innerHTML = `
            @csrf
            <input type="hidden" name="cart" id="cartPayload">
        `;
        document.body.appendChild(checkoutForm);

        const cartPayload = document.getElementById('cartPayload');

        document.getElementById('finalizePurchase').addEventListener('click', () => {
            if (cart.size === 0) {
                alert('Tu carrito esta vacio.');
                return;
            }

            const payload = Array.from(cart.values()).map(item => ({
                id: item.id,
                name: item.name,
                price: item.price,
                qty: item.qty,
            }));

            cartPayload.value = JSON.stringify(payload);
            checkoutForm.submit();
        });

        const historyModal = document.getElementById('historyModal');
        document.getElementById('historyBtn')?.addEventListener('click', () => {
            if (historyModal) historyModal.style.display = 'flex';
        });
        document.getElementById('closeHistory')?.addEventListener('click', () => {
            if (historyModal) historyModal.style.display = 'none';
        });

        applyCatalogFilters();
        updateBadge();
        renderCart();
    </script>
</body>
</html>
