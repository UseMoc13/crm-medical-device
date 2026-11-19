<?php

namespace Tests\Feature;

use App\Services\CrmMultiRoleReportsPrivacyAudit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Synthetic policy metadata only: no access to project business tables. */
final class CrmMultiRoleReportsPrivacyIsolatedTest extends TestCase
{
    public function test_preview_joins_selected_roles_only_and_reports_sensitive_surfaces(): void
    {
        self::assertTrue(app()->environment('testing'));
        $previous = DB::getDefaultConnection();
        $priorPilot = config('crm_reporting.csv_invoice_all_scope_pilot_enabled');
        $priorScope = config('crm_data_scope.customers_enabled');
        config(['database.connections.a7656_memory' => [
            'driver'=>'sqlite', 'database'=>':memory:', 'prefix'=>'', 'foreign_key_constraints'=>false,
        ]]);
        DB::purge('a7656_memory');
        DB::setDefaultConnection('a7656_memory');
        try {
            self::assertSame(':memory:', DB::connection('a7656_memory')->getDatabaseName());
            $schema = Schema::connection('a7656_memory');
            $schema->create('roles', static function (Blueprint $t): void {
                $t->string('role_id')->primary(); $t->string('role_name'); $t->boolean('is_system');
            });
            $schema->create('permissions', static function (Blueprint $t): void {
                $t->string('permission_id')->primary(); $t->string('permission_name');
            });
            $schema->create('role_permissions', static function (Blueprint $t): void {
                $t->string('role_id'); $t->string('permission_id'); $t->string('data_scope')->nullable();
            });
            DB::table('roles')->insert([
                ['role_id'=>'f','role_name'=>'Finance','is_system'=>false],
                ['role_id'=>'b','role_name'=>'Billing Scoped','is_system'=>false],
                ['role_id'=>'m','role_name'=>'Management','is_system'=>false],
            ]);
            DB::table('permissions')->insert([
                ['permission_id'=>'p1','permission_name'=>'reporting.access'],
                ['permission_id'=>'p2','permission_name'=>'crm_page.reports.view'],
                ['permission_id'=>'p3','permission_name'=>'billing.access'],
                ['permission_id'=>'p4','permission_name'=>'crm_page.invoices.view'],
            ]);
            DB::table('role_permissions')->insert([
                ['role_id'=>'f','permission_id'=>'p1','data_scope'=>null],
                ['role_id'=>'f','permission_id'=>'p2','data_scope'=>null],
                ['role_id'=>'b','permission_id'=>'p3','data_scope'=>null],
                ['role_id'=>'b','permission_id'=>'p4','data_scope'=>'all'],
                ['role_id'=>'m','permission_id'=>'p1','data_scope'=>null],
                ['role_id'=>'m','permission_id'=>'p2','data_scope'=>null],
            ]);
            config(['crm_reporting.csv_invoice_all_scope_pilot_enabled'=>true,
                'crm_data_scope.customers_enabled'=>false]);
            $sql = [];
            DB::connection('a7656_memory')->listen(static function ($event) use (&$sql): void {
                $sql[] = ltrim(strtolower($event->sql));
            });
            $audit = app(CrmMultiRoleReportsPrivacyAudit::class);
            $ok = $audit->run(['Finance','Billing Scoped']);
            self::assertSame('REVIEW_REQUIRED', $ok['status']);
            self::assertSame(['Billing Scoped','Finance'], $ok['roles']);
            self::assertTrue($ok['effective_access']['reports_page']);
            self::assertTrue($ok['effective_access']['csv_export']);
            self::assertTrue($ok['effective_access']['dashboard_invoice_kpis']);
            self::assertTrue($ok['source_contracts']['matches']);
            self::assertFalse($ok['user_rows_read']);
            self::assertFalse($ok['business_rows_read']);
            self::assertFalse($ok['actual_user_memberships_checked']);
            $missing = $audit->run(['Finance','Not A Role']);
            self::assertSame('ROLE_NOT_FOUND', $missing['status']);
            $duplicate = $audit->run(['Finance','finance']);
            self::assertSame('DUPLICATE_ROLE', $duplicate['status']);
            self::assertNotEmpty($sql);
            foreach ($sql as $query) {
                self::assertTrue(str_starts_with($query, 'select') || str_starts_with($query, 'pragma'), 'Non-read query: '.$query);
                self::assertStringNotContainsString('user_roles', $query);
                self::assertStringNotContainsString('invoices', $query);
                self::assertStringNotContainsString('customers', $query);
            }
        } finally {
            DB::setDefaultConnection($previous);
            config(['crm_reporting.csv_invoice_all_scope_pilot_enabled'=>$priorPilot,
                'crm_data_scope.customers_enabled'=>$priorScope]);
            DB::disconnect('a7656_memory');
            DB::purge('a7656_memory');
        }
    }
}
