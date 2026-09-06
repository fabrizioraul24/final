@extends('reports.layout')

@section('content')
    @php
        $activeCount = $users->filter(fn($user) => is_null($user->deleted_at))->count();
        $inactiveCount = $users->filter(fn($user) => ! is_null($user->deleted_at))->count();
        $totalUsers = max($activeCount + $inactiveCount, 1);
        $byRole = $users->groupBy(fn($user) => $user->role->name ?? 'Sin rol')->map->count()->sortDesc();
        $maxRole = max($byRole->values()->all() ?: [1]);
        $withEmail = $users->filter(fn($user) => filled($user->email))->count();
        $recentUsers = $users->filter(fn($user) => $user->created_at && $user->created_at->gte(now()->subDays(30)))->count();
        $filterLabel = !empty($filters['role'])
            ? 'Rol: '.$filters['role']
            : (!empty($filters['search']) ? 'Busqueda: "'.$filters['search'].'"' : 'Sin filtros');
    @endphp

    <style>
        .users-report .summary { display: table; width: 100%; border-spacing: 0.45rem 0; table-layout: fixed; }
        .users-report .summary-card { display: table-cell; width: 33.333%; min-height: 58px; padding: 0.55rem 0.7rem; vertical-align: top; }
        .users-report .summary-card span { font-size: 0.98rem; }
        .summary-card.blue { border-left: 5px solid #4e6baf; }
        .summary-card.green { border-left: 5px solid #10b981; }
        .summary-card.cyan { border-left: 5px solid #0ea5e9; }
        .summary-card.amber { border-left: 5px solid #f59e0b; }
        .summary-card.rose { border-left: 5px solid #e11d48; }
        .report-note { margin: 0.2rem 0 0.9rem; color: #5f6a85; font-size: 0.86rem; }
        .bar-fill.green { background: #10b981; }
        .bar-fill.rose { background: #e11d48; }
        .bar-fill.cyan { background: #0ea5e9; }
        .user-table { margin-top: 0.75rem; }
        .user-table th,
        .user-table td { font-size: 0.68rem; padding: 0.42rem; vertical-align: top; }
        .user-table th:nth-child(1) { width: 8%; }
        .user-table th:nth-child(2) { width: 28%; }
        .user-table th:nth-child(3) { width: 24%; }
        .user-table th:nth-child(4) { width: 20%; }
        .user-table th:nth-child(5) { width: 12%; }
        .user-table th:nth-child(6) { width: 8%; }
        .user-name { color: #1c1c2d; font-weight: 700; }
        .username { display: block; margin-top: 0.12rem; color: #5f6a85; font-size: 0.58rem; }
        .role-pill { display: inline-block; padding: 0.18rem 0.46rem; border-radius: 999px; background: #e0ecff; color: #4e6baf; font-size: 0.62rem; font-weight: 700; }
        .status-pill { display: inline-block; min-width: 58px; padding: 0.18rem 0.45rem; border-radius: 999px; font-size: 0.62rem; font-weight: 700; text-align: center; }
        .status-pill.active { background: #dcfce7; color: #047857; }
        .status-pill.inactive { background: #ffe4e6; color: #be123c; }
    </style>

    <div class="users-report">
        <p class="report-note">Usuarios filtrados por {{ $filterLabel }}.</p>

        <div class="summary">
            <div class="summary-card blue">
                <strong>Total usuarios</strong>
                <span>{{ $users->count() }}</span>
            </div>
            <div class="summary-card green">
                <strong>Activos</strong>
                <span>{{ $activeCount }}</span>
            </div>
            <div class="summary-card rose">
                <strong>Inactivos</strong>
                <span>{{ $inactiveCount }}</span>
            </div>
        </div>

        <div class="summary">
            <div class="summary-card cyan">
                <strong>Roles</strong>
                <span>{{ $byRole->count() }}</span>
            </div>
            <div class="summary-card amber">
                <strong>Con correo</strong>
                <span>{{ $withEmail }}</span>
            </div>
            <div class="summary-card blue">
                <strong>Nuevos 30 dias</strong>
                <span>{{ $recentUsers }}</span>
            </div>
        </div>

        <div class="chart-block">
            <p class="chart-title">Usuarios activos vs inactivos</p>
            <div class="bar-row">
                <span class="bar-label">Activos</span>
                <div class="bar-track"><div class="bar-fill green" style="width: {{ ($activeCount / $totalUsers) * 100 }}%;"></div></div>
                <span class="bar-value">{{ $activeCount }}</span>
            </div>
            <div class="bar-row">
                <span class="bar-label">Inactivos</span>
                <div class="bar-track"><div class="bar-fill rose" style="width: {{ ($inactiveCount / $totalUsers) * 100 }}%;"></div></div>
                <span class="bar-value">{{ $inactiveCount }}</span>
            </div>
        </div>

        <div class="chart-block">
            <p class="chart-title">Usuarios por rol</p>
            @forelse($byRole->take(7) as $role => $count)
                <div class="bar-row">
                    <span class="bar-label">{{ $role }}</span>
                    <div class="bar-track"><div class="bar-fill cyan" style="width: {{ ($count / max($maxRole, 1)) * 100 }}%;"></div></div>
                    <span class="bar-value">{{ $count }}</span>
                </div>
            @empty
                <p class="report-note">Sin roles para mostrar.</p>
            @endforelse
        </div>

        <h3 style="margin-top:1.3rem;">Detalle de usuarios</h3>
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Creado</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <span class="user-name">{{ $user->name }}</span>
                            <span class="username">{{ $user->username }}</span>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td><span class="role-pill">{{ $user->role->name ?? 'Sin rol' }}</span></td>
                        <td>{{ optional($user->created_at)->format('d/m/Y') }}</td>
                        <td>
                            <span class="status-pill {{ is_null($user->deleted_at) ? 'active' : 'inactive' }}">
                                {{ is_null($user->deleted_at) ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Sin usuarios para el filtro seleccionado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
