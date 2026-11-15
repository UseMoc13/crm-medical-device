<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\UserReportingLine;
use App\Services\CrmOrganizationHierarchy;
use App\Services\CrmPermissionResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class OrganizationController extends Controller
{
    public function index(CrmOrganizationHierarchy $hierarchy): View
    {
        $roles = Role::query()->orderBy('role_name')->get();
        $users = User::query()->with(['roles', 'branch'])->orderBy('name')->get();
        $reporting = UserReportingLine::query()->get()->keyBy('user_id');
        $roleLookup = $roles->keyBy('role_id');
        $protectedUserIds = DB::table('user_roles as ur')
            ->join('roles as r', 'ur.role_id', '=', 'r.role_id')
            ->where('r.is_system', true)->whereRaw('LOWER(r.role_name) = ?', ['super admin'])
            ->pluck('ur.user_id')->map(fn ($id) => (string) $id)->all();
        $userLookup = $users->keyBy('user_id');

        $eligibleManagers = [];
        foreach ($users as $employee) {
            $eligibleManagers[(string) $employee->getKey()] = $users
                ->filter(fn (User $candidate) => $hierarchy->isEligibleManager($employee, $candidate))
                ->values();
        }
        return view('organization.index', compact(
            'roles', 'users', 'reporting', 'roleLookup', 'userLookup', 'eligibleManagers', 'protectedUserIds', 'hierarchy'
        ));
    }

    public function updateRoleParent(Request $request, Role $role, CrmOrganizationHierarchy $hierarchy): RedirectResponse
    {
        $data = $request->validate([
            'parent_role_id' => ['nullable', 'uuid', 'exists:roles,role_id'],
        ]);
        $parentId = $data['parent_role_id'] ?? null;

        DB::transaction(function () use ($role, $parentId, $hierarchy): void {
            // Serialize hierarchy edits so concurrent requests cannot form a cycle.
            DB::select('SELECT pg_advisory_xact_lock(710425001)');
            $locked = Role::query()->whereKey($role->getKey())->lockForUpdate()->firstOrFail();
            abort_if((bool) ($locked->is_system ?? false) || strcasecmp((string) $locked->role_name, 'Super Admin') === 0,
                403, 'Protected system roles cannot be assigned a reporting parent.');

            if ($parentId) {
                if ((string) $parentId === (string) $locked->getKey()
                    || in_array((string) $locked->getKey(), $hierarchy->ancestorRoleIds((string) $parentId), true)) {
                    throw ValidationException::withMessages(['parent_role_id' => 'A role cannot report to itself or one of its descendants.']);
                }
                $parent = Role::query()->whereKey($parentId)->firstOrFail();
                if ((bool) ($parent->is_system ?? false) || strcasecmp((string) $parent->role_name, 'Super Admin') === 0) {
                    throw ValidationException::withMessages(['parent_role_id' => 'System roles cannot be used as a reporting parent.']);
                }
            }
            $previous = $locked->parent_role_id;
            if ((string) ($previous ?? '') === (string) ($parentId ?? '')) return;
            $locked->forceFill(['parent_role_id' => $parentId])->save();
            $this->audit('roles', (string) $locked->getKey(),
                ['parent_role_id' => $previous], ['parent_role_id' => $parentId]);
        });

        return redirect()->to(route('organization.index') . '#roles')
            ->with('success', 'Role hierarchy updated. Existing permissions were not changed.');
    }

    public function updateUserManager(Request $request, User $user, CrmOrganizationHierarchy $hierarchy): RedirectResponse
    {
        $data = $request->validate([
            'manager_user_id' => ['nullable', 'uuid', 'exists:users,user_id'],
        ]);
        $managerId = $data['manager_user_id'] ?? null;

        DB::transaction(function () use ($user, $managerId, $hierarchy): void {
            DB::select('SELECT pg_advisory_xact_lock(710425001)');
            $employee = User::query()->with('roles')->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            // The protected bootstrap administrator is not an ordinary subordinate.
            abort_if(app(CrmPermissionResolver::class)->isSuperAdmin($employee), 403,
                'Super Admin cannot be assigned a reporting manager here.');
            $previous = UserReportingLine::query()->where('user_id', $employee->getKey())
                ->lockForUpdate()->first();
            $oldManagerId = $previous?->manager_user_id;
            if ((string) ($oldManagerId ?? '') === (string) ($managerId ?? '')) return;

            if ($managerId) {
                if (strtolower((string) $employee->status) !== 'active') {
                    throw ValidationException::withMessages(['manager_user_id' => 'Activate the employee before assigning a manager.']);
                }
                $manager = User::query()->with('roles')->whereKey($managerId)->firstOrFail();
                if (!$hierarchy->isEligibleManager($employee, $manager)) {
                    throw ValidationException::withMessages([
                        'manager_user_id' => 'Choose an active manager in the same branch with a role above the employee primary role.',
                    ]);
                }
                $hierarchy->assertNoUserCycle((string) $employee->getKey(), (string) $managerId);
                if ($previous) {
                    $previous->update([
                        'manager_user_id' => $managerId,
                        'updated_by_user_id' => (string) Auth::id(),
                    ]);
                } else {
                    UserReportingLine::create([
                        'user_id' => (string) $employee->getKey(),
                        'manager_user_id' => $managerId,
                        'updated_by_user_id' => (string) Auth::id(),
                    ]);
                }
            } elseif ($previous) {
                $previous->delete();
            }

            $this->audit('user_reporting_lines', (string) $employee->getKey(),
                ['manager_user_id' => $oldManagerId], ['manager_user_id' => $managerId]);
        });

        return redirect()->to(route('organization.index') . '#reporting')
            ->with('success', 'Reporting manager updated. CRM data access was not changed.');
    }

    private function audit(string $table, string $recordId, array $old, array $new): void
    {
        DB::table('audit_logs')->insert([
            'audit_log_id' => (string) Str::uuid(),
            'user_id' => (string) Auth::id(),
            'action' => 'UPDATE',
            'table_name' => $table,
            'record_id' => $recordId,
            'old_value' => json_encode($old, JSON_THROW_ON_ERROR),
            'new_value' => json_encode($new, JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }
}
