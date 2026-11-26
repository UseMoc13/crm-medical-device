<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\WorkspaceCollaborationMember;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTaskAssignment;
use App\Services\WorkspaceRoleAuditGrant;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Isolated in-memory fixture. Must never query or reset the actual CRM database. */
final class WorkspaceRoleAuditAccessHttpTest extends TestCase
{
    private User $admin;
    private User $staff;
    private Role $target;
    private string $auditPermission;
    private string $pagePermission;
    private string $taskId;

    protected function setUp(): void
    {
        parent::setUp();
        if (!extension_loaded('pdo_sqlite') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            $this->markTestSkipped('Only isolated SQLite :memory: fixtures are allowed.');
        }
        $this->withoutVite();
        Schema::create('users', function (Blueprint $t) {
            $t->uuid('user_id')->primary(); $t->uuid('role_id')->nullable();
            $t->string('name'); $t->string('email'); $t->string('password');
            $t->string('status')->default('active'); $t->timestamps();
        });
        Schema::create('roles', function (Blueprint $t) {
            $t->uuid('role_id')->primary(); $t->string('role_name'); $t->text('description')->nullable();
            $t->boolean('is_system')->default(false); $t->timestamps();
        });
        Schema::create('user_roles', function (Blueprint $t) {
            $t->uuid('user_role_id')->primary(); $t->uuid('user_id'); $t->uuid('role_id');
            $t->boolean('is_primary')->default(false); $t->uuid('assigned_by')->nullable();
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('permissions', function (Blueprint $t) {
            $t->uuid('permission_id')->primary(); $t->string('permission_name')->unique();
            $t->string('module')->nullable(); $t->string('action')->nullable();
            $t->text('description')->nullable(); $t->timestamp('created_at')->nullable();
        });
        Schema::create('role_permissions', function (Blueprint $t) {
            $t->uuid('role_permission_id')->primary(); $t->uuid('role_id'); $t->uuid('permission_id');
            $t->string('data_scope')->nullable(); $t->timestamp('created_at')->nullable();
            $t->unique(['role_id', 'permission_id']);
        });
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->uuid('audit_log_id')->primary(); $t->uuid('user_id')->nullable();
            $t->string('action'); $t->string('table_name'); $t->uuid('record_id')->nullable();
            $t->text('old_value')->nullable(); $t->text('new_value')->nullable();
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('workspace_audit_events', function (Blueprint $t) {
            $t->uuid('audit_event_id')->primary(); $t->uuid('actor_user_id')->nullable();
            $t->string('event_type'); $t->string('subject_type'); $t->uuid('subject_id')->nullable();
            $t->json('details')->nullable(); $t->timestamp('created_at')->nullable();
        });
        Schema::create('workspace_notifications', function (Blueprint $t) {
            $t->uuid('notification_id')->primary(); $t->uuid('recipient_user_id'); $t->uuid('actor_user_id')->nullable();
            $t->string('kind'); $t->string('title'); $t->string('message'); $t->string('target_type');
            $t->uuid('target_id'); $t->string('dedupe_key'); $t->timestamp('read_at')->nullable();
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('workspace_tasks', function (Blueprint $t) {
            $t->uuid('task_id')->primary(); $t->uuid('owner_user_id'); $t->string('title');
            $t->string('visibility')->default('work'); $t->string('status')->default('todo');
            $t->string('priority')->default('medium'); $t->timestamp('starts_at')->nullable();
            $t->timestamp('due_at')->nullable(); $t->timestamps();
        });
        Schema::create('workspace_task_assignments', function (Blueprint $t) {
            $t->uuid('assignment_id')->primary(); $t->uuid('task_id'); $t->uuid('assigned_user_id');
            $t->boolean('completion_required')->default(true); $t->boolean('submission_required')->default(false);
            $t->string('status')->default('assigned'); $t->string('review_status')->nullable();
            $t->timestamp('completed_at')->nullable(); $t->timestamps();
        });
        Schema::create('workspace_collaboration_members', function (Blueprint $t) {
            $t->uuid('member_id')->primary(); $t->uuid('activity_id')->nullable(); $t->uuid('task_id')->nullable();
            $t->uuid('user_id'); $t->string('access_role'); $t->timestamps();
        });

        $adminRole = (string) Str::uuid(); $staffRole = (string) Str::uuid();
        $targetId = (string) Str::uuid();
        DB::table('roles')->insert([
            ['role_id'=>$adminRole,'role_name'=>'Super Admin','is_system'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>$staffRole,'role_name'=>'Staff','is_system'=>false,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>$targetId,'role_name'=>'Audit Viewer','is_system'=>false,'created_at'=>now(),'updated_at'=>now()],
        ]);
        $this->admin=$this->user('Admin', $adminRole);
        $this->staff=$this->user('Staff', $staffRole);
        $this->target=Role::query()->findOrFail($targetId);
        $this->auditPermission=(string) Str::uuid(); $this->pagePermission=(string) Str::uuid();
        DB::table('permissions')->insert([
            ['permission_id'=>$this->auditPermission, 'permission_name'=>'workspace_audit.view', 'module'=>'workspace_audit', 'action'=>'view'],
            ['permission_id'=>$this->pagePermission, 'permission_name'=>'crm_page.customers.view', 'module'=>'crm_page', 'action'=>'view'],
        ]);
        $this->taskId=(string) Str::uuid();
        DB::table('workspace_tasks')->insert([
            'task_id'=>$this->taskId,'owner_user_id'=>$this->admin->getKey(),
            'title'=>'Do not store this private task title', 'visibility'=>'work', 'status'=>'todo',
            'priority'=>'medium','created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function user(string $name, string $roleId): User
    {
        $id=(string) Str::uuid();
        DB::table('users')->insert([
            'user_id'=>$id,'role_id'=>$roleId,'name'=>$name,'email'=>strtolower($name).'@audit.invalid',
            'password'=>bcrypt('fixture'),'status'=>'active','created_at'=>now(),'updated_at'=>now(),
        ]);
        DB::table('user_roles')->insert([
            'user_role_id'=>(string)Str::uuid(),'user_id'=>$id,'role_id'=>$roleId,
            'is_primary'=>true,'created_at'=>now(),
        ]);
        return User::query()->findOrFail($id);
    }

    private function rolePayload(bool $audit): array
    {
        $data=[
            'role_name'=>'Audit Viewer','description'=>'Safe role configuration',
            'access_mode'=>'custom','page_access'=>['customers'=>['view']],
            'workspace_audit_access_present'=>'1',
        ];
        if ($audit) $data['workspace_audit_access']='1';
        return $data;
    }

    protected function tearDown(): void
    {
        if (extension_loaded('pdo_sqlite') && config('database.default')==='sqlite'
            && config('database.connections.sqlite.database')===':memory:') {
            foreach (['workspace_collaboration_members','workspace_task_assignments','workspace_tasks',
                'workspace_notifications','workspace_audit_events','audit_logs','role_permissions',
                'permissions','user_roles','roles','users'] as $table) Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_super_admin_can_grant_and_revoke_audit_view_without_losing_page_permissions(): void
    {
        $this->actingAs($this->admin,'web');
        $this->put(route('roles.update', $this->target), $this->rolePayload(true))
            ->assertRedirect(route('roles.show', $this->target));
        $this->assertTrue(app(WorkspaceRoleAuditGrant::class)->isGranted($this->target));
        $this->assertDatabaseHas('role_permissions', [
            'role_id'=>$this->target->getKey(),'permission_id'=>$this->pagePermission,
        ]);
        $this->assertDatabaseHas('workspace_audit_events', [
            'event_type'=>'role.audit_access_changed','subject_type'=>'role',
            'subject_id'=>$this->target->getKey(), 'actor_user_id'=>$this->admin->getKey(),
        ]);
        $this->put(route('roles.update', $this->target), $this->rolePayload(false))
            ->assertRedirect(route('roles.show', $this->target));
        $this->assertFalse(app(WorkspaceRoleAuditGrant::class)->isGranted($this->target));
        $this->assertDatabaseHas('role_permissions', [
            'role_id'=>$this->target->getKey(),'permission_id'=>$this->pagePermission,
        ]);
        $this->assertSame(2,DB::table('workspace_audit_events')->where('event_type','role.audit_access_changed')->count());
        $payload=DB::table('workspace_audit_events')->where('event_type','role.audit_access_changed')
            ->orderBy('created_at')->pluck('details')->implode(' ');
        $this->assertStringNotContainsString('Safe role configuration',$payload);
    }

    public function test_other_role_editors_cannot_forge_audit_grant_and_legacy_forms_preserve_it(): void
    {
        $this->actingAs($this->admin,'web');
        $this->put(route('roles.update',$this->target), $this->rolePayload(true))->assertRedirect();
        $this->actingAs($this->staff,'web');
        $this->put(route('roles.update',$this->target),$this->rolePayload(false))->assertForbidden();
        $this->assertTrue(app(WorkspaceRoleAuditGrant::class)->isGranted($this->target));
        $this->actingAs($this->admin,'web');
        $legacy=$this->rolePayload(false);
        unset($legacy['workspace_audit_access_present']);
        $this->put(route('roles.update',$this->target),$legacy)->assertRedirect();
        $this->assertTrue(app(WorkspaceRoleAuditGrant::class)->isGranted($this->target));
    }

    public function test_role_editor_and_details_require_super_admin_and_hide_audit_controls_from_staff(): void
    {
        // Route RBAC: roles.* is Super-Admin-only, including read-only details.
        // Never weaken route authorization to satisfy an obsolete test assertion.
        $this->actingAs($this->admin,'web')->get(route('roles.edit',$this->target))
            ->assertOk()->assertSee('Audit Trail Permission')->assertSee('workspace_audit_access_present');
        $this->get(route('roles.show',$this->target))->assertOk()->assertSee('Audit Trail Access');
        $this->actingAs($this->staff,'web')->get(route('roles.edit',$this->target))
            ->assertForbidden()->assertDontSee('workspace_audit_access_present');
        $this->get(route('roles.show',$this->target))->assertForbidden();
    }

    public function test_additional_audit_coverage_records_only_allowlisted_changed_field_names(): void
    {
        $this->actingAs($this->admin,'web');
        $task=WorkspaceTask::query()->findOrFail($this->taskId);
        $task->update(['priority'=>'high', 'due_at'=>now()->addDay()]);
        $assignment=WorkspaceTaskAssignment::query()->create([
            'task_id'=>$task->getKey(),'assigned_user_id'=>$this->staff->getKey(),
            'completion_required'=>true,'submission_required'=>false,
        ]);
        $assignment->update(['submission_required'=>true]);
        $member=WorkspaceCollaborationMember::query()->create([
            'task_id'=>$task->getKey(), 'user_id'=>$this->staff->getKey(), 'access_role'=>'viewer',
        ]);
        $member->update(['access_role'=>'contributor']);
        $this->assertDatabaseHas('workspace_audit_events',['event_type'=>'todo.schedule_changed']);
        $this->assertDatabaseHas('workspace_audit_events',['event_type'=>'assignment.requirements_changed']);
        $this->assertDatabaseHas('workspace_audit_events',['event_type'=>'collaboration.role_changed']);
        $json=DB::table('workspace_audit_events')->pluck('details')->implode(' ');
        $this->assertStringNotContainsString('Do not store this private task title',$json);
        $this->assertStringNotContainsString('customer',$json);
        $this->assertStringContainsString('submission_required',$json);
    }

    public function test_rollback_removes_audit_permission_grant_and_event(): void
    {
        $this->actingAs($this->admin,'web');
        DB::beginTransaction();
        try {
            app(WorkspaceRoleAuditGrant::class)->setGrant($this->target,true,(string)$this->admin->getKey());
            $this->assertTrue(app(WorkspaceRoleAuditGrant::class)->isGranted($this->target));
        } finally { DB::rollBack(); }
        $this->assertFalse(app(WorkspaceRoleAuditGrant::class)->isGranted($this->target));
        $this->assertDatabaseMissing('workspace_audit_events',['event_type'=>'role.audit_access_changed']);
    }
}
