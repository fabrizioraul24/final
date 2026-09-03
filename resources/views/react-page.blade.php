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

        .fit-sidebar,
        html.light .fit-sidebar,
        .bg-\[\#4f46e5\],
        .bg-indigo-500,
        .bg-indigo-600,
        .bg-fitonist-purple {
            background-color: #0b4fc1 !important;
        }

        .border-indigo-600 {
            border-color: #0a3f9f !important;
        }

        .text-\[\#4f46e5\],
        .text-indigo-400,
        .text-indigo-500,
        .text-indigo-600,
        .text-indigo-700,
        .text-fitonist-purple {
            color: #0b4fc1 !important;
        }

        html.dark .text-indigo-400,
        html.dark .text-fitonist-purple {
            color: #7ea6ff !important;
        }

        .bg-amber-300,
        .bg-amber-400,
        .bg-amber-500\/20,
        .bg-orange-500 {
            background-color: rgba(242, 90, 89, 0.18) !important;
        }

        .text-amber-300,
        .text-amber-400,
        .text-amber-500,
        .text-orange-500 {
            color: #f25a59 !important;
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
    <div id="react-root" data-page="{{ $page }}"></div>
    <script id="react-page-props" type="application/json">@json($props)</script>
</body>
</html>
