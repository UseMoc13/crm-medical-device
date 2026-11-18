<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * A7.4.12.2: manage the ONE sensitive audit-view grant from the existing Role editor.
 * The caller's RoleController create/update transaction must enclose this operation.
 * This grant does not bestow any module CRUD or data-scope permissions.
 */
final class WorkspaceRoleAuditGrant
{
    public const PERMISSION = 'workspace_audit.view';

    public function isGranted(Role $role): bool
    {
        $permission = DB::table('permissions')->where('permission_name', self::PERMISSION)->value('permission_id');
        if (!$permission) return false;
        return DB::table('role_permissions')->where('role_id', $role->getKey())
            ->where('permission_id', $permission)->exists();
    }

    public function synchronizeFromRoleForm(Request $request, Role $role): void
    {
        // Legacy clients, old forms and non-Super-Admin editors must preserve the grant.
        // Supplying the checkbox without its hidden form marker is also forbidden.
        if (!$request->exists('workspace_audit_access_present')) {
            abort_if($request->exists('workspace_audit_access'), 403,
                'Only Super Admin can modify Audit Logs permissions.');
            return;
        }

        $actor = $request->user('web');
        abort_unless($actor instanceof User && app(CrmPermissionResolver::class)->isSuperAdmin($actor),
            403, 'Only Super Admin can modify Audit Logs permissions.');

        $validated = $request->validate([
            'workspace_audit_access_present' => ['required', 'in:1'],
            'workspace_audit_access' => ['nullable', 'in:1'],
        ]);
        $desired = ($validated['workspace_audit_access'] ?? null) === '1';
        $this->setGrant($role, $desired, (string) $actor->getKey());
    }

    public function setGrant(Role $role, bool $desired, string $actorId): void
    {
        // Defense-in-depth: even a future caller bypassing the Role form cannot
        // use this public service to grant permissions as an ordinary account.
        $actor = auth('web')->user();
        abort_unless($actor instanceof User && (string) $actor->getKey() === $actorId
            && app(CrmPermissionResolver::class)->isSuperAdmin($actor),
            403, 'Only Super Admin can modify Audit Logs permissions.');

        // Always transact the grant + event together, even outside RoleController.
        // A nested call participates in the caller's encompassing transaction.
        DB::transaction(function () use ($role, $desired, $actorId): void {
            $permission = DB::table('permissions')->where('permission_name', self::PERMISSION)->value('permission_id');
            if (!$permission) {
                throw new \RuntimeException('Audit permission is not seeded. Run php artisan migrate.');
            }
            $locked = DB::table('roles')->where('role_id', $role->getKey())->lockForUpdate()->first();
            if (!$locked) throw new \RuntimeException('Role no longer exists.');
            if ((bool) ($locked->is_system ?? false) || strcasecmp((string) $locked->role_name, 'Super Admin') === 0) {
                abort(403, 'Protected system roles cannot have their grants modified.');
            }
            $query = DB::table('role_permissions')->where('role_id', $role->getKey())
                ->where('permission_id', $permission);
            $previous = $query->exists();
            if ($previous === $desired) return;
            if ($desired) {
                DB::table('role_permissions')->insert([
                    'role_permission_id' => (string) Str::uuid(),
                    'role_id' => $role->getKey(),
                    'permission_id' => $permission,
                    'data_scope' => null,
                    'created_at' => now(),
                ]);
            } else {
                $query->delete();
            }
            app(WorkspaceAuditTrail::class)->record('role.audit_access_changed', 'role',
                (string) $role->getKey(), [
                    'audit_access_from' => $previous,
                    'audit_access_to' => $desired,
                ], $actorId);
        }, 3);
    }
}
