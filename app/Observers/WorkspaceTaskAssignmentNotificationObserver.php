<?php

namespace App\Observers;

use App\Models\WorkspaceTaskAssignment;
use App\Services\WorkspaceNotificationCenter;

final class WorkspaceTaskAssignmentNotificationObserver
{
    public function created(WorkspaceTaskAssignment $assignment): void
    {
        $task = $assignment->task;
        if (!$task || $task->visibility !== 'work') return;
        if ((string) $task->owner_user_id === (string) $assignment->assigned_user_id) return;

        app(WorkspaceNotificationCenter::class)->send(
            (string) $assignment->assigned_user_id, 'assignment', 'todo',
            (string) $task->getKey(), 'New TO-DO assignment',
            'A work task has been assigned to you.',
            'assignment:'.$assignment->getKey(), (string) $task->owner_user_id,
        );
    }

    public function updated(WorkspaceTaskAssignment $assignment): void
    {
        if (!$assignment->wasChanged('review_status')
            || $assignment->review_status !== 'pending_review') return;
        $task = $assignment->task;
        if (!$task || $task->visibility !== 'work') return;
        if ((string) $task->owner_user_id === (string) $assignment->assigned_user_id) return;

        // Completion timestamps also change after a revision, so resubmits
        // produce a new notice while normal update() calls are ignored.
        $version = $assignment->completed_at?->format('Y-m-d H:i:s.u') ?? now()->format('Y-m-d H:i:s.u');
        app(WorkspaceNotificationCenter::class)->send(
            (string) $task->owner_user_id, 'submission', 'todo',
            (string) $task->getKey(), 'Submission awaiting review',
            'A work submission is ready for your approval or revision.',
            'submission:'.$assignment->getKey().':'.hash('sha256', $version),
            (string) $assignment->assigned_user_id,
        );
    }
}
