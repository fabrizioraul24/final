<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('fitonist_theme') || 'dark';
                document.documentElement.classList.toggle('dark', theme === 'dark');
                document.documentElement.classList.toggle('light', theme !== 'dark');
                document.documentElement.style.colorScheme = theme === 'dark' ? 'dark' : 'light';
            } catch (error) {
                document.documentElement.classList.add('dark');
                document.documentElement.style.colorScheme = 'dark';
            }
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Almacen')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
    <link rel="stylesheet" href="{{ asset('landing/dashboard.css') }}">
    @vite('resources/css/app.css')
</head>
<body>
    <div class="dashboard-shell warehouse-shell">
        <button type="button" class="fit-sidebar-scrim" id="sidebarScrim" aria-label="Cerrar menu movil" hidden></button>
        <aside class="fit-sidebar warehouse-sidebar" id="sidebar">
            <div class="fit-sidebar-main">
                <div class="fit-sidebar-brand">
                    <div class="fit-sidebar-logo">
                        <span>PIL</span>
                    </div>
                    <div class="fit-sidebar-brand-copy">
                        <span class="fit-sidebar-title">Almacen Rio Seco</span>
                    </div>
                    <button type="button" class="fit-sidebar-close" id="sidebarClose" aria-label="Cerrar menu">
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <div class="fit-sidebar-nav-block">
                    <span class="fit-sidebar-section">Menu</span>
                    <nav class="fit-sidebar-nav" id="sidebarNav">
                        <a href="{{ route('dashboard.almacen') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.almacen') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-dashboard-line"></i></span><span class="fit-sidebar-copy"><span>Dashboard</span><small>{{ request()->routeIs('dashboard.almacen') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.almacen'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('dashboard.almacen.lots') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.almacen.lots*') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-box-3-line"></i></span><span class="fit-sidebar-copy"><span>Inventario</span><small>{{ request()->routeIs('dashboard.almacen.lots*') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.almacen.lots*'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('dashboard.almacen.transfers') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.almacen.transfers*') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-swap-box-line"></i></span><span class="fit-sidebar-copy"><span>Traspasos</span><small>{{ request()->routeIs('dashboard.almacen.transfers*') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.almacen.transfers*'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('dashboard.almacen.receptions') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.almacen.receptions*') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-truck-line"></i></span><span class="fit-sidebar-copy"><span>Pedidos</span><small>{{ request()->routeIs('dashboard.almacen.receptions*') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.almacen.receptions*'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('dashboard.almacen.damages') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.almacen.damages*') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-alert-line"></i></span><span class="fit-sidebar-copy"><span>Danos</span><small>{{ request()->routeIs('dashboard.almacen.damages*') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.almacen.damages*'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                    </nav>
                </div>
            </div>
        </aside>

        <main class="main-area">
            <header class="fit-navbar warehouse-navbar">
                <div class="fit-navbar-left">
                    <button class="fit-icon-button fit-mobile-menu" id="sidebarToggle" type="button" aria-label="Abrir menu"><i class="ri-menu-2-line"></i></button>
                    <div class="fit-navbar-title">
                        <i class="ri-archive-drawer-line"></i>
                        <span>@yield('page-title', 'Control operativo')</span>
                    </div>
                </div>
                <div class="fit-navbar-right">
                    @php
                        $warehouseInitials = collect(explode(' ', Auth::user()->name ?? 'Almacen Pil'))
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                            ->implode('');
                    @endphp
                    <div class="fit-navbar-search" role="search">
                        <i class="ri-search-line"></i>
                        <input type="search" placeholder="Buscar..." aria-label="Buscar en almacen">
                    </div>
                    <button class="fit-icon-button" type="button" title="Cambiar tema" id="themeToggle">
                        <i class="ri-sun-line" id="themeToggleIcon"></i>
                    </button>
                    <div class="fit-profile">
                        <button type="button" class="fit-profile-button" id="profileToggle">
                            <span class="fit-profile-avatar">{{ $warehouseInitials ?: 'AP' }}</span>
                            <span class="fit-profile-name">{{ Auth::user()->name ?? 'Almacen Pil' }}</span>
                            <i class="ri-arrow-down-s-line"></i>
                        </button>
                        <div class="fit-profile-menu" id="profileMenu" hidden>
                            <div class="fit-profile-menu-head">
                                <strong>{{ Auth::user()->name ?? 'Almacen Pil' }}</strong>
                                <span>{{ optional(Auth::user()->role)->name ?? 'Rol almacen' }}</span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"><i class="ri-logout-circle-r-line"></i> Cerrar sesion</button>
                            </form>
                            <button type="button" id="themeToggleMenu"><i class="ri-sun-line"></i> Cambiar tema</button>
                        </div>
                    </div>
                </div>
            </header>
            <section class="content-scroll">
                @yield('content')
            </section>
        </main>
    </div>
    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebarScrim = document.getElementById('sidebarScrim');
        const sidebar = document.getElementById('sidebar');
        const setSidebarOpen = (open) => {
            sidebar?.classList.toggle('open', open);
            if (sidebarScrim) sidebarScrim.hidden = !open;
        };
        sidebarToggle?.addEventListener('click', () => setSidebarOpen(!sidebar?.classList.contains('open')));
        sidebarClose?.addEventListener('click', () => setSidebarOpen(false));
        sidebarScrim?.addEventListener('click', () => setSidebarOpen(false));

        const applyTheme = (theme) => {
            const isDark = theme === 'dark';
            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.classList.toggle('light', !isDark);
            document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
            localStorage.setItem('fitonist_theme', theme);
            document.querySelectorAll('#themeToggleIcon, #themeToggleMenu i').forEach((icon) => {
                icon.className = isDark ? 'ri-sun-line' : 'ri-moon-line';
            });
        };
        const toggleTheme = () => applyTheme((localStorage.getItem('fitonist_theme') || 'dark') === 'dark' ? 'light' : 'dark');
        applyTheme(localStorage.getItem('fitonist_theme') || 'dark');
        document.getElementById('themeToggle')?.addEventListener('click', toggleTheme);
        document.getElementById('themeToggleMenu')?.addEventListener('click', toggleTheme);

        const profileToggle = document.getElementById('profileToggle');
        const profileMenu = document.getElementById('profileMenu');
        profileToggle?.addEventListener('click', (event) => {
            event.stopPropagation();
            if (profileMenu) profileMenu.hidden = !profileMenu.hidden;
        });
        document.addEventListener('click', (event) => {
            if (!event.target.closest('.fit-profile') && profileMenu) {
                profileMenu.hidden = true;
            }
        });
    </script>
    <script src="{{ asset('landing/dashboard-live-search.js') }}"></script>
    @stack('scripts')
</body>
</html>
