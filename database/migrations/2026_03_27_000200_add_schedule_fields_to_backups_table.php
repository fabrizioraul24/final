<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->string('triggered_by')->default('manual')->after('created_by');
            $table->foreignId('backup_schedule_id')->nullable()->after('triggered_by')->constrained('backup_schedules')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('backup_schedule_id');
            $table->dropColumn('triggered_by');
        });
    }
};
