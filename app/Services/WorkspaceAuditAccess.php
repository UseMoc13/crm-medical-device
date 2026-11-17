<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

/** Explicit permission, no implicit grant from team/branch membership. */
final class WorkspaceAuditAccess
{
    public function canView(?User $user): bool
    {
        if (!$user || strtolower(trim((string) $user->status)) !== 'active') return false;
        // Defensive: old SQLite feature fixtures may lack the role tables.
        foreach (['roles', 'user_roles'] as $table) {
            if (!Schema::hasTable($table)) return false;
        }
        $resolver = app(CrmPermissionResolver::class);
        if ($resolver->isSuperAdmin($user)) return true;
        if (!Schema::hasTable('permissions') || !Schema::hasTable('role_permissions')) return false;
        return $resolver->allows($user, 'workspace_audit.view');
    }

    public function assertView(?User $user): void
    {
        abort_unless($this->canView($user), 403, 'You are not allowed to view workspace audit events.');
    }
}
