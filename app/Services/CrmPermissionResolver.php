<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * A4 foundational resolver. It does NOT install module authorization by itself.
 * Every permission is denied unless granted; only a protected Super Admin
 * bypasses permission checks (never business workflow / data invariants).
 */
class CrmPermissionResolver
{
    public function isSuperAdmin(User $user): bool
    {
        return DB::table('user_roles as ur')
            ->join('roles as r', 'r.role_id', '=', 'ur.role_id')
            ->where('ur.user_id', $user->getKey())
            ->where('r.is_system', true)
            ->whereRaw('LOWER(r.role_name) = ?', ['super admin'])
            ->exists();
    }

    public function hasRole(User $user, string $roleName): bool
    {
        return DB::table('user_roles as ur')
            ->join('roles as r', 'r.role_id', '=', 'ur.role_id')
            ->where('ur.user_id', $user->getKey())
            ->whereRaw('LOWER(r.role_name) = ?', [mb_strtolower($roleName)])
            ->exists();
    }

    public function allows(User $user, string $permissionName): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->grantQuery($user, $permissionName)->exists();
    }

    /**
     * Returns explicit non-null scopes only. No scope = no scoped data access.
     * Multiple roles combine scopes as a UNION when policies are introduced.
     * Never treat NULL as `all`.
     *
     * @return list<string>
     */
    public function scopes(User $user, string $permissionName): array
    {
        if ($this->isSuperAdmin($user)) {
            return ['all'];
        }

        return $this->grantQuery($user, $permissionName)
            ->whereNotNull('rp.data_scope')
            ->distinct()
            ->pluck('rp.data_scope')
            ->all();
    }

    private function grantQuery(User $user, string $permissionName): \Illuminate\Database\Query\Builder
    {
        return DB::table('user_roles as ur')
            ->join('role_permissions as rp', 'rp.role_id', '=', 'ur.role_id')
            ->join('permissions as p', 'p.permission_id', '=', 'rp.permission_id')
            ->where('ur.user_id', $user->getKey())
            ->where('p.permission_name', $permissionName);
    }
}
