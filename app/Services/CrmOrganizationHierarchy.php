<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Organization metadata for future assignment/review workflows.
 * IMPORTANT: this service does NOT grant permissions or CRM record visibility.
 */
final class CrmOrganizationHierarchy
{
    private ?array $roleParents = null;

    private function parents(): array
    {
        return $this->roleParents ??= DB::table('roles')
            ->pluck('parent_role_id', 'role_id')->all();
    }

    /** @return list<string> Strict ancestor role IDs, never the role itself. */
    public function ancestorRoleIds(?string $roleId): array
    {
        if (!$roleId) return [];
        $parents = $this->parents();
        $visited = [$roleId => true];
        $ancestors = [];
        $cursor = $roleId;
        for ($i = 0; $i < 128; ++$i) {
            $parent = $parents[$cursor] ?? null;
            if (!$parent) return $ancestors;
            if (isset($visited[$parent])) {
                throw ValidationException::withMessages([
                    'parent_role_id' => 'The role hierarchy contains a cycle. Resolve it before continuing.',
                ]);
            }
            $ancestors[] = (string) $parent;
            $visited[$parent] = true;
            $cursor = (string) $parent;
        }
        throw ValidationException::withMessages(['parent_role_id' => 'The role hierarchy is too deep.']);
    }

    /** User primary role from the multi-role pivot, falling back to users.role_id. */
    public function primaryRoleId(User $user): ?string
    {
        if ($user->relationLoaded('roles')) {
            $primary = $user->roles->first(fn ($role) => (bool) ($role->pivot?->is_primary ?? false));
            return $primary ? (string) $primary->role_id : ($user->role_id ? (string) $user->role_id : null);
        }
        $id = DB::table('user_roles')->where('user_id', $user->getKey())
            ->where('is_primary', true)->value('role_id');
        return $id ? (string) $id : ($user->role_id ? (string) $user->role_id : null);
    }

    /** Allowed manager must be active, same-branch, and hold an ancestor role. */
    public function isEligibleManager(User $employee, User $manager): bool
    {
        if ((string) $employee->getKey() === (string) $manager->getKey()) return false;
        if (strtolower((string) $manager->status) !== 'active') return false;
        // No automatic cross-branch delegation, including unassigned branches.
        if ((string) ($employee->branch_id ?? '') !== (string) ($manager->branch_id ?? '')) return false;

        $ancestors = $this->ancestorRoleIds($this->primaryRoleId($employee));
        if (!$ancestors) return false;

        $managerRoleIds = $manager->relationLoaded('roles')
            ? $manager->roles->pluck('role_id')->map(fn ($id) => (string) $id)->all()
            : DB::table('user_roles')->where('user_id', $manager->getKey())->pluck('role_id')->map(fn ($id) => (string) $id)->all();
        if ($manager->role_id) $managerRoleIds[] = (string) $manager->role_id;

        return count(array_intersect($ancestors, $managerRoleIds)) > 0;
    }

    /** In a serialized transaction, disallow employee -> manager -> ... -> employee. */
    public function assertNoUserCycle(string $employeeId, string $managerId): void
    {
        $cursor = $managerId;
        $visited = [];
        for ($i = 0; $i < 128; ++$i) {
            if ($cursor === $employeeId || isset($visited[$cursor])) {
                throw ValidationException::withMessages([
                    'manager_user_id' => 'This manager would create a circular reporting relationship.',
                ]);
            }
            $visited[$cursor] = true;
            $next = DB::table('user_reporting_lines')->where('user_id', $cursor)->value('manager_user_id');
            if (!$next) return;
            $cursor = (string) $next;
        }
        throw ValidationException::withMessages(['manager_user_id' => 'Reporting chain exceeds the allowed depth.']);
    }
}
