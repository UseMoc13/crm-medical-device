<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CrmScopedKpiQuery;
use Carbon\CarbonImmutable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Dedicated SQLite :memory: database. Never migrates, seeds or writes to CRM. */
final class CrmScopedKpiIsolatedTest extends TestCase
{
    private function withIsolatedDb(callable $test): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PDO SQLite required for isolated KPI fixture.');
        }
        $oldDefault = DB::getDefaultConnection();
        $oldConfig = config('database.connections.a762_isolated');
        config()->set('database.connections.a762_isolated', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('a762_isolated');
        DB::setDefaultConnection('a762_isolated');
        Schema::clearResolvedInstance('db.schema');
        try {
            Schema::connection('a762_isolated')->create('roles', function (Blueprint $t) {
                $t->uuid('role_id')->primary(); $t->string('role_name'); $t->boolean('is_system');
            });
            Schema::connection('a762_isolated')->create('user_roles', function (Blueprint $t) {
                $t->uuid('user_id'); $t->uuid('role_id');
            });
            Schema::connection('a762_isolated')->create('permissions', function (Blueprint $t) {
                $t->uuid('permission_id')->primary(); $t->string('permission_name');
            });
            Schema::connection('a762_isolated')->create('role_permissions', function (Blueprint $t) {
                $t->uuid('role_id'); $t->uuid('permission_id'); $t->string('data_scope')->nullable();
            });
            Schema::connection('a762_isolated')->create('customers', function (Blueprint $t) {
                $t->uuid('customer_id')->primary(); $t->uuid('user_id')->nullable();
            });
            Schema::connection('a762_isolated')->create('invoices', function (Blueprint $t) {
                $t->uuid('invoice_id')->primary(); $t->string('status');
                $t->date('invoice_date'); $t->date('due_date')->nullable();
                $t->decimal('total_amount', 18, 2);
            });
            $role = (string) Str::uuid();
            $id = (string) Str::uuid();
            $other = (string) Str::uuid();
            DB::table('roles')->insert(['role_id'=>$role,'role_name'=>'staff','is_system'=>false]);
            DB::table('user_roles')->insert(['role_id'=>$role,'user_id'=>$id]);
            $user = new User();
            $user->setAttribute('user_id', $id);
            $user->setAttribute('branch_id', null);
            Auth::guard('web')->setUser($user);
            $test($role, $id, $other);
        } finally {
            DB::setDefaultConnection($oldDefault);
            Schema::clearResolvedInstance('db.schema');
            DB::purge('a762_isolated');
            config()->set('database.connections.a762_isolated', $oldConfig);
        }
    }

    private function grant(string $role, string $name, ?string $scope = null): void
    {
        $id = (string) Str::uuid();
        DB::table('permissions')->insert(['permission_id'=>$id, 'permission_name'=>$name]);
        DB::table('role_permissions')->insert(['role_id'=>$role,'permission_id'=>$id,'data_scope'=>$scope]);
    }

    public function test_staff_only_sees_own_customer_count_and_denies_unsafe_invoice_scope(): void
    {
        $this->withIsolatedDb(function (string $role, string $user, string $other): void {
            $this->grant($role, 'customer_lead.access');
            $this->grant($role, 'crm_page.customers.view', 'own');
            $this->grant($role, 'billing.access');
            $this->grant($role, 'crm_page.invoices.view', 'own');
            DB::table('customers')->insert([
                ['customer_id'=>(string)Str::uuid(),'user_id'=>$user],
                ['customer_id'=>(string)Str::uuid(),'user_id'=>$other],
            ]);
            $unsafe = [];
            DB::listen(static function ($event) use (&$unsafe): void {
                if (!preg_match('/^\s*(select|with|pragma|show|explain)\b/i', $event->sql)) $unsafe[] = $event->sql;
            });
            $result = app(CrmScopedKpiQuery::class)->run(
                CarbonImmutable::parse('2026-01-01'), CarbonImmutable::parse('2026-11-19'),
                CarbonImmutable::parse('2026-11-19')
            );
            self::assertSame(1, $result['metrics']['customers.total']['value']);
            self::assertSame('restricted', $result['metrics']['invoices.invoiced']['status']);
            self::assertArrayNotHasKey('value', $result['metrics']['invoices.invoiced']);
            self::assertSame('restricted', $result['metrics']['leads.active']['status']);
            self::assertSame([], $unsafe, 'KPI calculations must issue SELECT only.');
        });
    }

    public function test_issued_invoice_aggregates_exclude_legacy_unpaid_and_draft(): void
    {
        $this->withIsolatedDb(function (string $role): void {
            $this->grant($role, 'billing.access');
            $this->grant($role, 'crm_page.invoices.view', 'all');
            foreach ([
                ['Sent', '2026-11-01', '2026-11-10', 100],
                ['Paid', '2026-11-02', '2026-11-10', 200],
                ['unpaid', '2026-11-03', '2026-11-10', 500],
                ['Draft', '2026-11-04', '2026-11-10', 300],
                ['Sent', '2025-11-01', '2025-11-10', 999],
                ['Sent', '2026-11-05', '2026-12-10', 25],
            ] as [$status,$date,$due,$amount]) {
                DB::table('invoices')->insert([
                    'invoice_id'=>(string) Str::uuid(), 'status'=>$status,
                    'invoice_date'=>$date, 'due_date'=>$due, 'total_amount'=>$amount,
                ]);
            }
            $result = app(CrmScopedKpiQuery::class)->run(
                CarbonImmutable::parse('2026-01-01'), CarbonImmutable::parse('2026-11-19'),
                CarbonImmutable::parse('2026-11-19')
            );
            self::assertSame('325', (string) (float) $result['metrics']['invoices.invoiced']['value']);
            self::assertSame('200', (string) (float) $result['metrics']['invoices.paid']['value']);
            self::assertSame('125', (string) (float) $result['metrics']['invoices.outstanding']['value']);
            self::assertSame('100', (string) (float) $result['metrics']['invoices.overdue']['value']);
        });
    }

    public function test_guest_cannot_call_scoped_kpi_service(): void
    {
        $this->expectException(AuthenticationException::class);
        app(CrmScopedKpiQuery::class)->run();
    }
}
