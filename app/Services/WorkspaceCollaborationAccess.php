<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\WorkspaceCollaborationEntry;
use App\Models\WorkspaceCollaborationMember;
use App\Models\WorkspaceTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * A7.4.8: All collaboration authorization happens on the server.
 * Organization/team membership or CRM role is NEVER an implicit grant.
 * Activity CRM references and TO-DO submission files are NOT granted by collaboration.
 */
final class WorkspaceCollaborationAccess
{
    public function actorId(): string
    {
        abort_unless(Auth::guard('web')->check(), 401);
        return (string) Auth::guard('web')->id();
    }

    public function ownerId(Activity|WorkspaceTask $resource): string
    {
        return (string) ($resource instanceof Activity ? $resource->user_id : $resource->owner_user_id);
    }

    public function isOwner(Activity|WorkspaceTask $resource): bool
    {
        return $this->ownerId($resource) === $this->actorId();
    }

    public function type(Activity|WorkspaceTask $resource): string
    {
        return $resource instanceof Activity ? 'activity' : 'todo';
    }

    public function foreignKey(Activity|WorkspaceTask $resource): string
    {
        return $resource instanceof Activity ? 'activity_id' : 'task_id';
    }

    public function members(Activity|WorkspaceTask $resource): Builder
    {
        return WorkspaceCollaborationMember::query()->where($this->foreignKey($resource), (string) $resource->getKey());
    }

    public function entries(Activity|WorkspaceTask $resource): Builder
    {
        return WorkspaceCollaborationEntry::query()->where($this->foreignKey($resource), (string) $resource->getKey());
    }

    public function role(Activity|WorkspaceTask $resource): ?string
    {
        if ($this->isOwner($resource)) return 'creator';
        if ($resource instanceof WorkspaceTask) {
            if ($resource->visibility !== 'work') return null;
            if ($resource->assignments()->where('assigned_user_id', $this->actorId())->exists()) {
                return 'assignee';
            }
        }
        return $this->members($resource)->where('user_id', $this->actorId())->value('access_role');
    }

    public function assertVisible(Activity|WorkspaceTask $resource): string
    {
        $role = $this->role($resource);
        abort_unless($role !== null, 404);
        return $role;
    }

    public function assertOwner(Activity|WorkspaceTask $resource): void
    {
        $this->assertVisible($resource);
        abort_unless($this->isOwner($resource), 403, 'Only the creator may manage collaborators.');
    }

    public function assertCanWrite(Activity|WorkspaceTask $resource): string
    {
        $role = $this->assertVisible($resource);
        abort_unless(in_array($role, ['creator', 'assignee', 'contributor'], true), 403,
            'Viewer access is read-only.');
        return $role;
    }

    public function canSeeCrmDetails(Activity $activity): bool
    {
        // Do not let a collaboration grant escape the existing CRM reference scope.
        // The existing detail controller is only used for the original Activity creator.
        return $this->isOwner($activity);
    }
}
