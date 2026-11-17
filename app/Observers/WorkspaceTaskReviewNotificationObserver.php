<?php

namespace App\Observers;

use App\Models\WorkspaceTaskReview;
use App\Services\WorkspaceNotificationCenter;

final class WorkspaceTaskReviewNotificationObserver
{
    public function created(WorkspaceTaskReview $review): void
    {
        $assignment = $review->assignment;
        if (!$assignment || !$review->task || $review->task->visibility !== 'work') return;
        $revision = $review->decision === 'revision_requested';
        app(WorkspaceNotificationCenter::class)->send(
            (string) $assignment->assigned_user_id,
            $revision ? 'revision' : 'approval', 'todo',
            (string) $review->task_id,
            $revision ? 'Revision requested' : 'Submission approved',
            $revision ? 'Your submission needs a revision. Open the task to read the feedback.'
                : 'Your submission has been approved.',
            'review:'.$review->getKey(), (string) $review->actor_user_id,
        );
    }
}
