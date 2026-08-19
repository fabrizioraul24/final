<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Almacén Pil Andina')</title>
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
                <span>Almacén</span>
            </div>
            <nav class="nav-section">
                <a href="{{ route('dashboard.almacen') }}" class="nav-item {{ request()->routeIs('dashboard.almacen') ? 'active' : '' }}"><i class="ri-dashboard-line"></i>Dashboard</a>
                <a href="{{ route('dashboard.almacen.lots') }}" class="nav-item {{ request()->routeIs('dashboard.almacen.lots') ? 'active' : '' }}"><i class="ri-box-3-line"></i>Inventario por lotes</a>
                <a href="{{ route('dashboard.almacen.transfers') }}" class="nav-item {{ request()->routeIs('dashboard.almacen.transfers') ? 'active' : '' }}"><i class="ri-swap-box-line"></i>Traspasos</a>
                <a href="{{ route('dashboard.almacen.receptions') }}" class="nav-item {{ request()->routeIs('dashboard.almacen.receptions') ? 'active' : '' }}"><i class="ri-truck-line"></i>Pedidos en bodega</a>
                <a href="{{ route('dashboard.almacen.damages') }}" class="nav-item {{ request()->routeIs('dashboard.almacen.damages') ? 'active' : '' }}"><i class="ri-alert-line"></i>Registro de daños</a>
            </nav>
        </aside>
        <main class="main-area">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="icon-button" id="sidebarToggle"><i class="ri-menu-line"></i></button>
                    <div>
                        <p class="text-sm text-white/70" style="margin:0;font-size:0.85rem;">Pil Andina | Almacén</p>
                        <h1>@yield('page-title', 'Control operativo')</h1>
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="user-chip">
                        <i class="ri-user-settings-line"></i>
                        <div>
                            <strong>{{ Auth::user()->name ?? 'Usuario Almacén' }}</strong>
                            <p style="margin:0;font-size:0.8rem;color:rgba(255,255,255,0.7);">{{ optional(Auth::user()->role)->name ?? 'Rol almacén' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="pill-button" type="submit">Cerrar sesión</button>
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
        sidebarToggle?.addEventListener('click', () => sidebar?.classList.toggle('open'));
    </script>
    @stack('scripts')
</body>
</html>
