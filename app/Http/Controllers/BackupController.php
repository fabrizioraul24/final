<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LogsAudit;
use App\Models\Backup;
use App\Models\BackupSchedule;
use App\Services\BackupService;
use App\Support\AdminReact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BackupController extends Controller
{
    use LogsAudit;

    public function index(BackupService $service): View
    {
        $schedule = $service->ensureDefaultSchedule();
        $backups = Backup::with('creator')->latest()->paginate(10)
            ->through(function (Backup $backup) {
                $statusLabels = [
                    'completed' => ['label' => 'Completado', 'class' => 'chip-success'],
                    'running' => ['label' => 'En proceso', 'class' => ''],
                    'failed' => ['label' => 'Fallido', 'class' => ''],
                ];
                $label = $statusLabels[$backup->status] ?? ['label' => ucfirst($backup->status), 'class' => ''];

                return [
                    'id' => $backup->id,
                    'file_name' => $backup->file_name,
                    'disk_label' => strtoupper($backup->disk),
                    'message' => $backup->message,
                    'readable_size' => $backup->readableSize ?? $backup->readable_size,
                    'triggered_by_label' => $backup->triggered_by === 'scheduled' ? 'Automatico' : 'Manual',
                    'status_label' => $label['label'],
                    'status_class' => $label['class'],
                    'creator' => $backup->creator ? ['name' => $backup->creator->name] : null,
                    'created_at_formatted' => optional($backup->created_at)->format('d/m/Y H:i'),
                    'can_download' => $backup->status === 'completed',
                    'download_url' => route('dashboard.backups.download', $backup),
                    'destroy_url' => route('dashboard.backups.destroy', $backup),
                ];
            });
        $stats = [
            'total' => Backup::count(),
            'completed' => Backup::where('status', 'completed')->count(),
            'failed' => Backup::where('status', 'failed')->count(),
            'last' => Backup::latest()->first(),
            'automatic' => Backup::where('triggered_by', 'scheduled')->count(),
            'manual' => Backup::where('triggered_by', 'manual')->count(),
        ];

        return view('react-page', AdminReact::page('backups', 'Backups | Pil Andina', 'Historial de backups', 'backups', [
            'data' => [
                'backups' => AdminReact::paginator($backups),
                'stats' => [
                    'total' => $stats['total'],
                    'completed' => $stats['completed'],
                    'automatic' => $stats['automatic'],
                    'last_label' => optional($stats['last'])->created_at?->format('d/m H:i') ?? 'Sin registros',
                ],
                'schedule' => $schedule,
                'scheduleCards' => [
                    ['label' => 'Proximo backup', 'value' => $schedule->next_run_at?->format('d/m H:i') ?? 'Sin programar', 'chip' => 'Siguiente ejecucion', 'chipClass' => 'chip-muted'],
                    ['label' => 'Ultimo automatico', 'value' => $schedule->last_run_at?->format('d/m H:i') ?? 'Aun no corre', 'chip' => 'Ultima ejecucion', 'chipClass' => 'chip-muted'],
                    ['label' => 'Backups manuales', 'value' => $stats['manual'], 'chip' => 'Ejecutados por usuario', 'chipClass' => 'chip-muted'],
                    ['label' => 'Fallidos', 'value' => $stats['failed'], 'chip' => 'Requieren revision'],
                ],
                'routes' => [
                    'store' => route('dashboard.backups.store'),
                    'schedule' => route('dashboard.backups.schedule'),
                ],
            ],
        ], 'adminBackups'));
    }

    public function store(Request $request, BackupService $service): RedirectResponse
    {
        try {
            $backup = $service->create($request->user()?->id);

            if ($backup instanceof Backup) {
                $this->logAudit($backup, 'backup_create', [], $backup->only([
                    'file_name','disk','size','status','message','created_by','triggered_by'
                ]), 'Backup manual generado');
            }

            return redirect()
                ->route('dashboard.backups')
                ->with('status', 'Backup generado correctamente. Puedes descargarlo cuando lo necesites.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('dashboard.backups')
                ->with('error', 'No logramos completar el backup: ' . $e->getMessage());
        }
    }

    public function download(Backup $backup)
    {
        $path = 'backups/' . $backup->file_name;

        if (! Storage::disk($backup->disk)->exists($path)) {
            return redirect()
                ->route('dashboard.backups')
                ->with('error', 'El archivo ya no se encuentra disponible.');
        }

        return Storage::disk($backup->disk)->download($path);
    }

    public function destroy(Backup $backup): RedirectResponse
    {
        $old = $backup->only(['file_name','disk','size','status','message','created_by','triggered_by']);
        $path = 'backups/' . $backup->file_name;
        Storage::disk($backup->disk)->delete($path);
        $backup->delete();

        $this->logAudit(Backup::class, 'delete', $old, [], 'Backup eliminado del historial');

        return redirect()
            ->route('dashboard.backups')
            ->with('status', 'Backup eliminado del historial.');
    }

    public function updateSchedule(Request $request, BackupService $service): RedirectResponse
    {
        $data = $request->validate([
            'schedule_id' => ['required', 'integer', Rule::exists('backup_schedules', 'id')],
            'frequency_days' => ['required', 'integer', 'min:1', 'max:30'],
            'run_time' => ['required', 'date_format:H:i'],
            'is_active' => ['required', 'in:0,1'],
        ]);

        $schedule = BackupSchedule::query()->findOrFail($data['schedule_id']);
        $old = $schedule->only(['name','frequency_days','run_time','is_active','next_run_at','last_run_at']);
        $schedule->fill([
            'frequency_days' => $data['frequency_days'],
            'run_time' => $data['run_time'],
            'is_active' => $data['is_active'] === '1',
        ]);

        $schedule->next_run_at = $schedule->is_active
            ? $service->calculateNextRunAt($schedule, now())
            : null;

        $schedule->save();

        $this->logAudit($schedule, 'schedule_update', $old, $schedule->only(['name','frequency_days','run_time','is_active','next_run_at','last_run_at']), 'Programacion de backups actualizada');

        return redirect()
            ->route('dashboard.backups')
            ->with('status', 'Programacion de backups actualizada correctamente.');
    }
}
