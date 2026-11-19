<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CrmDashboardViewData;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/** The ONLY inserted rows are in a dedicated SQLite :memory: database. */
final class CrmDashboardViewerIsolationTest extends TestCase
{
    private function sandbox(callable $action): void
    {
        if (app()->environment() !== 'testing') {
            $this->markTestSkipped('A7.6.3 fixture runs only in APP_ENV=testing.');
        }
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PDO SQLite is required for the isolated fixture.');
        }
        $connection = 'a763_memory';
        $before = DB::getDefaultConnection();
        $beforeConfig = config("database.connections.{$connection}");
        $beforeScope = config('crm_data_scope.activities_enabled');
        $beforeUser = Auth::guard('web')->user();
        config()->set("database.connections.{$connection}", [
            'driver'=>'sqlite', 'database'=>':memory:', 'prefix'=>'', 'foreign_key_constraints'=>false,
        ]);
        config()->set('crm_data_scope.activities_enabled', false);
        DB::purge($connection);
        DB::setDefaultConnection($connection);
        Schema::clearResolvedInstance('db.schema');
        try {
            foreach (['roles', 'user_roles', 'permissions', 'role_permissions'] as $table) {
                Schema::connection($connection)->create($table, function (Blueprint $t) use ($table) {
                    if ($table === 'roles') {
                        $t->uuid('role_id')->primary(); $t->string('role_name'); $t->boolean('is_system');
                    } elseif ($table === 'user_roles') {
                        $t->uuid('role_id'); $t->uuid('user_id');
                    } elseif ($table === 'permissions') {
                        $t->uuid('permission_id')->primary(); $t->string('permission_name');
                    } else {
                        $t->uuid('role_id'); $t->uuid('permission_id'); $t->string('data_scope')->nullable();
                    }
                });
            }
            Schema::connection($connection)->create('customers', function (Blueprint $t) {
                $t->uuid('customer_id')->primary(); $t->uuid('user_id')->nullable();
            });
            Schema::connection($connection)->create('opportunities', function (Blueprint $t) {
                $t->uuid('opportunity_id')->primary(); $t->uuid('user_id')->nullable();
            });
            Schema::connection($connection)->create('activities', function (Blueprint $t) {
                $t->uuid('activity_id')->primary(); $t->uuid('user_id');
                $t->string('title'); $t->text('description')->nullable(); $t->timestamp('created_at');
            });
            Schema::connection($connection)->create('workspace_collaboration_members', function (Blueprint $t) {
                $t->uuid('activity_id'); $t->uuid('user_id');
            });
            $roleId = (string) Str::uuid();
            $mine = (string) Str::uuid();
            $other = (string) Str::uuid();
            DB::table('roles')->insert(['role_id'=>$roleId, 'role_name'=>'viewer', 'is_system'=>false]);
            DB::table('user_roles')->insert(['user_id'=>$mine, 'role_id'=>$roleId]);
            $user = new User();
            $user->setAttribute('user_id', $mine);
            $user->setAttribute('branch_id', null);
            Auth::guard('web')->setUser($user);
            $action($roleId, $mine, $other);
        } finally {
            if ($beforeUser instanceof User) Auth::guard('web')->setUser($beforeUser);
            else Auth::guard('web')->forgetUser();
            DB::setDefaultConnection($before);
            Schema::clearResolvedInstance('db.schema');
            DB::purge($connection);
            config()->set("database.connections.{$connection}", $beforeConfig);
            config()->set('crm_data_scope.activities_enabled', $beforeScope);
        }
    }

    private function grant(string $roleId, string $name, ?string $scope = null): void
    {
        $permissionId = (string) Str::uuid();
        DB::table('permissions')->insert(['permission_id'=>$permissionId, 'permission_name'=>$name]);
        DB::table('role_permissions')->insert([
            'role_id'=>$roleId, 'permission_id'=>$permissionId, 'data_scope'=>$scope,
        ]);
    }

    public function test_dashboard_only_returns_owned_and_explicit_collaborative_text(): void
    {
        $this->sandbox(function (string $role, string $mine, string $other): void {
            $this->grant($role, 'customer_lead.access');
            $this->grant($role, 'crm_page.customers.view', 'own');
            $this->grant($role, 'crm_page.activities.view', 'own');
            $this->grant($role, 'sales_crm.access');
            $this->grant($role, 'crm_page.opportunities.view', 'own');
            DB::table('customers')->insert([
                ['customer_id'=>(string)Str::uuid(), 'user_id'=>$mine],
                ['customer_id'=>(string)Str::uuid(), 'user_id'=>$other],
            ]);
            DB::table('opportunities')->insert([
                ['opportunity_id'=>(string)Str::uuid(), 'user_id'=>$mine],
                ['opportunity_id'=>(string)Str::uuid(), 'user_id'=>$other],
            ]);
            $collaborationId = (string) Str::uuid();
            DB::table('activities')->insert([
                ['activity_id'=>(string)Str::uuid(), 'user_id'=>$mine, 'title'=>'Owned', 'description'=>'Owned only', 'created_at'=>'2026-11-19 10:00:00'],
                ['activity_id'=>$collaborationId, 'user_id'=>$other, 'title'=>'Shared', 'description'=>'Explicit collaboration', 'created_at'=>'2026-11-19 09:00:00'],
                ['activity_id'=>(string)Str::uuid(), 'user_id'=>$other, 'title'=>'Secret', 'description'=>'Not shared', 'created_at'=>'2026-11-19 11:00:00'],
            ]);
            DB::table('workspace_collaboration_members')->insert(['activity_id'=>$collaborationId, 'user_id'=>$mine]);
            $writes = [];
            DB::listen(static function ($event) use (&$writes) {
                if (!preg_match('/^\s*(select|with|pragma|show|explain)\b/i', $event->sql)) $writes[] = $event->sql;
            });
            $result = app(CrmDashboardViewData::class)->forCurrentUser();
            self::assertSame('1', $result['stats']['customers']);
            self::assertSame('—', $result['stats']['leads']);
            self::assertSame('1', $result['stats']['opportunities']);
            self::assertSame(['Owned', 'Shared'], $result['recentActivities']->pluck('title')->all());
            self::assertCount(1, $result['salesCards']);
            self::assertSame([], $writes);
            self::assertStringNotContainsString('Secret', json_encode($result));
        });
    }

    public function test_dashboard_hides_activity_text_without_view_permission(): void
    {
        $this->sandbox(function (string $role, string $mine): void {
            $this->grant($role, 'customer_lead.access');
            DB::table('activities')->insert(['activity_id'=>(string)Str::uuid(),
                'user_id'=>$mine, 'title'=>'Owned but no page grant', 'created_at'=>'2026-11-19 10:00:00']);
            $result = app(CrmDashboardViewData::class)->forCurrentUser();
            self::assertCount(0, $result['recentActivities']);
            self::assertSame([], $result['salesCards']);
            self::assertSame('—', $result['stats']['customers']);
        });
    }

    public function test_guest_cannot_run_dashboard_read_model(): void
    {
        $this->expectException(AuthenticationException::class);
        app(CrmDashboardViewData::class)->forCurrentUser();
    }
}
