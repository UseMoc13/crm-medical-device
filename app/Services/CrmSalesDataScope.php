<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * A6.11.2 opt-in sales row policy.
 * This is a service, not a global model scope: OFF by default.
 */
final class CrmSalesDataScope
{
    public function enabled(string $table): bool
    {
        return in_array($table, ['leads', 'opportunities'], true)
            && (bool) config('crm_data_scope.' . $table . '_enabled', false);
    }

    public function visible(Builder $query, string $table): Builder
    {
        if (! $this->enabled($table)) {
            return $query;
        }
        $user = $this->actor();
        return app(CrmDataScope::class)->applyForPermission(
            $query, $user, $table, 'crm_page.' . $table . '.view',
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

    /**
     * Verify the intended owner of a new or edited record before saving.
     * Role page/action permissions are still enforced by existing middleware.
     * NULL owner is assigned to the current user for Leads only when scoped.
     */
    public function authorizeOwner(string $table, ?string $ownerId): string
    {
        if (! $this->enabled($table)) {
            return (string) ($ownerId ?? '');
        }

        $user = $this->actor();
        $permissions = app(CrmPermissionResolver::class);
        $permission = 'crm_page.' . $table . '.view';
        abort_unless($permissions->allows($user, $permission), 403);

        $scopes = $permissions->scopes($user, $permission);
        abort_if(!$scopes, 403, 'The role has no configured data scope.');

        // Unassigned Leads are not visible in Own / Branch, so make their
        // owner explicit. Opportunities already require a user_id.
        $ownerId = $ownerId ?: (string) $user->getKey();
        if (in_array('all', $scopes, true)) {
            return $ownerId;
        }

        $valid = in_array('own', $scopes, true) && $ownerId === (string) $user->getKey();
        if (!$valid && in_array('branch', $scopes, true) && $user->branch_id !== null) {
            $valid = DB::table('users')
                ->where('user_id', $ownerId)
                ->where('branch_id', $user->branch_id)
                ->exists();
        }
        abort_unless($valid, 403, 'The selected owner is outside your data scope.');
        return $ownerId;
    }

    public function actor(): User
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(401);
        }
        return $user;
    }
}
