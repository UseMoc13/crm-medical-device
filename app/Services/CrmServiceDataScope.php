<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** Opt-in service / maintenance row-scope policy; no global scopes. */
final class CrmServiceDataScope
{
    private const TABLES = ['service_tickets', 'maintenance_schedules'];

    public function enabled(string $table): bool
    {
        return in_array($table, self::TABLES, true)
            && (bool) config('crm_data_scope.' . $table . '_enabled', false);
    }

    public function visible(Builder $query, string $table): Builder
    {
        if (! $this->enabled($table)) {
            return $query;
        }
        $user = $this->actor();
        return app(CrmDataScope::class)->applyForPermission(
            $query,
            $user,
            $table,
            'crm_page.' . ($table === 'maintenance_schedules' ? 'maintenance' : $table) . '.view',
            app(CrmPermissionResolver::class)
        );
    }

    public function assertVisible(Builder $query, string $table, string $id): void
    {
        if (! $this->enabled($table)) {
            return;
        }
        abort_unless($this->visible($query, $table)->whereKey($id)->exists(), 404);
    }

    /** Validates assigned technician against the effective union of role scopes. */
    public function authorizeAssignee(string $table, ?string $assigneeId): ?string
    {
        if (! $this->enabled($table)) {
            return $assigneeId;
        }

        $user = $this->actor();
        $permissionName = 'crm_page.' . ($table === 'maintenance_schedules' ? 'maintenance' : $table) . '.view';
        $resolver = app(CrmPermissionResolver::class);
        abort_unless($resolver->allows($user, $permissionName), 403);
        $scopes = $resolver->scopes($user, $permissionName);
        if (in_array('all', $scopes, true)) {
            return $assigneeId;
        }

        // Unassigned operational records would be invisible to the assignee.
        $assigneeId = $assigneeId ?: (string) $user->getKey();
        $allowed = in_array('assigned', $scopes, true)
            && $assigneeId === (string) $user->getKey();
        if (! $allowed && in_array('branch', $scopes, true) && $user->branch_id !== null) {
            $allowed = DB::table('users')
                ->where('user_id', $assigneeId)
                ->where('branch_id', $user->branch_id)
                ->exists();
        }
        abort_unless($allowed, 403, 'Selected technician is outside your configured data scope.');
        return $assigneeId;
    }

    private function actor(): User
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(401);
        }
        return $user;
    }
}
