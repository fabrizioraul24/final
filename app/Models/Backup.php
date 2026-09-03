<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'disk',
        'size',
        'status',
        'message',
        'created_by',
        'triggered_by',
        'backup_schedule_id',
    ];

    protected $casts = [
        'size' => 'int',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(BackupSchedule::class, 'backup_schedule_id');
    }

    public function getReadableSizeAttribute(): string
    {
        if ($this->size >= 1073741824) {
            return number_format($this->size / 1073741824, 2) . ' GB';
        }

        if ($this->size >= 1048576) {
            return number_format($this->size / 1048576, 2) . ' MB';
        }

        if ($this->size >= 1024) {
            return number_format($this->size / 1024, 2) . ' KB';
        }

        return $this->size . ' B';
    }
}
