<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CrmTeam;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkspaceTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A7.4.7 directory for TO-DO recipient selection. FILTERS ARE NEVER GRANTS.
 * Assignment rights remain identical to A7.4.4: active same-branch users,
 * or only oneself if unassigned; protected Super Admin may target all active.
 */
final class CrmTodoRecipientDirectory
{
    private function isAdmin(User $actor): bool
    {
        return app(CrmPermissionResolver::class)->isSuperAdmin($actor);
    }

    public function eligibleUsers(User $actor): Builder
    {
        $query = User::query()->where('users.status', 'active');
        if (!$this->isAdmin($actor)) {
            if ($actor->branch_id === null) {
                $query->where('users.user_id', (string) $actor->getKey());
            } else {
                $query->where('users.branch_id', (string) $actor->branch_id);
            }
        }
        return $query;
    }

    private function eligibleIds(User $actor): Builder
    {
        return $this->eligibleUsers($actor)->select('users.user_id');
    }

    /** Only teams containing an assignable user; non-admins see only their active teams. */
    private function visibleTeams(User $actor): Builder
    {
        $query = CrmTeam::query()->where('status', 'active')
            ->whereHas('activeMemberships', fn (Builder $q) =>
                $q->whereIn('user_id', $this->eligibleIds($actor)));
        if (!$this->isAdmin($actor)) {
            $query->whereHas('activeMemberships', fn (Builder $q) =>
                $q->where('user_id', (string) $actor->getKey()));
        }
        return $query;
    }

    public function filterOptions(User $actor): array
    {
        $eligible = $this->eligibleIds($actor);
        $roleIds = DB::table('user_roles')->whereIn('user_id', $eligible)->select('role_id');
        $roles = Role::query()->where(function (Builder $q) use ($actor, $roleIds) {
            $q->whereIn('role_id', $roleIds)
                ->orWhereIn('role_id', $this->eligibleUsers($actor)->whereNotNull('role_id')->select('role_id'));
        })->orderBy('role_name')->get(['role_id', 'role_name']);
        $branches = Branch::query()->whereIn('branch_id',
            $this->eligibleUsers($actor)->whereNotNull('branch_id')->select('branch_id'))
            ->orderBy('branch_name')->get(['branch_id', 'branch_name']);
        $teams = $this->visibleTeams($actor)->orderBy('team_name')
            ->get(['team_id', 'team_name', 'team_type', 'branch_id']);
        return compact('roles', 'branches', 'teams');
    }

    public function search(User $actor, array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->eligibleUsers($actor)->with(['roles', 'branch']);
        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $escaped = '%' . addcslashes($search, '%_\\') . '%';
            $query->where(fn (Builder $q) => $q->where('users.name', 'ilike', $escaped)
                ->orWhere('users.email', 'ilike', $escaped));
        }
        if ($id = ($filters['role_id'] ?? null)) {
            $query->where(function (Builder $q) use ($id) {
                $q->where('users.role_id', $id)
                    ->orWhereHas('roles', fn (Builder $r) => $r->where('roles.role_id', $id));
            });
        }
        if ($id = ($filters['branch_id'] ?? null)) $query->where('users.branch_id', $id);
        if ($id = ($filters['team_id'] ?? null)) {
            // 404 rather than exposing metadata about invisible teams.
            abort_unless($this->visibleTeams($actor)->whereKey($id)->exists(), 404);
            $query->whereIn('users.user_id', DB::table('crm_team_members')
                ->where('team_id', $id)->whereNull('left_at')->select('user_id'));
        }
        return $query->orderBy('users.name')->orderBy('users.user_id')
            ->paginate(20, ['users.user_id', 'users.name', 'users.email', 'users.branch_id', 'users.role_id', 'users.status']);
    }

    /** Keep previously assigned users on Edit (even after status/branch changes). */
    public function selected(User $actor, array $ids, ?WorkspaceTask $todo = null): array
    {
        $ids = array_values(array_unique(array_filter(array_map('strval', array_filter($ids, 'is_scalar')))));
        if (!$ids) return [];
        $existingIds = $todo ? $todo->assignments()->whereIn('assigned_user_id', $ids)
            ->pluck('assigned_user_id')->map(fn ($v) => (string) $v)->all() : [];
        $allowed = $this->eligibleUsers($actor)->whereIn('users.user_id', $ids)
            ->pluck('user_id')->map(fn ($v) => (string) $v)->all();
        $visibleIds = array_values(array_unique(array_merge($allowed, $existingIds)));
        if (!$visibleIds) return [];
        $people = User::query()->with(['roles', 'branch'])->whereIn('user_id', $visibleIds)->get();
        return $people->map(fn (User $u) => $this->person($u) + [
            'retained_only' => !in_array((string) $u->getKey(), $allowed, true),
        ])->values()->all();
    }

    public function assertAssignable(User $actor, array $ids, ?WorkspaceTask $todo = null): void
    {
        if (!$ids) return;
        $selected = array_column($this->selected($actor, $ids, $todo), 'id');
        if (array_diff($ids, $selected)) {
            throw ValidationException::withMessages([
                'assignee_ids' => 'Select only eligible active users. Existing assignees may be kept if their branch or status changed.',
            ]);
        }
    }

    public function person(User $user): array
    {
        $names = $user->roles->pluck('role_name')->filter()->unique()->values()->all();
        if (!$names && $user->role_id) {
            $names = [Role::query()->whereKey($user->role_id)->value('role_name') ?? 'Role'];
        }
        return [
            'id' => (string) $user->getKey(),
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'branch' => (string) ($user->branch?->branch_name ?? 'No branch'),
            'roles' => $names,
        ];
    }
}
