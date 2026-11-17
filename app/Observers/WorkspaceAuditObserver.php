<?php

namespace App\Observers;

use App\Models\WorkspaceCollaborationMember;
use App\Models\WorkspaceNotificationPreference;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTaskAssignment;
use App\Models\WorkspaceTaskReview;
use App\Services\WorkspaceAuditTrail;
use Illuminate\Database\Eloquent\Model;

/** Records changes within the source transaction. No free-text payloads or files. */
final class WorkspaceAuditObserver
{
    private function trail(): WorkspaceAuditTrail
    {
        return app(WorkspaceAuditTrail::class);
    }

    public function created(Model $model): void
    {
        if ($model instanceof WorkspaceTaskAssignment) {
            $this->trail()->record('assignment.created', 'assignment', (string) $model->getKey(), [
                'task_id' => (string) $model->task_id,
                'assignee_user_id' => (string) $model->assigned_user_id,
            ]);
        } elseif ($model instanceof WorkspaceCollaborationMember) {
            $type = $model->activity_id ? 'activity' : 'todo';
            $this->trail()->record('collaboration.granted', $type,
                (string) ($model->activity_id ?: $model->task_id), [
                    'member_id' => (string) $model->getKey(),
                    'collaborator_user_id' => (string) $model->user_id,
                    'member_role' => (string) $model->access_role,
                ]);
        } elseif ($model instanceof WorkspaceNotificationPreference) {
            // Initial settings save: only the names of configured controls.
            $this->trail()->record('notification_preferences.updated', 'notification_preferences',
                (string) $model->getKey(), ['changed_settings' => [
                    'assignment_enabled', 'collaboration_enabled', 'submission_enabled',
                    'review_enabled', 'reminder_note_enabled', 'reminder_calendar_enabled',
                    'reminder_activity_enabled', 'reminder_todo_enabled', 'reminder_lead_minutes',
                ]]);
        } elseif ($model instanceof WorkspaceTaskReview) {
            $this->trail()->record('review.decision', 'todo', (string) $model->task_id, [
                'assignment_id' => (string) $model->assignment_id,
                'decision' => (string) $model->decision,
            ], (string) $model->actor_user_id);
        }
    }

    public function updated(Model $model): void
    {
        if ($model instanceof WorkspaceTaskAssignment) {
            $changes = [];
            if ($model->wasChanged('status')) {
                $changes['status_from'] = (string) $model->getOriginal('status');
                $changes['status_to'] = (string) $model->status;
            }
            if ($model->wasChanged('review_status')) {
                $changes['review_from'] = (string) $model->getOriginal('review_status');
                $changes['review_to'] = (string) $model->review_status;
            }
            if ($changes) {
                $changes['task_id'] = (string) $model->task_id;
                $this->trail()->record('assignment.status_changed', 'assignment', (string) $model->getKey(), $changes);
            }
        } elseif ($model instanceof WorkspaceTask) {
            if ($model->wasChanged('status')) {
                $this->trail()->record('todo.status_changed', 'todo', (string) $model->getKey(), [
                    'status_from' => (string) $model->getOriginal('status'),
                    'status_to' => (string) $model->status,
                ]);
            }
            if ($model->wasChanged('visibility')) {
                $this->trail()->record('todo.visibility_changed', 'todo', (string) $model->getKey(), [
                    'visibility_from' => (string) $model->getOriginal('visibility'),
                    'visibility_to' => (string) $model->visibility,
                ]);
            }
        } elseif ($model instanceof WorkspaceNotificationPreference) {
            $fields = array_values(array_intersect(array_keys($model->getChanges()), [
                'assignment_enabled', 'collaboration_enabled', 'submission_enabled',
                'review_enabled', 'reminder_note_enabled', 'reminder_calendar_enabled',
                'reminder_activity_enabled', 'reminder_todo_enabled', 'reminder_lead_minutes',
            ]));
            if ($fields) $this->trail()->record('notification_preferences.updated',
                'notification_preferences', (string) $model->getKey(), ['changed_settings' => $fields]);
        }
    }

    public function deleted(Model $model): void
    {
        if ($model instanceof WorkspaceTaskAssignment) {
            $this->trail()->record('assignment.removed', 'assignment', (string) $model->getKey(), [
                'task_id' => (string) $model->task_id,
                'assignee_user_id' => (string) $model->assigned_user_id,
            ]);
        } elseif ($model instanceof WorkspaceCollaborationMember) {
            $type = $model->activity_id ? 'activity' : 'todo';
            $this->trail()->record('collaboration.revoked', $type,
                (string) ($model->activity_id ?: $model->task_id), [
                    'member_id' => (string) $model->getKey(),
                    'collaborator_user_id' => (string) $model->user_id,
                    'member_role' => (string) $model->access_role,
                ]);
        }
    }
}
