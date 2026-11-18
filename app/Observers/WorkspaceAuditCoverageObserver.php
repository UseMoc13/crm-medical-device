<?php

namespace App\Observers;

use App\Models\WorkspaceCollaborationMember;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTaskAssignment;
use App\Services\WorkspaceAuditTrail;
use Illuminate\Database\Eloquent\Model;

/** Additional A7.4.12.2 coverage. Never stores titles, descriptions, files or free text. */
final class WorkspaceAuditCoverageObserver
{
    public function updated(Model $model): void
    {
        $trail = app(WorkspaceAuditTrail::class);
        if ($model instanceof WorkspaceTaskAssignment) {
            $fields = array_values(array_intersect(['completion_required', 'submission_required'],
                array_keys($model->getChanges())));
            if ($fields) $trail->record('assignment.requirements_changed', 'assignment',
                (string) $model->getKey(), [
                    'task_id' => (string) $model->task_id,
                    'changed_settings' => $fields,
                ]);
            return;
        }
        if ($model instanceof WorkspaceCollaborationMember && $model->wasChanged('access_role')) {
            $type = $model->activity_id ? 'activity' : 'todo';
            $trail->record('collaboration.role_changed', $type,
                (string) ($model->activity_id ?: $model->task_id), [
                    'member_id' => (string) $model->getKey(),
                    'member_role_from' => (string) $model->getOriginal('access_role'),
                    'member_role_to' => (string) $model->access_role,
                ]);
            return;
        }
        if ($model instanceof WorkspaceTask) {
            $fields = array_values(array_intersect(['starts_at', 'due_at', 'priority'],
                array_keys($model->getChanges())));
            if ($fields) $trail->record('todo.schedule_changed', 'todo',
                (string) $model->getKey(), ['changed_settings' => $fields]);
        }
    }
}
