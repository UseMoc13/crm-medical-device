<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorkspaceTask extends Model
{
    use HasUuids;

    protected $table = 'workspace_tasks';
    protected $primaryKey = 'task_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'owner_user_id', 'assigned_user_id', 'title', 'description', 'starts_at',
        'visibility', 'status', 'priority', 'due_at', 'completed_at',
        'customer_id', 'lead_id', 'opportunity_id', 'activity_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function owner() { return $this->belongsTo(User::class, 'owner_user_id', 'user_id'); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_user_id', 'user_id'); }
    public function assignments() { return $this->hasMany(WorkspaceTaskAssignment::class, 'task_id', 'task_id'); }
    public function activity() { return $this->belongsTo(Activity::class, 'activity_id', 'activity_id'); }
}
