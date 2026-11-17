<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class WorkspaceCollaborationEntry extends Model
{
    use HasUuids;

    protected $table = 'workspace_collaboration_entries';
    protected $primaryKey = 'entry_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['activity_id', 'task_id', 'author_user_id', 'entry_type', 'body'];

    public function author() { return $this->belongsTo(User::class, 'author_user_id', 'user_id'); }
    public function activity() { return $this->belongsTo(Activity::class, 'activity_id', 'activity_id'); }
    public function task() { return $this->belongsTo(WorkspaceTask::class, 'task_id', 'task_id'); }
}
