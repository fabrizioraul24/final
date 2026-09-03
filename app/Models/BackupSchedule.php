<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'frequency_days',
        'run_time',
        'is_active',
        'next_run_at',
        'last_run_at',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'next_run_at' => 'datetime',
        'last_run_at' => 'datetime',
    ];

    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class, 'backup_schedule_id');
    }
}
