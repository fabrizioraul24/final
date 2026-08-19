<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\BackupSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    private Filesystem $disk;
    private string $connection;

    public function __construct()
    {
        $this->disk = Storage::disk('local');
        $this->connection = Config::get('database.default');
    }

    public function create(?int $userId = null, ?BackupSchedule $schedule = null, string $triggeredBy = 'manual'): Backup
    {
        $fileName = 'backup_' . now()->format('Ymd_His') . '.sql';
        $path = 'backups/' . $fileName;
        $absolutePath = storage_path('app/' . $path);

        $this->disk->makeDirectory('backups');

        $backup = Backup::create([
            'file_name' => $fileName,
            'disk' => 'local',
            'status' => 'running',
            'created_by' => $userId,
            'triggered_by' => $triggeredBy,
            'backup_schedule_id' => $schedule?->id,
        ]);

        try {
            $this->dumpDatabase($absolutePath);

            $size = is_file($absolutePath) ? filesize($absolutePath) : 0;
            $backup->update([
                'size' => $size,
                'status' => 'completed',
                'message' => 'Copia generada correctamente',
            ]);

            if ($schedule) {
                $schedule->forceFill([
                    'last_run_at' => now(),
                    'next_run_at' => $this->calculateNextRunAt($schedule, now()),
                ])->save();
            }
        } catch (\Throwable $e) {
            $backup->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);

            if ($schedule) {
                $schedule->forceFill([
                    'last_run_at' => now(),
                    'next_run_at' => $this->calculateNextRunAt($schedule, now()),
                ])->save();
            }

            throw $e;
        }

        return $backup;
    }

    public function ensureDefaultSchedule(): BackupSchedule
    {
        return BackupSchedule::firstOrCreate(
            ['name' => 'Respaldo automatico principal'],
            [
                'frequency_days' => 3,
                'run_time' => '02:00',
                'is_active' => true,
                'next_run_at' => $this->calculateNextRunAt(
                    new BackupSchedule(['frequency_days' => 3, 'run_time' => '02:00']),
                    now()
                ),
            ]
        );
    }

    public function dueSchedules(?Carbon $reference = null)
    {
        $reference = $reference ?? now();

        return BackupSchedule::query()
            ->where('is_active', true)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', $reference)
            ->orderBy('next_run_at')
            ->get();
    }

    public function calculateNextRunAt(BackupSchedule $schedule, ?Carbon $from = null): Carbon
    {
        $from = ($from ?? now())->copy();
        [$hour, $minute] = array_pad(explode(':', (string) $schedule->run_time), 2, '00');

        $nextRun = $from->copy()->setTime((int) $hour, (int) $minute, 0);

        if ($nextRun->lessThanOrEqualTo($from)) {
            $nextRun->addDays(max(1, (int) $schedule->frequency_days));
        }

        return $nextRun;
    }

    private function dumpDatabase(string $targetPath): void
    {
        $connection = DB::connection($this->connection);
        $pdo = $connection->getPdo();
        $database = $connection->getDatabaseName();

        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        if (! $tables) {
            throw new \RuntimeException('No se encontraron tablas para respaldar.');
        }

        $handle = fopen($targetPath, 'w+');
        if (! $handle) {
            throw new \RuntimeException('No se pudo crear el archivo temporal del backup.');
        }

        fwrite($handle, sprintf("-- Backup generado el %s\r\n", now()->toDateTimeString()));
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\r\n\r\n");

        foreach ($tables as $table) {
            $createRecord = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $createSql = $createRecord['Create Table'] ?? null;
            if (! $createSql) {
                continue;
            }

            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\r\n");
            fwrite($handle, $createSql . ";\r\n\r\n");

            $columns = $connection->getSchemaBuilder()->getColumnListing($table);
            if (! $columns) {
                continue;
            }
            $columnList = '(' . implode(', ', array_map(fn ($col) => "`{$col}`", $columns)) . ')';

            $rows = $connection->table($table)->cursor();

            foreach ($rows as $row) {
                $values = [];
                foreach ($columns as $column) {
                    $values[] = $this->escapeValue($row->{$column} ?? null);
                }
                fwrite($handle, sprintf(
                    "INSERT INTO `%s` %s VALUES (%s);\r\n",
                    $table,
                    $columnList,
                    implode(', ', $values)
                ));
            }

            fwrite($handle, "\r\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\r\n");
        fclose($handle);
    }

    private function escapeValue($value): string
    {
        if (is_null($value)) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        $escaped = str_replace(
            ["\\", "\0", "\n", "\r", "'", '"', "\x1a"],
            ["\\\\", "\\0", "\\n", "\\r", "\\'", '\\"', "\\Z"],
            $value
        );

        return "'" . $escaped . "'";
    }
}
