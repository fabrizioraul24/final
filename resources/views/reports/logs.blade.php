@extends('reports.layout')

@section('content')
    <div class="summary">
        <div class="summary-card">
            <strong>Total registros</strong>
            <span>{{ $logs->count() }}</span>
        </div>
        <div class="summary-card">
            <strong>Filtros aplicados</strong>
            <span>
                @if($filters['scope'] !== 'all') 
                    Filtro: {{ ucfirst($filters['scope']) }}
                @endif
                @if($filters['actor'] !== 'Todos')
                    | Actor: {{ $filters['actor'] }}
                @endif
                @if($filters['action'] !== 'Todas')
                    | Accion: {{ ucfirst($filters['action']) }}
                @endif
                @if($filters['scope'] === 'all' && $filters['actor'] === 'Todos' && $filters['action'] === 'Todas')
                    Vista completa
                @endif
            </span>
        </div>
    </div>

    @php
        $byAction = $logs->groupBy('action')->map->count();
        $maxAction = max($byAction->values()->all() ?: [1]);
    @endphp

    <div class="chart-block">
        <p class="chart-title">Distribución por acciones</p>
        @foreach($byAction->take(6) as $action => $count)
            <div class="bar-row">
                <span class="bar-label">{{ ucfirst($action) }}</span>
                <div class="bar-track"><div class="bar-fill" style="width: {{ ($count / max($maxAction,1))*100 }}%;"></div></div>
                <span class="bar-value">{{ $count }}</span>
            </div>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Fecha</th>
                <th style="width: 15%;">Actor</th>
                <th style="width: 15%;">Entidad</th>
                <th style="width: 15%;">Acción</th>
                <th style="width: 40%;">Descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ optional($log->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $log->user->name ?? 'Sistema' }}</td>
                    <td>{{ class_basename($log->entity_type) }} #{{ $log->entity_id }}</td>
                    <td>{{ ucfirst($log->action) }}</td>
                    <td>{{ $log->description ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 1.5rem; font-size: 0.75rem; color: #5f6a85; text-align: center; border-top: 1px solid #e1e4f2; padding-top: 1rem;">
        Este documento es un reporte oficial de auditoría generado por el sistema Pil Andina.
    </div>
@endsection
