<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        :root {
            --primary: #4e6baf;
            --text-dark: #1c1c2d;
            --text-muted: #5f6a85;
            --border: #e1e4f2;
        }
        * { font-family: 'Inter', Arial, sans-serif; box-sizing: border-box; }
        body { margin: 0; padding: 2.5rem; color: var(--text-dark); }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.8rem; padding-bottom: 1rem; border-bottom: 2px solid var(--border); }
        .brand { font-size: 1.2rem; font-weight: 700; color: var(--primary); letter-spacing: 0.05em; }
        .meta { text-align: right; font-size: 0.85rem; color: var(--text-muted); }
        h1 { font-size: 1.6rem; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th { text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em; text-align: left; padding: 0.75rem; background: rgba(78, 107, 175, 0.1); color: var(--text-muted); }
        td { padding: 0.75rem; border-bottom: 1px solid var(--border); font-size: 0.85rem; }
        .summary { display: flex; gap: 1rem; margin-top: 1rem; }
        .summary-card { flex: 1; border: 1px solid var(--border); border-radius: 0.75rem; padding: 0.8rem 1rem; }
        .summary-card strong { display: block; color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.25rem; }
        .summary-card span { font-size: 1.2rem; font-weight: 700; }
        .chart-block { margin-top: 1.2rem; border: 1px solid var(--border); border-radius: 0.75rem; padding: 1rem; }
        .chart-title { margin: 0 0 0.6rem; color: var(--text-muted); font-size: 0.95rem; font-weight: 700; }
        .bar-row { display: flex; align-items: center; gap: 0.6rem; margin: 0.4rem 0; font-size: 0.85rem; }
        .bar-label { flex: 0 0 140px; color: var(--text-muted); }
        .bar-track { flex: 1; height: 12px; background: rgba(78,107,175,0.12); border-radius: 999px; overflow: hidden; }
        .bar-fill { height: 100%; background: linear-gradient(90deg, #4e6baf, #86acd4); }
        .bar-value { min-width: 70px; text-align: right; font-weight: 700; }
    </style>
</head>
<body>
    <header>
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <img src="{{ public_path('storage/images/logo.png') }}" alt="Pil Andina" style="height:48px; width:auto;">
            <div>
                <div class="brand">Pil Andina - Reportes Ejecutivos</div>
                <h1>{{ $title ?? 'Reporte' }}</h1>
            </div>
        </div>
        <div class="meta">
            <div>{{ $generatedAt->format('d/m/Y H:i') }}</div>
            <div>Emitido por: {{ auth()->user()->name ?? 'Sistema' }}</div>
        </div>
    </header>

    @yield('content')
</body>
</html>

