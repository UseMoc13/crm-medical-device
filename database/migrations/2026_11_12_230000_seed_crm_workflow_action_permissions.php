<?php

use App\Services\CrmRoleAccessCatalog;
use App\Services\CrmWorkflowAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        foreach (CrmWorkflowAccess::ACTIONS as $slug => $action) {
            $key = CrmWorkflowAccess::key($slug);
            $pid = DB::table('permissions')->where('permission_name', $key)->value('permission_id');
            if (! $pid) {
                $pid = (string) Str::uuid();
                DB::table('permissions')->insert([
                    'permission_id' => $pid,
                    'permission_name' => $key,
                    'module' => 'crm_workflow',
                    'action' => 'execute',
                    'description' => 'A6.8 workflow action: ' . $action['label'],
                    'created_at' => now(),
                ]);
            }
            // Transitional compatibility: preserve existing effective access.
            // Only roles WITH the necessary page-action grant inherit the workflow grant.
            $parent = CrmRoleAccessCatalog::key($action['page'], $action['action']);
            $parentPid = DB::table('permissions')->where('permission_name', $parent)->value('permission_id');
            if (! $parentPid) {
                throw new RuntimeException('A6.2 page permissions must be migrated before A6.8: ' . $parent);
            }
            $roleIds = DB::table('role_permissions')->where('permission_id', $parentPid)
                ->pluck('role_id');
            foreach ($roleIds as $roleId) {
                if (! DB::table('role_permissions')->where('role_id', $roleId)->where('permission_id', $pid)->exists()) {
                    DB::table('role_permissions')->insert([
                        'role_permission_id' => (string) Str::uuid(),
                        'role_id' => $roleId,
                        'permission_id' => $pid,
                        'data_scope' => null,
                        'created_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Security-sensitive grants are not silently removed on rollback.
        // Restoration requires explicit review, per README.
    }
};
