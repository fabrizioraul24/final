@extends('reports.layout')

@section('content')
    @php
        $institutional = $companies->where('company_type', 'empresa_institucional')->count();
        $retail = $companies->where('company_type', 'tienda_barrio')->count();
        $total = max($companies->count(), 1);
    @endphp
    <div class="summary">
        <div class="summary-card">
            <strong>Total clientes</strong>
            <span>{{ $companies->count() }}</span>
        </div>
        <div class="summary-card">
            <strong>Institucionales</strong>
            <span>{{ $institutional }}</span>
        </div>
        <div class="summary-card">
            <strong>Tiendas de barrio</strong>
            <span>{{ $retail }}</span>
        </div>
    </div>

    <div class="chart-block">
        <p class="chart-title">Tipos de cliente</p>
        <div class="bar-row">
            <span class="bar-label">Institucional</span>
            <div class="bar-track"><div class="bar-fill" style="width: {{ ($institutional/$total)*100 }}%;"></div></div>
            <span class="bar-value">{{ $institutional }}</span>
        </div>
        <div class="bar-row">
            <span class="bar-label">Tienda de barrio</span>
            <div class="bar-track"><div class="bar-fill" style="width: {{ ($retail/$total)*100 }}%;"></div></div>
            <span class="bar-value">{{ $retail }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>NIT</th>
                <th>Tipo</th>
                <th>Ciudad</th>
                <th>Email</th>
                <th>Telefono</th>
            </tr>
        </thead>
        <tbody>
            @foreach($companies as $company)
                <tr>
                    <td>{{ $company->name }}</td>
                    <td>{{ $company->nit }}</td>
                    <td>{{ $company->company_type }}</td>
                    <td>{{ $company->city }}</td>
                    <td>{{ $company->email ?? 'Sin correo' }}</td>
                    <td>{{ $company->phone ?? 'Sin telefono' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

