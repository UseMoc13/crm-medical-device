<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** Immutable reviewer decision history; never expose it to unrelated collaborators. */
final class WorkspaceTaskReview extends Model
{
    use HasUuids;

    protected $table = 'workspace_task_reviews';
    protected $primaryKey = 'review_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['task_id', 'assignment_id', 'actor_user_id', 'decision', 'note'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function task() { return $this->belongsTo(WorkspaceTask::class, 'task_id', 'task_id'); }
    public function assignment() { return $this->belongsTo(WorkspaceTaskAssignment::class, 'assignment_id', 'assignment_id'); }
    public function actor() { return $this->belongsTo(User::class, 'actor_user_id', 'user_id'); }
}
