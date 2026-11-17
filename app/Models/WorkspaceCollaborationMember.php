<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class WorkspaceCollaborationMember extends Model
{
    use HasUuids;

    protected $table = 'workspace_collaboration_members';
    protected $primaryKey = 'member_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['activity_id', 'task_id', 'user_id', 'access_role'];

    public function user() { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
    public function activity() { return $this->belongsTo(Activity::class, 'activity_id', 'activity_id'); }
    public function task() { return $this->belongsTo(WorkspaceTask::class, 'task_id', 'task_id'); }
}
