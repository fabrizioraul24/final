@extends('layouts.sidebar-almacen')

@section('title', 'Almacén | Pil Andina')
@section('page-title', 'Tablero de Almacén')

@section('content')
    <!-- Scoped CSS for Premium Warehouse UX/UI -->
    <style>
        .warehouse-dashboard {
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
            background: linear-gradient(135deg, rgba(16, 36, 44, 0.85) 0%, rgba(20, 52, 60, 0.6) 50%, rgba(38, 25, 68, 0.5) 100%);
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
            background: radial-gradient(circle at 90% 10%, rgba(16, 185, 129, 0.1) 0%, transparent 60%);
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
            background: linear-gradient(120deg, #ffffff 40%, #e2e8f0 70%, #a7f3d0 100%);
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
            color: #10b981;
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
            border-color: rgba(16, 185, 129, 0.35);
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.35), 0 0 15px rgba(16, 185, 129, 0.1);
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
            color: #10b981;
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

        /* Target and Stats Row */
        .capacity-stats-split {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 1.5rem;
            animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
        }

        @media (max-width: 1100px) {
            .capacity-stats-split { grid-template-columns: 1fr; }
        }

        .premium-capacity-card {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(245, 158, 11, 0.06) 100%) !important;
            border: 1px solid rgba(16, 185, 129, 0.22) !important;
            border-radius: 1.5rem !important;
            padding: 1.5rem !important;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.05) !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .capacity-bar-track {
            height: 9px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 999px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.03);
            margin-top: 0.5rem;
        }

        .capacity-bar-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #10b981, #f59e0b);
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.4);
            transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .capacity-details {
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
        .warehouse-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.25rem;
            animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both;
        }

        @media (max-width: 960px) {
            .warehouse-stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .warehouse-stats-grid { grid-template-columns: 1fr; }
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
            font-size: clamp(1.8rem, 3.2vw, 2.15rem);
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

        /* Two Column Layout */
        .warehouse-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.5rem;
            animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
        }

        @media (max-width: 1024px) {
            .warehouse-info-grid { grid-template-columns: 1fr; }
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

        .warehouse-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .warehouse-list-row {
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

        .warehouse-list-row:hover {
            background: rgba(255, 255, 255, 0.025);
            border-color: rgba(255, 255, 255, 0.08);
            transform: translateX(3px);
        }

        .warehouse-list-title {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 260px;
        }

        .warehouse-list-meta {
            display: block;
            margin-top: 0.15rem;
            font-size: 0.74rem;
            color: rgba(255, 255, 255, 0.45);
        }

        .warehouse-list-value {
            font-size: 0.88rem;
            font-weight: 700;
        }

        .safety-bar-track {
            height: 5px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 999px;
            margin-top: 0.4rem;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.03);
            width: 100px;
        }

        .safety-bar-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #ef4444, #f97316);
            box-shadow: 0 0 8px rgba(239, 68, 68, 0.8);
            transition: width 0.5s ease;
        }

        .warehouse-empty-state {
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

        .warehouse-empty-state i {
            font-size: 1.4rem;
        }

        /* Charts section */
        .warehouse-chart-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.5rem;
            animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.25s both;
        }

        @media (max-width: 768px) {
            .warehouse-chart-grid { grid-template-columns: 1fr; }
        }

        .premium-chart-card {
            background: rgba(255, 255, 255, 0.02) !important;
            border: 1px solid rgba(255, 255, 255, 0.06) !important;
            border-radius: 1.5rem !important;
            padding: 1.5rem !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2) !important;
        }

        /* Recent Transfers Table Card */
        .warehouse-table-card {
            background: rgba(255, 255, 255, 0.02) !important;
            border: 1px solid rgba(255, 255, 255, 0.06) !important;
            border-radius: 1.5rem !important;
            padding: 1.5rem !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2) !important;
            animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.3s both;
        }

        .data-table-container {
            overflow-x: auto;
            margin-top: 1.25rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .data-table th, .data-table td {
            padding: 0.9rem 1.1rem;
            font-size: 0.86rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .data-table th {
            color: rgba(255, 255, 255, 0.55);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.74rem;
            letter-spacing: 0.04em;
        }

        .data-table tbody tr {
            transition: all 0.2s;
        }

        .data-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.015);
        }
    </style>

    <div class="warehouse-dashboard">
        <!-- Live Sync Bar -->
        <div class="sync-indicator-bar">
            <div class="sync-status">
                <div class="sync-dot-container">
                    <div class="sync-dot"></div>
                    <div class="sync-pulse"></div>
                </div>
                <span style="color: rgba(255,255,255,0.85);">Panel de Almacén Sincronizado</span>
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
                <h2>Buenos días, Operador de Almacén 📦</h2>
                <p>
                    El panel de control y distribución física de Pil Andina está listo. Supervisa la capacidad global de almacenamiento de las sucursales, despacha y recibe traspasos en tránsito, y monitorea las alertas de vencimiento de stock de seguridad.
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
                <a href="{{ route('dashboard.almacen.transfers') }}" class="shortcut-card">
                    <div class="shortcut-icon-container" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(16, 185, 129, 0.05)); border: 1px solid rgba(16, 185, 129, 0.35);">
                        <i class="ri-shuffle-line" style="color: #34d399;"></i>
                    </div>
                    <div class="shortcut-info">
                        <h4>Generar Traspaso</h4>
                        <p>Inicia despachos, transferencias y envíos de stock entre sucursales y bodegas.</p>
                    </div>
                    <span class="shortcut-action">Operar Traspaso <i class="ri-arrow-right-s-line"></i></span>
                </a>

                <a href="{{ route('dashboard.almacen.damages') }}" class="shortcut-card">
                    <div class="shortcut-icon-container" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.22), rgba(239, 68, 68, 0.05)); border: 1px solid rgba(239, 68, 68, 0.35);">
                        <i class="ri-alert-line" style="color: #fca5a5;"></i>
                    </div>
                    <div class="shortcut-info">
                        <h4>Reportar Mermas</h4>
                        <p>Registra daños, roturas o mermas operativas en bodegas con justificación física.</p>
                    </div>
                    <span class="shortcut-action">Reportar Daños <i class="ri-arrow-right-s-line"></i></span>
                </a>

                <a href="{{ route('dashboard.almacen.receptions') }}" class="shortcut-card">
                    <div class="shortcut-icon-container" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(245, 158, 11, 0.05)); border: 1px solid rgba(245, 158, 11, 0.35);">
                        <i class="ri-truck-line" style="color: #fbbf24;"></i>
                    </div>
                    <div class="shortcut-info">
                        <h4>Recibir Pedidos</h4>
                        <p>Valida y recibe compras institucionales y despachos externos en bodegas.</p>
                    </div>
                    <span class="shortcut-action">Registrar Recepción <i class="ri-arrow-right-s-line"></i></span>
                </a>

                <a href="/admin/agente-reposicion" class="shortcut-card">
                    <div class="shortcut-icon-container" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.22), rgba(139, 92, 246, 0.05)); border: 1px solid rgba(139, 92, 246, 0.35);">
                        <i class="ri-robot-2-line" style="color: #a78bfa;"></i>
                    </div>
                    <div class="shortcut-info">
                        <h4>Optimización IA</h4>
                        <p>Sugerencias de reabastecimiento automáticas generadas por el agente inteligente.</p>
                    </div>
                    <span class="shortcut-action">Ver Recomendaciones <i class="ri-arrow-right-s-line"></i></span>
                </a>
            </div>
        </section>

        <!-- Capacity Target & Stats Split Row -->
        <div class="capacity-stats-split">
            <!-- Capacity Summary Card -->
            <article class="card premium-capacity-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem;">
                    <div>
                        <h3 style="margin: 0; font-size: 1.12rem; color: #fff; display: flex; align-items: center; gap: 0.45rem; font-weight: 700;">
                            <i class="ri-bar-chart-box-line" style="color: #10b981;"></i>
                            Ocupación Global de Almacenes
                        </h3>
                        <p style="margin: 0.35rem 0 0; font-size: 0.82rem; color: rgba(255, 255, 255, 0.58); line-height: 1.4;">
                            Monitoreo global de almacenamiento consolidado físico a través de todas las sucursales del sistema.
                        </p>
                    </div>
                    <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); color: #34d399; padding: 0.4rem 0.85rem; border-radius: 0.75rem; font-size: 1.05rem; font-weight: 800; text-align: right; white-space: nowrap; box-shadow: 0 4px 12px rgba(16,185,129,0.15);" id="globalStockSummary">
                        {{ number_format($stats['stock']) }} uds
                    </div>
                </div>
                <div style="margin-top: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.45rem; font-weight: 700;">
                        <span style="color: rgba(255,255,255,0.75);">Ocupación Consolidada</span>
                        <span style="color: #10b981;" id="globalCapacityPercentText">Cargando...</span>
                    </div>
                    <div class="capacity-bar-track">
                        <div class="capacity-bar-fill" id="globalProgressBar" style="width: 0%"></div>
                    </div>
                    <div class="capacity-details">
                        <span>Lotes en Reserva: <strong id="expiringCountSummary" style="color: #fca5a5;">{{ $stats['expiring_lots'] }} lotes</strong></span>
                        <span>Estado: <strong style="color: #34d399;">Seguridad Operativa</strong></span>
                    </div>
                </div>
            </article>

            <!-- Stats Summary Strip -->
            <article class="card stats-summary-strip">
                <div>
                    <h3 style="margin: 0; font-size: 1.12rem; color: #fff; display: flex; align-items: center; gap: 0.45rem; font-weight: 700;">
                        <i class="ri-compass-3-line" style="color: #fbbf24;"></i>
                        Indicadores de Flujo Logístico
                    </h3>
                    <p style="margin: 0.35rem 0 0; font-size: 0.82rem; color: rgba(255, 255, 255, 0.58); line-height: 1.4;">
                        Resumen operativo de traspasos y requerimientos de entrada consolidados de hoy.
                    </p>
                </div>
                <div class="strip-grid" style="margin-top: 1.1rem;">
                    <div class="strip-item">
                        <span>Traspasos Hoy</span>
                        <strong id="kpiTransfersTodaySummary">{{ $stats['transfers_today'] }}</strong>
                        <p>Envíos registrados</p>
                    </div>
                    <div class="strip-item">
                        <span>Preparación</span>
                        <strong id="kpiPendingOrdersSummary">{{ $stats['pending_orders'] }}</strong>
                        <p>Pedidos en cola</p>
                    </div>
                </div>
            </article>
        </div>

        <!-- KPI Cards Grid -->
        <div class="warehouse-stats-grid">
            <!-- Stock Disponible -->
            <article class="card premium-kpi-card" style="--kpi-border-hover: #10b981; --kpi-glow: rgba(16, 185, 129, 0.1); --kpi-color-glow: #10b981; --kpi-color-shadow: rgba(16, 185, 129, 0.2);">
                <div class="kpi-top-row">
                    <h3 class="kpi-title">Stock Disponible</h3>
                    <div class="kpi-icon-container" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.25);">
                        <i class="ri-dropbox-line" style="color: #10b981;"></i>
                    </div>
                </div>
                <div class="kpi-main-val" id="kpiStock">{{ number_format($stats['stock']) }} <span style="font-size:0.5em; font-weight:500; color:rgba(255,255,255,0.5)">uds</span></div>
                <div class="kpi-bottom-row">
                    <span class="kpi-chip-text">
                        <i class="ri-checkbox-circle-line" style="color:#34d399;"></i>
                        Reserva física total
                    </span>
                </div>
            </article>

            <!-- Pedidos Pendientes -->
            <article class="card premium-kpi-card" style="--kpi-border-hover: #f59e0b; --kpi-glow: rgba(245, 158, 11, 0.1); --kpi-color-glow: #f59e0b; --kpi-color-shadow: rgba(245, 158, 11, 0.2);">
                <div class="kpi-top-row">
                    <h3 class="kpi-title">Pedidos Pendientes</h3>
                    <div class="kpi-icon-container" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.25);">
                        <i class="ri-timer-2-line" style="color: #f59e0b;"></i>
                    </div>
                </div>
                <div class="kpi-main-val" id="kpiPendingOrders">{{ $stats['pending_orders'] }}</div>
                <div class="kpi-bottom-row">
                    <span class="kpi-chip-text">
                        <i class="ri-time-line" style="color:#fbbf24;"></i>
                        En cola de preparación
                    </span>
                </div>
            </article>

            <!-- Traspasos Hoy -->
            <article class="card premium-kpi-card" style="--kpi-border-hover: #a855f7; --kpi-glow: rgba(168, 85, 247, 0.1); --kpi-color-glow: #a855f7; --kpi-color-shadow: rgba(168, 85, 247, 0.2);">
                <div class="kpi-top-row">
                    <h3 class="kpi-title">Traspasos Hoy</h3>
                    <div class="kpi-icon-container" style="background: rgba(168, 85, 247, 0.12); border: 1px solid rgba(168, 85, 247, 0.25);">
                        <i class="ri-shuffle-line" style="color: #a855f7;"></i>
                    </div>
                </div>
                <div class="kpi-main-val" id="kpiTransfersToday">{{ $stats['transfers_today'] }}</div>
                <div class="kpi-bottom-row">
                    <span class="kpi-chip-text">
                        <i class="ri-check-line" style="color:#c084fc;"></i>
                        Operaciones registradas
                    </span>
                </div>
            </article>

            <!-- Alertas de Caducidad -->
            <article class="card premium-kpi-card" style="--kpi-border-hover: #f43f5e; --kpi-glow: rgba(244, 63, 94, 0.1); --kpi-color-glow: #f43f5e; --kpi-color-shadow: rgba(244, 63, 94, 0.2);">
                <div class="kpi-top-row">
                    <h3 class="kpi-title">Alertas de Caducidad</h3>
                    <div class="kpi-icon-container" style="background: rgba(244, 63, 94, 0.12); border: 1px solid rgba(244, 63, 94, 0.25);">
                        <i class="ri-alarm-warning-line" style="color: #f43f5e;"></i>
                    </div>
                </div>
                <div class="kpi-main-val" id="kpiExpiringLots">{{ $stats['expiring_lots'] }} <span style="font-size:0.5em; font-weight:500; color:rgba(255,255,255,0.5)">lotes</span></div>
                <div class="kpi-bottom-row">
                    <span class="kpi-chip-text" style="color: #fca5a5;">
                        <i class="ri-alert-line" style="color:#f43f5e;"></i>
                        Vencen en 30 días o menos
                    </span>
                </div>
            </article>
        </div>

        <!-- Detailed Warning Panels & Charts Split Section -->
        <div class="warehouse-info-grid">
            <!-- Left Column: Warnings and alerts lists -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Expiring Lots -->
                <article class="card premium-list-card">
                    <div class="info-card-header">
                        <h4>
                            <i class="ri-hourglass-2-fill" style="color: #f43f5e; margin-right: 0.35rem;"></i>
                            Caducidad Próxima Crítica
                        </h4>
                        <p>Lotes con fecha de expiración menor a 30 días.</p>
                    </div>
                    <div class="warehouse-list" id="expiringLotsContainer">
                        @forelse($expiringLotsList as $lot)
                            <div class="warehouse-list-row">
                                <div style="min-width: 0; flex: 1;">
                                    <span class="warehouse-list-title" title="{{ $lot->product->name ?? 'Producto' }}">{{ $lot->product->name ?? 'Producto' }}</span>
                                    <span class="warehouse-list-meta">
                                        {{ $lot->warehouse->name ?? 'Sin almacén' }} · Vence: {{ optional($lot->expires_at)->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div style="text-align: right; flex-shrink: 0;">
                                    <span class="warehouse-list-value" style="display:block;color:#fca5a5;">{{ $lot->quantity }} uds</span>
                                    <span style="font-size:0.74rem;color:rgba(255,255,255,0.4)">{{ (int) now()->diffInDays($lot->expires_at, false) }} días rest.</span>
                                </div>
                            </div>
                        @empty
                            <div class="warehouse-empty-state">
                                <i class="ri-checkbox-circle-line" style="color: #10b981;"></i>
                                No hay lotes con vencimiento próximo.
                            </div>
                        @endforelse
                    </div>
                </article>

                <!-- Low Stock Under Threshold -->
                <article class="card premium-list-card">
                    <div class="info-card-header">
                        <h4>
                            <i class="ri-error-warning-line" style="color: #f59e0b; margin-right: 0.35rem;"></i>
                            Stock Bajo Mínimo de Seguridad
                        </h4>
                        <p>Lotes de productos con cantidad actual inferior a su reserva de seguridad.</p>
                    </div>
                    <div class="warehouse-list" id="criticalLotsContainer">
                        @forelse($criticalLotsList as $lot)
                            @php
                                $percentLeft = max(0, min(100, round(($lot->quantity / max(1, $lot->safety_threshold)) * 100)));
                            @endphp
                            <div class="warehouse-list-row" style="background:rgba(239,68,68,0.03);border-color:rgba(239,68,68,0.15)">
                                <div style="min-width: 0; flex: 1;">
                                    <span class="warehouse-list-title" title="{{ $lot->product->name ?? 'Producto' }}">{{ $lot->product->name ?? 'Producto' }}</span>
                                    <span class="warehouse-list-meta">
                                        SKU: {{ $lot->product->sku ?? 'N/D' }} · {{ $lot->warehouse->name ?? 'Sin almacén' }}
                                    </span>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.35rem;">
                                        <div class="safety-bar-track" style="margin: 0; flex: 1;">
                                            <div class="safety-bar-fill" style="width: {{ $percentLeft }}%"></div>
                                        </div>
                                        <span style="font-size: 0.7rem; color: #fca5a5; font-weight: 700;">{{ $percentLeft }}% rest.</span>
                                    </div>
                                </div>
                                <div style="text-align: right; flex-shrink: 0;">
                                    <span class="warehouse-list-value" style="display:block;color:#ef4444;">{{ $lot->quantity }} uds</span>
                                    <span style="font-size:0.74rem;color:rgba(255,255,255,0.4)">Mín: {{ $lot->safety_threshold }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="warehouse-empty-state">
                                <i class="ri-checkbox-circle-line" style="color: #10b981;"></i>
                                Todos los lotes en bodega tienen stock seguro.
                            </div>
                        @endforelse
                    </div>
                </article>
            </div>

            <!-- Right Column: Charts -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Capacity Chart -->
                <article class="card premium-chart-card">
                    <div class="chart-card-header">
                        <div>
                            <h4>Ocupación por Almacén</h4>
                            <p>Ocupación volumétrica física comparada con la capacidad máxima configurada en bodega.</p>
                        </div>
                        <span class="chip premium-chip">Ocupación %</span>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="capacityChart"></canvas>
                    </div>
                </article>

                <!-- Flow Chart -->
                <article class="card premium-chart-card">
                    <div class="chart-card-header">
                        <div>
                            <h4>Movimiento de Traspasos (Últimos 7 Días)</h4>
                            <p>Flujo diario consolidado de operaciones de envío interno entre almacenes.</p>
                        </div>
                        <span class="chip premium-chip">Traspasos</span>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="flowChart"></canvas>
                    </div>
                </article>
            </div>
        </div>

        <!-- Recent Transfers Table -->
        <article class="card warehouse-table-card">
            <div class="chart-card-header" style="margin-bottom: 0;">
                <div>
                    <h4 style="display: flex; align-items: center; gap: 0.45rem; margin: 0;">
                        <i class="ri-history-line" style="color: #10b981;"></i>
                        Últimos Traspasos Operativos en Tránsito
                    </h4>
                    <p style="margin: 0.2rem 0 0;">Control de despacho de envíos internos activos entre sucursales.</p>
                </div>
                <a href="{{ route('dashboard.almacen.transfers') }}" class="pill-button ghost" style="font-size: 0.8rem; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 999px; padding: 0.4rem 1rem; color: #fff; text-decoration: none;">Ver todos</a>
            </div>
            
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID de Operación</th>
                            <th>Bodega Origen</th>
                            <th>Bodega Destino</th>
                            <th>Estado Logístico</th>
                            <th>Fecha Estimada</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="transfersTableBody">
                        @forelse($recentTransfers as $transfer)
                            <tr>
                                <td><strong>#{{ $transfer->id }}</strong></td>
                                <td>{{ $transfer->fromWarehouse->name ?? 'Sin origen' }}</td>
                                <td>{{ $transfer->toWarehouse->name ?? 'Sin destino' }}</td>
                                <td>
                                    @php
                                        $chipColor = 'rgba(255,255,255,0.06)';
                                        $textColor = 'rgba(255,255,255,0.75)';
                                        if ($transfer->status === 'recibido') {
                                            $chipColor = 'rgba(16, 185, 129, 0.12)';
                                            $textColor = '#34d399';
                                        } else if ($transfer->status === 'en_transito') {
                                            $chipColor = 'rgba(245, 158, 11, 0.12)';
                                            $textColor = '#fbbf24';
                                        }
                                    @endphp
                                    <span class="chip" style="font-size:0.75rem; padding:0.25rem 0.6rem; border-radius: 999px; background:{{ $chipColor }}; color:{{ $textColor }}; border: 1px solid rgba(255,255,255,0.05); font-weight: 700;">
                                        {{ ucfirst(str_replace('_', ' ', $transfer->status)) }}
                                    </span>
                                </td>
                                <td>{{ optional($transfer->expected_date)->format('d/m/Y') ?? 'Sin fecha' }}</td>
                                <td>
                                    <button class="pill-button ghost" onclick="location.href='{{ route('dashboard.almacen.transfers') }}'" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 999px; padding: 0.35rem 0.8rem; color: #fff; cursor: pointer; font-size: 0.78rem;">Ver Detalle</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; color:rgba(255,255,255,0.35); padding: 2rem;">No hay traspasos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    let capacityChartInstance = null;
    let flowChartInstance = null;

    // Harmonious vibrant color scheme - AVOIDING BLUE (Using emerald, amber, purple, rose)
    const chartColors = {
        primary: '#10b981',   // Emerald Green
        secondary: '#f59e0b', // Amber
        purple: '#a855f7',    // Purple
        danger: '#f43f5e',    // Rose Red
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
            capacityLabels: @json($capacityChart['labels'] ?? []),
            capacityData: @json($capacityChart['data'] ?? []),
            transferLabels: @json($transferSeries['labels'] ?? []),
            transferData: @json($transferSeries['data'] ?? [])
        });

        // Set last updated time
        updateSyncTime();

        // Start 15 seconds polling loop
        setInterval(fetchLiveStats, 15000);
    });

    function initCharts(data) {
        // Capacity Chart (Bar) - Emerald Theme, NO BLUE
        const capacityCtx = document.getElementById('capacityChart')?.getContext('2d');
        if (capacityCtx) {
            capacityChartInstance = new Chart(capacityCtx, {
                type: 'bar',
                data: {
                    labels: data.capacityLabels,
                    datasets: [
                        {
                            label: 'Ocupado (%)',
                            data: data.capacityData,
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            hoverBackgroundColor: '#10b981',
                            borderRadius: 6,
                            barThickness: 16
                        },
                        {
                            label: 'Capacidad Máx',
                            data: data.capacityLabels.map(() => 100),
                            backgroundColor: 'rgba(255, 255, 255, 0.05)',
                            borderRadius: 6,
                            barThickness: 16
                        }
                    ]
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
                            bodyColor: '#10b981',
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.6)', font: { size: 10 } } },
                        y: { stacked: false, beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.6)', font: { size: 10 }, callback: v => v + '%' } }
                    }
                }
            });

            // Update global progress bar based on first warehouse occupancy (or average)
            updateGlobalCapacityProgressBar(data.capacityData);
        }

        // Flow Chart (Line) - Amber Theme, NO BLUE
        const flowCtx = document.getElementById('flowChart')?.getContext('2d');
        if (flowCtx) {
            flowChartInstance = new Chart(flowCtx, {
                type: 'line',
                data: {
                    labels: data.transferLabels,
                    datasets: [{
                        label: 'Traspasos',
                        data: data.transferData,
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
    }

    function updateGlobalCapacityProgressBar(capacityData) {
        if (capacityData.length === 0) return;
        const total = capacityData.reduce((acc, v) => acc + parseFloat(v), 0);
        const avg = Math.round(total / capacityData.length);
        
        const text = document.getElementById('globalCapacityPercentText');
        if (text) text.textContent = `${avg}% de Uso`;

        const bar = document.getElementById('globalProgressBar');
        if (bar) bar.style.width = `${avg}%`;
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
        return fetch("{{ route('dashboard.almacen.live-stats') }}")
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
                el.style.color = '#10b981';
                el.style.transition = 'all 0.15s ease';
                setTimeout(() => {
                    el.textContent = formatted;
                    el.style.transform = 'scale(1)';
                    el.style.color = '';
                }, 150);
            }
        };

        // Update KPIs
        updateNumber('kpiStock', data.stats.stock);
        updateNumber('kpiPendingOrders', data.stats.pending_orders);
        updateNumber('kpiTransfersToday', data.stats.transfers_today);
        updateNumber('kpiExpiringLots', data.stats.expiring_lots);

        updateNumber('kpiTransfersTodaySummary', data.stats.transfers_today);
        updateNumber('kpiPendingOrdersSummary', data.stats.pending_orders);

        // Update summaries
        const globalStockSummary = document.getElementById('globalStockSummary');
        if (globalStockSummary) globalStockSummary.textContent = `${data.stats.stock.toLocaleString()} uds`;

        const expiringCountSummary = document.getElementById('expiringCountSummary');
        if (expiringCountSummary) expiringCountSummary.textContent = `${data.stats.expiring_lots} lotes`;

        // Update charts data
        if (capacityChartInstance) {
            capacityChartInstance.data.labels = data.capacityChart.labels;
            capacityChartInstance.data.datasets[0].data = data.capacityChart.data;
            capacityChartInstance.data.datasets[1].data = data.capacityChart.labels.map(() => 100);
            capacityChartInstance.update('none');
            updateGlobalCapacityProgressBar(data.capacityChart.data);
        }

        if (flowChartInstance) {
            flowChartInstance.data.labels = data.transferSeries.labels;
            flowChartInstance.data.datasets[0].data = data.transferSeries.data;
            flowChartInstance.update('none');
        }

        // Update Lists (Expiring Lots)
        const expiringLotsContainer = document.getElementById('expiringLotsContainer');
        if (expiringLotsContainer) {
            if (data.expiringLotsList.length > 0) {
                let html = '';
                data.expiringLotsList.forEach(l => {
                    html += `
                        <div class="warehouse-list-row">
                            <div style="min-width: 0; flex: 1;">
                                <span class="warehouse-list-title" title="${l.product_name}">${l.product_name}</span>
                                <span class="warehouse-list-meta">
                                    ${l.warehouse_name} · Vence: ${l.expires_at_formatted}
                                </span>
                            </div>
                            <div style="text-align: right; flex-shrink: 0;">
                                <span class="warehouse-list-value" style="display:block;color:#fca5a5;">${l.quantity} uds</span>
                                <span style="font-size:0.74rem;color:rgba(255,255,255,0.4)">${l.days_left} días rest.</span>
                            </div>
                        </div>
                    `;
                });
                expiringLotsContainer.innerHTML = html;
            } else {
                expiringLotsContainer.innerHTML = `
                    <div class="warehouse-empty-state">
                        <i class="ri-checkbox-circle-line" style="color: #10b981;"></i>
                        No hay lotes con vencimiento próximo.
                    </div>
                `;
            }
        }

        // Update Lists (Critical Lots / Low Stock)
        const criticalLotsContainer = document.getElementById('criticalLotsContainer');
        if (criticalLotsContainer) {
            if (data.criticalLotsList.length > 0) {
                let html = '';
                data.criticalLotsList.forEach(l => {
                    const ratio = Math.max(0, Math.min(100, Math.round((l.quantity / Math.max(1, l.safety_threshold)) * 100)));
                    html += `
                        <div class="warehouse-list-row" style="background:rgba(239,68,68,0.03);border-color:rgba(239,68,68,0.15)">
                            <div style="min-width: 0; flex: 1;">
                                <span class="warehouse-list-title" title="${l.product_name}">${l.product_name}</span>
                                <span class="warehouse-list-meta">
                                    SKU: ${l.sku} · ${l.warehouse_name}
                                </span>
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.35rem;">
                                    <div class="safety-bar-track" style="margin: 0; flex: 1;">
                                        <div class="safety-bar-fill" style="width: ${ratio}%"></div>
                                    </div>
                                    <span style="font-size: 0.7rem; color: #fca5a5; font-weight: 700;">${ratio}% rest.</span>
                                </div>
                            </div>
                            <div style="text-align: right; flex-shrink: 0;">
                                <span class="warehouse-list-value" style="display:block;color:#ef4444;">${l.quantity} uds</span>
                                <span style="font-size:0.74rem;color:rgba(255,255,255,0.4)">Mínimo: ${l.safety_threshold}</span>
                            </div>
                        </div>
                    `;
                });
                criticalLotsContainer.innerHTML = html;
            } else {
                criticalLotsContainer.innerHTML = `
                    <div class="warehouse-empty-state">
                        <i class="ri-checkbox-circle-line" style="color: #10b981;"></i>
                        Todos los lotes tienen stock seguro.
                    </div>
                `;
            }
        }

        // Update Recent Transfers Table
        const transfersTableBody = document.getElementById('transfersTableBody');
        if (transfersTableBody) {
            if (data.recentTransfers.length > 0) {
                let html = '';
                data.recentTransfers.forEach(t => {
                    let chipColor = 'rgba(255,255,255,0.06)';
                    let textColor = 'rgba(255,255,255,0.75)';
                    if (t.status === 'recibido') {
                        chipColor = 'rgba(16, 185, 129, 0.12)';
                        textColor = '#34d399';
                    } else if (t.status === 'en_transito') {
                        chipColor = 'rgba(245, 158, 11, 0.12)';
                        textColor = '#fbbf24';
                    }
                    html += `
                        <tr>
                            <td><strong>#${t.id}</strong></td>
                            <td>${t.from_warehouse}</td>
                            <td>${t.to_warehouse}</td>
                            <td>
                                <span class="chip" style="font-size:0.75rem; padding:0.25rem 0.6rem; border-radius: 999px; background:${chipColor}; color:${textColor}; border: 1px solid rgba(255,255,255,0.05); font-weight: 700;">
                                    ${t.status_label}
                                </span>
                            </td>
                            <td>${t.expected_date}</td>
                            <td>
                                <button class="pill-button ghost" onclick="location.href='{{ route('dashboard.almacen.transfers') }}'" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 999px; padding: 0.35rem 0.8rem; color: #fff; cursor: pointer; font-size: 0.78rem;">Ver Detalle</button>
                            </td>
                        </tr>
                    `;
                });
                transfersTableBody.innerHTML = html;
            } else {
                transfersTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align:center; color:rgba(255,255,255,0.35); padding: 2rem;">No hay traspasos registrados.</td>
                    </tr>
                `;
            }
        }
    }
</script>
@endpush
