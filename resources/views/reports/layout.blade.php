<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { font-family: 'Inter', Arial, sans-serif; box-sizing: border-box; }
        body { margin: 0; padding: 2.35rem 2.35rem 3rem; color: #1c1c2d; }
        header { width: 100%; margin-bottom: 1.8rem; padding-bottom: 1rem; border-bottom: 2px solid #e1e4f2; }
        .header-left, .header-right { display: inline-block; vertical-align: middle; }
        .header-left { width: 64%; }
        .header-right { width: 34%; text-align: right; }
        .logo-cell, .title-cell { display: inline-block; vertical-align: middle; }
        .logo-cell { margin-right: 0.75rem; }
        .brand { font-size: 1.2rem; font-weight: 700; color: #4e6baf; letter-spacing: 0.05em; }
        .meta { text-align: right; font-size: 0.85rem; color: #5f6a85; }
        .report-code { display: inline-block; margin-top: 0.35rem; padding: 0.25rem 0.5rem; border: 1px solid #e1e4f2; border-radius: 999px; color: #4e6baf; font-size: 0.72rem; font-weight: 700; }
        h1 { font-size: 1.6rem; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th { text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em; text-align: left; padding: 0.75rem; background: #eef2fb; color: #5f6a85; }
        td { padding: 0.75rem; border-bottom: 1px solid #e1e4f2; font-size: 0.85rem; }
        .summary { width: 100%; margin-top: 1rem; }
        .summary-card { display: inline-block; width: 18.7%; min-height: 72px; margin-right: 0.45%; border: 1px solid #e1e4f2; border-radius: 0.75rem; padding: 0.8rem 1rem; vertical-align: top; }
        .summary-card strong { display: block; color: #5f6a85; font-size: 0.75rem; margin-bottom: 0.25rem; }
        .summary-card span { font-size: 1.2rem; font-weight: 700; }
        .chart-block { margin-top: 1.2rem; border: 1px solid #e1e4f2; border-radius: 0.75rem; padding: 1rem; }
        .chart-title { margin: 0 0 0.6rem; color: #5f6a85; font-size: 0.95rem; font-weight: 700; }
        .bar-row { width: 100%; margin: 0.45rem 0; font-size: 0.85rem; }
        .bar-label { display: inline-block; width: 140px; color: #5f6a85; vertical-align: middle; }
        .bar-track { display: inline-block; width: 64%; height: 12px; background: #eef2fb; border-radius: 999px; overflow: hidden; vertical-align: middle; }
        .bar-fill { height: 12px; background: #4e6baf; }
        .bar-value { display: inline-block; width: 70px; text-align: right; font-weight: 700; vertical-align: middle; }
        footer { margin-top: 1.4rem; padding-top: 0.55rem; border-top: 1px solid #e1e4f2; color: #5f6a85; font-size: 0.72rem; }
        .footer-left { float: left; }
        .footer-right { float: right; color: #4e6baf; font-weight: 700; }
    </style>
</head>
<body>
    <header>
        <div class="header-left">
            <div class="logo-cell"><img src="{{ public_path('storage/images/logo.png') }}" alt="Pil Andina" style="height:48px; width:auto;"></div>
            <div class="title-cell">
                <div class="brand">Pil Andina - Reportes Ejecutivos</div>
                <h1>{{ $title ?? 'Reporte' }}</h1>
                <div class="report-code">Codigo: {{ $reportCode ?? 'RPT-SIN-CODIGO' }}</div>
            </div>
        </div>
        <div class="header-right meta">
            <div>{{ $generatedAt->format('d/m/Y H:i') }}</div>
            <div>Emitido por: {{ auth()->user()->name ?? 'Sistema' }}</div>
            <div>Codigo: {{ $reportCode ?? 'RPT-SIN-CODIGO' }}</div>
        </div>
    </header>

    @yield('content')

    <footer>
        <span class="footer-left">Pil Andina - documento interno generado por el sistema</span>
        <span class="footer-right">Codigo de reporte: {{ $reportCode ?? 'RPT-SIN-CODIGO' }}</span>
    </footer>
</body>
</html>
