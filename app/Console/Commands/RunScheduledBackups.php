<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class RunScheduledBackups extends Command
{
    protected $signature = 'backups:run-scheduled';

    protected $description = 'Ejecuta los backups automaticos pendientes.';

    public function handle(BackupService $service): int
    {
        $service->ensureDefaultSchedule();

        $schedules = $service->dueSchedules();

        if ($schedules->isEmpty()) {
            $this->info('No hay backups programados pendientes.');

            return self::SUCCESS;
        }

        foreach ($schedules as $schedule) {
            try {
                $service->create(null, $schedule, 'scheduled');
                $this->info('Backup automatico ejecutado: ' . $schedule->name);
            } catch (\Throwable $e) {
                $this->error('Fallo el backup automatico: ' . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
