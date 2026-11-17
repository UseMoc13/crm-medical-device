<?php

namespace App\Observers;

use App\Models\WorkspaceCollaborationMember;
use App\Services\WorkspaceNotificationCenter;

final class WorkspaceCollaborationMemberNotificationObserver
{
    public function created(WorkspaceCollaborationMember $member): void
    {
        $type = $member->activity_id ? 'activity' : 'todo';
        $resource = $member->activity_id ? $member->activity : $member->task;
        if (!$resource || ($type === 'todo' && $resource->visibility !== 'work')) return;
        $ownerId = (string) ($type === 'activity' ? $resource->user_id : $resource->owner_user_id);
        if ((string) $member->user_id === $ownerId) return;

        app(WorkspaceNotificationCenter::class)->send(
            (string) $member->user_id, 'collaboration', $type,
            (string) $resource->getKey(), 'Collaboration invitation',
            'You have been invited to collaborate on a CRM item.',
            'collaboration:'.$member->getKey(), $ownerId,
        );
    }
}
