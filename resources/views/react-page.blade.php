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
    <style>
        @view-transition {
            navigation: auto;
        }

        html,
        body,
        #react-root {
            min-height: 100%;
        }

        html {
            background: #0e0f14;
        }

        html.light {
            background: #f4f6fc;
        }

        html.dark {
            background: #0e0f14;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #0e0f14;
        }

        html.light body,
        html.light #react-root {
            background: #f4f6fc;
        }

        html.dark body,
        html.dark #react-root {
            background: #0e0f14;
        }

        #react-root {
            min-height: 100vh;
        }

        .react-boot-loader {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 264px minmax(0, 1fr);
            background: #0e0f14;
            color: #f8fafc;
            opacity: 1;
            transition: opacity 160ms ease;
        }

        html.light .react-boot-loader {
            background: #f4f6fc;
            color: #1f2937;
        }

        .react-boot-sidebar {
            min-height: 100vh;
            padding: 24px 18px;
            background: #151722;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        html.light .react-boot-sidebar {
            background: #4f46e5;
            border-right-color: rgba(79, 70, 229, 0.55);
        }

        .react-boot-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 28px;
        }

        .react-boot-logo {
            width: 40px;
            height: 40px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.24);
            color: #ffffff;
            font: 900 13px/1 Inter, system-ui, sans-serif;
        }

        .react-boot-title {
            display: grid;
            gap: 5px;
        }

        .react-boot-line {
            display: block;
            height: 10px;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.28);
        }

        .react-boot-line.brand {
            width: 116px;
            height: 14px;
            background: rgba(255, 255, 255, 0.76);
        }

        .react-boot-line.sub {
            width: 88px;
            height: 8px;
        }

        .react-boot-pill {
            height: 46px;
            border-radius: 18px;
            margin-bottom: 26px;
            background: #4f46e5;
            box-shadow: 0 14px 24px rgba(79, 70, 229, 0.22);
        }

        html.light .react-boot-pill {
            background: #ffffff;
        }

        .react-boot-nav {
            display: grid;
            gap: 12px;
        }

        .react-boot-nav span {
            height: 48px;
            border-radius: 18px;
            background: rgba(148, 163, 184, 0.12);
        }

        html.light .react-boot-nav span {
            background: rgba(255, 255, 255, 0.18);
        }

        .react-boot-main {
            min-width: 0;
            padding: 22px 30px;
        }

        .react-boot-topbar {
            height: 52px;
            border-radius: 26px;
            margin-bottom: 28px;
            background: rgba(21, 23, 32, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        html.light .react-boot-topbar {
            background: #ffffff;
            border-color: rgba(79, 70, 229, 0.14);
        }

        .react-boot-card-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
        }

        .react-boot-card {
            min-height: 150px;
            border-radius: 22px;
            background: #161820;
            border: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
            overflow: hidden;
        }

        html.light .react-boot-card {
            background: #ffffff;
            border-color: rgba(79, 70, 229, 0.12);
        }

        .react-boot-card::after {
            content: "";
            position: absolute;
            inset: 0;
            transform: translateX(-100%);
            background: linear-gradient(90deg, transparent, rgba(129, 140, 248, 0.16), transparent);
            animation: react-boot-shimmer 1.15s ease-in-out infinite;
        }

        .react-boot-status {
            position: fixed;
            right: 30px;
            bottom: 26px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 999px;
            background: #151722;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.22);
            font: 800 12px/1 Inter, system-ui, sans-serif;
        }

        html.light .react-boot-status {
            background: #ffffff;
            border-color: rgba(79, 70, 229, 0.14);
            box-shadow: 0 18px 36px rgba(79, 70, 229, 0.12);
        }

        .react-boot-spinner {
            width: 16px;
            height: 16px;
            border-radius: 999px;
            border: 2px solid rgba(129, 140, 248, 0.28);
            border-top-color: #4f46e5;
            animation: react-boot-spin 700ms linear infinite;
        }

        .admin-route-loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: grid;
            place-items: center;
            background: rgba(14, 15, 20, 0.88);
            opacity: 0;
            pointer-events: none;
            transition: opacity 120ms ease;
        }

        html.light .admin-route-loader {
            background: rgba(244, 246, 252, 0.88);
        }

        .admin-route-loader.is-active {
            opacity: 1;
            pointer-events: auto;
        }

        .admin-route-loader-card {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            min-width: 190px;
            padding: 14px 18px;
            border-radius: 999px;
            background: #151722;
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #f8fafc;
            box-shadow: 0 24px 50px rgba(0, 0, 0, 0.28);
            font: 900 13px/1 Inter, system-ui, sans-serif;
        }

        html.light .admin-route-loader-card {
            background: #ffffff;
            border-color: rgba(79, 70, 229, 0.14);
            color: #1f2937;
            box-shadow: 0 24px 50px rgba(79, 70, 229, 0.16);
        }

        @keyframes react-boot-shimmer {
            to {
                transform: translateX(100%);
            }
        }

        @keyframes react-boot-spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 900px) {
            .react-boot-loader {
                grid-template-columns: 1fr;
            }

            .react-boot-sidebar {
                display: none;
            }

            .react-boot-main {
                padding: 18px;
            }

            .react-boot-card-grid {
                grid-template-columns: 1fr;
            }

            .react-boot-status {
                right: 18px;
                bottom: 18px;
            }
        }

        ::view-transition-old(root) {
            animation: admin-page-out 120ms ease both;
        }

        ::view-transition-new(root) {
            animation: admin-page-in 180ms cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes admin-page-out {
            from {
                opacity: 1;
            }

            to {
                opacity: 0.96;
            }
        }

        @keyframes admin-page-in {
            from {
                opacity: 0.96;
            }

            to {
                opacity: 1;
            }
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @isset($description)
        <meta name="description" content="{{ $description }}">
    @endisset
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
    @foreach ($stylesheets ?? [] as $stylesheet)
        <link rel="stylesheet" href="{{ $stylesheet }}">
    @endforeach
    @viteReactRefresh
    @vite('resources/js/react-pages.jsx')
</head>
<body>
    <div id="react-root" data-page="{{ $page }}">
        @if (str_starts_with($page, 'admin'))
        <div class="react-boot-loader" role="status" aria-live="polite" aria-label="Cargando modulo">
            <aside class="react-boot-sidebar" aria-hidden="true">
                <div class="react-boot-brand">
                    <div class="react-boot-logo">PIL</div>
                    <div class="react-boot-title">
                        <span class="react-boot-line brand"></span>
                        <span class="react-boot-line sub"></span>
                    </div>
                </div>
                <div class="react-boot-pill"></div>
                <div class="react-boot-nav">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </aside>
            <main class="react-boot-main" aria-hidden="true">
                <div class="react-boot-topbar"></div>
                <div class="react-boot-card-grid">
                    <div class="react-boot-card"></div>
                    <div class="react-boot-card"></div>
                    <div class="react-boot-card"></div>
                    <div class="react-boot-card"></div>
                    <div class="react-boot-card"></div>
                    <div class="react-boot-card"></div>
                </div>
            </main>
            <div class="react-boot-status">
                <span class="react-boot-spinner"></span>
                <span>Cargando modulo</span>
            </div>
        </div>
        @endif
    </div>
    @if (str_starts_with($page, 'admin'))
    <div class="admin-route-loader" id="admin-route-loader" aria-hidden="true">
        <div class="admin-route-loader-card">
            <span class="react-boot-spinner"></span>
            <span>Cargando modulo</span>
        </div>
    </div>
    <script>
        (function () {
            var loader = document.getElementById('admin-route-loader');

            if (!loader) {
                return;
            }

            document.addEventListener('click', function (event) {
                var link = event.target.closest('a[href]');

                if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }

                var url = new URL(link.href, window.location.href);
                var isAdminRoute = url.origin === window.location.origin
                    && (url.pathname.indexOf('/dashboard/') === 0 || url.pathname.indexOf('/admin/') === 0);

                if (!isAdminRoute || url.href === window.location.href || link.target) {
                    return;
                }

                loader.classList.add('is-active');
            }, true);

            window.addEventListener('pageshow', function () {
                loader.classList.remove('is-active');
            });
        })();
    </script>
    @endif
    <script id="react-page-props" type="application/json">@json($props)</script>
</body>
</html>
