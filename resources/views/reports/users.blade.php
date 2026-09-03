@extends('reports.layout')

@section('content')
    <div class="summary">
        <div class="summary-card">
            <strong>Total usuarios</strong>
            <span>{{ $users->count() }}</span>
        </div>
        <div class="summary-card">
            <strong>Filtro aplicado</strong>
            <span>
                @if(!empty($filters['role']))
                    Rol: {{ $filters['role'] }}
                @elseif(!empty($filters['search']))
                    Busqueda: "{{ $filters['search'] }}"
                @else
                    Sin filtros
                @endif
            </span>
        </div>
    </div>

    @php
        $activeCount = $users->filter(fn($u) => is_null($u->deleted_at))->count();
        $inactiveCount = $users->filter(fn($u) => !is_null($u->deleted_at))->count();
        $byRole = $users->groupBy(fn($u) => $u->role->name ?? 'Sin rol')->map->count();
        $maxRole = max($byRole->values()->all() ?: [1]);
    @endphp

    <div class="chart-block">
        <p class="chart-title">Usuarios activos vs inactivos</p>
        @php $totalUsers = max($activeCount + $inactiveCount, 1); @endphp
        <div class="bar-row">
            <span class="bar-label">Activos</span>
            <div class="bar-track"><div class="bar-fill" style="width: {{ ($activeCount/$totalUsers)*100 }}%;"></div></div>
            <span class="bar-value">{{ $activeCount }}</span>
        </div>
        <div class="bar-row">
            <span class="bar-label">Inactivos</span>
            <div class="bar-track"><div class="bar-fill" style="width: {{ ($inactiveCount/$totalUsers)*100 }}%;"></div></div>
            <span class="bar-value">{{ $inactiveCount }}</span>
        </div>
    </div>

    <div class="chart-block">
        <p class="chart-title">Usuarios por rol</p>
        @foreach($byRole as $role => $count)
            <div class="bar-row">
                <span class="bar-label">{{ $role }}</span>
                <div class="bar-track"><div class="bar-fill" style="width: {{ ($count / max($maxRole,1))*100 }}%;"></div></div>
                <span class="bar-value">{{ $count }}</span>
            </div>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Creado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->role->name ?? 'Sin rol' }}</td>
                    <td>{{ optional($user->created_at)->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

