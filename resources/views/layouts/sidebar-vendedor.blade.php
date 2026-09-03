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
    <title>@yield('title', 'Panel Vendedor')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
    <link rel="stylesheet" href="{{ asset('landing/dashboard.css') }}">
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/react-vendor-shell.jsx'])
</head>
@php
    $vendorNavItems = [
        [
            'label' => 'Dashboard',
            'href' => route('dashboard.vendedor.home'),
            'icon' => 'ri-dashboard-line',
            'page' => null,
            'active' => request()->routeIs('dashboard.vendedor.home'),
        ],
        [
            'label' => 'Clientes',
            'href' => route('dashboard.vendedor.companies'),
            'icon' => 'ri-user-smile-line',
            'page' => null,
            'active' => request()->routeIs('dashboard.vendedor.companies'),
        ],
        [
            'label' => 'Ventas',
            'href' => route('dashboard.vendedor.sales'),
            'icon' => 'ri-currency-line',
            'page' => null,
            'active' => request()->routeIs('dashboard.vendedor.sales'),
        ],
        [
            'label' => 'Agenda',
            'href' => route('dashboard.vendedor.visits'),
            'icon' => 'ri-calendar-event-line',
            'page' => null,
            'active' => request()->routeIs('dashboard.vendedor.visits*'),
        ],
        [
            'label' => 'Cotizaciones',
            'href' => route('dashboard.vendedor.quotations'),
            'icon' => 'ri-file-list-3-line',
            'page' => null,
            'active' => request()->routeIs('dashboard.vendedor.quotations*'),
        ],
        [
            'label' => 'Registro de ventas',
            'href' => route('dashboard.vendedor.sales.log'),
            'icon' => 'ri-bar-chart-2-line',
            'page' => null,
            'active' => request()->routeIs('dashboard.vendedor.sales.log'),
        ],
    ];

    $vendorShellProps = [
        'sidebar' => [
            'title' => 'PIL Bolivia',
            'subtitle' => 'Panel Vendedor',
            'items' => $vendorNavItems,
        ],
        'topbar' => [
            'pageTitle' => trim($__env->yieldContent('page-title', 'Panel de Vendedor')),
            'user' => [
                'name' => Auth::user()->name ?? 'Vendedor Pil',
                'email' => Auth::user()->email ?? '',
                'role' => optional(Auth::user()?->role)->name ?? 'Rol vendedor',
            ],
        ],
        'csrfToken' => csrf_token(),
        'logoutAction' => route('logout'),
    ];
@endphp
<body>
    <div id="vendor-react-shell"></div>
    <div class="dashboard-shell">
        <div id="vendor-sidebar-root"></div>
        <main class="main-area">
            <div id="vendor-topbar-root"></div>
            <section class="content-scroll">
                @yield('content')
            </section>
        </main>
    </div>
    <script id="vendor-shell-props" type="application/json">@json($vendorShellProps)</script>
    <script src="{{ asset('landing/dashboard-live-search.js') }}"></script>
    @stack('scripts')
</body>
</html>
