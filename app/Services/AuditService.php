<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log a state-changing action to the append-only audit log.
     */
    public static function log(string $action, ?Model $subject = null, array $changes = [], ?User $actor = null): AuditLog
    {
        $actorId = $actor?->id ?? Auth::id();

        // Sanitize changes to never record passwords or security tokens
        $sanitizedChanges = self::sanitizeChanges($changes);

        return AuditLog::create([
            'actor_id' => $actorId,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'changes' => empty($sanitizedChanges) ? null : $sanitizedChanges,
            'ip_address' => config('connected.audit_ip', false) ? Request::ip() : null,
            'created_at' => now(),
        ]);
    }

    protected static function sanitizeChanges(array $changes): array
    {
        $sensitiveKeys = ['password', 'remember_token', 'token', 'secret', 'two_factor_secret'];

        foreach ($sensitiveKeys as $key) {
            unset($changes[$key], $changes['old'][$key], $changes['new'][$key]);
        }

        return $changes;
    }
}
