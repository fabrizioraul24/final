@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Str;

    $user = Auth::user();
    $availableCount = $products->where('available_qty', '>', 0)->count();
    $totalProducts = $products->count();
    $productCatalogForJs = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price_for_buyer,
            'cat' => $product->category->id ?? 0,
            'stock' => (int) ($product->available_qty ?? 0),
            'img' => $product->getImageUrl(),
        ];
    })->values();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Comprador Minorista | PIL Bolivia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue: #0b4fc1;
            --blue-deep: #0a3f9f;
            --coral: #f25a59;
            --cream: #f7f0e2;
            --white: #ffffff;
            --ink: #0d2b5f;
            --soft-sky: #e4f2ff;
            --soft-pink: #f9d5e5;
            --soft-gold: #ffd06e;
            --soft-cream: #fff3d2;
            --dark-blue: #0c2b6e;
            --gray-light: #f1f5f9;
            --gray-border: #e2e8f0;
            --text-muted: #526484;
            --shadow-sm: 0 4px 14px rgba(11, 79, 193, 0.08);
            --shadow-md: 0 12px 30px rgba(11, 79, 193, 0.15);
            --shadow-lg: 0 24px 60px rgba(11, 79, 193, 0.22);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', "Segoe UI", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body {
            background-color: var(--cream);
            color: var(--ink);
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.5;
        }

        /* --- Global Scrollbar --- */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--cream); }
        ::-webkit-scrollbar-thumb { background: var(--blue); border-radius: 4px; }

        /* --- Header Topbar --- */
        .page-header {
            background: var(--blue);
            padding: 18px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(10, 63, 159, 0.25);
        }

        .brand-logo-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--white);
            padding: 8px 20px;
            border-radius: 999px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .brand-logo-text {
            font-size: 1.6rem;
            font-weight: 900;
            color: var(--blue);
            letter-spacing: -0.04em;
            line-height: 1;
        }

        .brand-tag {
            font-size: 0.72rem;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-link {
            color: var(--white);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            transition: opacity 0.2s ease;
        }

        .nav-link:hover {
            opacity: 0.85;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .buyer-logout-form {
            margin: 0;
            display: inline-flex;
        }

        /* --- Pill Buttons --- */
        .btn-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 24px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s ease;
            white-space: nowrap;
        }

        .btn-pill-white {
            background: rgba(255, 255, 255, 0.14);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.34);
            box-shadow: 0 4px 14px rgba(0,0,0,0.1);
        }

        .btn-pill-white:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.24);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .btn-pill-coral {
            background: var(--coral);
            color: var(--white);
            box-shadow: 0 8px 20px rgba(242, 90, 89, 0.3);
        }

        .btn-pill-coral:hover {
            transform: translateY(-2px);
            filter: brightness(1.08);
            box-shadow: 0 12px 28px rgba(242, 90, 89, 0.4);
        }

        .btn-pill-outline {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.4);
            color: var(--white);
        }

        .btn-pill-outline:hover {
            border-color: var(--white);
            background: rgba(255, 255, 255, 0.1);
        }

        .cart-trigger {
            position: relative;
        }

        .cart-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: var(--coral);
            color: var(--white);
            font-size: 0.75rem;
            font-weight: 900;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--white);
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        /* --- Hero Section (Exact PIL Landing Style) --- */
        .hero-banner {
            background: var(--blue);
            color: var(--white);
            position: relative;
            padding: 60px 72px 80px;
            overflow: hidden;
        }

        .backdrop-arc {
            position: absolute;
            top: -10%;
            left: -5%;
            width: 500px;
            height: 500px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            align-items: center;
            gap: 40px;
            max-width: 1300px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .hero-eyebrow {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.85rem;
            font-weight: 800;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.9);
        }

        .eyebrow-line {
            width: 36px;
            height: 3px;
            background: var(--coral);
            border-radius: 2px;
        }

        .hero-title {
            font-size: 4.8rem;
            font-weight: 900;
            line-height: 0.95;
            letter-spacing: -0.05em;
            margin-bottom: 24px;
            color: var(--white);
        }

        .hero-title span {
            display: block;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
            max-width: 580px;
            margin-bottom: 36px;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        /* --- Hero Visual Card --- */
        .hero-visual {
            position: relative;
            height: 420px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .visual-coral-accent {
            position: absolute;
            right: 0px;
            top: 20px;
            width: 170px;
            height: 380px;
            background: var(--coral);
            border-radius: 999px;
            z-index: 1;
        }

        .photo-frame {
            position: absolute;
            right: 40px;
            top: 0;
            width: 100%;
            max-width: 520px;
            height: 400px;
            border-radius: 170px 160px 110px 110px / 120px 150px 95px 95px;
            overflow: hidden;
            background: var(--soft-sky);
            box-shadow: 0 30px 60px rgba(3, 29, 79, 0.25);
            z-index: 2;
            border: 4px solid var(--white);
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .years-badge {
            position: absolute;
            left: 10px;
            top: 40px;
            width: 120px;
            height: 120px;
            background: var(--coral);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 14px 30px rgba(0,0,0,0.18);
            transform: rotate(-8deg);
            z-index: 4;
            line-height: 1;
            text-align: center;
        }

        .years-badge strong {
            font-size: 3.2rem;
            font-weight: 900;
            letter-spacing: -0.06em;
        }

        .years-badge span {
            font-size: 0.58rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .sabor-note {
            position: absolute;
            right: 20px;
            bottom: 10px;
            font-family: "Brush Script MT", "Segoe Script", cursive;
            font-size: 3.2rem;
            color: var(--white);
            transform: rotate(-6deg);
            z-index: 4;
            text-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        /* --- Ticker Banner --- */
        .ticker-banner {
            background: var(--coral);
            color: var(--white);
            overflow: hidden;
            padding: 14px 0;
            font-weight: 900;
            font-size: 0.95rem;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            box-shadow: inset 0 2px 6px rgba(0,0,0,0.1);
        }

        .ticker-track {
            display: flex;
            align-items: center;
            gap: 40px;
            white-space: nowrap;
            animation: ticker 30s linear infinite;
        }

        @keyframes ticker {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        /* --- Content Container --- */
        .catalog-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 48px 32px 90px;
        }

        /* --- Filters & Search Bar --- */
        .filter-panel {
            background: var(--white);
            padding: 24px 32px;
            border-radius: 32px 8px 32px 32px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 40px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            border: 1px solid rgba(11, 79, 193, 0.08);
        }

        .filter-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .search-field {
            flex: 1;
            min-width: 280px;
            position: relative;
        }

        .search-field i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--blue);
            font-size: 1.3rem;
        }

        .search-field input {
            width: 100%;
            padding: 14px 20px 14px 50px;
            border-radius: 999px;
            border: 2px solid var(--gray-border);
            background: var(--gray-light);
            font-size: 1rem;
            font-weight: 600;
            outline: none;
            transition: all 0.2s ease;
        }

        .search-field input:focus {
            border-color: var(--blue);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(11, 79, 193, 0.1);
        }

        .category-pills {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 6px;
        }

        .cat-pill {
            padding: 10px 22px;
            border-radius: 999px;
            border: 1px solid var(--gray-border);
            background: var(--gray-light);
            color: var(--ink);
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .cat-pill:hover {
            border-color: var(--blue);
            color: var(--blue);
            background: var(--soft-sky);
        }

        .cat-pill.active {
            background: var(--blue);
            color: var(--white);
            border-color: var(--blue);
            box-shadow: 0 4px 12px rgba(11, 79, 193, 0.25);
        }

        /* --- Product Catalog Grid --- */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 300px), 1fr));
            gap: 22px;
        }

        .product-card {
            background: var(--white);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            min-height: 100%;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(11, 79, 193, 0.1);
            border-radius: 22px;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            border-color: var(--blue);
        }

        .product-image-box {
            height: 190px;
            background: linear-gradient(180deg, #eef6ff 0%, #ffffff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 22px 18px 12px;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(11, 79, 193, 0.08);
        }

        .product-image-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.4s ease;
        }

        .product-card:hover .product-image-box img {
            transform: scale(1.05);
        }

        .stock-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            box-shadow: 0 8px 18px rgba(13, 43, 95, 0.12);
            z-index: 2;
        }

        .stock-available { background: #dcfce7; color: #166534; }
        .stock-out { background: #fee2e2; color: #b91c1c; }

        .product-card-body {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 20px;
        }

        .product-cat-name {
            font-size: 0.78rem;
            font-weight: 800;
            color: var(--blue);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }

        .product-name {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .product-desc {
            font-size: 0.88rem;
            color: var(--text-muted);
            margin-bottom: 18px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.8em;
        }

        .product-footer {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .price-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding-top: 14px;
            border-top: 1px solid var(--gray-border);
        }

        .price-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .price-value {
            font-size: 1.45rem;
            font-weight: 900;
            color: var(--blue);
        }

        .qty-picker {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--gray-light);
            border-radius: 14px;
            padding: 4px;
            border: 1px solid var(--gray-border);
        }

        .qty-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: var(--white);
            color: var(--blue);
            font-size: 1.1rem;
            font-weight: 900;
            cursor: pointer;
            display: grid;
            place-items: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            transition: all 0.2s ease;
        }

        .qty-btn:hover {
            background: var(--blue);
            color: var(--white);
        }

        .qty-input {
            width: 50px;
            border: none;
            background: transparent;
            text-align: center;
            font-weight: 900;
            font-size: 1rem;
            color: var(--ink);
            outline: none;
        }

        /* --- Drawer Shopping Cart --- */
        .cart-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(13, 43, 95, 0.5);
            backdrop-filter: blur(6px);
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .cart-backdrop.active {
            opacity: 1;
            pointer-events: all;
        }

        .cart-drawer {
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            left: 0;
            max-width: none;
            height: 100%;
            background: var(--white);
            z-index: 1001;
            box-shadow: 0 20px 70px rgba(0,0,0,0.22);
            transform: translateX(100%);
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .cart-drawer.active {
            transform: translateX(0);
        }

        .drawer-header {
            padding: 22px clamp(18px, 4vw, 56px);
            background: var(--blue);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .drawer-header h3 {
            font-size: 1.4rem;
            font-weight: 900;
        }

        .drawer-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: var(--white);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: grid;
            place-items: center;
            font-size: 1.2rem;
            transition: background 0.2s ease;
        }

        .drawer-close:hover {
            background: rgba(255, 255, 255, 0.35);
        }

        .drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 28px clamp(18px, 4vw, 56px);
            display: flex;
            flex-direction: column;
            gap: 22px;
            background: #f7fbff;
        }

        .checkout-progress {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .checkout-step-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.16);
            color: rgba(255,255,255,0.75);
            font-size: 0.78rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .checkout-step-pill.active {
            background: var(--white);
            color: var(--blue);
        }

        .checkout-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(280px, 360px);
            gap: 24px;
            align-items: start;
        }

        .checkout-main,
        .checkout-summary,
        .checkout-suggestions {
            background: var(--white);
            border: 1px solid var(--gray-border);
            border-radius: 22px;
            box-shadow: var(--shadow-sm);
        }

        .checkout-main {
            padding: 24px;
        }

        .checkout-summary {
            position: sticky;
            top: 24px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .checkout-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
        }

        .checkout-section-title h4 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 900;
            color: var(--ink);
        }

        .checkout-suggestions {
            padding: 22px;
        }

        .suggestions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 14px;
        }

        .suggestion-card {
            border: 1px solid var(--gray-border);
            border-radius: 16px;
            padding: 12px;
            display: grid;
            grid-template-columns: 58px 1fr;
            gap: 12px;
            align-items: center;
            background: #fbfdff;
        }

        .suggestion-card img {
            width: 58px;
            height: 58px;
            object-fit: contain;
            border-radius: 12px;
            background: var(--white);
        }

        .suggestion-card strong {
            display: block;
            font-size: 0.86rem;
            line-height: 1.25;
            color: var(--ink);
            margin-bottom: 4px;
        }

        .suggestion-card span {
            display: block;
            font-size: 0.8rem;
            font-weight: 900;
            color: var(--blue);
            margin-bottom: 8px;
        }

        .suggestion-card button {
            border: none;
            background: var(--blue);
            color: var(--white);
            border-radius: 999px;
            padding: 7px 10px;
            font-size: 0.76rem;
            font-weight: 900;
            cursor: pointer;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: var(--gray-light);
            border-radius: 20px;
            border: 1px solid var(--gray-border);
        }

        .cart-item img {
            width: 64px;
            height: 64px;
            object-fit: contain;
            background: var(--white);
            border-radius: 12px;
            padding: 4px;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 4px;
        }

        .cart-item-price {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--blue);
        }

        .cart-item-remove {
            background: transparent;
            border: none;
            color: var(--coral);
            cursor: pointer;
            font-size: 1.2rem;
            padding: 4px;
        }

        .drawer-footer {
            padding: 24px clamp(18px, 4vw, 56px);
            border-top: 2px dashed var(--gray-border);
            background: #fafcff;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            color: var(--text-muted);
        }

        .cart-summary-row.total {
            font-size: 1.3rem;
            font-weight: 900;
            color: var(--ink);
            border-top: 1px solid var(--gray-border);
            padding-top: 12px;
        }

        .cart-summary-row.total strong {
            color: var(--blue);
        }

        /* --- Payment & Receipt Modals --- */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(13, 43, 95, 0.65);
            backdrop-filter: blur(8px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .modal-card {
            background: var(--white);
            width: 100%;
            max-width: 680px;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 40px 8px 40px 40px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            animation: popIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        #paymentModal .modal-card,
        #successReceiptModal .modal-card {
            max-width: min(1120px, calc(100vw - 36px));
            width: 100%;
            border-radius: 24px;
        }

        #paymentModal .modal-body,
        #successReceiptModal .modal-body {
            background: #f7fbff;
        }

        @keyframes popIn {
            from { transform: scale(0.9) translateY(20px); }
            to { transform: scale(1) translateY(0); }
        }

        .modal-header {
            background: var(--blue);
            color: var(--white);
            padding: 24px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            font-size: 1.5rem;
            font-weight: 900;
        }

        .modal-body {
            padding: 32px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* Payment Tab Switcher */
        .payment-tabs {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            background: var(--gray-light);
            padding: 6px;
            border-radius: 999px;
        }

        .pay-tab {
            padding: 12px;
            border-radius: 999px;
            border: none;
            background: transparent;
            font-weight: 800;
            font-size: 0.88rem;
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .pay-tab.active {
            background: var(--blue);
            color: var(--white);
            box-shadow: 0 4px 12px rgba(11, 79, 193, 0.25);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 0.88rem;
            font-weight: 800;
            color: var(--ink);
        }

        .form-control {
            padding: 14px 18px;
            border-radius: 16px;
            border: 2px solid var(--gray-border);
            font-size: 0.95rem;
            font-weight: 600;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--blue);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        /* QR Box Display */
        .qr-display-box {
            background: radial-gradient(circle, var(--soft-sky) 0%, #f0f6ff 100%);
            border: 2px dashed var(--blue);
            border-radius: 24px;
            padding: 24px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        .qr-image-frame {
            background: var(--white);
            padding: 16px;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            display: inline-block;
        }

        .qr-image-frame img {
            width: 180px;
            height: 180px;
            display: block;
        }

        /* Cash Box Display */
        .cash-display-box {
            background: var(--soft-cream);
            border: 2px dashed var(--soft-gold);
            border-radius: 24px;
            padding: 24px;
            text-align: center;
            color: #854d0e;
        }

        /* --- Printable Receipt Voucher --- */
        .receipt-card {
            background: var(--white);
            border-radius: 24px;
            border: 2px solid var(--blue);
            overflow: hidden;
        }

        .receipt-header {
            background: var(--blue);
            color: var(--white);
            padding: 24px;
            text-align: center;
        }

        .receipt-body {
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .receipt-qr-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--gray-light);
            padding: 16px 24px;
            border-radius: 18px;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
        }

        .receipt-table th, .receipt-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--gray-border);
            font-size: 0.9rem;
        }

        .receipt-table th {
            background: var(--soft-sky);
            color: var(--blue);
            font-weight: 800;
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .hero-grid { grid-template-columns: 1fr; text-align: center; }
            .hero-eyebrow { justify-content: center; }
            .hero-actions { justify-content: center; }
            .hero-visual { display: none; }
            .hero-title { font-size: 3.4rem; }
            .page-header { padding: 14px 20px; }
            .catalog-container { padding: 24px 16px 60px; }
            .drawer-header,
            .modal-header {
                align-items: flex-start;
                flex-direction: column;
            }
            .checkout-layout {
                grid-template-columns: 1fr;
            }
            .checkout-summary {
                position: static;
            }
            .receipt-qr-section {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 600px) {
            .payment-tabs { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
            .cart-drawer { width: 100%; }
            .topbar-actions { gap: 8px; }
            .buyer-logout-form .btn-pill { padding-inline: 14px; }
            .buyer-logout-form .btn-pill span { display: none; }
            .checkout-main,
            .checkout-summary,
            .checkout-suggestions,
            .modal-body {
                padding: 18px;
            }
            .cart-item {
                align-items: flex-start;
                flex-direction: column;
            }
            .checkout-progress {
                gap: 6px;
            }
            .checkout-step-pill {
                font-size: 0.68rem;
                padding: 7px 9px;
            }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="page-header">
        <a href="{{ route('dashboard.comprador') }}" class="brand-logo-pill">
            <span class="brand-logo-text">PIL</span>
            <span class="brand-tag">Bolivia</span>
        </a>

        <nav class="header-nav" style="display: none; @media(min-width: 768px){display: flex;}">
            <a href="#cat-section" class="nav-link">Catálogo</a>
            <a href="#promos" class="nav-link">Puntos de Recojo</a>
            <a href="javascript:void(0)" onclick="openHistoryModal()" class="nav-link"><i class="ri-history-line"></i> Mis Pedidos</a>
        </nav>

        <div class="topbar-actions">
            <button class="btn-pill btn-pill-outline cart-trigger" onclick="toggleCartDrawer()" style="position: relative;">
                <i class="ri-shopping-cart-2-fill" style="font-size: 1.2rem;"></i>
                <span>Carrito</span>
                <span class="cart-badge" id="cartBadgeCount">0</span>
            </button>
            
            <button class="btn-pill btn-pill-white" onclick="openHistoryModal()">
                <i class="ri-user-smile-fill"></i>
                <span style="display: none; @media(min-width: 600px){display: inline;}">{{ $user->name ?? 'Comprador PIL' }}</span>
            </button>

            <form method="POST" action="{{ route('logout') }}" class="buyer-logout-form">
                @csrf
                <button type="submit" class="btn-pill btn-pill-outline">
                    <i class="ri-logout-circle-r-line"></i>
                    <span>Cerrar sesion</span>
                </button>
            </form>
        </div>
    </header>

    <!-- Main Hero Banner (Exact style as uploaded PIL landing page) -->
    <section class="hero-banner">
        <div class="backdrop-arc"></div>
        <div class="hero-grid">
            <div class="hero-left">
                <div class="hero-eyebrow">
                    <span class="eyebrow-line"></span>
                    <span>DESDE 1960 · VENTA DIRECTA AL MINORISTA</span>
                </div>
                <h1 class="hero-title">
                    <span>Alimentando</span>
                    <span>a Bolivia.</span>
                </h1>
                <p class="hero-subtitle">
                    65 años creciendo junto a las familias bolivianas. Realiza tu compra minorista desde tu celular o PC con pago seguro y retira tu pedido en nuestra planta o tienda autorizada.
                </p>
                <div class="hero-actions">
                    <a href="#cat-section" class="btn-pill btn-pill-coral">
                        <span>Ver productos disponibles</span>
                        <i class="ri-arrow-down-line"></i>
                    </a>
                    <button class="btn-pill btn-pill-outline" onclick="openHistoryModal()">
                        <i class="ri-ticket-2-line"></i>
                        <span>Mis Recibos</span>
                    </button>
                </div>
            </div>

            <div class="hero-visual">
                <div class="visual-coral-accent"></div>
                <div class="years-badge">
                    <strong>65</strong>
                    <span>Años contigo</span>
                </div>
                <div class="photo-frame">
                    <img src="https://images.unsplash.com/photo-1550583724-b2692b85b150?auto=format&fit=crop&w=850&q=80" alt="Productos PIL Andina">
                </div>
                <div class="sabor-note">sabor de casa</div>
            </div>
        </div>
    </section>

    <!-- Moving Ticker Ribbon -->
    <div class="ticker-banner">
        <div class="ticker-track">
            <span>NUTRICIÓN + CONFIANZA + BOLIVIA + CALIDAD + RECOJO EN PLANTA + PIL CONTIGO + COMPRA DIRECTA</span>
            <span>NUTRICIÓN + CONFIANZA + BOLIVIA + CALIDAD + RECOJO EN PLANTA + PIL CONTIGO + COMPRA DIRECTA</span>
        </div>
    </div>

    <!-- Main Catalog Container -->
    <main class="catalog-container" id="cat-section">

        <!-- Search & Category Filters -->
        <div class="filter-panel">
            <div class="filter-top">
                <div class="search-field">
                    <i class="ri-search-2-line"></i>
                    <input type="text" id="searchInput" placeholder="Buscar leche, yogurt, queso, mantequilla..." oninput="filterProducts()">
                </div>
                <div style="font-weight: 800; color: var(--blue); font-size: 0.95rem;">
                    <i class="ri-store-3-line"></i> {{ $availableCount }} productos listos para recojo
                </div>
            </div>

            <div class="category-pills">
                <button class="cat-pill active" onclick="selectCategory(0, this)">Todos los productos</button>
                @foreach($categories as $cat)
                    <button class="cat-pill" onclick="selectCategory({{ $cat['id'] }}, this)">
                        {{ $cat['name'] }} ({{ $cat['count'] }})
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Product Cards Grid -->
        <div class="products-grid" id="productsGrid">
            @forelse($products as $product)
                @php
                    $inStock = ($product->available_qty ?? 0) > 0;
                    $stockClass = $inStock ? 'stock-available' : 'stock-out';
                    $stockText = $inStock ? 'Stock disponible' : 'Stock no disponible';
                    $stockIcon = $inStock ? 'ri-checkbox-circle-fill' : 'ri-close-circle-fill';
                @endphp
                <div class="product-card" 
                     data-id="{{ $product->id }}" 
                     data-name="{{ e($product->name) }}" 
                     data-price="{{ $product->price_for_buyer }}" 
                     data-cat="{{ $product->category->id ?? 0 }}"
                     data-stock="{{ $product->available_qty ?? 0 }}"
                     data-img="{{ $product->getImageUrl() }}">
                    
                    <div class="product-image-box">
                        <span class="stock-badge {{ $stockClass }}"><i class="{{ $stockIcon }}"></i>{{ $stockText }}</span>
                        <img src="{{ $product->getImageUrl() }}" alt="{{ $product->name }}" loading="lazy">
                    </div>

                    <div class="product-card-body">
                    <span class="product-cat-name">{{ $product->category->name ?? 'PIL' }}</span>
                    <h3 class="product-name">{{ $product->name }}</h3>
                    <p class="product-desc">{{ $product->description ?: 'Producto PIL de alta calidad y nutrición para la familia.' }}</p>

                    <div class="product-footer">
                        <div class="price-row">
                            <div>
                                <span class="price-label">Precio Minorista</span>
                                <div class="price-value">Bs {{ number_format($product->price_for_buyer, 2) }}</div>
                            </div>
                            @if($product->nearest_expire)
                                <div style="text-align: right; font-size: 0.72rem; color: var(--coral); font-weight: 800;">
                                    <i class="ri-time-line"></i> Vence {{ $product->nearest_expire }}
                                </div>
                            @endif
                        </div>

                        @if($inStock)
                            <div class="qty-picker">
                                <button type="button" class="qty-btn" onclick="changeQtyCard({{ $product->id }}, -1)">-</button>
                                <input type="number" id="qty_input_{{ $product->id }}" class="qty-input" value="1" min="1" max="{{ $product->available_qty }}">
                                <button type="button" class="qty-btn" onclick="changeQtyCard({{ $product->id }}, 1)">+</button>
                            </div>

                            <button type="button" class="btn-pill btn-pill-coral" style="width: 100%;" onclick="addToCart({{ $product->id }})">
                                <i class="ri-shopping-bag-3-fill"></i>
                                <span>Agregar al carrito</span>
                            </button>
                        @else
                            <button type="button" class="btn-pill" style="width: 100%; background: #e2e8f0; color: #94a3b8; cursor: not-allowed;" disabled>
                                <i class="ri-error-warning-line"></i> Sin stock por el momento
                            </button>
                        @endif
                    </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: var(--white); border-radius: 30px;">
                    <i class="ri-inbox-archive-line" style="font-size: 4rem; color: var(--blue);"></i>
                    <h3 style="font-size: 1.5rem; margin-top: 16px;">No hay productos disponibles por ahora</h3>
                    <p style="color: var(--text-muted);">Pronto se actualizará el stock de la planta PIL.</p>
                </div>
            @endforelse
        </div>
    </main>

    <!-- Full-Screen Checkout Step 1 -->
    <div class="cart-backdrop" id="cartBackdrop" onclick="toggleCartDrawer()"></div>
    <div class="cart-drawer" id="cartDrawer">
        <div class="drawer-header">
            <div>
                <h3><i class="ri-shopping-cart-2-line"></i> Checkout PIL</h3>
                <div class="checkout-progress">
                    <span class="checkout-step-pill active"><i class="ri-shopping-basket-2-fill"></i> Paso 1 Carrito</span>
                    <span class="checkout-step-pill"><i class="ri-secure-payment-fill"></i> Paso 2 Pago</span>
                    <span class="checkout-step-pill"><i class="ri-receipt-fill"></i> Paso 3 Recibo</span>
                </div>
            </div>
            <button class="drawer-close" onclick="toggleCartDrawer()"><i class="ri-close-line"></i></button>
        </div>

        <div class="drawer-body" id="cartItemsList">
            <div class="checkout-layout">
                <div style="display:flex; flex-direction:column; gap:22px;">
                    <section class="checkout-main">
                        <div class="checkout-section-title">
                            <h4>Productos seleccionados</h4>
                            <button type="button" class="btn-pill btn-pill-outline" style="color: var(--ink); border-color: var(--gray-border); padding: 9px 16px;" onclick="clearCart()">Vaciar</button>
                        </div>
                        <div id="cartItemsPanel"></div>
                    </section>

                    <section class="checkout-suggestions">
                        <div class="checkout-section-title">
                            <h4>Sugerencias para completar tu compra</h4>
                            <span style="font-size:0.82rem; font-weight:800; color:var(--text-muted);">Basado en lo que llevas</span>
                        </div>
                        <div class="suggestions-grid" id="cartSuggestionsList"></div>
                    </section>
                </div>

                <aside class="checkout-summary">
                    <div class="cart-summary-row">
                        <span>Subtotal productos:</span>
                        <span id="cartSubtotalText">Bs 0.00</span>
                    </div>
                    <div class="cart-summary-row">
                        <span>Costo de envio / recojo:</span>
                        <span style="color: var(--blue); font-weight: 800;">Bs 0.00 (Recojo Gratis)</span>
                    </div>
                    <div class="cart-summary-row total">
                        <span>Total a Pagar:</span>
                        <strong id="cartTotalText">Bs 0.00</strong>
                    </div>

                    <button type="button" class="btn-pill btn-pill-coral" style="width: 100%;" onclick="openPaymentModal()">
                        <span>Continuar al paso 2</span>
                        <i class="ri-arrow-right-line"></i>
                    </button>
                </aside>
            </div>
        </div>
    </div>
    <!-- Modal Pasarela de Pago (Payment Gateway) -->
    <div class="modal-overlay" id="paymentModal">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3><i class="ri-secure-payment-line"></i> Checkout PIL</h3>
                    <div class="checkout-progress">
                        <span class="checkout-step-pill"><i class="ri-shopping-basket-2-fill"></i> Paso 1 Carrito</span>
                        <span class="checkout-step-pill active"><i class="ri-secure-payment-fill"></i> Paso 2 Pago</span>
                        <span class="checkout-step-pill"><i class="ri-receipt-fill"></i> Paso 3 Recibo</span>
                    </div>
                </div>
                <button class="drawer-close" onclick="closePaymentModal()"><i class="ri-close-line"></i></button>
            </div>

            <div class="modal-body">
                <!-- Summary bar -->
                <div style="background: var(--soft-sky); padding: 16px 24px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--blue); text-transform: uppercase;">Total de tu compra:</span>
                        <div style="font-size: 1.8rem; font-weight: 900; color: var(--blue);" id="modalTotalPay">Bs 0.00</div>
                    </div>
                    <div style="text-align: right;">
                        <span class="btn-pill btn-pill-white" style="font-size: 0.8rem; padding: 6px 14px;">
                            <i class="ri-shield-check-fill" style="color: var(--blue);"></i> Pago Seguro TLS
                        </span>
                    </div>
                </div>

                <!-- Form targeting payment confirmation route -->
                <form action="{{ route('dashboard.payment.process') }}" method="POST" id="checkoutForm">
                    @csrf
                    <input type="hidden" name="cart" id="hiddenCartInput">
                    <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="Tarjeta">

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="pickupLocation"><i class="ri-map-pin-2-fill" style="color: var(--coral);"></i> Selecciona el Punto de Recojo PIL:</label>
                        <select name="warehouse_pickup" id="pickupLocation" class="form-control" required>
                            <option value="Planta Cochabamba - Av. Blanco Galindo Km 10">Planta Cochabamba - Av. Blanco Galindo Km 10 (Principal)</option>
                            <option value="Agencia Central La Paz - Av. 6 de Marzo El Alto">Agencia Central La Paz - Av. 6 de Marzo El Alto</option>
                            <option value="Planta Santa Cruz - Parque Industrial PI-27">Planta Santa Cruz - Parque Industrial PI-27</option>
                            <option value="Sucursal Sucre - Av. Jaime Mendoza">Sucursal Sucre - Av. Jaime Mendoza</option>
                            <option value="Agencia Tarija - Av. Las Américas">Agencia Tarija - Av. Las Américas</option>
                        </select>
                    </div>

                    <!-- Payment Method Tabs -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Elige tu método de pago:</label>
                        <div class="payment-tabs">
                            <button type="button" class="pay-tab active" onclick="switchPayTab('Tarjeta', this)">
                                <i class="ri-bank-card-fill"></i> Tarjeta
                            </button>
                            <button type="button" class="pay-tab" onclick="switchPayTab('QR', this)">
                                <i class="ri-qr-code-line"></i> QR Simple
                            </button>
                            <button type="button" class="pay-tab" onclick="switchPayTab('Efectivo', this)">
                                <i class="ri-money-dollar-circle-fill"></i> Efectivo
                            </button>
                        </div>
                    </div>

                    <!-- Method 1: Credit/Debit Card -->
                    <div id="payFormTarjeta" class="pay-method-panel">
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label>Nombre del titular en la tarjeta:</label>
                            <input type="text" class="form-control" placeholder="Ej. Juan Pérez" value="{{ $user->name ?? '' }}" required id="cardHolderInput">
                        </div>
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label>Número de tarjeta:</label>
                            <input type="text" class="form-control" placeholder="4000 1234 5678 9010" maxlength="19" required id="cardNumberInput">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Vencimiento (MM/AA):</label>
                                <input type="text" class="form-control" placeholder="12/28" maxlength="5" required id="cardExpInput">
                            </div>
                            <div class="form-group">
                                <label>CVV / CVC:</label>
                                <input type="password" class="form-control" placeholder="123" maxlength="4" required id="cardCvvInput">
                            </div>
                        </div>
                    </div>

                    <!-- Method 2: QR Payment -->
                    <div id="payFormQR" class="pay-method-panel" style="display: none;">
                        <div class="qr-display-box">
                            <h4 style="font-weight: 800; color: var(--blue);">Escanea el Código QR para Pagar</h4>
                            <p style="font-size: 0.88rem; color: var(--text-muted);">Abre la app de tu banco (BNB, BCP, Banco Unión, Mercantil, etc.) y escanea el código:</p>
                            
                            <div class="qr-image-frame">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=PIL-BOLIVIA-PAGO-COMPRADOR-MINORISTA-BS" id="qrImageCode" alt="QR de Pago PIL">
                            </div>
                            <div style="font-weight: 900; color: var(--blue); font-size: 1.1rem;" id="qrTotalLabel">Monto: Bs 0.00</div>
                            <small style="color: var(--text-muted);"><i class="ri-time-line"></i> El código es válido por 15 minutos</small>
                        </div>
                    </div>

                    <!-- Method 3: Cash Payment -->
                    <div id="payFormEfectivo" class="pay-method-panel" style="display: none;">
                        <div class="cash-display-box">
                            <i class="ri-hand-coin-fill" style="font-size: 3rem; margin-bottom: 12px; display: block;"></i>
                            <h4 style="font-weight: 800; margin-bottom: 8px;">Pago en Caja de Almacén PIL</h4>
                            <p style="font-size: 0.9rem; line-height: 1.5;">
                                Se generará tu <strong>Recibo de Reserva</strong>. Podrás dirigirte al punto de recojo seleccionado y realizar el pago exacto en efectivo en la ventanilla para retirar tus productos.
                            </p>
                        </div>
                    </div>

                    <div style="margin-top: 28px; display: flex; gap: 16px;">
                        <button type="button" class="btn-pill btn-pill-outline" style="color: var(--ink); border-color: var(--gray-border);" onclick="closePaymentModal()">Volver al carrito</button>
                        <button type="submit" class="btn-pill btn-pill-coral" style="flex: 1;" id="submitPayBtn">
                            <i class="ri-checkbox-circle-fill"></i>
                            <span>Confirmar Pago y Generar Recibo</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Historial de Pedidos -->
    <div class="modal-overlay" id="historyModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="ri-history-line"></i> Mis Pedidos y Recibos</h3>
                <button class="drawer-close" onclick="closeHistoryModal()"><i class="ri-close-line"></i></button>
            </div>
            <div class="modal-body">
                @if(isset($history) && $history->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        @foreach($history as $ord)
                            <div style="background: var(--gray-light); padding: 20px; border-radius: 20px; border: 1px solid var(--gray-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                                <div>
                                    <div style="font-weight: 900; color: var(--blue); font-size: 1.1rem;">Recibo #{{ $ord->receipt_number }}</div>
                                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
                                        Emitido el: {{ optional($ord->issued_at)->format('d/m/Y H:i') }} | Método: {{ ucfirst($ord->payment_method) }}
                                    </div>
                                    <div style="margin-top: 8px;">
                                        @if($ord->payment_status === 'completado')
                                            <span style="background: var(--soft-sky); color: var(--blue); padding: 4px 12px; border-radius: 999px; font-weight: 800; font-size: 0.75rem;">
                                                <i class="ri-check-double-line"></i> PAGADO
                                            </span>
                                        @else
                                            <span style="background: var(--soft-gold); color: #854d0e; padding: 4px 12px; border-radius: 999px; font-weight: 800; font-size: 0.75rem;">
                                                <i class="ri-time-line"></i> PENDIENTE EN CAJA
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.4rem; font-weight: 900; color: var(--ink);">Bs {{ number_format($ord->total, 2) }}</div>
                                    <a href="{{ route('dashboard.payment.receipt', $ord->receipt_number) }}" target="_blank" class="btn-pill btn-pill-white" style="font-size: 0.85rem; padding: 8px 16px; margin-top: 8px;">
                                        <i class="ri-file-list-3-line"></i> Ver Recibo
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 40px;">
                        <i class="ri-ticket-2-line" style="font-size: 3.5rem; color: var(--blue);"></i>
                        <h4 style="font-size: 1.2rem; margin-top: 12px;">Aún no realizaste ningún pedido</h4>
                        <p style="color: var(--text-muted);">Selecciona tus productos en la tienda para hacer tu primera compra.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Printable Receipt Modal (Triggered after successful checkout) -->
    @if(session('payment_success') && session('receipt_number'))
        @php
            $successOrder = \App\Models\BuyerOrder::with('items')->where('receipt_number', session('receipt_number'))->first();
        @endphp
        <div class="modal-overlay active" id="successReceiptModal">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3><i class="ri-checkbox-circle-fill"></i> Checkout PIL</h3>
                        <div class="checkout-progress">
                            <span class="checkout-step-pill"><i class="ri-shopping-basket-2-fill"></i> Paso 1 Carrito</span>
                            <span class="checkout-step-pill"><i class="ri-secure-payment-fill"></i> Paso 2 Pago</span>
                            <span class="checkout-step-pill active"><i class="ri-receipt-fill"></i> Paso 3 Recibo</span>
                        </div>
                    </div>
                    <a href="{{ route('dashboard.comprador') }}" class="drawer-close" style="text-decoration:none;"><i class="ri-close-line"></i></a>
                </div>
                <div class="modal-body">
                    <div class="receipt-card">
                        <div class="receipt-header">
                            <i class="ri-checkbox-circle-fill" style="font-size: 3.5rem; color: var(--coral);"></i>
                            <h2 style="font-weight: 900; margin-top: 8px;">Pedido finalizado</h2>
                            <p style="font-size: 0.9rem; opacity: 0.9;">Conserva este recibo para recoger tu pedido en almacen</p>
                        </div>
                        <div class="receipt-body">
                            <div class="receipt-qr-section">
                                <div>
                                    <span style="font-size: 0.75rem; font-weight: 800; color: var(--blue); text-transform: uppercase;">Nro. de recibo</span>
                                    <div style="font-size: 1.3rem; font-weight: 900; color: var(--ink);">{{ session('receipt_number') }}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Emitido: {{ optional($successOrder?->issued_at)->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</div>
                                </div>
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=96x96&data={{ session('receipt_number') }}" alt="QR Validation" style="border-radius: 10px; border: 2px solid var(--blue);">
                            </div>

                            @if($successOrder)
                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:12px;">
                                    <div style="background:var(--gray-light); border-radius:16px; padding:14px;">
                                        <span style="font-size:0.72rem; font-weight:900; color:var(--text-muted); text-transform:uppercase;">Metodo</span>
                                        <strong style="display:block; color:var(--ink);">{{ ucfirst($successOrder->payment_method) }}</strong>
                                    </div>
                                    <div style="background:var(--gray-light); border-radius:16px; padding:14px;">
                                        <span style="font-size:0.72rem; font-weight:900; color:var(--text-muted); text-transform:uppercase;">Estado</span>
                                        <strong style="display:block; color:var(--blue);">{{ $successOrder->payment_status === 'completado' ? 'Pagado' : 'Pendiente en caja' }}</strong>
                                    </div>
                                    <div style="background:var(--gray-light); border-radius:16px; padding:14px;">
                                        <span style="font-size:0.72rem; font-weight:900; color:var(--text-muted); text-transform:uppercase;">Total</span>
                                        <strong style="display:block; color:var(--blue);">Bs {{ number_format($successOrder->total, 2) }}</strong>
                                    </div>
                                </div>

                                <table class="receipt-table">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Precio</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($successOrder->items as $item)
                                            <tr>
                                                <td>{{ $item->product_name }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>Bs {{ number_format($item->unit_price, 2) }}</td>
                                                <td>Bs {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            <div style="display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                                <a href="{{ route('dashboard.payment.receipt', session('receipt_number')) }}" target="_blank" class="btn-pill btn-pill-white" style="flex: 1; border: 1px solid var(--blue);">
                                    <i class="ri-printer-line"></i> Ver Recibo Completo
                                </a>
                                <a href="{{ route('dashboard.payment.receipt.download', session('receipt_number')) }}" class="btn-pill btn-pill-coral" style="flex: 1;">
                                    <i class="ri-download-2-line"></i> Descargar PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!-- JavaScript Logic -->
    <script>
        // Shopping Cart state saved in localStorage for seamless experience
        @if(session('payment_success') && session('receipt_number'))
            localStorage.removeItem('pil_cart');
        @endif
        let cart = JSON.parse(localStorage.getItem('pil_cart') || '[]');
        const productCatalog = @json($productCatalogForJs);

        document.addEventListener('DOMContentLoaded', () => {
            renderCart();
        });

        // Quantity control on Product Cards
        function changeQtyCard(productId, delta) {
            const input = document.getElementById(`qty_input_${productId}`);
            if (!input) return;
            let current = parseInt(input.value) || 1;
            let min = parseInt(input.min) || 1;
            let max = parseInt(input.max) || 999;
            current += delta;
            if (current < min) current = min;
            if (current > max) current = max;
            input.value = current;
        }

        // Add item to cart
        function addToCart(productId) {
            const card = document.querySelector(`.product-card[data-id="${productId}"]`);
            if (!card) return;

            const name = card.getAttribute('data-name');
            const price = parseFloat(card.getAttribute('data-price'));
            const stock = parseInt(card.getAttribute('data-stock'));
            const img = card.getAttribute('data-img');
            const qtyInput = document.getElementById(`qty_input_${productId}`);
            const qtyToAdd = parseInt(qtyInput ? qtyInput.value : 1) || 1;

            let existing = cart.find(item => item.id === productId);
            if (existing) {
                existing.stock = stock;
                if (existing.qty + qtyToAdd > stock) {
                    alert('No puedes agregar mas unidades de este producto por disponibilidad de stock.');
                    existing.qty = stock;
                } else {
                    existing.qty += qtyToAdd;
                }
            } else {
                cart.push({
                    id: productId,
                    name: name,
                    price: price,
                    qty: Math.min(qtyToAdd, stock),
                    img: img,
                    stock: stock
                });
            }

            saveCart();
            renderCart();
            toggleCartDrawer(true);
        }

        // Save cart to LocalStorage
        function saveCart() {
            localStorage.setItem('pil_cart', JSON.stringify(cart));
        }

        // Render Cart items in drawer
        function renderCart() {
            const listContainer = document.getElementById('cartItemsPanel');
            const badgeCount = document.getElementById('cartBadgeCount');
            const subtotalText = document.getElementById('cartSubtotalText');
            const totalText = document.getElementById('cartTotalText');
            const modalTotalPay = document.getElementById('modalTotalPay');
            const hiddenCartInput = document.getElementById('hiddenCartInput');

            if (!listContainer) return;

            listContainer.innerHTML = '';
            let totalQty = 0;
            let subtotal = 0;

            if (cart.length === 0) {
                listContainer.innerHTML = `
                    <div style="text-align: center; padding: 40px 20px;">
                        <i class="ri-shopping-basket-line" style="font-size: 4rem; color: var(--blue); opacity: 0.4;"></i>
                        <p style="margin-top: 12px; font-weight: 700; color: var(--text-muted);">Tu carrito está vacío.</p>
                        <small style="color: var(--text-muted);">¡Explora el catálogo y agrega tus productos PIL favoritos!</small>
                    </div>
                `;
            } else {
                cart.forEach((item, index) => {
                    totalQty += item.qty;
                    const itemTotal = item.price * item.qty;
                    subtotal += itemTotal;

                    const itemEl = document.createElement('div');
                    itemEl.className = 'cart-item';
                    itemEl.innerHTML = `
                        <img src="${item.img}" alt="${item.name}">
                        <div class="cart-item-info">
                            <div class="cart-item-title">${item.name}</div>
                            <div class="cart-item-price">Bs ${item.price.toFixed(2)} x ${item.qty} = <strong>Bs ${itemTotal.toFixed(2)}</strong></div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <button type="button" class="qty-btn" style="width: 28px; height: 28px; font-size: 0.8rem;" onclick="updateCartQty(${index}, -1)">-</button>
                            <span style="font-weight: 900; font-size: 0.9rem;">${item.qty}</span>
                            <button type="button" class="qty-btn" style="width: 28px; height: 28px; font-size: 0.8rem;" onclick="updateCartQty(${index}, 1)">+</button>
                            <button type="button" class="cart-item-remove" onclick="removeCartItem(${index})"><i class="ri-delete-bin-line"></i></button>
                        </div>
                    `;
                    listContainer.appendChild(itemEl);
                });
            }

            if (badgeCount) badgeCount.textContent = totalQty;
            if (subtotalText) subtotalText.textContent = `Bs ${subtotal.toFixed(2)}`;
            if (totalText) totalText.textContent = `Bs ${subtotal.toFixed(2)}`;
            if (modalTotalPay) modalTotalPay.textContent = `Bs ${subtotal.toFixed(2)}`;
            if (hiddenCartInput) hiddenCartInput.value = JSON.stringify(cart);

            const qrTotalLabel = document.getElementById('qrTotalLabel');
            if (qrTotalLabel) qrTotalLabel.textContent = `Monto a Pagar: Bs ${subtotal.toFixed(2)}`;
            const qrImageCode = document.getElementById('qrImageCode');
            if (qrImageCode) {
                qrImageCode.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=PIL-BOLIVIA-PAGO-BS-${subtotal.toFixed(2)}`;
            }

            renderCartSuggestions();
        }

        function renderCartSuggestions() {
            const container = document.getElementById('cartSuggestionsList');
            if (!container) return;

            const cartIds = new Set(cart.map(item => Number(item.id)));
            const cartCats = new Set(cart.map(item => {
                const product = productCatalog.find(p => Number(p.id) === Number(item.id));
                return product ? Number(product.cat) : null;
            }).filter(Boolean));

            const suggested = productCatalog
                .filter(product => product.stock > 0 && !cartIds.has(Number(product.id)))
                .sort((a, b) => {
                    const aMatch = cartCats.has(Number(a.cat)) ? 0 : 1;
                    const bMatch = cartCats.has(Number(b.cat)) ? 0 : 1;
                    return aMatch - bMatch || a.name.localeCompare(b.name);
                })
                .slice(0, 6);

            if (!suggested.length) {
                container.innerHTML = '<p style="grid-column:1/-1; color:var(--text-muted); font-weight:700; margin:0;">No hay sugerencias disponibles por ahora.</p>';
                return;
            }

            container.innerHTML = suggested.map(product => `
                <article class="suggestion-card">
                    <img src="${product.img}" alt="${product.name}">
                    <div>
                        <strong>${product.name}</strong>
                        <span>Bs ${Number(product.price).toFixed(2)}</span>
                        <button type="button" onclick="addSuggestionToCart(${product.id})"><i class="ri-add-line"></i> Agregar</button>
                    </div>
                </article>
            `).join('');
        }

        function addSuggestionToCart(productId) {
            const product = productCatalog.find(item => Number(item.id) === Number(productId));
            if (!product || product.stock <= 0) return;

            const existing = cart.find(item => Number(item.id) === Number(productId));
            if (existing) {
                existing.stock = product.stock;
                if (existing.qty >= product.stock) {
                    alert('No puedes agregar mas unidades de este producto por disponibilidad de stock.');
                    return;
                }
                existing.qty += 1;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: Number(product.price),
                    qty: 1,
                    img: product.img,
                    stock: product.stock
                });
            }

            saveCart();
            renderCart();
        }

        function updateCartQty(index, delta) {
            if (cart[index]) {
                const card = document.querySelector(`.product-card[data-id="${cart[index].id}"]`);
                const stock = parseInt(cart[index].stock || (card ? card.getAttribute('data-stock') : 0)) || 0;
                cart[index].qty += delta;
                if (cart[index].qty <= 0) {
                    cart.splice(index, 1);
                } else if (stock > 0 && cart[index].qty > stock) {
                    cart[index].qty = stock;
                    cart[index].stock = stock;
                    alert('No puedes agregar mas unidades de este producto por disponibilidad de stock.');
                }
                saveCart();
                renderCart();
            }
        }

        function removeCartItem(index) {
            cart.splice(index, 1);
            saveCart();
            renderCart();
        }

        function clearCart() {
            if (confirm('¿Deseas vaciar tu carrito?')) {
                cart = [];
                saveCart();
                renderCart();
            }
        }

        // Toggle Drawer
        function toggleCartDrawer(forceOpen = false) {
            const drawer = document.getElementById('cartDrawer');
            const backdrop = document.getElementById('cartBackdrop');
            if (forceOpen) {
                drawer.classList.add('active');
                backdrop.classList.add('active');
            } else {
                drawer.classList.toggle('active');
                backdrop.classList.toggle('active');
            }
        }

        // Payment Modal handlers
        function openPaymentModal() {
            if (cart.length === 0) {
                alert('Tu carrito está vacío. Agrega productos antes de pagar.');
                return;
            }
            toggleCartDrawer(false);
            document.getElementById('paymentModal').classList.add('active');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.remove('active');
            toggleCartDrawer(true);
        }

        function switchPayTab(method, btn) {
            document.querySelectorAll('.pay-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('selectedPaymentMethod').value = method;

            document.getElementById('payFormTarjeta').style.display = method === 'Tarjeta' ? 'block' : 'none';
            document.getElementById('payFormQR').style.display = method === 'QR' ? 'block' : 'none';
            document.getElementById('payFormEfectivo').style.display = method === 'Efectivo' ? 'block' : 'none';

            // Toggle card required inputs
            const cardInputs = document.querySelectorAll('#payFormTarjeta input');
            cardInputs.forEach(input => {
                if (method === 'Tarjeta') {
                    input.setAttribute('required', 'required');
                } else {
                    input.removeAttribute('required');
                }
            });
        }

        // History Modal handlers
        function openHistoryModal() {
            document.getElementById('historyModal').classList.add('active');
        }

        function closeHistoryModal() {
            document.getElementById('historyModal').classList.remove('active');
        }

        // Live Search & Category Filtering
        function filterProducts() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const activeCat = parseInt(document.querySelector('.cat-pill.active')?.getAttribute('onclick')?.match(/\d+/)?.[0] || 0);
            
            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.getAttribute('data-name').toLowerCase();
                const cat = parseInt(card.getAttribute('data-cat'));
                
                const matchesSearch = name.includes(query);
                const matchesCat = (activeCat === 0 || cat === activeCat);

                if (matchesSearch && matchesCat) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function selectCategory(catId, btn) {
            document.querySelectorAll('.cat-pill').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filterProducts();
        }
    </script>
</body>
</html>
