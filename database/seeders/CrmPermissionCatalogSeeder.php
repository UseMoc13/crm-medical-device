<?php

namespace Database\Seeders;

use App\Services\CrmRoleAccessCatalog;
use App\Services\CrmWorkflowAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Catalogue only: no roles, user accounts, assignments or grants. */
class CrmPermissionCatalogSeeder extends Seeder
{
    public const MODULES = [
        'customer_lead.access',
        'sales_crm.access',
        'catalog.access',
        'catalog.manage',
        'inventory.access',
        'delivery.access',
        'installation.access',
        'service.access',
        'billing.access',
        'reporting.access',
        'announcements.manage',
    ];

    public static function rows(): array
    {
        $rows = [];
        foreach (self::MODULES as $key) {
            [$module, $action] = explode('.', $key, 2);
            $rows[$key] = [$module, $action, 'CRM module entry; no role grant'];
        }
        foreach (CrmRoleAccessCatalog::PAGES as $page => $config) {
            foreach (CrmRoleAccessCatalog::ACTIONS as $action) {
                $rows[CrmRoleAccessCatalog::key($page, $action)] = ['crm_page', $action, 'CRM page permission; no role grant'];
            }
        }
        foreach (CrmWorkflowAccess::ACTIONS as $slug => $config) {
            $rows[CrmWorkflowAccess::key($slug)] = ['crm_workflow', 'execute', 'CRM workflow permission; no role grant'];
        }
        ksort($rows);
        return $rows;
    }

    public function run(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new \RuntimeException('CRM catalogue requires PostgreSQL.');
        }
        DB::transaction(function (): void {
            foreach (self::rows() as $key => [$module, $action, $description]) {
                DB::table('permissions')->insertOrIgnore([
                    'permission_id' => (string) Str::uuid(),
                    'permission_name' => $key,
                    'module' => $module,
                    'action' => $action,
                    'description' => $description,
                    'created_at' => now(),
                ]);
            }
        });
    }
}
