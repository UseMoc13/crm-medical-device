<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** A6.11.5 opt-in scope for Installations and future Activities CRUD. */
final class CrmInstallationActivityDataScope
{
    private const PERMISSIONS = [
        'installations' => 'crm_page.installations.view',
        'activities' => 'crm_page.activities.view',
    ];

    public function enabled(string $table): bool
    {
        return isset(self::PERMISSIONS[$table])
            && (bool) config('crm_data_scope.' . $table . '_enabled', false);
    }

    public function visible(Builder $query, string $table): Builder
    {
        if (! $this->enabled($table)) {
            return $query;
        }

        return app(CrmDataScope::class)->applyForPermission(
            $query,
            $this->actor(),
            $table,
            self::PERMISSIONS[$table],
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

    /** In opt-in mode, reject reassignment beyond the current role's effective scope. */
    public function authorizeAssignee(?string $assigneeId): ?string
    {
        if (! $this->enabled('installations')) {
            return $assigneeId;
        }

        $actor = $this->actor();
        $resolver = app(CrmPermissionResolver::class);
        $permission = self::PERMISSIONS['installations'];
        abort_unless($resolver->allows($actor, $permission), 403);
        $scopes = $resolver->scopes($actor, $permission);
        abort_unless(count($scopes) > 0, 403, 'Installation data scope is not configured.');
        $assigneeId = $assigneeId ?: (string) $actor->getKey();

        if (in_array('all', $scopes, true)) {
            return $assigneeId;
        }
        $allowed = in_array('assigned', $scopes, true)
            && $assigneeId === (string) $actor->getKey();

        if (! $allowed && in_array('branch', $scopes, true) && $actor->branch_id !== null) {
            $allowed = DB::table('users')
                ->where('user_id', $assigneeId)
                ->where('branch_id', $actor->branch_id)
                ->exists();
        }
        abort_unless($allowed, 403, 'The selected installer is outside your data scope.');
        return $assigneeId;
    }

    /** Optional ownership helper for Activities when its CRUD controller exists. */
    public function authorizeActivityOwner(?string $ownerId): ?string
    {
        if (! $this->enabled('activities')) {
            return $ownerId;
        }

        $actor = $this->actor();
        $resolver = app(CrmPermissionResolver::class);
        $permission = self::PERMISSIONS['activities'];
        abort_unless($resolver->allows($actor, $permission), 403);
        $scopes = $resolver->scopes($actor, $permission);
        abort_unless(count($scopes) > 0, 403, 'Activities data scope is not configured.');
        $ownerId = $ownerId ?: (string) $actor->getKey();
        if (in_array('all', $scopes, true)) {
            return $ownerId;
        }
        $allowed = in_array('own', $scopes, true) && $ownerId === (string) $actor->getKey();
        if (! $allowed && in_array('branch', $scopes, true) && $actor->branch_id !== null) {
            $allowed = DB::table('users')->where('user_id', $ownerId)
                ->where('branch_id', $actor->branch_id)->exists();
        }
        abort_unless($allowed, 403, 'Activity owner is outside your data scope.');
        return $ownerId;
    }

    private function actor(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);
        return $user;
    }
}
