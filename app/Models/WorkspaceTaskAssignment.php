<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorkspaceTaskAssignment extends Model
{
    use HasUuids;

    protected $table = 'workspace_task_assignments';
    protected $primaryKey = 'assignment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // A7.4.9.1: review attributes must be mass assignable; otherwise Laravel
    // silently ignores update([...]) in submission/reviewer controllers.
    protected $fillable = [
        'task_id', 'assigned_user_id', 'completion_required', 'submission_required',
        'status', 'completed_at',
        'review_status', 'reviewed_by_user_id', 'reviewed_at', 'review_note',
    ];

    protected function casts(): array
    {
        return [
            'completion_required' => 'boolean',
            'submission_required' => 'boolean',
            'completed_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function task() { return $this->belongsTo(WorkspaceTask::class, 'task_id', 'task_id'); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_user_id', 'user_id'); }
    public function files() { return $this->hasMany(WorkspaceTaskSubmissionFile::class, 'assignment_id', 'assignment_id'); }
}
