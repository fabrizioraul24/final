<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Vendedor')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
    <link rel="stylesheet" href="{{ asset('landing/dashboard.css') }}">
</head>
<body>
    <div class="dashboard-shell">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <div style="width: 48px; height: 48px; border-radius: 1.2rem; overflow:hidden; display:flex; align-items:center; justify-content:center; background: rgba(255,255,255,0.08);">
                    <img src="{{ asset('storage/images/logo.png') }}" alt="Pil Andina" style="max-width:100%; max-height:100%; object-fit:contain;">
                </div>
                <span>Vendedor</span>
            </div>
            <nav class="nav-section" id="sidebarNav">
                <a href="{{ route('dashboard.vendedor.home') }}" class="nav-item {{ request()->routeIs('dashboard.vendedor.home') ? 'active' : '' }}"><i class="ri-dashboard-line"></i>Dashboard</a>
                <a href="{{ route('dashboard.vendedor.companies') }}" class="nav-item {{ request()->routeIs('dashboard.vendedor.companies') ? 'active' : '' }}"><i class="ri-price-tag-3-line"></i>Clientes</a>
                <a href="{{ route('dashboard.vendedor.sales') }}" class="nav-item {{ request()->routeIs('dashboard.vendedor.sales') ? 'active' : '' }}"><i class="ri-archive-line"></i>Ventas</a>
                <a href="{{ route('dashboard.vendedor.visits') }}" class="nav-item {{ request()->routeIs('dashboard.vendedor.visits*') ? 'active' : '' }}"><i class="ri-calendar-event-line"></i>Agenda</a>
                <a href="{{ route('dashboard.vendedor.quotations') }}" class="nav-item {{ request()->routeIs('dashboard.vendedor.quotations*') ? 'active' : '' }}"><i class="ri-file-list-3-line"></i>Cotizaciones</a>
                <a href="{{ route('dashboard.vendedor.sales.log') }}" class="nav-item {{ request()->routeIs('dashboard.vendedor.sales.log') ? 'active' : '' }}"><i class="ri-bar-chart-2-line"></i>Registro de ventas</a>
            </nav>
        </aside>
        <main class="main-area">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="icon-button" id="sidebarToggle"><i class="ri-menu-line"></i></button>
                    <div>
                        <p class="text-sm text-white/70" style="margin:0;font-size:0.85rem;">Pil Andina | Vendedor</p>
                        <h1>@yield('page-title', 'Panel de Vendedor')</h1>
                    </div>
                </div>
                <div class="topbar-right">
                    @php
                        $upcomingVisits = 0;
                        $todayVisits = collect();
                        if(Auth::check()) {
                            try {
                                $todayVisits = \App\Models\VendorVisit::with('company')
                                    ->where('user_id', Auth::id())
                                    ->whereDate('visit_date', now()->toDateString())
                                    ->orderBy('visit_date')
                                    ->get();
                                $upcomingVisits = \App\Models\VendorVisit::where('user_id', Auth::id())
                                    ->whereDate('visit_date', '>=', now()->toDateString())
                                    ->count();
                            } catch (\Throwable $e) {
                                $todayVisits = collect();
                                $upcomingVisits = 0;
                            }
                        }
                    @endphp
                    <button class="icon-button" type="button" title="Notificaciones" id="notifToggle" style="position:relative;">
                        <i class="ri-notification-3-line"></i>
                        @if(($upcomingVisits ?? 0) > 0)
                            <span style="position:absolute; top:-6px; right:-6px; background:#f97316; color:#fff; border-radius:999px; padding:2px 6px; font-size:0.75rem; font-weight:800;">{{ $upcomingVisits }}</span>
                        @endif
                    </button>
                    <div class="user-chip">
                        <i class="ri-user-3-line"></i>
                        <div>
                            <strong>{{ Auth::user()->name ?? 'Vendedor Pil' }}</strong>
                            <p style="margin:0;font-size:0.8rem;color:rgba(255,255,255,0.7);">{{ optional(Auth::user()->role)->name ?? 'Rol vendedor' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="pill-button" type="submit">Cerrar sesion</button>
                    </form>
                </div>
            </header>
            <section class="content-scroll">
                @yield('content')
            </section>
        </main>
    </div>
    @if(isset($todayVisits))
    <div class="modal" id="notifModal" style="display:none;">
        <div class="modal-content" style="max-width:520px;">
            <div class="modal-header">
                <h3>Notificaciones</h3>
                <button class="close-button" type="button" id="closeNotif">&times;</button>
            </div>
            <div style="max-height:360px; overflow-y:auto;">
                @if($todayVisits->count())
                    <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:10px;">
                        @foreach($todayVisits as $visit)
                            <li class="card" style="padding:10px;">
                                <strong>{{ $visit->company->name ?? 'Cliente' }}</strong>
                                <p style="margin:2px 0; color:rgba(255,255,255,0.8);">NIT: {{ $visit->company->nit ?? 'N/D' }}</p>
                                <p style="margin:2px 0; color:rgba(255,255,255,0.8);">Fecha: {{ optional($visit->visit_date)->format('Y-m-d') }}</p>
                                <p style="margin:2px 0; color:rgba(255,255,255,0.75);">Nota: {{ $visit->note ?? 'Sin nota' }}</p>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p style="margin:0;">Sin visitas hoy.</p>
                @endif
            </div>
            <div style="margin-top:12px; display:flex; justify-content:flex-end; gap:8px;">
                <a class="pill-button" href="{{ route('dashboard.vendedor.visits') }}">Ver agenda</a>
                <button class="btn-secondary" type="button" id="closeNotif2">Cerrar</button>
            </div>
        </div>
    </div>
    @endif
    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        sidebarToggle?.addEventListener('click', () => {
            sidebar?.classList.toggle('open');
        });

        const notifToggle = document.getElementById('notifToggle');
        const notifModal = document.getElementById('notifModal');
        const closeNotif = document.getElementById('closeNotif');
        const closeNotif2 = document.getElementById('closeNotif2');
        notifToggle?.addEventListener('click', () => {
            if (notifModal) notifModal.style.display = 'flex';
        });
        closeNotif?.addEventListener('click', () => {
            if (notifModal) notifModal.style.display = 'none';
        });
        closeNotif2?.addEventListener('click', () => {
            if (notifModal) notifModal.style.display = 'none';
        });
        window.addEventListener('click', (event) => {
            if (event.target === notifModal) notifModal.style.display = 'none';
        });
    </script>
    <script src="{{ asset('landing/dashboard-live-search.js') }}"></script>
    @stack('scripts')
</body>
</html>
