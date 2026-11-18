<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class WorkspaceNotificationPreference extends Model
{
    protected $table = 'workspace_notification_preferences';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id', 'assignment_enabled', 'collaboration_enabled',
        'submission_enabled', 'review_enabled',
        'reminder_note_enabled', 'reminder_calendar_enabled',
        'reminder_activity_enabled', 'reminder_todo_enabled',
        'crm_status_enabled',
        'reminder_lead_minutes',
    ];

    protected function casts(): array
    {
        return [
            'assignment_enabled' => 'boolean',
            'collaboration_enabled' => 'boolean',
            'submission_enabled' => 'boolean',
            'review_enabled' => 'boolean',
            'reminder_note_enabled' => 'boolean',
            'reminder_calendar_enabled' => 'boolean',
            'reminder_activity_enabled' => 'boolean',
            'reminder_todo_enabled' => 'boolean',
            'crm_status_enabled' => 'boolean',
            'reminder_lead_minutes' => 'integer',
        ];
    }
}
