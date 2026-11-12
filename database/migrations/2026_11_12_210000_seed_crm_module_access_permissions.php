<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A6.1: module entrance permissions only, not row-level access or CRUD permission.
 * Additive and idempotent: preserve existing roles and grants.
 */
return new class extends Migration {
    private const MODULE_ROLES = [
        'customer_lead.access' => ['Sales'],
        'sales_crm.access' => ['Sales'],
        'catalog.access' => ['Sales', 'Warehouse', 'Technician', 'Delivery Staff'],
        'catalog.manage' => ['Warehouse'],
        'inventory.access' => ['Warehouse'],
        'delivery.access' => ['Delivery Staff'],
        'installation.access' => ['Delivery Staff', 'Technician'],
        'service.access' => ['Technician'],
        'billing.access' => ['Finance'],
        'reporting.access' => ['Management', 'Finance'],
        'announcements.manage' => ['Admin'],
    ];

    public function up(): void
    {
        foreach (['Delivery Staff', 'Finance'] as $name) {
            if (! DB::table('roles')->whereRaw('LOWER(role_name) = ?', [strtolower($name)])->exists()) {
                DB::table('roles')->insert([
                    'role_id' => DB::raw('gen_random_uuid()'),
                    'role_name' => $name,
                    'description' => 'CRM division role (A6.1)',
                    'is_system' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        foreach (self::MODULE_ROLES as $permission => $roleNames) {
            [$module, $action] = explode('.', $permission, 2);
            $permissionId = DB::table('permissions')->where('permission_name', $permission)->value('permission_id');
            if (! $permissionId) {
                $permissionId = (string) DB::selectOne('SELECT gen_random_uuid() AS id')->id;
                DB::table('permissions')->insert([
                    'permission_id' => $permissionId,
                    'permission_name' => $permission,
                    'module' => $module,
                    'action' => $action,
                    'description' => 'A6.1 route/module entry; NOT a row-level or workflow grant',
                    'created_at' => now(),
                ]);
            }
            foreach ($roleNames as $roleName) {
                $roleId = DB::table('roles')->whereRaw('LOWER(role_name) = ?', [strtolower($roleName)])->value('role_id');
                if (! $roleId) continue;
                if (! DB::table('role_permissions')->where('role_id', $roleId)->where('permission_id', $permissionId)->exists()) {
                    DB::table('role_permissions')->insert([
                        'role_permission_id' => DB::raw('gen_random_uuid()'),
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'data_scope' => null,
                        'created_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Do not delete role assignments or existing role records on rollback.
        // Remove grants created for A6.1 permission names (existing grants also
        // referencing these names are intentionally preserved for manual review).
    }
};
