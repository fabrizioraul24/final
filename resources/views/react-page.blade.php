<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @isset($description)
        <meta name="description" content="{{ $description }}">
    @endisset
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
    @foreach ($stylesheets ?? [] as $stylesheet)
        <link rel="stylesheet" href="{{ $stylesheet }}">
    @endforeach
    @vite('resources/js/react-pages.jsx')
</head>
<body>
    <div id="react-root" data-page="{{ $page }}"></div>
    <script id="react-page-props" type="application/json">@json($props)</script>
</body>
</html>
