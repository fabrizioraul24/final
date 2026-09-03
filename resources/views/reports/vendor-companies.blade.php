@extends('reports.layout')

@section('content')
    <div class="summary">
        <div class="summary-card">
            <strong>Vendedor</strong>
            <span>{{ $vendor->name ?? 'Vendedor Pil' }}</span>
        </div>
        <div class="summary-card">
            <strong>Clientes registrados</strong>
            <span>{{ $stats['total'] }}</span>
        </div>
        <div class="summary-card">
            <strong>Empresas institucionales</strong>
            <span>{{ $stats['institutional'] }}</span>
        </div>
        <div class="summary-card">
            <strong>Tiendas de barrio</strong>
            <span>{{ $stats['retail'] }}</span>
        </div>
    </div>

    <div class="chart-block">
        <p class="chart-title">Resumen de clientes</p>
        <div class="bar-row">
            <span class="bar-label">Con correo</span>
            <div class="bar-track">
                <div class="bar-fill" style="width: {{ $stats['total'] ? ($stats['with_email'] / max($stats['total'], 1)) * 100 : 0 }}%;"></div>
            </div>
            <span class="bar-value">{{ $stats['with_email'] }}</span>
        </div>
        <div class="bar-row">
            <span class="bar-label">Sin correo</span>
            <div class="bar-track">
                <div class="bar-fill" style="width: {{ $stats['total'] ? (($stats['total'] - $stats['with_email']) / max($stats['total'], 1)) * 100 : 0 }}%; background: linear-gradient(90deg, #fbbf24, #f97316);"></div>
            </div>
            <span class="bar-value">{{ $stats['total'] - $stats['with_email'] }}</span>
        </div>
    </div>

    <table style="margin-top:1.5rem;">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>NIT</th>
                <th>Tipo</th>
                <th>Contacto</th>
                <th>Ciudad</th>
                <th>Mapa</th>
                <th>Fecha registro</th>
            </tr>
        </thead>
        <tbody>
            @forelse($companies as $company)
                <tr>
                    <td>{{ $company->id }}</td>
                    <td>{{ $company->name }}</td>
                    <td>{{ $company->nit }}</td>
                    <td>{{ \App\Models\Company::TYPES[$company->company_type] ?? ucfirst($company->company_type) }}</td>
                    <td>
                        {{ $company->owner_first_name }} {{ $company->owner_last_name_paterno }}
                        @if($company->phone)
                            <br><small>{{ $company->phone }}</small>
                        @endif
                        @if($company->email)
                            <br><small>{{ $company->email }}</small>
                        @endif
                    </td>
                    <td>{{ $company->city }}</td>
                    <td>
                        @if($company->google_maps_url)
                            <a href="{{ $company->google_maps_url }}" target="_blank" rel="noopener">Ver mapa</a>
                        @elseif($company->address)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($company->address) }}" target="_blank" rel="noopener">Buscar</a>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ optional($company->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:1rem;">Sin clientes registrados con los filtros aplicados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
