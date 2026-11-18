<?php

namespace App\Services;

use App\Models\WorkspaceAuditEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/** Writes only structured event identifiers and safe, explicitly allowlisted metadata. */
final class WorkspaceAuditTrail
{
    public const EVENTS = [
        'assignment.created', 'assignment.status_changed', 'assignment.removed',
        'collaboration.granted', 'collaboration.revoked',
        'review.decision', 'todo.status_changed', 'todo.visibility_changed',
        'notification_preferences.updated',
    ];

    public const SUBJECTS = ['todo', 'assignment', 'activity', 'notification_preferences'];

    private const FIELDS = [
        'task_id', 'assignment_id', 'member_id', 'member_role', 'assignee_user_id',
        'collaborator_user_id', 'status_from', 'status_to', 'review_from',
        'review_to', 'decision', 'visibility_from', 'visibility_to',
        'changed_settings',
    ];

    public function record(string $event, string $subject, ?string $id,
        array $details = [], ?string $actor = null): void
    {
        if (!in_array($event, self::EVENTS, true) || !in_array($subject, self::SUBJECTS, true)) {
            throw new \InvalidArgumentException('Unsupported audit event.');
        }
        if (!Schema::hasTable('workspace_audit_events')) {
            // Previous standalone SQLite test fixtures intentionally lack the new
            // audit schema. Production must run the migration before using observers.
            if (app()->environment('testing')) return;
            throw new \RuntimeException('Missing workspace audit table: run php artisan migrate.');
        }
        $safe = [];
        foreach ($details as $key => $value) {
            if (!in_array($key, self::FIELDS, true)) continue;
            if ($key === 'changed_settings') {
                if (!is_array($value)) continue;
                $safe[$key] = array_values(array_filter(
                    array_slice($value, 0, 12),
                    fn ($field) => is_string($field) && preg_match('/^[a-z_]{1,50}$/D', $field) === 1
                ));
                continue;
            }
            if (!is_string($value) && !is_int($value) && !is_bool($value) && $value !== null) continue;
            $safe[$key] = is_string($value) ? mb_substr($value, 0, 100) : $value;
        }
        WorkspaceAuditEvent::query()->create([
            'actor_user_id' => $actor ?: Auth::guard('web')->id(),
            'event_type' => $event,
            'subject_type' => $subject,
            'subject_id' => $id,
            'details' => $safe,
        ]);
    }
}
