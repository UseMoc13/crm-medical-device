<?php

namespace Tests\Feature;

use App\Services\CrmFinancialRoleEntitlementAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

/** Uses isolated SQLite :memory: only. No test can write to the CRM connection. */
final class CrmFinancialRoleEntitlementIsolatedTest extends TestCase
{
    public function test_single_role_audit_separates_report_only_from_invoice_all(): void
    {
        self::assertSame('testing', app()->environment());
        $previous = DB::getDefaultConnection();
        $priorCustomers = config('crm_data_scope.customers_enabled');
        $priorOpportunities = config('crm_data_scope.opportunities_enabled');
        config(['database.connections.a7653_memory' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            'foreign_key_constraints' => false,
        ]]);
        DB::purge('a7653_memory');
        DB::setDefaultConnection('a7653_memory');
        try {
            $conn = DB::connection('a7653_memory');
            self::assertSame(':memory:', $conn->getDatabaseName());
            $schema = Schema::connection('a7653_memory');
            $schema->create('roles', static function (Blueprint $t): void {
                $t->string('role_id')->primary(); $t->string('role_name'); $t->boolean('is_system')->default(false);
            });
            $schema->create('permissions', static function (Blueprint $t): void {
                $t->string('permission_id')->primary(); $t->string('permission_name');
            });
            $schema->create('role_permissions', static function (Blueprint $t): void {
                $t->string('role_id'); $t->string('permission_id'); $t->string('data_scope')->nullable();
            });
            $roles = [
                ['role_id'=>'r1','role_name'=>'Management','is_system'=>false],
                ['role_id'=>'r2','role_name'=>'Finance','is_system'=>false],
                ['role_id'=>'r3','role_name'=>'Super Admin','is_system'=>true],
            ];
            $conn->table('roles')->insert($roles);
            $permissions = [
                ['permission_id'=>'p1','permission_name'=>'reporting.access'],
                ['permission_id'=>'p2','permission_name'=>'crm_page.reports.view'],
                ['permission_id'=>'p3','permission_name'=>'billing.access'],
                ['permission_id'=>'p4','permission_name'=>'crm_page.invoices.view'],
            ];
            $conn->table('permissions')->insert($permissions);
            $conn->table('role_permissions')->insert([
                ['role_id'=>'r1','permission_id'=>'p1','data_scope'=>null],
                ['role_id'=>'r1','permission_id'=>'p2','data_scope'=>null],
                ['role_id'=>'r2','permission_id'=>'p1','data_scope'=>null],
                ['role_id'=>'r2','permission_id'=>'p2','data_scope'=>null],
                ['role_id'=>'r2','permission_id'=>'p3','data_scope'=>null],
                ['role_id'=>'r2','permission_id'=>'p4','data_scope'=>'all'],
            ]);
            config(['crm_data_scope.customers_enabled' => false, 'crm_data_scope.opportunities_enabled' => false]);
            $sql = [];
            DB::connection('a7653_memory')->listen(static function ($query) use (&$sql): void {
                $sql[] = ltrim(strtolower($query->sql));
            });
            $audit = app(CrmFinancialRoleEntitlementAudit::class);
            $result = $audit->run();
            self::assertSame('REVIEW_REQUIRED', $result['status']);
            self::assertSame(3, $result['summary']['roles_checked']);
            self::assertSame(3, $result['summary']['reports_capable_roles']);
            self::assertSame(1, $result['summary']['csv_without_invoice_kpi']);
            self::assertSame(1, $result['summary']['eligible_for_numeric_comparison']);
            self::assertTrue($result['read_only']);
            self::assertFalse($result['business_rows_read']);
            self::assertFalse($result['user_rows_read']);
            self::assertCount(3, $result['single_role_preview']);
            self::assertSame('reports_csv_without_invoice_kpi', $result['single_role_preview'][1]['review_code']);
            self::assertSame('eligible_for_numeric_comparison', $result['single_role_preview'][0]['review_code']);
            self::assertSame('super_admin_bypass', $result['single_role_preview'][2]['review_code']);
            self::assertNotEmpty($sql);
            foreach ($sql as $query) {
                self::assertTrue(str_starts_with($query, 'select') || str_starts_with($query, 'pragma'), 'Non-read query in policy audit: '.$query);
                self::assertStringNotContainsString('users', $query);
                self::assertStringNotContainsString('invoices', $query);
                self::assertStringNotContainsString('customers', $query);
            }
        } finally {
            DB::setDefaultConnection($previous);
            config(['crm_data_scope.customers_enabled'=>$priorCustomers,
                'crm_data_scope.opportunities_enabled'=>$priorOpportunities]);
            DB::disconnect('a7653_memory');
            DB::purge('a7653_memory');
        }
    }
}
