<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Super Admin-only assignment writer. Always invoke within a DB transaction. */
class CrmRoleAssignmentService
{
    public function sync(User $user, string $primaryId, array $additionalIds, string $actorId): void
    {
        $additionalIds = array_values(array_unique(array_map('strval', $additionalIds)));
        if (in_array($primaryId, $additionalIds, true)) {
            throw ValidationException::withMessages([
                'additional_role_ids' => 'Primary Role cannot also be an Additional Role.',
            ]);
        }

        $ids = array_merge([$primaryId], $additionalIds);
        $roles = DB::table('roles')->whereIn('role_id', $ids)->get(['role_id', 'role_name', 'is_system']);
        if ($roles->count() !== count($ids) || $roles->contains(fn ($r) =>
            (bool) $r->is_system || strcasecmp((string) $r->role_name, 'Super Admin') === 0)) {
            throw ValidationException::withMessages([
                'additional_role_ids' => 'Invalid or protected system role selected.',
            ]);
        }

        // Locking the users row serializes writes for a single target user.
        $locked = DB::table('users')->where('user_id', $user->getKey())->lockForUpdate()->first();
        if (! $locked) {
            throw ValidationException::withMessages(['role_id' => 'User not found.']);
        }
        $existing = DB::table('user_roles')->where('user_id', $user->getKey())->pluck('role_id')->all();
        $existingSystem = DB::table('user_roles as ur')->join('roles as r', 'ur.role_id', '=', 'r.role_id')
            ->where('ur.user_id', $user->getKey())
            ->where(fn ($q) => $q->where('r.is_system', true)->orWhereRaw('LOWER(r.role_name) = ?', ['super admin']))
            ->exists();
        if ($existingSystem) {
            abort(403, 'System accounts cannot be modified through User CRUD.');
        }

        $now = now();
        // Reset primary before promoting a different assignment (partial unique index).
        DB::table('user_roles')->where('user_id', $user->getKey())->where('is_primary', true)
            ->update(['is_primary' => false]);

        foreach ($ids as $id) {
            if (! in_array($id, $existing, true)) {
                DB::table('user_roles')->insert([
                    'user_role_id' => DB::raw('gen_random_uuid()'),
                    'user_id' => $user->getKey(),
                    'role_id' => $id,
                    'is_primary' => false,
                    'assigned_by' => $actorId,
                    'created_at' => $now,
                ]);
            }
        }
        DB::table('user_roles')->where('user_id', $user->getKey())
            ->whereNotIn('role_id', $ids)->delete();
        DB::table('user_roles')->where('user_id', $user->getKey())
            ->where('role_id', $primaryId)->update(['is_primary' => true]);

        DB::table('users')->where('user_id', $user->getKey())->update([
            'role_id' => $primaryId,
            'updated_at' => $now,
        ]);

        // Record role set changes in existing audit_logs (does not store credentials).
        $before = array_values(array_map('strval', $existing)); sort($before);
        $after = array_values(array_map('strval', $ids)); sort($after);
        if ($before !== $after || (string) $locked->role_id !== $primaryId) {
            DB::table('audit_logs')->insert([
                'audit_log_id' => DB::raw('gen_random_uuid()'),
                'user_id' => $actorId,
                'action' => 'UPDATE',
                'table_name' => 'user_roles',
                'record_id' => $user->getKey(),
                'old_value' => json_encode(['role_ids' => $before, 'primary_role_id' => $locked->role_id]),
                'new_value' => json_encode(['role_ids' => $after, 'primary_role_id' => $primaryId]),
                'created_at' => $now,
            ]);
        }
    }
}
