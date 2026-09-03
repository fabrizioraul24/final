@extends('layouts.sidebar')

@section('title', 'Backups | Pil Andina')
@section('page-title', 'Historial de backups')

@section('content')
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90"><i class="ri-check-line"></i> {{ session('status') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="card" style="border:1px solid rgba(248,113,113,0.4);">
            <span class="chip" style="background:rgba(248,113,113,0.2); color:#fee2e2;"><i class="ri-error-warning-line"></i> {{ session('error') }}</span>
        </div>
    @endif

    <div class="stats-grid">
        <div class="card">
            <h3>Total respaldos</h3>
            <div class="value">{{ $stats['total'] }}</div>
            <span class="chip text-white/70"><i class="ri-database-2-line"></i> Historial</span>
        </div>
        <div class="card">
            <h3>Completados</h3>
            <div class="value">{{ $stats['completed'] }}</div>
            <span class="chip text-green-300"><i class="ri-check-double-line"></i> OK</span>
        </div>
        <div class="card">
            <h3>Automaticos</h3>
            <div class="value">{{ $stats['automatic'] }}</div>
            <span class="chip text-yellow-300"><i class="ri-robot-2-line"></i> Scheduler</span>
        </div>
        <div class="card">
            <h3>Ultimo backup</h3>
            <div class="value">{{ optional($stats['last'])->created_at?->format('d/m H:i') ?? 'Sin registros' }}</div>
            <span class="chip text-white/70"><i class="ri-time-line"></i> Fecha/hora</span>
        </div>
    </div>

    <div class="card">
        <div class="chart-head">
            <div>
                <h4>Programacion automatica</h4>
            </div>
            <span class="chip {{ $schedule->is_active ? 'text-green-300' : 'text-red-300' }}">
                <i class="ri-timer-line"></i>
                {{ $schedule->is_active ? 'Activa' : 'Pausada' }}
            </span>
        </div>

        <form method="POST" action="{{ route('dashboard.backups.schedule') }}" class="form-grid" style="margin-top:1rem;">
            @csrf
            @method('PUT')
            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

            <div class="form-group">
                <label for="frequency_days">Frecuencia en dias</label>
                <input type="number" id="frequency_days" name="frequency_days" min="1" max="30" class="input-ghost" value="{{ old('frequency_days', $schedule->frequency_days) }}" required>
            </div>

            <div class="form-group">
                <label for="run_time">Hora de ejecucion</label>
                <input type="time" id="run_time" name="run_time" class="input-ghost" value="{{ old('run_time', $schedule->run_time) }}" required>
            </div>

            <div class="form-group">
                <label for="is_active">Estado</label>
                <select id="is_active" name="is_active" class="select-light">
                    <option value="1" @selected(old('is_active', $schedule->is_active ? '1' : '0') === '1')>Activo</option>
                    <option value="0" @selected(old('is_active', $schedule->is_active ? '1' : '0') === '0')>Pausado</option>
                </select>
            </div>

            <div class="form-group" style="align-self:flex-end;">
                <button type="submit" class="pill-button">Guardar programacion</button>
            </div>
        </form>

        <div class="stats-grid" style="margin-top:1rem;">
            <div class="card" style="padding:1rem 1.2rem;">
                <h3 style="font-size:0.95rem;">Proximo backup</h3>
                <div class="value" style="font-size:1.35rem;">{{ $schedule->next_run_at?->format('d/m H:i') ?? 'Sin programar' }}</div>
                <span class="chip text-white/70"><i class="ri-calendar-schedule-line"></i> Siguiente ejecucion</span>
            </div>
            <div class="card" style="padding:1rem 1.2rem;">
                <h3 style="font-size:0.95rem;">Ultimo automatico</h3>
                <div class="value" style="font-size:1.35rem;">{{ $schedule->last_run_at?->format('d/m H:i') ?? 'Aun no corre' }}</div>
                <span class="chip text-white/70"><i class="ri-history-line"></i> Ultima ejecucion</span>
            </div>
            <div class="card" style="padding:1rem 1.2rem;">
                <h3 style="font-size:0.95rem;">Backups manuales</h3>
                <div class="value" style="font-size:1.35rem;">{{ $stats['manual'] }}</div>
                <span class="chip text-white/70"><i class="ri-hand-line"></i> Ejecutados por usuario</span>
            </div>
            <div class="card" style="padding:1rem 1.2rem;">
                <h3 style="font-size:0.95rem;">Fallidos</h3>
                <div class="value" style="font-size:1.35rem;">{{ $stats['failed'] }}</div>
                <span class="chip text-red-300"><i class="ri-close-line"></i> Requieren revision</span>
            </div>
        </div>

    </div>

    <div class="card">
        <div class="chart-head" style="display:flex; justify-content:space-between; align-items:center;">
            <h4>Generar nuevo backup</h4>
            <form method="POST" action="{{ route('dashboard.backups.store') }}">
                @csrf
                <button type="submit" class="pill-button">
                    <i class="ri-download-cloud-2-line"></i> Crear backup
                </button>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Peso</th>
                        <th>Origen</th>
                        <th>Estado</th>
                        <th>Creado por</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                        <tr>
                            <td>
                                <strong>{{ $backup->file_name }}</strong>
                                <p style="margin:0; color:rgba(255,255,255,0.6);">{{ strtoupper($backup->disk) }}</p>
                                @if($backup->message)
                                    <small style="color:rgba(255,255,255,0.7);">{{ $backup->message }}</small>
                                @endif
                            </td>
                            <td>{{ $backup->readableSize ?? $backup->readable_size }}</td>
                            <td>
                                <span class="chip {{ $backup->triggered_by === 'scheduled' ? 'text-yellow-300' : 'text-white/70' }}">
                                    {{ $backup->triggered_by === 'scheduled' ? 'Automatico' : 'Manual' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusLabels = [
                                        'completed' => ['label' => 'Completado', 'class' => 'text-green-300'],
                                        'running' => ['label' => 'En proceso', 'class' => 'text-yellow-300'],
                                        'failed' => ['label' => 'Fallido', 'class' => 'text-red-300'],
                                    ];
                                    $label = $statusLabels[$backup->status] ?? ['label' => ucfirst($backup->status), 'class' => ''];
                                @endphp
                                <span class="chip {{ $label['class'] }}">{{ $label['label'] }}</span>
                            </td>
                            <td>{{ $backup->creator->name ?? 'Sistema' }}</td>
                            <td>{{ optional($backup->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('dashboard.backups.download', $backup) }}" class="pill-button ghost" @if($backup->status !== 'completed') style="pointer-events:none; opacity:0.4;" @endif>
                                        <i class="ri-download-2-line"></i> Descargar
                                    </a>
                                    <form method="POST" action="{{ route('dashboard.backups.destroy', $backup) }}" onsubmit="return confirm('Eliminar este backup del historial?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:1.5rem;">Aun no generaste backups.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">
            {{ $backups->links() }}
        </div>
    </div>
@endsection
