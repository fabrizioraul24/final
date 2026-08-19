<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('frequency_days')->default(3);
            $table->string('run_time', 5)->default('02:00');
            $table->boolean('is_active')->default(true);
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });

        $nextRunAt = Carbon::now()->setTime(2, 0, 0);
        if ($nextRunAt->lessThanOrEqualTo(Carbon::now())) {
            $nextRunAt->addDays(3);
        }

        DB::table('backup_schedules')->insert([
            'name' => 'Respaldo automatico principal',
            'frequency_days' => 3,
            'run_time' => '02:00',
            'is_active' => true,
            'next_run_at' => $nextRunAt,
            'last_run_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_schedules');
    }
};
