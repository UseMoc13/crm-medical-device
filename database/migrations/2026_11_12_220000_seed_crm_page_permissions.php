<?php

use App\Services\CrmRoleAccessCatalog as Catalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Seed permissions without altering existing role assignments; derive
        // baseline from legacy module permissions, then use templates for
        // finer defaults such as read-only catalogue.
        foreach (Catalog::PAGES as $slug => $page) {
            foreach (Catalog::ACTIONS as $action) {
                $key = Catalog::key($slug, $action);
                if (! DB::table('permissions')->where('permission_name', $key)->exists()) {
                    DB::table('permissions')->insert([
                        'permission_id' => (string) DB::selectOne('SELECT gen_random_uuid() AS id')->id,
                        'permission_name' => $key,
                        'module' => 'crm_page', 'action' => $action,
                        'description' => 'A6.2 page action permission: ' . $page['label'],
                        'created_at' => now(),
                    ]);
                }
            }
        }
        $roles = DB::table('roles')->where('is_system', false)->get();
        foreach ($roles as $role) {
            $template = Catalog::template(Catalog::templateForRole($role->role_name));
            foreach ($template as $slug => $actions) {
                foreach ($actions as $action) {
                    $pid = DB::table('permissions')->where('permission_name', Catalog::key($slug, $action))->value('permission_id');
                    if (! DB::table('role_permissions')->where('role_id', $role->role_id)->where('permission_id', $pid)->exists()) {
                        DB::table('role_permissions')->insert([
                            'role_permission_id' => (string) DB::selectOne('SELECT gen_random_uuid() AS id')->id,
                            'role_id' => $role->role_id, 'permission_id' => $pid,
                            'data_scope' => null, 'created_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
    public function down(): void
    {
        // No destructive rollback of role grants. Manual security review required.
    }
};
