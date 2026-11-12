<?php

namespace App\Console\Commands;

use App\Services\CrmDataScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/** Read-only preflight: never prints users, identifiers or customer records. */
class AuditCrmDataScopes extends Command
{
    protected $signature = 'crm:rbac:audit-scopes';
    protected $description = 'Read-only audit of CRM page View permissions and Data Scope configuration';

    public function handle(): int
    {
        foreach (['roles', 'permissions', 'role_permissions', 'user_roles'] as $table) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                $this->error("Required table missing: {$table}");
                return self::FAILURE;
            }
        }
        if (! DB::getSchemaBuilder()->hasColumn('role_permissions', 'data_scope')) {
            $this->error('A4 data_scope column is missing.');
            return self::FAILURE;
        }

        $this->info('A6.9 READ-ONLY Data Scope preflight (no records or access rules changed)');
        $rows = DB::table('role_permissions as rp')
            ->join('permissions as p', 'p.permission_id', '=', 'rp.permission_id')
            ->select('p.permission_name', 'rp.data_scope')
            ->where('p.module', '=', 'crm_page')
            ->where('p.action', '=', 'view')
            ->get();

        $summary = [];
        foreach ($rows as $row) {
            $permission = (string) $row->permission_name;
            $slug = str_replace(['crm_page.', '.view'], '', $permission);
            $key = $row->data_scope === null ? 'NULL' : (string) $row->data_scope;
            $summary[$key] = ($summary[$key] ?? 0) + 1;
        }
        ksort($summary);
        $this->table(['Scope value', 'Page View role-grants'],
            array_map(fn ($k) => [$k, $summary[$k]], array_keys($summary)));
        $this->line('Audited user_id subjects: ' . implode(', ', array_keys(CrmDataScope::SUBJECTS)));
        $this->warn('NULL grants are NOT all-data grants. Do not activate scoped controllers until approved role scopes are configured.');
        $this->warn('No write operations or customer data are performed or displayed.');
        return self::SUCCESS;
    }
}
