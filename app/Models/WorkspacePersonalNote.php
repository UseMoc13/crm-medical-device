<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspacePersonalNote extends Model
{
    protected $table = 'workspace_personal_notes';
    protected $primaryKey = 'note_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'owner_user_id', 'title', 'body', 'is_pinned', 'archived_at',
        'remind_at', 'task_id', 'calendar_event_id',
    ];

    protected function casts(): array
    {
        return ['is_pinned' => 'boolean', 'archived_at' => 'datetime', 'remind_at' => 'datetime'];
    }

    public function owner() { return $this->belongsTo(User::class, 'owner_user_id', 'user_id'); }
    public function task() { return $this->belongsTo(WorkspaceTask::class, 'task_id', 'task_id'); }
    public function calendarEvent() { return $this->belongsTo(WorkspaceCalendarEvent::class, 'calendar_event_id', 'calendar_event_id'); }
}
