<?php

namespace App\Services;

use App\Models\WorkspaceNotificationPreference;
use Illuminate\Support\Facades\Schema;

/** Fail-safe defaults keep older notifications operational during deployment. */
final class WorkspaceNotificationPreferences
{
    public const LEAD_OPTIONS = [0, 15, 60, 180, 1440];

    public const FLAGS = [
        'assignment_enabled', 'collaboration_enabled', 'submission_enabled',
        'review_enabled', 'reminder_note_enabled', 'reminder_calendar_enabled',
        'reminder_activity_enabled', 'reminder_todo_enabled',
    ];

    public function defaults(): array
    {
        return array_fill_keys(self::FLAGS, true) + ['reminder_lead_minutes' => 1440];
    }

    public function forUser(string $userId): array
    {
        $defaults = $this->defaults();
        if (!Schema::hasTable('workspace_notification_preferences')) return $defaults;
        $record = WorkspaceNotificationPreference::query()->whereKey($userId)->first();
        if (!$record) return $defaults;
        foreach (self::FLAGS as $flag) $defaults[$flag] = (bool) $record->{$flag};
        $lead = (int) $record->reminder_lead_minutes;
        $defaults['reminder_lead_minutes'] = in_array($lead, self::LEAD_OPTIONS, true) ? $lead : 1440;
        return $defaults;
    }

    public function allows(string $userId, string $kind, string $targetType): bool
    {
        $flag = match ($kind) {
            'assignment' => 'assignment_enabled',
            'collaboration' => 'collaboration_enabled',
            'submission' => 'submission_enabled',
            'approval', 'revision' => 'review_enabled',
            'reminder' => match ($targetType) {
                'note' => 'reminder_note_enabled',
                'calendar' => 'reminder_calendar_enabled',
                'activity' => 'reminder_activity_enabled',
                'todo' => 'reminder_todo_enabled',
                default => null,
            },
            default => null,
        };
        return $flag === null || $this->forUser($userId)[$flag];
    }
}
