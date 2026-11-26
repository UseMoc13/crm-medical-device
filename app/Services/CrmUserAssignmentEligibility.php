<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A7.9.7: operational eligibility, separate from permission and row-scope policy.
 * A valid business assignee must be active, non-protected and hold a recognized
 * job-function role via user_roles (including additional roles). The users.role_id
 * compatibility fallback is used ONLY for users with no pivot assignments.
 *
 * Existing assignments remain visible/editable without reassignment so a role
 * change does not retroactively corrupt historical CRM rows. This exception
 * must never be used to assign the same ineligible person to a new record.
 */
final class CrmUserAssignmentEligibility
{
    /** @return list<string> */
    public function rolesFor(string $purpose): array
    {
        $map = (array) config('crm_assignment.roles', []);
        if (!array_key_exists($purpose, $map)) {
            throw new \InvalidArgumentException('Unknown CRM assignment purpose: ' . $purpose);
        }
        return array_values(array_unique(array_filter(array_map(
            static fn ($name): string => mb_strtolower(trim((string) $name)),
            (array) $map[$purpose]
        ))));
    }

    /** @return Builder<User> */
    public function eligibleQuery(string $purpose): Builder
    {
        $names = $this->rolesFor($purpose);
        $roleIds = Role::query()
            ->whereIn(DB::raw('LOWER(TRIM(role_name))'), $names)
            ->pluck('role_id');

        return User::query()
            ->whereRaw('LOWER(TRIM(users.status)) = ?', ['active'])
            ->whereNotIn('users.user_id', DB::table('user_roles as protected_ur')
                ->join('roles as protected_r', 'protected_r.role_id', '=', 'protected_ur.role_id')
                ->where('protected_r.is_system', true)
                ->whereRaw('LOWER(TRIM(protected_r.role_name)) = ?', ['super admin'])
                ->select('protected_ur.user_id'))
            ->where(function (Builder $q) use ($roleIds): void {
                $q->whereHas('roles', fn (Builder $r) => $r->whereIn('roles.role_id', $roleIds))
                    ->orWhere(function (Builder $legacy) use ($roleIds): void {
                        $legacy->whereDoesntHave('roles')->whereIn('users.role_id', $roleIds);
                    });
            });
    }

    /** @return Collection<int,User> */
    public function options(string $purpose, ?string $currentId = null): Collection
    {
        // Limit fields: Leads serializes this directory as JSON in Blade.
        // Never leak hashes, phones, login timestamps or role membership into it.
        $users = $this->eligibleQuery($purpose)
            ->orderBy('users.name')->orderBy('users.user_id')
            ->get(['users.user_id', 'users.name', 'users.email']);

        if ($currentId !== null && $currentId !== '' && !$users->contains('user_id', $currentId)) {
            $previous = User::query()->whereKey($currentId)
                ->first(['user_id', 'name', 'email']);
            if ($previous) {
                $previous->setAttribute('assignment_warning', true);
                $users->push($previous);
            }
        }
        return $users;
    }

    /**
     * @throws ValidationException
     */
    public function assertAssignable(
        ?string $userId,
        string $purpose,
        ?string $existingUserId = null,
        bool $required = false,
        string $field = 'user_id'
    ): ?string {
        $this->rolesFor($purpose); // Fail closed for unknown purposes, including null selections.
        if ($userId === null || trim($userId) === '') {
            if ($required) {
                throw ValidationException::withMessages([$field => 'Please select an eligible active staff member.']);
            }
            return null;
        }

        // Keeping the SAME historical assignee is not a new assignment.
        if ($existingUserId !== null && $existingUserId !== '' && $existingUserId === $userId) {
            return $userId;
        }

        if (!$this->eligibleQuery($purpose)->whereKey($userId)->exists()) {
            $identity = $purpose === 'technician' ? 'technician/service engineer' : 'sales representative';
            throw ValidationException::withMessages([
                $field => 'Select an active ' . $identity . ' with a valid job role. Inactive and protected accounts cannot be assigned.',
            ]);
        }
        return $userId;
    }
}
