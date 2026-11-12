<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Transitional primary-role synchronization for the A1 user_roles pivot.
 * No public additional-role assignment endpoint is exposed in A2.
 * In A3/A4, use a separate authorization-gated service for changing role grants.
 */
class UserRoleAssignmentService
{
    /**
     * Call inside the SAME transaction as a legacy users.role_id update.
     * Caller must lock the user row first for concurrent role changes.
     */
    public function syncPrimary(User $user, string $roleId): void
    {
        $role = Role::query()->whereKey($roleId)->first();
        if (!$role) {
            throw ValidationException::withMessages(['role_id' => 'The selected role no longer exists.']);
        }

        // A2 is deliberately not a privileged role-granting interface.
        // Existing privileged accounts may retain their unchanged primary role.
        if (strcasecmp($role->role_name, 'Super Admin') === 0
            && (string) $user->getOriginal('role_id') !== $roleId) {
            throw ValidationException::withMessages([
                'role_id' => 'Super Admin assignments are unavailable until protected authorization is enabled.',
            ]);
        }

        $now = now();

        // Unset old primary FIRST to comply with partial unique PostgreSQL index.
        DB::table('user_roles')
            ->where('user_id', $user->user_id)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);

        // If selected role already exists as an additional grant, promote it.
        // If absent, insert it without changing any additional assignments.
        $exists = DB::table('user_roles')
            ->where('user_id', $user->user_id)
            ->where('role_id', $roleId)
            ->exists();

        if (!$exists) {
            DB::table('user_roles')->insert([
                'user_role_id' => DB::raw('gen_random_uuid()'),
                'user_id' => $user->user_id,
                'role_id' => $roleId,
                'is_primary' => false,
                'assigned_by' => null,
                'created_at' => $now,
            ]);
        }

        DB::table('user_roles')
            ->where('user_id', $user->user_id)
            ->where('role_id', $roleId)
            ->update(['is_primary' => true]);

        // Legacy column remains required for all current CRM code paths.
        if ((string) $user->role_id !== $roleId) {
            $user->role_id = $roleId;
            $user->save();
        }
    }
}
