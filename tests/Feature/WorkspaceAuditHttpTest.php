<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkspaceCollaborationMember;
use App\Models\WorkspaceTaskAssignment;
use App\Models\WorkspaceTaskReview;
use App\Services\WorkspaceAuditTrail;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Isolated SQLite :memory:, never the real CRM database. */
final class WorkspaceAuditHttpTest extends TestCase
{
    private User $admin;
    private User $staff;
    private string $adminRole;
    private string $staffRole;
    private string $taskId;
    private string $activityId;

    protected function setUp(): void
    {
        parent::setUp();
        if (!extension_loaded('pdo_sqlite') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            $this->markTestSkipped('Requires isolated SQLite :memory: only.');
        }
        $this->withoutVite();
        Schema::create('users', function (Blueprint $t) {
            $t->uuid('user_id')->primary(); $t->uuid('role_id')->nullable();
            $t->string('name'); $t->string('email'); $t->string('password');
            $t->string('status')->default('active'); $t->timestamps();
        });
        Schema::create('roles', function (Blueprint $t) {
            $t->uuid('role_id')->primary(); $t->string('role_name'); $t->boolean('is_system')->default(false);
        });
        Schema::create('user_roles', function (Blueprint $t) {
            $t->uuid('user_role_id')->primary(); $t->uuid('user_id'); $t->uuid('role_id');
            $t->boolean('is_primary')->default(false); $t->uuid('assigned_by')->nullable(); $t->timestamp('created_at')->nullable();
        });
        Schema::create('permissions', function (Blueprint $t) {
            $t->uuid('permission_id')->primary(); $t->string('permission_name'); $t->string('module')->nullable();
        });
        Schema::create('role_permissions', function (Blueprint $t) {
            $t->uuid('role_permission_id')->primary(); $t->uuid('role_id'); $t->uuid('permission_id');
            $t->string('data_scope')->nullable();
        });
        Schema::create('workspace_audit_events', function (Blueprint $t) {
            $t->uuid('audit_event_id')->primary(); $t->uuid('actor_user_id')->nullable();
            $t->string('event_type'); $t->string('subject_type'); $t->uuid('subject_id')->nullable();
            $t->json('details')->nullable(); $t->timestamp('created_at')->useCurrent();
        });
        Schema::create('workspace_notifications', function (Blueprint $t) {
            $t->uuid('notification_id')->primary(); $t->uuid('recipient_user_id'); $t->uuid('actor_user_id')->nullable();
            $t->string('kind'); $t->string('title'); $t->string('message'); $t->string('target_type');
            $t->uuid('target_id'); $t->string('dedupe_key'); $t->timestamp('read_at')->nullable();
            $t->timestamp('created_at')->nullable(); $t->unique(['recipient_user_id','dedupe_key']);
        });
        Schema::create('workspace_tasks', function (Blueprint $t) {
            $t->uuid('task_id')->primary(); $t->uuid('owner_user_id'); $t->string('title');
            $t->string('visibility')->default('work'); $t->string('status')->default('todo'); $t->timestamps();
        });
        Schema::create('workspace_task_assignments', function (Blueprint $t) {
            $t->uuid('assignment_id')->primary(); $t->uuid('task_id'); $t->uuid('assigned_user_id');
            $t->boolean('completion_required')->default(true); $t->boolean('submission_required')->default(false);
            $t->string('status')->default('assigned'); $t->timestamp('completed_at')->nullable();
            $t->string('review_status')->default('not_submitted'); $t->uuid('reviewed_by_user_id')->nullable();
            $t->timestamp('reviewed_at')->nullable(); $t->text('review_note')->nullable(); $t->timestamps();
        });
        Schema::create('workspace_task_reviews', function (Blueprint $t) {
            $t->uuid('review_id')->primary(); $t->uuid('task_id'); $t->uuid('assignment_id');
            $t->uuid('actor_user_id'); $t->string('decision'); $t->text('note')->nullable();
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('workspace_collaboration_members', function (Blueprint $t) {
            $t->uuid('member_id')->primary(); $t->uuid('activity_id')->nullable(); $t->uuid('task_id')->nullable();
            $t->uuid('user_id'); $t->string('access_role'); $t->timestamps();
        });
        Schema::create('activities', function (Blueprint $t) {
            $t->uuid('activity_id')->primary(); $t->uuid('user_id'); $t->string('subject'); $t->timestamp('activity_date');
        });
        $this->adminRole = (string) Str::uuid();
        $this->staffRole = (string) Str::uuid();
        DB::table('roles')->insert([
            ['role_id'=>$this->adminRole,'role_name'=>'Super Admin','is_system'=>true],
            ['role_id'=>$this->staffRole,'role_name'=>'Staff','is_system'=>false],
        ]);
        $this->admin = $this->makeUser('admin', $this->adminRole);
        $this->staff = $this->makeUser('staff', $this->staffRole);
        $this->taskId = (string) Str::uuid();
        $this->activityId = (string) Str::uuid();
        DB::table('workspace_tasks')->insert([
            'task_id'=>$this->taskId,'owner_user_id'=>$this->admin->getKey(),
            'title'=>'Sensitive customer data must not be logged', 'visibility'=>'work', 'status'=>'todo',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        DB::table('activities')->insert([
            'activity_id'=>$this->activityId, 'user_id'=>$this->admin->getKey(),
            'subject'=>'Private health clinic', 'activity_date'=>now(),
        ]);
    }

    private function makeUser(string $name, string $roleId): User
    {
        $id = (string) Str::uuid();
        DB::table('users')->insert([
            'user_id'=>$id,'role_id'=>$roleId,'name'=>strtoupper($name),'email'=>$name.'@audit.invalid',
            'password'=>bcrypt('fixture'), 'status'=>'active', 'created_at'=>now(), 'updated_at'=>now(),
        ]);
        DB::table('user_roles')->insert([
            'user_role_id'=>(string) Str::uuid(),'user_id'=>$id,'role_id'=>$roleId,
            'is_primary'=>true,'created_at'=>now(),
        ]);
        return User::query()->findOrFail($id);
    }

    protected function tearDown(): void
    {
        if (extension_loaded('pdo_sqlite') && config('database.default') === 'sqlite'
            && config('database.connections.sqlite.database') === ':memory:') {
            foreach (['workspace_collaboration_members','workspace_task_reviews','workspace_task_assignments',
                'workspace_notifications','workspace_audit_events','activities','workspace_tasks',
                'role_permissions','permissions','user_roles','roles','users'] as $table) {
                Schema::dropIfExists($table);
            }
        }
        parent::tearDown();
    }

    public function test_guest_and_ordinary_staff_cannot_view_audit(): void
    {
        $this->getJson(route('workspace-audit.index'))->assertUnauthorized();
        $this->actingAs($this->staff,'web')->get(route('workspace-audit.index'))->assertForbidden();
        $this->actingAs($this->staff,'web')->getJson(route('workspace-audit.index'))->assertForbidden();
    }

    public function test_super_admin_reads_only_sanitized_audit_and_filters(): void
    {
        $this->actingAs($this->admin,'web');
        app(WorkspaceAuditTrail::class)->record('review.decision','todo',$this->taskId, [
            'decision'=>'approved', 'note'=>'DO NOT STORE THIS SECRET',
            'password'=>'SECRET PASSWORD', 'task_id'=>$this->taskId,
        ]);
        $this->get(route('workspace-audit.index'))->assertOk()
            ->assertSee('review decision')->assertDontSee('DO NOT STORE THIS SECRET')
            ->assertDontSee('SECRET PASSWORD');
        $this->get(route('workspace-audit.index',['event_type'=>'review.decision']))->assertOk();
        $this->getJson(route('workspace-audit.index',['event_type'=>'missing.event']))->assertUnprocessable();
        $this->postJson(route('workspace-audit.index'))->assertStatus(405);
        $this->assertDatabaseCount('workspace_audit_events',1);
    }

    public function test_explicit_permission_grant_can_authorize_staff_without_super_admin(): void
    {
        $permissionId = (string) Str::uuid();
        DB::table('permissions')->insert([
            'permission_id'=>$permissionId,'permission_name'=>'workspace_audit.view','module'=>'workspace_audit',
        ]);
        $this->actingAs($this->staff,'web')->get(route('workspace-audit.index'))->assertForbidden();
        DB::table('role_permissions')->insert([
            'role_permission_id'=>(string)Str::uuid(),'role_id'=>$this->staffRole,
            'permission_id'=>$permissionId,
        ]);
        $this->get(route('workspace-audit.index'))->assertOk();
        DB::table('role_permissions')->where('permission_id',$permissionId)->delete();
        $this->get(route('workspace-audit.index'))->assertForbidden();
    }

    public function test_assignment_collaboration_and_review_are_logged_without_free_text(): void
    {
        $this->actingAs($this->admin,'web');
        $assignment = WorkspaceTaskAssignment::query()->create([
            'task_id'=>$this->taskId,'assigned_user_id'=>$this->staff->getKey(), 'status'=>'assigned',
        ]);
        $assignment->update(['status'=>'completed','review_status'=>'pending_review','completed_at'=>now()]);
        $member = WorkspaceCollaborationMember::query()->create([
            'activity_id'=>$this->activityId, 'user_id'=>$this->staff->getKey(), 'access_role'=>'viewer',
        ]);
        $member->delete();
        WorkspaceTaskReview::query()->create([
            'task_id'=>$this->taskId,'assignment_id'=>$assignment->getKey(),
            'actor_user_id'=>$this->admin->getKey(), 'decision'=>'revision_requested',
            'note'=>'Private diagnosis and review contents',
        ]);
        $this->assertDatabaseHas('workspace_audit_events',['event_type'=>'assignment.created']);
        $this->assertDatabaseHas('workspace_audit_events',['event_type'=>'assignment.status_changed']);
        $this->assertDatabaseHas('workspace_audit_events',['event_type'=>'collaboration.granted']);
        $this->assertDatabaseHas('workspace_audit_events',['event_type'=>'collaboration.revoked']);
        $this->assertDatabaseHas('workspace_audit_events',['event_type'=>'review.decision']);
        $all = DB::table('workspace_audit_events')->pluck('details')->implode('\n');
        $this->assertStringNotContainsString('Private diagnosis', $all);
        $this->assertStringNotContainsString('Sensitive customer', $all);
        $this->assertStringNotContainsString('Private health clinic', $all);
    }

    public function test_rolled_back_task_assignment_does_not_leave_audit_record(): void
    {
        $this->actingAs($this->admin,'web');
        DB::beginTransaction();
        try {
            WorkspaceTaskAssignment::query()->create([
                'task_id'=>$this->taskId,'assigned_user_id'=>$this->staff->getKey(), 'status'=>'assigned',
            ]);
            $this->assertDatabaseHas('workspace_audit_events',['event_type'=>'assignment.created']);
        } finally {
            DB::rollBack();
        }
        $this->assertDatabaseCount('workspace_audit_events', 0);
    }
}
