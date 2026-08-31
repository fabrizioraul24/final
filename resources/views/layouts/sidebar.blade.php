<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Pil Andina')</title>
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
                <span>Pil Andina</span>
            </div>
            <nav class="nav-section" id="sidebarNav">
                <a href="{{ route('dashboard.admin') }}" class="nav-item {{ request()->routeIs('dashboard.admin') ? 'active' : '' }}"><i class="ri-dashboard-line"></i>Dashboard</a>
                <a href="{{ route('dashboard.users') }}" class="nav-item {{ request()->routeIs('dashboard.users') ? 'active' : '' }}"><i class="ri-group-line"></i>Usuarios</a>
                <a href="{{ route('dashboard.companies') }}" class="nav-item {{ request()->routeIs('dashboard.companies*') ? 'active' : '' }}"><i class="ri-user-smile-line"></i>Clientes</a>
                <a href="{{ route('dashboard.products') }}" class="nav-item {{ request()->routeIs('dashboard.products*') ? 'active' : '' }}"><i class="ri-shopping-bag-line"></i>Productos</a>
                <a href="{{ route('dashboard.lots') }}" class="nav-item {{ request()->routeIs('dashboard.lots*') ? 'active' : '' }}"><i class="ri-archive-2-line"></i>Lotes</a>
                <a href="{{ route('dashboard.categories') }}" class="nav-item {{ request()->routeIs('dashboard.categories*') ? 'active' : '' }}"><i class="ri-price-tag-3-line"></i>Categorias</a>
                <a href="{{ route('dashboard.transfers') }}" class="nav-item {{ request()->routeIs('dashboard.transfers*') ? 'active' : '' }}"><i class="ri-shuffle-line"></i>Traspasos</a>
                <a href="{{ route('dashboard.sales') }}" class="nav-item {{ request()->routeIs('dashboard.sales*') ? 'active' : '' }}"><i class="ri-currency-line"></i>Ventas</a>
                <a href="{{ route('dashboard.quotations') }}" class="nav-item {{ request()->routeIs('dashboard.quotations*') ? 'active' : '' }}"><i class="ri-file-list-3-line"></i>Cotizaciones</a>
                <a href="{{ route('dashboard.logs') }}" class="nav-item {{ request()->routeIs('dashboard.logs') ? 'active' : '' }}"><i class="ri-history-line"></i>Logs</a>
                <a href="{{ route('dashboard.backups') }}" class="nav-item {{ request()->routeIs('dashboard.backups*') ? 'active' : '' }}"><i class="ri-shield-keyhole-line"></i>Backups</a>
                <a href="{{ route('admin.agent.replenishment') }}" class="nav-item {{ request()->routeIs('admin.agent.replenishment*') || request()->routeIs('dashboard.agent') ? 'active' : '' }}"><i class="ri-robot-2-line"></i>Agente Inteligente</a>
            </nav>
        </aside>
        <main class="main-area">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="icon-button" id="sidebarToggle"><i class="ri-menu-line"></i></button>
                    <div>
                        <p class="text-sm text-white/70" style="margin:0;font-size:0.85rem;">Pil Andina HQ</p>
                        <h1>@yield('page-title', 'Resumen Ejecutivo')</h1>
                    </div>
                </div>
                <div class="topbar-right">
                    <button class="icon-button" title="Notificaciones">
                        <i class="ri-notification-3-line"></i>
                    </button>
                    <div class="user-chip">
                        <i class="ri-user-3-line"></i>
                        <div>
                            <strong>{{ Auth::user()->name ?? 'Usuario Pil' }}</strong>
                            <p style="margin:0;font-size:0.8rem;color:rgba(255,255,255,0.7);">{{ optional(Auth::user()->role)->name ?? 'Rol no asignado' }}</p>
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

    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');

        sidebarToggle?.addEventListener('click', () => {
            sidebar?.classList.toggle('open');
        });

    </script>
    <script src="{{ asset('landing/dashboard-live-search.js') }}"></script>
    @stack('scripts')
</body>
</html>
