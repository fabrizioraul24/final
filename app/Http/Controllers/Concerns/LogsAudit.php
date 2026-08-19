<?php

namespace App\Http\Controllers\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait LogsAudit
{
    protected function logAudit(Model|string $entity, string $action, array $old = [], array $new = [], ?string $description = null): void
    {
        $entityType = is_string($entity) ? $entity : $entity::class;
        $entityId = is_string($entity) ? 0 : ($entity->getKey() ?? 0);

        AuditLog::create([
            'user_id' => Auth::id(),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'description' => $description,
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'created_at' => now(),
        ]);
    }
}
