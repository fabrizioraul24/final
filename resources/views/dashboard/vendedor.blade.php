@extends('layouts.sidebar-vendedor')

@section('title', 'Vendedor | Pil Andina')
@section('page-title', 'Resumen de Vendedor')

@section('content')
    <!-- Scoped CSS for Premium Seller UX/UI -->
    <style>
        .seller-dashboard {
            display: flex;
            flex-direction: column;
            gap: 1.6rem;
            padding-bottom: 3rem;
        }

        /* Real-Time Sync Indicator Bar */
        .sync-indicator-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(16, 22, 56, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1.2rem;
            padding: 0.7rem 1.25rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .sync-status {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.84rem;
            font-weight: 600;
        }

        .sync-dot-container {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sync-dot {
            width: 7px;
            height: 7px;
            background-color: #10b981;
            border-radius: 50%;
        }

        .sync-pulse {
            position: absolute;
            width: 17px;
            height: 17px;
            border: 2px solid #10b981;
            border-radius: 50%;
            opacity: 0;
            animation: pulseGlow 2s infinite;
        }

        @keyframes pulseGlow {
            0% { transform: scale(0.5); opacity: 0.8; }
            100% { transform: scale(1.6); opacity: 0; }
        }

        .sync-time {
            color: rgba(255, 255, 255, 0.45);
            font-weight: 400;
            margin-left: 0.2rem;
        }

        .btn-sync {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            font-size: 0.78rem;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .btn-sync:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .btn-sync i {
            transition: transform 0.5s ease;
        }

        .btn-sync.spinning i {
            transform: rotate(360deg);
        }

        /* Welcome Banner Component */
        .welcome-hero {
            background: linear-gradient(135deg, rgba(24, 20, 56, 0.85) 0%, rgba(45, 30, 80, 0.6) 50%, rgba(68, 20, 56, 0.5) 100%);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 1.75rem;
            padding: 1.75rem 2.25rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            backdrop-filter: blur(12px);
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .welcome-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 90% 10%, rgba(236, 72, 153, 0.08) 0%, transparent 60%);
            pointer-events: none;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .welcome-text h2 {
            margin: 0;
            font-size: clamp(1.4rem, 2.5vw, 1.95rem);
            font-weight: 800;
            background: linear-gradient(120deg, #ffffff 40%, #e2e8f0 70%, #fbcfe8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.02em;
        }

        .welcome-text p {
            margin: 0.5rem 0 0;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.78);
            line-height: 1.5;
            max-width: 720px;
        }

        .welcome-meta {
            text-align: right;
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            flex-shrink: 0;
        }

        .welcome-time {
            font-size: 1.85rem;
            font-weight: 800;
            color: #fff;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.02em;
            text-shadow: 0 0 15px rgba(255, 255, 255, 0.15);
        }

        .welcome-date {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.55);
            font-weight: 500;
            text-transform: capitalize;
        }

        /* Shortcuts Section */
        .shortcuts-section {
            animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.05s both;
        }

        .section-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .section-header-bar h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .section-header-bar h3 i {
            color: #f43f5e;
            font-size: 1.25rem;
        }

        .shortcuts-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        @media (max-width: 960px) {
            .shortcuts-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .shortcuts-grid { grid-template-columns: 1fr; }
        }

        .shortcut-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 1.35rem;
            padding: 1.25rem 1.15rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.35s cubic-bezier(0.2, 0.8, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            min-height: 148px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.12);
        }

        .shortcut-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.045);
            border-color: rgba(244, 63, 94, 0.35);
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.35), 0 0 15px rgba(244, 63, 94, 0.1);
        }

        .shortcut-icon-container {
            width: 42px;
            height: 42px;
            border-radius: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 0.85rem;
            color: #fff;
            transition: transform 0.3s ease;
        }

        .shortcut-card:hover .shortcut-icon-container {
            transform: scale(1.1);
        }

        .shortcut-info h4 {
            margin: 0;
            font-size: 0.88rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.25rem;
        }

        .shortcut-info p {
            margin: 0;
            font-size: 0.76rem;
            color: rgba(255, 255, 255, 0.52);
            line-height: 1.35;
        }

        .shortcut-action {
            font-size: 0.76rem;
            font-weight: 700;
            color: #f43f5e;
            display: flex;
            align-items: center;
            gap: 0.2rem;
            margin-top: 0.75rem;
            transition: all 0.2s ease;
        }

        .shortcut-card:hover .shortcut-action {
            color: #fff;
            transform: translateX(3px);
        }

        /* Target and Commission Panel */
        .seller-hero-row {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 1.5rem;
            animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
        }

        @media (max-width: 1100px) {
            .seller-hero-row { grid-template-columns: 1fr; }
        }

        .commission-target-card {
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.1) 0%, rgba(139, 92, 246, 0.08) 100%) !important;
            border: 1px solid rgba(244, 63, 94, 0.22) !important;
            border-radius: 1.5rem !important;
            padding: 1.5rem !important;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.05) !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .commission-amount-badge {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.25);
            color: #34d399;
            padding: 0.4rem 0.85rem;
            border-radius: 0.75rem;
            font-size: 1.12rem;
            font-weight: 800;
            text-shadow: 0 0 10px rgba(52, 211, 153, 0.2);
            white-space: nowrap;
        }

        .target-progress-bar-container {
            height: 9px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 999px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.03);
            margin-top: 0.5rem;
        }

        .target-progress-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #f43f5e, #8b5cf6);
            box-shadow: 0 0 12px rgba(244, 63, 94, 0.4);
            transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .target-details {
            margin-top: 0.65rem;
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.45);
            display: flex;
            justify-content: space-between;
        }

        .stats-summary-strip {
            background: rgba(255, 255, 255, 0.02) !important;
            border: 1px solid rgba(255, 255, 255, 0.06) !important;
            border-radius: 1.5rem !important;
            padding: 1.5rem !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.18) !important;
        }

        .strip-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.85rem;
        }

        .strip-item {
            background: rgba(255, 255, 255, 0.015);
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: 1.1rem;
            padding: 0.85rem 1rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .strip-item span {
            font-size: 0.72rem;
            color: rgba(255, 255, 255, 0.45);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.2rem;
        }

        .strip-item strong {
            font-size: 1.12rem;
            font-weight: 800;
            color: #ffffff;
        }

        .strip-item p {
            margin: 0.15rem 0 0;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.55);
        }

        /* Premium KPI Cards */
        .seller-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.25rem;
            animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both;
        }

        @media (max-width: 960px) {
            .seller-stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .seller-stats-grid { grid-template-columns: 1fr; }
        }

        .premium-kpi-card {
            background: rgba(255, 255, 255, 0.02) !important;
            border: 1px solid rgba(255, 255, 255, 0.06) !important;
            border-radius: 1.5rem !important;
            padding: 1.4rem 1.6rem !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 148px !important;
            transition: all 0.35s cubic-bezier(0.2, 0.8, 0.2, 1) !important;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15) !important;
        }

        .premium-kpi-card:hover {
            transform: translateY(-4px);
            background: rgba(255, 255, 255, 0.035) !important;
            border-color: var(--kpi-border-hover);
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.25), 0 0 15px var(--kpi-glow) !important;
        }

        .kpi-top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .kpi-title {
            margin: 0;
            font-size: 0.88rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.55);
        }

        .kpi-icon-container {
            width: 38px;
            height: 38px;
            border-radius: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #fff;
            transition: all 0.3s ease;
        }

        .premium-kpi-card:hover .kpi-icon-container {
            transform: scale(1.1) rotate(6deg);
        }

        .kpi-main-val {
            font-size: clamp(1.6rem, 3vw, 2.1rem);
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.03em;
            line-height: 1.1;
            margin-top: 0.65rem;
        }

        .kpi-bottom-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.85rem;
        }

        .kpi-chip-text {
            font-size: 0.74rem;
            color: rgba(255, 255, 255, 0.48);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Three Column Lists Info Layout */
        .seller-info-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            gap: 1.5rem;
            animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
        }

        @media (max-width: 1200px) {
            .seller-info-grid { grid-template-columns: 1fr; }
        }

        .premium-list-card {
            background: rgba(255, 255, 255, 0.02) !important;
            border: 1px solid rgba(255, 255, 255, 0.06) !important;
            border-radius: 1.5rem !important;
            padding: 1.5rem !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2) !important;
        }

        .info-card-header {
            margin-bottom: 1.25rem;
        }

        .info-card-header h4 {
            margin: 0;
            font-size: 1.05rem;
            color: rgba(255, 255, 255, 0.95);
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .info-card-header p {
            margin: 0.15rem 0 0;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.55);
        }

        .seller-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .seller-list-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            padding: 0.8rem 1rem;
            border-radius: 1.1rem;
            background: rgba(255, 255, 255, 0.012);
            border: 1px solid rgba(255, 255, 255, 0.04);
            transition: all 0.25s ease;
        }

        .seller-list-row:hover {
            background: rgba(255, 255, 255, 0.025);
            border-color: rgba(255, 255, 255, 0.08);
            transform: translateX(3px);
        }

        .seller-list-title {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 170px;
        }

        .seller-list-meta {
            display: block;
            margin-top: 0.15rem;
            font-size: 0.74rem;
            color: rgba(255, 255, 255, 0.45);
        }

        .seller-list-value {
            font-size: 0.88rem;
            font-weight: 700;
        }

        .seller-empty-state {
            padding: 2.5rem 1rem;
            border-radius: 1.1rem;
            background: rgba(255, 255, 255, 0.01);
            border: 1px dashed rgba(255, 255, 255, 0.06);
            color: rgba(255, 255, 255, 0.4);
            text-align: center;
            font-size: 0.85rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        .seller-empty-state i {
            font-size: 1.4rem;
        }

        /* Circular Progress Widget */
        .conversion-rate-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 0.9rem;
            margin: 0.5rem auto 0;
            width: 100%;
        }

        .circular-progress {
            position: relative;
            height: 106px;
            width: 106px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.2);
            transition: background 0.8s ease;
        }

        .circular-progress::before {
            content: "";
            position: absolute;
            height: 82px;
            width: 82px;
            border-radius: 50%;
            background-color: #12193b;
        }

        .circular-value {
            position: relative;
            font-size: 1.45rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.02em;
        }

        .conversion-details {
            text-align: center;
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.55);
            font-weight: 500;
        }

        /* Charts section */
        .seller-chart-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            gap: 1.5rem;
            animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.25s both;
        }

        @media (max-width: 1200px) {
            .seller-chart-grid { grid-template-columns: 1fr; }
        }

        .premium-chart-card {
            background: rgba(255, 255, 255, 0.02) !important;
            border: 1px solid rgba(255, 255, 255, 0.06) !important;
            border-radius: 1.5rem !important;
            padding: 1.5rem !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2) !important;
        }

        .chart-wrapper {
            position: relative;
            width: 100%;
            height: 200px;
            margin-top: auto;
        }
    </style>

    <div class="seller-dashboard">
        <!-- Live Sync Bar -->
        <div class="sync-indicator-bar">
            <div class="sync-status">
                <div class="sync-dot-container">
                    <div class="sync-dot"></div>
                    <div class="sync-pulse"></div>
                </div>
                <span style="color: rgba(255,255,255,0.85);">Panel de Ventas Sincronizado</span>
                <span class="sync-time" id="lastSyncText">Conectando...</span>
            </div>
            <button class="btn-sync" id="syncButton" onclick="triggerManualSync()">
                <i class="ri-refresh-line" id="syncIcon" style="font-size: 0.9rem;"></i>
                Refrescar Datos
            </button>
        </div>

        <!-- Welcome Hero Banner with Clock -->
        <div class="welcome-hero">
            <div class="welcome-text">
                <h2>Buenos días, Asesor de Ventas 💼</h2>
                <p>
                    El radar comercial de Pil Andina se encuentra operativo. Aquí puedes dar seguimiento a tu cuota mensual de ventas, proyectar comisiones estimadas, registrar cobros y agendar visitas presenciales con clientes de tu cartera.
                </p>
            </div>
            <div class="welcome-meta">
                <span class="welcome-time" id="liveClock">00:00:00</span>
                <span class="welcome-date" id="liveDate">Cargando fecha...</span>
            </div>
        </div>

        <!-- Quick Access Grid (Accesos Directos) -->
        <section class="shortcuts-section">
            <div class="section-header-bar">
                <h3>
                    <i class="ri-flashlight-line"></i>
                    Accesos Directos Operativos
                </h3>
            </div>
            <div class="shortcuts-grid">
                <a href="{{ route('dashboard.vendedor.sales') }}" class="shortcut-card">
                    <div class="shortcut-icon-container" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(16, 185, 129, 0.05)); border: 1px solid rgba(16, 185, 129, 0.35);">
                        <i class="ri-shopping-cart-line" style="color: #34d399;"></i>
                    </div>
                    <div class="shortcut-info">
                        <h4>Registrar Venta</h4>
                        <p>Factura y registra cobros directos de clientes institucionales o minoristas.</p>
                    </div>
                    <span class="shortcut-action">Registrar Venta <i class="ri-arrow-right-s-line"></i></span>
                </a>

                <a href="{{ route('dashboard.vendedor.quotations') }}" class="shortcut-card">
                    <div class="shortcut-icon-container" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.22), rgba(139, 92, 246, 0.05)); border: 1px solid rgba(139, 92, 246, 0.35);">
                        <i class="ri-file-add-line" style="color: #c084fc;"></i>
                    </div>
                    <div class="shortcut-info">
                        <h4>Nueva Cotización</h4>
                        <p>Crea propuestas comerciales y cotizaciones con descuentos parametrizados.</p>
                    </div>
                    <span class="shortcut-action">Crear Cotización <i class="ri-arrow-right-s-line"></i></span>
                </a>

                <a href="{{ route('dashboard.vendedor.visits') }}" class="shortcut-card">
                    <div class="shortcut-icon-container" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(245, 158, 11, 0.05)); border: 1px solid rgba(245, 158, 11, 0.35);">
                        <i class="ri-calendar-event-line" style="color: #fbbf24;"></i>
                    </div>
                    <div class="shortcut-info">
                        <h4>Agenda de Visitas</h4>
                        <p>Planifica y registra visitas presenciales en tiendas o sucursales de tu zona.</p>
                    </div>
                    <span class="shortcut-action">Agendar Visita <i class="ri-arrow-right-s-line"></i></span>
                </a>

                <a href="/dashboard/productos" class="shortcut-card">
                    <div class="shortcut-icon-container" style="background: linear-gradient(135deg, rgba(244, 63, 94, 0.22), rgba(244, 63, 94, 0.05)); border: 1px solid rgba(244, 63, 94, 0.35);">
                        <i class="ri-shopping-bag-line" style="color: #f43f5e;"></i>
                    </div>
                    <div class="shortcut-info">
                        <h4>Catálogo General</h4>
                        <p>Consulta productos activos, precios oficiales y stock total de SKUs en vivo.</p>
                    </div>
                    <span class="shortcut-action">Ver Catálogo <i class="ri-arrow-right-s-line"></i></span>
                </a>
            </div>
        </section>

        <!-- Quota & Commissions Row -->
        <div class="seller-hero-row">
            <!-- Target Progress Card -->
            <article class="card commission-target-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem;">
                    <div>
                        <h3 style="margin: 0; font-size: 1.12rem; color: #fff; display: flex; align-items: center; gap: 0.45rem; font-weight: 700;">
                            <i class="ri-flag-2-line" style="color: #f43f5e;"></i>
                            Objetivo de Ventas Individual
                        </h3>
                        <p style="margin: 0.35rem 0 0; font-size: 0.82rem; color: rgba(255, 255, 255, 0.58); line-height: 1.4;">
                            Seguimiento mensual de ventas acumuladas individuales frente a tu meta de ventas y comisiones estimadas (5%).
                        </p>
                    </div>
                    <div>
                        <span style="font-size:0.75rem;color:rgba(255,255,255,0.45);display:block;margin-bottom:0.25rem;text-align:right;">Comisión (5%)</span>
                        <div class="commission-amount-badge" id="commissionBadge">
                            Bs {{ number_format($estimatedCommission, 2) }}
                        </div>
                    </div>
                </div>
                <div style="margin-top: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.45rem; font-weight: 700;">
                        <span style="color: rgba(255,255,255,0.75);">Cumplimiento de Ventas</span>
                        <span style="color: #f43f5e;" id="targetPercentText">{{ $targetProgress }}%</span>
                    </div>
                    <div class="target-progress-bar-container">
                        <div class="target-progress-fill" id="progressBar" style="width: {{ $targetProgress }}%"></div>
                    </div>
                    <div class="target-details">
                        <span>Acumulado: <strong id="amountAccumulated">Bs {{ number_format($amountMonth, 2) }}</strong></span>
                        <span>Cuota Mensual: <strong id="amountTarget">Bs {{ number_format($monthlyTarget, 2) }}</strong></span>
                    </div>
                </div>
            </article>

            <!-- Stats Summary Strip -->
            <article class="card stats-summary-strip">
                <div>
                    <h3 style="margin: 0; font-size: 1.12rem; color: #fff; display: flex; align-items: center; gap: 0.45rem; font-weight: 700;">
                        <i class="ri-medal-line" style="color: #fbbf24;"></i>
                        Resumen de Eficiencia Semanal
                    </h3>
                    <p style="margin: 0.35rem 0 0; font-size: 0.82rem; color: rgba(255, 255, 255, 0.58); line-height: 1.4;">
                        Tu mejor día de facturación registrado esta semana y promedio por transacción de caja.
                    </p>
                </div>
                <div class="strip-grid" style="margin-top: 1.1rem;">
                    <div class="strip-item">
                        <span>Ticket Promedio</span>
                        <strong>Bs {{ number_format($avgTicket, 2) }}</strong>
                        <p>Por cada venta</p>
                    </div>
                    <div class="strip-item">
                        <span>Mejor Día</span>
                        <strong style="font-size: 1.05rem;">{{ $bestDay ? $bestDay['date'] : 'N/D' }}</strong>
                        <p>Pico: Bs {{ number_format($bestDay ? $bestDay['value'] : 0, 2) }}</p>
                    </div>
                </div>
            </article>
        </div>

        <!-- KPI Stats Grid -->
        <div class="seller-stats-grid">
            <!-- Ventas del Mes -->
            <article class="card premium-kpi-card" style="--kpi-border-hover: #8b5cf6; --kpi-glow: rgba(139, 92, 246, 0.1); --kpi-color-glow: #8b5cf6; --kpi-color-shadow: rgba(139, 92, 246, 0.2);">
                <div class="kpi-top-row">
                    <h3 class="kpi-title">Ventas del Mes</h3>
                    <div class="kpi-icon-container" style="background: rgba(139, 92, 246, 0.12); border: 1px solid rgba(139, 92, 246, 0.25);">
                        <i class="ri-shopping-bag-3-line" style="color: #8b5cf6;"></i>
                    </div>
                </div>
                <div class="kpi-main-val" id="kpiSalesCount">{{ $countSales }}</div>
                <div class="kpi-bottom-row">
                    <span class="kpi-chip-text">
                        <i class="ri-checkbox-circle-line" style="color:#a78bfa;"></i>
                        Transacciones cerradas
                    </span>
                </div>
            </article>

            <!-- Monto Facturado -->
            <article class="card premium-kpi-card" style="--kpi-border-hover: #10b981; --kpi-glow: rgba(16, 185, 129, 0.1); --kpi-color-glow: #10b981; --kpi-color-shadow: rgba(16, 185, 129, 0.2);">
                <div class="kpi-top-row">
                    <h3 class="kpi-title">Monto Facturado</h3>
                    <div class="kpi-icon-container" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.25);">
                        <i class="ri-money-dollar-circle-line" style="color: #10b981;"></i>
                    </div>
                </div>
                <div class="kpi-main-val" id="kpiAmountMonth" style="font-size: clamp(1.4rem, 2.4vw, 1.85rem);">Bs {{ number_format($amountMonth, 2) }}</div>
                <div class="kpi-bottom-row">
                    <span class="kpi-chip-text">
                        <i class="ri-arrow-up-line" style="color:#34d399;"></i>
                        En el mes en curso
                    </span>
                </div>
            </article>

            <!-- Clientes Registrados -->
            <article class="card premium-kpi-card" style="--kpi-border-hover: #ec4899; --kpi-glow: rgba(236, 72, 153, 0.1); --kpi-color-glow: #ec4899; --kpi-color-shadow: rgba(236, 72, 153, 0.2);">
                <div class="kpi-top-row">
                    <h3 class="kpi-title">Clientes en Cartera</h3>
                    <div class="kpi-icon-container" style="background: rgba(236, 72, 153, 0.12); border: 1px solid rgba(236, 72, 153, 0.25);">
                        <i class="ri-group-line" style="color: #ec4899;"></i>
                    </div>
                </div>
                <div class="kpi-main-val" id="kpiClientsCount">{{ $clientsCount }}</div>
                <div class="kpi-bottom-row">
                    <span class="kpi-chip-text">
                        <i class="ri-global-line" style="color:#f472b6;"></i>
                        Empresas y tiendas registradas
                    </span>
                </div>
            </article>

            <!-- Visitas Pendientes -->
            <article class="card premium-kpi-card" style="--kpi-border-hover: #f59e0b; --kpi-glow: rgba(245, 158, 11, 0.1); --kpi-color-glow: #f59e0b; --kpi-color-shadow: rgba(245, 158, 11, 0.2);">
                <div class="kpi-top-row">
                    <h3 class="kpi-title">Visitas Pendientes</h3>
                    <div class="kpi-icon-container" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.25);">
                        <i class="ri-calendar-check-line" style="color: #f59e0b;"></i>
                    </div>
                </div>
                <div class="kpi-main-val" id="kpiPendingVisits">{{ $pendingVisits }}</div>
                <div class="kpi-bottom-row">
                    <span class="kpi-chip-text">
                        <i class="ri-time-line" style="color:#fbbf24;"></i>
                        En agenda desde hoy
                    </span>
                </div>
            </article>
        </div>

        <!-- Tables & Alerts Section -->
        <div class="seller-info-grid">
            <!-- Recent Sales -->
            <article class="card premium-list-card">
                <div class="info-card-header">
                    <h4>
                        <i class="ri-shopping-cart-2-line" style="color: #10b981; margin-right: 0.35rem;"></i>
                        Ventas Recientes
                    </h4>
                    <p>Últimas facturaciones realizadas en tu cuenta.</p>
                </div>
                <div class="seller-list" id="recentSalesContainer">
                    @forelse($recentSales as $sale)
                        <div class="seller-list-row">
                            <div style="min-width: 0; flex: 1;">
                                <span class="seller-list-title" title="{{ $sale->company->name ?? 'Venta minorista' }}">{{ $sale->company->name ?? 'Venta minorista' }}</span>
                                <span class="seller-list-meta">
                                    {{ ucfirst(str_replace('_', ' ', $sale->sale_type ?? 'sin tipo')) }} · {{ optional($sale->created_at)->format('d/m H:i') }}
                                </span>
                            </div>
                            <strong class="seller-list-value" style="color: #34d399; flex-shrink: 0;">Bs {{ number_format((float) $sale->total_amount, 2) }}</strong>
                        </div>
                    @empty
                        <div class="seller-empty-state">
                            <i class="ri-shopping-bag-line" style="color: rgba(255,255,255,0.3);"></i>
                            No tienes ventas registradas.
                        </div>
                    @endforelse
                </div>
            </article>

            <!-- Upcoming Agenda -->
            <article class="card premium-list-card">
                <div class="info-card-header">
                    <h4>
                        <i class="ri-calendar-todo-line" style="color: #8b5cf6; margin-right: 0.35rem;"></i>
                        Agenda Próxima
                    </h4>
                    <p>Tus próximas visitas agendadas pendientes.</p>
                </div>
                <div class="seller-list" id="upcomingVisitsContainer">
                    @forelse($upcomingVisitsList as $visit)
                        <div class="seller-list-row">
                            <div style="min-width: 0; flex: 1;">
                                <span class="seller-list-title" title="{{ $visit->company->name ?? 'Cliente sin nombre' }}">{{ $visit->company->name ?? 'Cliente sin nombre' }}</span>
                                <span class="seller-list-meta">
                                    {{ optional($visit->visit_date)->format('d/m/Y') }} · {{ $visit->company->city ?? 'Sin ciudad' }}
                                </span>
                            </div>
                            <span class="chip" style="font-size:0.75rem; padding:0.25rem 0.55rem; background:rgba(139,92,241,0.12); color:#c7d2fe; border: 1px solid rgba(139,92,241,0.25); font-weight:700; flex-shrink: 0;">
                                {{ $visit->note ? 'Con nota' : 'Pendiente' }}
                            </span>
                        </div>
                    @empty
                        <div class="seller-empty-state">
                            <i class="ri-calendar-line" style="color: rgba(255,255,255,0.3);"></i>
                            No tienes visitas próximas agendadas.
                        </div>
                    @endforelse
                </div>
            </article>

            <!-- Conversion & Alerts -->
            <article class="card premium-list-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div class="info-card-header" style="margin-bottom: 0.75rem;">
                        <h4>
                            <i class="ri-bar-chart-2-line" style="color: #ec4899; margin-right: 0.35rem;"></i>
                            Conversión de Cotizaciones
                        </h4>
                        <p>Porcentaje de cotizaciones ganadas / aprobadas en tu cuenta.</p>
                    </div>
                    
                    <!-- Circular Conversion Ratio Widget -->
                    <div class="conversion-rate-container">
                        <div class="circular-progress" id="conversionProgress" style="background: conic-gradient(#8b5cf6 {{ $quotationConversion }}%, rgba(255, 255, 255, 0.06) {{ $quotationConversion }}%);">
                            <span class="circular-value" id="conversionText">{{ $quotationConversion }}%</span>
                        </div>
                        <div class="conversion-details">
                            <span id="conversionDetailsText">{{ $acceptedQuotations }} de {{ $totalQuotations }} cotizaciones ganadas</span>
                        </div>
                    </div>
                </div>

                <!-- Unvisited Clients Small List -->
                <div style="border-top:1px solid rgba(255,255,255,0.06); margin-top:1rem; padding-top:1rem;">
                    <span style="font-size:0.78rem; font-weight:700; color:#fca5a5; display:flex; align-items:center; gap:0.3rem; margin-bottom:0.5rem;">
                        <i class="ri-error-warning-line"></i> Clientes desatendidos (&gt;15 días sin visita)
                    </span>
                    <div class="seller-list" id="unvisitedClientsContainer" style="gap:0.4rem;">
                        @forelse($unvisitedCompanies as $company)
                            <div style="display:flex; justify-content:space-between; font-size:0.78rem; padding:0.45rem 0.65rem; background:rgba(239,68,68,0.04); border:1px solid rgba(239,68,68,0.12); border-radius:0.6rem; color:rgba(255,255,255,0.85);">
                                <span style="font-weight:700; max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $company->name }}</span>
                                <span style="color:rgba(255,255,255,0.5)">{{ $company->city ?? 'N/D' }}</span>
                            </div>
                        @empty
                            <span style="font-size:0.74rem; color:rgba(255,255,255,0.45)">Todos tus clientes han sido visitados recientemente.</span>
                        @endforelse
                    </div>
                </div>
            </article>
        </div>

        <!-- Analytical Charts Section (NO BLUE) -->
        <div class="seller-chart-grid">
            <!-- Ventas últimos 7 días -->
            <article class="card premium-chart-card">
                <div class="chart-card-header">
                    <div>
                        <h4>Historial de Ventas Diarias</h4>
                        <p>Ingresos generados por tus ventas cerradas en los últimos 7 días.</p>
                    </div>
                    <span class="chip premium-chip">Monto Bs</span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="salesChart"></canvas>
                </div>
            </article>

            <!-- Nuevos Clientes -->
            <article class="card premium-chart-card">
                <div class="chart-card-header">
                    <div>
                        <h4>Registros de Clientes</h4>
                        <p>Nuevas empresas o tiendas incorporadas por ti al sistema comercial.</p>
                    </div>
                    <span class="chip premium-chip">Clientes</span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="clientsChart"></canvas>
                </div>
            </article>

            <!-- Distribución de Ventas -->
            <article class="card premium-chart-card">
                <div class="chart-card-header">
                    <div>
                        <h4>Participación del Cliente</h4>
                        <p>Porcentaje de facturación según el tipo de cliente comercial.</p>
                    </div>
                    <span class="chip premium-chip">Mix Comercial</span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="typesChart"></canvas>
                </div>
            </article>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    let salesChartInstance = null;
    let clientsChartInstance = null;
    let typesChartInstance = null;

    // Harmonious vibrant color scheme - AVOIDING BLUE (Using emerald, amber, purple, rose)
    const chartColors = {
        primary: '#f59e0b',   // Amber (Sales trend)
        success: '#10b981',   // Emerald Green (Retail etc)
        purple: '#8b5cf6',    // Purple (New clients)
        danger: '#ec4899',    // Pink / Rose (Wholesale etc)
        orange: '#f97316',    // Orange (Institutions etc)
        gray: 'rgba(255,255,255,0.04)'
    };

    document.addEventListener("DOMContentLoaded", () => {
        // Initialize Clock
        const clockEl = document.getElementById('liveClock');
        const dateEl = document.getElementById('liveDate');
        if (clockEl && dateEl) {
            const updateClock = () => {
                const now = new Date();
                clockEl.textContent = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
                dateEl.textContent = now.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            };
            updateClock();
            setInterval(updateClock, 1000);
        }

        // Initialize charts with default page data
        initCharts({
            salesData: @json($last7),
            clientsData: @json($clients7),
            typeData: @json($typeSummary)
        });

        // Set last updated time
        updateSyncTime();

        // Start 15 seconds polling loop
        setInterval(fetchLiveStats, 15000);
    });

    function initCharts(data) {
        // Sales Chart (Line) - Amber/Orange theme
        const salesCtx = document.getElementById('salesChart')?.getContext('2d');
        if (salesCtx) {
            salesChartInstance = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: data.salesData.map(d => d.date),
                    datasets: [{
                        label: 'Ventas (Bs)',
                        data: data.salesData.map(d => d.value),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.06)',
                        borderWidth: 2,
                        tension: 0.38,
                        fill: true,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#f59e0b',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            padding: 10,
                            backgroundColor: 'rgba(16, 22, 56, 0.95)',
                            titleColor: '#fff',
                            bodyColor: '#fbbf24',
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.6)', font: { size: 10 } } },
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.6)', font: { size: 10 } } }
                    }
                }
            });
        }

        // Clients Chart (Bar) - Purple theme
        const clientsCtx = document.getElementById('clientsChart')?.getContext('2d');
        if (clientsCtx) {
            clientsChartInstance = new Chart(clientsCtx, {
                type: 'bar',
                data: {
                    labels: data.clientsData.map(d => d.date),
                    datasets: [{
                        label: 'Clientes nuevos',
                        data: data.clientsData.map(d => d.value),
                        backgroundColor: 'rgba(139, 92, 246, 0.8)',
                        hoverBackgroundColor: '#8b5cf6',
                        borderRadius: 6,
                        barThickness: 16
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            padding: 10,
                            backgroundColor: 'rgba(16, 22, 56, 0.95)',
                            titleColor: '#fff',
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.6)', font: { size: 10 } } },
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { precision: 0, color: 'rgba(255,255,255,0.6)', font: { size: 10 } } }
                    }
                }
            });
        }

        // Types Chart (Doughnut) - Emerald/Pink/Orange mix
        const typesCtx = document.getElementById('typesChart')?.getContext('2d');
        if (typesCtx) {
            typesChartInstance = new Chart(typesCtx, {
                type: 'doughnut',
                data: {
                    labels: data.typeData.map(d => d.label),
                    datasets: [{
                        data: data.typeData.map(d => d.value),
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.85)', // Emerald
                            'rgba(236, 72, 153, 0.85)',  // Pink/Rose
                            'rgba(249, 115, 22, 0.85)',  // Orange
                            'rgba(139, 92, 246, 0.85)',  // Purple
                            'rgba(245, 158, 11, 0.85)'   // Amber
                        ],
                        borderWidth: 2,
                        borderColor: '#111827'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: 'rgba(255,255,255,0.7)',
                                font: { size: 10, weight: '500' },
                                boxWidth: 8,
                                padding: 12
                            }
                        },
                        tooltip: {
                            padding: 10,
                            backgroundColor: 'rgba(16, 22, 56, 0.95)',
                            titleColor: '#fff',
                            cornerRadius: 8
                        }
                    }
                }
            });
        }
    }

    function updateSyncTime() {
        const now = new Date();
        const hrs = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        const secs = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('lastSyncText').textContent = `En vivo · Refrescado a las ${hrs}:${mins}:${secs}`;
    }

    function triggerManualSync() {
        const btn = document.getElementById('syncButton');
        btn.classList.add('spinning');
        fetchLiveStats().finally(() => {
            setTimeout(() => btn.classList.remove('spinning'), 500);
        });
    }

    function fetchLiveStats() {
        return fetch("{{ route('dashboard.vendedor.live-stats') }}")
            .then(res => {
                if (!res.ok) throw new Error("Stats fetch failed");
                return res.json();
            })
            .then(data => {
                updateDashboardUI(data);
                updateSyncTime();
            })
            .catch(err => {
                console.error("Error polling live stats:", err);
                document.getElementById('lastSyncText').textContent = "Error al conectar.";
            });
    }

    function updateDashboardUI(data) {
        // Helper to animate metric change
        const updateNumber = (elId, value, format = false) => {
            const el = document.getElementById(elId);
            if (!el) return;
            const formatted = format ? value : value.toLocaleString();
            if (el.textContent !== formatted) {
                el.style.transform = 'scale(1.08)';
                el.style.color = '#34d399';
                el.style.transition = 'all 0.15s ease';
                setTimeout(() => {
                    el.textContent = formatted;
                    el.style.transform = 'scale(1)';
                    el.style.color = '';
                }, 150);
            }
        };

        // Update KPIs
        updateNumber('kpiSalesCount', data.countSales);
        updateNumber('kpiAmountMonth', 'Bs ' + data.amountMonth.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}), true);
        updateNumber('kpiClientsCount', data.clientsCount);
        updateNumber('kpiPendingVisits', data.pendingVisits);

        // Update target progress bar & badges
        const targetPercentText = document.getElementById('targetPercentText');
        if (targetPercentText) targetPercentText.textContent = `${data.targetProgress}%`;

        const progressBar = document.getElementById('progressBar');
        if (progressBar) progressBar.style.width = `${data.targetProgress}%`;

        const commissionBadge = document.getElementById('commissionBadge');
        if (commissionBadge) commissionBadge.textContent = 'Bs ' + data.estimatedCommission.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

        const amountAccumulated = document.getElementById('amountAccumulated');
        if (amountAccumulated) amountAccumulated.textContent = 'Bs ' + data.amountMonth.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

        // Update charts data
        if (salesChartInstance) {
            salesChartInstance.data.labels = data.last7.map(d => d.date);
            salesChartInstance.data.datasets[0].data = data.last7.map(d => d.value);
            salesChartInstance.update('none');
        }

        if (clientsChartInstance) {
            clientsChartInstance.data.labels = data.clients7.map(d => d.date);
            clientsChartInstance.data.datasets[0].data = data.clients7.map(d => d.value);
            clientsChartInstance.update('none');
        }

        if (typesChartInstance) {
            typesChartInstance.data.labels = data.typeSummary.map(d => d.label);
            typesChartInstance.data.datasets[0].data = data.typeSummary.map(d => d.value);
            typesChartInstance.update('none');
        }

        // Update Lists (Recent Sales)
        const recentSalesContainer = document.getElementById('recentSalesContainer');
        if (recentSalesContainer) {
            if (data.recentSales.length > 0) {
                let html = '';
                data.recentSales.forEach(s => {
                    html += `
                        <div class="seller-list-row">
                            <div style="min-width: 0; flex: 1;">
                                <span class="seller-list-title" title="${s.client_name}">${s.client_name}</span>
                                <span class="seller-list-meta">${s.sale_type} · ${s.time}</span>
                            </div>
                            <strong class="seller-list-value" style="color: #34d399; flex-shrink: 0;">Bs ${s.total_amount}</strong>
                        </div>
                    `;
                });
                recentSalesContainer.innerHTML = html;
            } else {
                recentSalesContainer.innerHTML = `
                    <div class="seller-empty-state">
                        <i class="ri-shopping-bag-line" style="color: rgba(255,255,255,0.3);"></i>
                        No tienes ventas registradas.
                    </div>
                `;
            }
        }

        // Update Lists (Upcoming Visits)
        const upcomingVisitsContainer = document.getElementById('upcomingVisitsContainer');
        if (upcomingVisitsContainer) {
            if (data.upcomingVisitsList.length > 0) {
                let html = '';
                data.upcomingVisitsList.forEach(v => {
                    html += `
                        <div class="seller-list-row">
                            <div style="min-width: 0; flex: 1;">
                                <span class="seller-list-title" title="${v.client_name}">${v.client_name}</span>
                                <span class="seller-list-meta">${v.visit_date} · ${v.city}</span>
                            </div>
                            <span class="chip" style="font-size:0.75rem; padding:0.25rem 0.55rem; background:rgba(139,92,241,0.12); color:#c7d2fe; border: 1px solid rgba(139,92,241,0.25); font-weight:700; flex-shrink: 0;">
                                ${v.note_status}
                            </span>
                        </div>
                    `;
                });
                upcomingVisitsContainer.innerHTML = html;
            } else {
                upcomingVisitsContainer.innerHTML = `
                    <div class="seller-empty-state">
                        <i class="ri-calendar-line" style="color: rgba(255,255,255,0.3);"></i>
                        No tienes visitas próximas en agenda.
                    </div>
                `;
            }
        }

        // Update Conversion Circle
        const conversionText = document.getElementById('conversionText');
        if (conversionText) conversionText.textContent = `${data.quotationConversion}%`;

        const conversionProgress = document.getElementById('conversionProgress');
        if (conversionProgress) {
            conversionProgress.style.background = `conic-gradient(#8b5cf6 ${data.quotationConversion}%, rgba(255, 255, 255, 0.06) ${data.quotationConversion}%)`;
        }

        const conversionDetailsText = document.getElementById('conversionDetailsText');
        if (conversionDetailsText) {
            conversionDetailsText.textContent = `${data.acceptedQuotations} de ${data.totalQuotations} cotizaciones ganadas`;
        }

        // Update Unvisited Clients list
        const unvisitedClientsContainer = document.getElementById('unvisitedClientsContainer');
        if (unvisitedClientsContainer) {
            if (data.unvisitedCompanies.length > 0) {
                let html = '';
                data.unvisitedCompanies.forEach(c => {
                    html += `
                        <div style="display:flex; justify-content:space-between; font-size:0.78rem; padding:0.45rem 0.65rem; background:rgba(239,68,68,0.04); border:1px solid rgba(239,68,68,0.12); border-radius:0.6rem; color:rgba(255,255,255,0.85);">
                            <span style="font-weight:700; max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${c.name}</span>
                            <span style="color:rgba(255,255,255,0.5)">${c.city}</span>
                        </div>
                    `;
                });
                unvisitedClientsContainer.innerHTML = html;
            } else {
                unvisitedClientsContainer.innerHTML = '<span style="font-size:0.74rem; color:rgba(255,255,255,0.45)">Todos tus clientes han sido visitados recientemente.</span>';
            }
        }
    }
</script>
@endpush
