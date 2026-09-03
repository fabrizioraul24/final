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
    <title>@yield('title', 'Panel Admin | PIL Bolivia')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
    <link rel="stylesheet" href="{{ asset('landing/dashboard.css') }}">
    @vite('resources/css/app.css')
</head>
<body>
    <div class="dashboard-shell admin-shell">
        <button type="button" class="fit-sidebar-scrim" id="sidebarScrim" aria-label="Cerrar menu movil" hidden></button>
        <aside class="fit-sidebar" id="sidebar">
            <div class="fit-sidebar-main">
                <div class="fit-sidebar-brand">
                    <div class="fit-sidebar-logo">
                        <img src="{{ asset('storage/images/logo.png') }}" alt="Pil Andina">
                    </div>
                    <div class="fit-sidebar-brand-copy">
                        <span class="fit-sidebar-title">PIL Bolivia</span>
                        <span class="fit-sidebar-subtitle">Panel Admin</span>
                    </div>
                    <button type="button" class="fit-sidebar-close" id="sidebarClose" aria-label="Cerrar menu">
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <div class="fit-sidebar-nav-block">
                    <span class="fit-sidebar-section">Menu Principal</span>
                    <nav class="fit-sidebar-nav" id="sidebarNav">
                        <a href="{{ route('dashboard.admin') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.admin') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-dashboard-line"></i></span><span class="fit-sidebar-copy"><span>Dashboard</span><small>{{ request()->routeIs('dashboard.admin') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.admin'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('dashboard.users') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.users') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-group-line"></i></span><span class="fit-sidebar-copy"><span>Usuarios</span><small>{{ request()->routeIs('dashboard.users') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.users'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('dashboard.companies') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.companies*') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-user-smile-line"></i></span><span class="fit-sidebar-copy"><span>Clientes</span><small>{{ request()->routeIs('dashboard.companies*') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.companies*'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('dashboard.products') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.products*') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-shopping-bag-line"></i></span><span class="fit-sidebar-copy"><span>Productos</span><small>{{ request()->routeIs('dashboard.products*') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.products*'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('dashboard.lots') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.lots*') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-archive-2-line"></i></span><span class="fit-sidebar-copy"><span>Lotes</span><small>{{ request()->routeIs('dashboard.lots*') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.lots*'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('dashboard.categories') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.categories*') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-price-tag-3-line"></i></span><span class="fit-sidebar-copy"><span>Categorias</span><small>{{ request()->routeIs('dashboard.categories*') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.categories*'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('dashboard.transfers') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.transfers*') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-shuffle-line"></i></span><span class="fit-sidebar-copy"><span>Traspasos</span><small>{{ request()->routeIs('dashboard.transfers*') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.transfers*'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('dashboard.sales') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.sales*') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-currency-line"></i></span><span class="fit-sidebar-copy"><span>Ventas</span><small>{{ request()->routeIs('dashboard.sales*') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.sales*'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('dashboard.quotations') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.quotations*') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-file-list-3-line"></i></span><span class="fit-sidebar-copy"><span>Cotizaciones</span><small>{{ request()->routeIs('dashboard.quotations*') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.quotations*'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('dashboard.logs') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.logs') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-history-line"></i></span><span class="fit-sidebar-copy"><span>Logs</span><small>{{ request()->routeIs('dashboard.logs') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.logs'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('dashboard.backups') }}" class="fit-sidebar-item {{ request()->routeIs('dashboard.backups*') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-shield-keyhole-line"></i></span><span class="fit-sidebar-copy"><span>Backups</span><small>{{ request()->routeIs('dashboard.backups*') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('dashboard.backups*'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                        <a href="{{ route('admin.agent.replenishment') }}" class="fit-sidebar-item {{ request()->routeIs('admin.agent.replenishment*') || request()->routeIs('dashboard.agent') ? 'active' : '' }}">
                            <span class="fit-sidebar-item-main"><span class="fit-sidebar-icon"><i class="ri-robot-2-line"></i></span><span class="fit-sidebar-copy"><span>Agente IA</span><small>{{ request()->routeIs('admin.agent.replenishment*') ? 'Actual' : 'Modulo' }}</small></span></span>
                            @if(request()->routeIs('admin.agent.replenishment*'))<span class="fit-sidebar-badge">Actual</span>@endif
                        </a>
                    </nav>
                </div>
            </div>
        </aside>

        <main class="main-area">
            <header class="fit-navbar">
                <div class="fit-navbar-left">
                    <button class="fit-icon-button fit-mobile-menu" id="sidebarToggle" type="button" aria-label="Abrir menu"><i class="ri-menu-2-line"></i></button>
                    <div class="fit-navbar-title">
                        <i class="ri-shield-user-line"></i>
                        <span>@yield('page-title', 'Resumen Ejecutivo')</span>
                    </div>
                </div>
                <div class="fit-navbar-right">
                    @php
                        $adminInitials = collect(explode(' ', Auth::user()->name ?? 'Admin Pil'))
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                            ->implode('');
                    @endphp
                    <div class="fit-navbar-search" role="search">
                        <i class="ri-search-line"></i>
                        <input type="search" placeholder="Buscar..." aria-label="Buscar en admin">
                    </div>
                    <button class="fit-icon-button" type="button" title="Cambiar tema" id="themeToggle">
                        <i class="ri-sun-line" id="themeToggleIcon"></i>
                    </button>
                    <div class="fit-profile">
                        <button type="button" class="fit-profile-button" id="profileToggle">
                            <span class="fit-profile-avatar">{{ $adminInitials ?: 'AP' }}</span>
                            <span class="fit-profile-name">{{ Auth::user()->name ?? 'Admin Pil' }}</span>
                            <i class="ri-arrow-down-s-line"></i>
                        </button>
                        <div class="fit-profile-menu" id="profileMenu" hidden>
                            <div class="fit-profile-menu-head">
                                <strong>{{ Auth::user()->name ?? 'Admin Pil' }}</strong>
                                <span>{{ optional(Auth::user()->role)->name ?? 'Administrador' }}</span>
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
