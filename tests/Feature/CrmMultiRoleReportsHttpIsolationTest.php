<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Real CrmPermissionResolver on isolated SQLite grants; invalid dates stop before business SQL. */
final class CrmMultiRoleReportsHttpIsolationTest extends TestCase
{
    public function test_two_role_union_denies_csv_without_all_and_allows_validation_after_explicit_all(): void
    {
        $this->withPolicyDatabase(function (): void {
            $this->seedPolicy();
            DB::table('user_roles')->insert([
                ['user_id'=>'u1','role_id'=>'finance'],
                ['user_id'=>'u1','role_id'=>'billing'],
            ]);
            config()->set('crm_reporting.csv_invoice_all_scope_pilot_enabled', true);
            $this->actingAs($this->user('u1'), 'web');
            $this->get(route('reports.index', ['date_from'=>'invalid-date']))->assertSessionHasErrors('date_from');
            $this->get(route('reports.export.invoices'))->assertForbidden();

            DB::table('role_permissions')->where('role_id','billing')
                ->where('permission_id','invoice_view')->update(['data_scope'=>'all']);
            $this->get(route('reports.export.invoices', ['date_from'=>'invalid-date']))
                ->assertSessionHasErrors('date_from');
        });
    }

    public function test_non_system_super_admin_name_does_not_bypass_but_system_role_does(): void
    {
        $this->withPolicyDatabase(function (): void {
            $this->seedPolicy();
            DB::table('user_roles')->insert(['user_id'=>'u2','role_id'=>'impostor']);
            config()->set('crm_reporting.csv_invoice_all_scope_pilot_enabled', true);
            $this->actingAs($this->user('u2'), 'web');
            $this->get(route('reports.export.invoices'))->assertForbidden();
            DB::table('user_roles')->insert(['user_id'=>'u3','role_id'=>'admin']);
            $this->actingAs($this->user('u3'), 'web');
            $this->get(route('reports.export.invoices', ['date_from'=>'invalid-date']))
                ->assertSessionHasErrors('date_from');
        });
    }

    private function user(string $id): User
    {
        $user = new User();
        $user->setAttribute('user_id', $id);
        $user->setAttribute('status', 'active');
        return $user;
    }

    private function seedPolicy(): void
    {
        DB::table('roles')->insert([
            ['role_id'=>'finance','role_name'=>'Finance','is_system'=>false],
            ['role_id'=>'billing','role_name'=>'Billing Scoped','is_system'=>false],
            ['role_id'=>'impostor','role_name'=>'Super Admin','is_system'=>false],
            ['role_id'=>'admin','role_name'=>'Super Admin','is_system'=>true],
        ]);
        DB::table('permissions')->insert([
            ['permission_id'=>'report_base','permission_name'=>'reporting.access'],
            ['permission_id'=>'report_view','permission_name'=>'crm_page.reports.view'],
            ['permission_id'=>'invoice_base','permission_name'=>'billing.access'],
            ['permission_id'=>'invoice_view','permission_name'=>'crm_page.invoices.view'],
        ]);
        DB::table('role_permissions')->insert([
            ['role_id'=>'finance','permission_id'=>'report_base','data_scope'=>null],
            ['role_id'=>'finance','permission_id'=>'report_view','data_scope'=>null],
            ['role_id'=>'billing','permission_id'=>'invoice_base','data_scope'=>null],
            ['role_id'=>'billing','permission_id'=>'invoice_view','data_scope'=>null],
        ]);
    }

    private function withPolicyDatabase(\Closure $test): void
    {
        self::assertTrue(app()->environment('testing'));
        $prior = DB::getDefaultConnection();
        $priorFlag = config('crm_reporting.csv_invoice_all_scope_pilot_enabled');
        config(['database.connections.a7656_http' => [
            'driver'=>'sqlite', 'database'=>':memory:', 'prefix'=>'', 'foreign_key_constraints'=>false,
        ]]);
        DB::purge('a7656_http');
        DB::setDefaultConnection('a7656_http');
        try {
            self::assertSame(':memory:', DB::connection('a7656_http')->getDatabaseName());
            $s = Schema::connection('a7656_http');
            $s->create('roles', static function (Blueprint $t): void {
                $t->string('role_id')->primary(); $t->string('role_name'); $t->boolean('is_system');
            });
            $s->create('permissions', static function (Blueprint $t): void {
                $t->string('permission_id')->primary(); $t->string('permission_name');
            });
            $s->create('user_roles', static function (Blueprint $t): void {
                $t->string('user_id'); $t->string('role_id');
            });
            $s->create('role_permissions', static function (Blueprint $t): void {
                $t->string('role_id'); $t->string('permission_id'); $t->string('data_scope')->nullable();
            });
            $test();
        } finally {
            DB::setDefaultConnection($prior);
            config()->set('crm_reporting.csv_invoice_all_scope_pilot_enabled', $priorFlag);
            DB::disconnect('a7656_http');
            DB::purge('a7656_http');
        }
    }
}
