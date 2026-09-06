@extends('reports.layout')

@section('content')
    @php
        $companyTypes = \App\Models\Company::TYPES;
        $institutional = $stats['institutional'] ?? $companies->where('company_type', 'empresa_institucional')->count();
        $retail = $stats['retail'] ?? $companies->where('company_type', 'tienda_barrio')->count();
        $withEmail = $stats['with_email'] ?? $companies->filter(fn($company) => filled($company->email))->count();
        $withPhone = $companies->filter(fn($company) => filled($company->phone))->count();
        $total = max($stats['total'] ?? $companies->count(), 1);
        $byCity = $companies->groupBy(fn($company) => $company->city ?: 'Sin ciudad')->map->count()->sortDesc();
        $maxCity = max($byCity->values()->all() ?: [1]);
        $typeLabel = $filters['type'] ? ($companyTypes[$filters['type']] ?? $filters['type']) : 'Todos';
    @endphp

    <style>
        .vendor-clients-report .summary { display: table; width: 100%; border-spacing: 0.45rem 0; table-layout: fixed; }
        .vendor-clients-report .summary-card { display: table-cell; width: 33.333%; min-height: 58px; padding: 0.55rem 0.7rem; vertical-align: top; }
        .vendor-clients-report .summary-card span { font-size: 0.98rem; }
        .summary-card.blue { border-left: 5px solid #4e6baf; }
        .summary-card.green { border-left: 5px solid #10b981; }
        .summary-card.cyan { border-left: 5px solid #0ea5e9; }
        .summary-card.amber { border-left: 5px solid #f59e0b; }
        .summary-card.rose { border-left: 5px solid #e11d48; }
        .report-note { margin: 0.2rem 0 0.9rem; color: #5f6a85; font-size: 0.84rem; }
        .bar-fill.green { background: #10b981; }
        .bar-fill.cyan { background: #0ea5e9; }
        .bar-fill.amber { background: #f59e0b; }
        .client-table { margin-top: 0.75rem; }
        .client-table th,
        .client-table td { font-size: 0.65rem; padding: 0.38rem 0.42rem; vertical-align: top; }
        .client-table th:nth-child(1) { width: 27%; }
        .client-table th:nth-child(2) { width: 12%; }
        .client-table th:nth-child(3) { width: 17%; }
        .client-table th:nth-child(4) { width: 13%; }
        .client-table th:nth-child(5) { width: 31%; }
        .client-name { color: #1c1c2d; font-weight: 700; }
        .client-owner { display: block; margin-top: 0.12rem; color: #5f6a85; font-size: 0.58rem; }
        .type-pill { display: inline-block; padding: 0.18rem 0.46rem; border-radius: 999px; font-size: 0.6rem; font-weight: 700; }
        .type-pill.institutional { background: #e0ecff; color: #4e6baf; }
        .type-pill.retail { background: #dcfce7; color: #047857; }
        .contact-line { display: block; white-space: nowrap; }
        .contact-line + .contact-line { margin-top: 0.12rem; color: #5f6a85; }
    </style>

    <div class="vendor-clients-report">
        <p class="report-note">
            Cartera del vendedor {{ $vendor->name ?? 'Vendedor Pil' }}. Tipo: {{ $typeLabel }}.
            @if($filters['search'])
                Busqueda: "{{ $filters['search'] }}".
            @endif
        </p>

        <div class="summary">
            <div class="summary-card blue">
                <strong>Total clientes</strong>
                <span>{{ $stats['total'] ?? $companies->count() }}</span>
            </div>
            <div class="summary-card green">
                <strong>Institucionales</strong>
                <span>{{ $institutional }}</span>
            </div>
            <div class="summary-card cyan">
                <strong>Tiendas barrio</strong>
                <span>{{ $retail }}</span>
            </div>
        </div>

        <div class="summary">
            <div class="summary-card amber">
                <strong>Con telefono</strong>
                <span>{{ $withPhone }}</span>
            </div>
            <div class="summary-card green">
                <strong>Con correo</strong>
                <span>{{ $withEmail }}</span>
            </div>
            <div class="summary-card rose">
                <strong>Ciudades</strong>
                <span>{{ $byCity->count() }}</span>
            </div>
        </div>

        <div class="chart-block">
            <p class="chart-title">Tipos de cliente</p>
            <div class="bar-row">
                <span class="bar-label">Institucional</span>
                <div class="bar-track"><div class="bar-fill green" style="width: {{ ($institutional / $total) * 100 }}%;"></div></div>
                <span class="bar-value">{{ $institutional }}</span>
            </div>
            <div class="bar-row">
                <span class="bar-label">Tienda barrio</span>
                <div class="bar-track"><div class="bar-fill cyan" style="width: {{ ($retail / $total) * 100 }}%;"></div></div>
                <span class="bar-value">{{ $retail }}</span>
            </div>
        </div>

        <div class="chart-block">
            <p class="chart-title">Clientes por ciudad</p>
            @forelse($byCity->take(6) as $city => $count)
                <div class="bar-row">
                    <span class="bar-label">{{ $city }}</span>
                    <div class="bar-track"><div class="bar-fill amber" style="width: {{ ($count / max($maxCity, 1)) * 100 }}%;"></div></div>
                    <span class="bar-value">{{ $count }}</span>
                </div>
            @empty
                <p class="report-note">Sin ciudades para mostrar.</p>
            @endforelse
        </div>

        <h3 style="margin-top:1.3rem;">Detalle de clientes</h3>
        <table class="client-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>NIT</th>
                    <th>Tipo</th>
                    <th>Ciudad</th>
                    <th>Contacto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                    <tr>
                        <td>
                            <span class="client-name">{{ $company->name }}</span>
                            <span class="client-owner">Resp.: {{ trim($company->owner_first_name.' '.$company->owner_last_name_paterno.' '.$company->owner_last_name_materno) ?: 'Sin responsable' }}</span>
                        </td>
                        <td>{{ $company->nit }}</td>
                        <td>
                            <span class="type-pill {{ $company->company_type === 'empresa_institucional' ? 'institutional' : 'retail' }}">
                                {{ $companyTypes[$company->company_type] ?? $company->company_type }}
                            </span>
                        </td>
                        <td>{{ $company->city ?: 'Sin ciudad' }}</td>
                        <td>
                            <span class="contact-line">{{ $company->email ?: 'Sin correo' }}</span>
                            <span class="contact-line">{{ $company->phone ?: 'Sin telefono' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:1rem;">Sin clientes registrados con los filtros aplicados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
