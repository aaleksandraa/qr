<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public function log(Model $model, string $action, ?array $old = null, ?array $new = null, ?User $user = null): void
    {
        AuditLog::create([
            'workspace_id' => $model->getAttribute('workspace_id'),
            'user_id' => $user?->id ?? auth()->id(),
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }
}
