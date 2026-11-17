<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use App\Models\WorkspaceCollaborationMember;
use App\Models\WorkspaceTask;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * A7.4.8.1 isolated authorization feature tests.
 *
 * Deliberately uses SQLite :memory: and a tiny disposable schema. The CRM
 * PostgreSQL migration chain expects pre-existing base tables, so running
 * migrate:fresh against a real CRM database would be dangerous.
 *
 * NEVER create/drop tables on PostgreSQL, on an on-disk SQLite database,
 * or against any database with CRM production/development data.
 */
final class WorkspaceCollaborationHttpTest extends TestCase
{
    private User $owner;
    private User $viewer;
    private User $contributor;
    private User $outsider;
    private User $otherBranch;
    private Activity $activity;
    private WorkspaceTask $todo;

    protected function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('pdo_sqlite') ||
            config('database.default') !== 'sqlite' ||
            config('database.connections.sqlite.database') !== ':memory:') {
            $this->markTestSkipped('A7.4.8.1 tests require isolated SQLite :memory: (pdo_sqlite).');
        }

        config()->set('crm_data_scope.activities_enabled', false);
        $this->withoutVite();
        $this->makeSchema();

        $role = (string) Str::uuid();
        $branch = (string) Str::uuid();
        $otherBranch = (string) Str::uuid();
        DB::table('roles')->insert(['role_id' => $role, 'role_name' => 'QA Role', 'is_system' => false]);
        DB::table('branches')->insert([
            ['branch_id' => $branch, 'branch_name' => 'QA Branch'],
            ['branch_id' => $otherBranch, 'branch_name' => 'Other QA Branch'],
        ]);
        $this->owner = $this->makeUser('owner', $role, $branch);
        $this->viewer = $this->makeUser('viewer', $role, $branch);
        $this->contributor = $this->makeUser('contributor', $role, $branch);
        $this->outsider = $this->makeUser('outsider', $role, $branch);
        $this->otherBranch = $this->makeUser('otherbranch', $role, $otherBranch);

        $this->activity = Activity::query()->create([
            'user_id' => (string) $this->owner->getKey(),
            'subject' => 'A748 Test Activity',
            'activity_type' => 'Meeting',
            'activity_date' => now()->addDay(),
        ]);
        $this->todo = WorkspaceTask::query()->create([
            'owner_user_id' => (string) $this->owner->getKey(),
            'title' => 'A748 Test Work Task',
            'visibility' => 'work',
            'status' => 'todo',
            'priority' => 'medium',
        ]);
    }

    protected function tearDown(): void
    {
        if (extension_loaded('pdo_sqlite') &&
            config('database.default') === 'sqlite' &&
            config('database.connections.sqlite.database') === ':memory:') {
            foreach (['workspace_task_reviews', 'workspace_task_submission_files', 'workspace_task_assignments',
                'workspace_collaboration_entries', 'workspace_collaboration_members',
                'workspace_tasks', 'activities', 'user_roles', 'users', 'branches', 'roles'] as $table) {
                Schema::dropIfExists($table);
            }
        }
        parent::tearDown();
    }

    private function makeSchema(): void
    {
        Schema::create('roles', function (Blueprint $t): void {
            $t->uuid('role_id')->primary();
            $t->string('role_name');
            $t->boolean('is_system')->default(false);
        });
        Schema::create('branches', function (Blueprint $t): void {
            $t->uuid('branch_id')->primary();
            $t->string('branch_name');
        });
        Schema::create('users', function (Blueprint $t): void {
            $t->uuid('user_id')->primary();
            $t->uuid('role_id')->nullable();
            $t->uuid('branch_id')->nullable();
            $t->string('name');
            $t->string('email');
            $t->string('password');
            $t->string('status')->default('active');
            $t->timestamps();
        });
        Schema::create('user_roles', function (Blueprint $t): void {
            $t->uuid('user_role_id')->primary();
            $t->uuid('user_id');
            $t->uuid('role_id');
            $t->boolean('is_primary')->default(true);
            $t->uuid('assigned_by')->nullable();
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('activities', function (Blueprint $t): void {
            $t->uuid('activity_id')->primary();
            $t->uuid('user_id');
            $t->uuid('customer_id')->nullable();
            $t->uuid('lead_id')->nullable();
            $t->uuid('opportunity_id')->nullable();
            $t->string('activity_type');
            $t->string('subject');
            $t->text('description')->nullable();
            $t->timestamp('activity_date');
            $t->timestamp('ends_at')->nullable();
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('workspace_tasks', function (Blueprint $t): void {
            $t->uuid('task_id')->primary();
            $t->uuid('owner_user_id');
            $t->uuid('assigned_user_id')->nullable();
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('visibility');
            $t->string('status');
            $t->string('priority');
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('due_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->uuid('activity_id')->nullable();
            $t->timestamps();
        });
        Schema::create('workspace_task_assignments', function (Blueprint $t): void {
            $t->uuid('assignment_id')->primary();
            $t->uuid('task_id');
            $t->uuid('assigned_user_id');
            $t->boolean('completion_required')->default(false);
            $t->boolean('submission_required')->default(false);
            $t->string('status')->default('assigned');
            // A7.4.9 compatibility for existing isolated A7.4.8 suite.
            $t->string('review_status')->default('not_submitted');
            $t->uuid('reviewed_by_user_id')->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->text('review_note')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });
        // A7.4.9 review decision history needed by To-Do detail.
        Schema::create('workspace_task_reviews', function (Blueprint $t): void {
            $t->uuid('review_id')->primary();
            $t->uuid('task_id');
            $t->uuid('assignment_id');
            $t->uuid('actor_user_id');
            $t->string('decision');
            $t->text('note')->nullable();
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('workspace_task_submission_files', function (Blueprint $t): void {
            $t->uuid('file_id')->primary();
            $t->uuid('assignment_id');
            $t->uuid('uploaded_by_user_id');
            $t->string('original_name');
            $t->string('stored_path');
            $t->string('mime_type')->nullable();
            $t->bigInteger('file_size');
            $t->timestamps();
        });
        Schema::create('workspace_collaboration_members', function (Blueprint $t): void {
            $t->uuid('member_id')->primary();
            $t->uuid('activity_id')->nullable();
            $t->uuid('task_id')->nullable();
            $t->uuid('user_id');
            $t->string('access_role');
            $t->timestamps();
        });
        Schema::create('workspace_collaboration_entries', function (Blueprint $t): void {
            $t->uuid('entry_id')->primary();
            $t->uuid('activity_id')->nullable();
            $t->uuid('task_id')->nullable();
            $t->uuid('author_user_id');
            $t->string('entry_type');
            $t->text('body');
            $t->timestamps();
        });
    }

    private function makeUser(string $tag, string $roleId, string $branchId): User
    {
        $id = (string) Str::uuid();
        DB::table('users')->insert([
            'user_id' => $id,
            'role_id' => $roleId,
            'branch_id' => $branchId,
            'name' => 'A748 '.$tag,
            'email' => $tag.'@a748.invalid',
            'password' => bcrypt('for-testing-only'),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_roles')->insert([
            'user_role_id' => (string) Str::uuid(),
            'user_id' => $id,
            'role_id' => $roleId,
            'is_primary' => true,
        ]);
        return User::query()->findOrFail($id);
    }

    private function invite(Activity|WorkspaceTask $resource, User $user, string $role): WorkspaceCollaborationMember
    {
        return WorkspaceCollaborationMember::query()->create([
            $resource instanceof Activity ? 'activity_id' : 'task_id' => (string) $resource->getKey(),
            'user_id' => (string) $user->getKey(),
            'access_role' => $role,
        ]);
    }

    public function test_creator_can_invite_and_revoke_an_explicit_viewer(): void
    {
        $path = route('activities.collaboration.add', ['activity' => $this->activity->getKey()]);
        $this->actingAs($this->owner, 'web')->post($path, [
            'user_id' => (string) $this->viewer->getKey(), 'access_role' => 'viewer',
        ])->assertRedirect();
        $member = WorkspaceCollaborationMember::query()->where('user_id', $this->viewer->getKey())->firstOrFail();
        $this->actingAs($this->viewer, 'web')
            ->post(route('activities.collaboration.entry', ['activity' => $this->activity->getKey()]), [
                'entry_type' => 'comment', 'body' => 'Blocked update',
            ])->assertForbidden();
        $this->assertDatabaseCount('workspace_collaboration_entries', 0);
        $this->actingAs($this->owner, 'web')
            ->delete(route('activities.collaboration.remove', [
                'activity' => $this->activity->getKey(), 'member' => $member->getKey(),
            ]))->assertRedirect();
        $this->assertDatabaseCount('workspace_collaboration_members', 0);
        $this->actingAs($this->viewer, 'web')
            ->post(route('activities.collaboration.entry', ['activity' => $this->activity->getKey()]), [
                'entry_type' => 'comment', 'body' => 'Not allowed after revocation',
            ])->assertNotFound();
    }

    public function test_contributor_can_post_but_cannot_manage_or_edit_activity(): void
    {
        $this->invite($this->activity, $this->contributor, 'contributor');
        $this->actingAs($this->contributor, 'web')
            ->post(route('activities.collaboration.entry', ['activity' => $this->activity->getKey()]), [
                'entry_type' => 'progress', 'body' => 'Progress noted',
            ])->assertRedirect();
        $this->assertDatabaseHas('workspace_collaboration_entries', [
            'activity_id' => $this->activity->getKey(),
            'author_user_id' => $this->contributor->getKey(),
            'entry_type' => 'progress', 'body' => 'Progress noted',
        ]);
        $this->actingAs($this->contributor, 'web')
            ->post(route('activities.collaboration.add', ['activity' => $this->activity->getKey()]), [
                'user_id' => $this->outsider->getKey(), 'access_role' => 'viewer',
            ])->assertForbidden();
        $this->actingAs($this->contributor, 'web')
            ->delete(route('activities.destroy', ['activity' => $this->activity->getKey()]))
            ->assertNotFound();
        $this->assertDatabaseCount('workspace_collaboration_members', 1);
    }

    public function test_same_branch_without_explicit_invitation_is_not_a_grant(): void
    {
        $this->actingAs($this->outsider, 'web')
            ->post(route('activities.collaboration.entry', ['activity' => $this->activity->getKey()]), [
                'entry_type' => 'comment', 'body' => 'Uninvited',
            ])->assertNotFound();
        $this->actingAs($this->outsider, 'web')
            ->get(route('todos.show', ['todo' => $this->todo->getKey()]))->assertNotFound();
    }

    public function test_only_creator_can_grant_permissions_and_out_of_branch_invites_fail(): void
    {
        $this->actingAs($this->viewer, 'web')
            ->post(route('todos.collaboration.add', ['todo' => $this->todo->getKey()]), [
                'user_id' => $this->outsider->getKey(), 'access_role' => 'viewer',
            ])->assertNotFound();
        $this->actingAs($this->owner, 'web')
            ->post(route('todos.collaboration.add', ['todo' => $this->todo->getKey()]), [
                'user_id' => $this->otherBranch->getKey(), 'access_role' => 'viewer',
            ])->assertSessionHasErrors('user_id');
        $this->assertDatabaseCount('workspace_collaboration_members', 0);
    }

    public function test_nested_revocation_is_scoped_to_the_correct_parent(): void
    {
        $member = $this->invite($this->todo, $this->viewer, 'viewer');
        $this->actingAs($this->owner, 'web')
            ->delete(route('activities.collaboration.remove', [
                'activity' => $this->activity->getKey(), 'member' => $member->getKey(),
            ]))->assertNotFound();
        $this->assertDatabaseHas('workspace_collaboration_members', [
            'member_id' => $member->getKey(), 'task_id' => $this->todo->getKey(),
        ]);
    }

    public function test_personal_todo_cannot_accept_collaboration_even_from_creator(): void
    {
        $this->todo->update(['visibility' => 'personal']);
        $this->actingAs($this->owner, 'web')
            ->post(route('todos.collaboration.add', ['todo' => $this->todo->getKey()]), [
                'user_id' => $this->viewer->getKey(), 'access_role' => 'viewer',
            ])->assertNotFound();
        $this->actingAs($this->owner, 'web')
            ->post(route('todos.collaboration.entry', ['todo' => $this->todo->getKey()]), [
                'entry_type' => 'comment', 'body' => 'Private task only',
            ])->assertNotFound();
        $this->assertDatabaseCount('workspace_collaboration_members', 0);
    }

    public function test_switching_work_to_personal_revokes_all_collaborators(): void
    {
        $this->invite($this->todo, $this->viewer, 'viewer');
        $this->invite($this->todo, $this->contributor, 'contributor');
        $this->actingAs($this->owner, 'web')
            ->put(route('todos.update', ['todo' => $this->todo->getKey()]), [
                'title' => 'Now Personal', 'visibility' => 'personal',
                'status' => 'todo', 'priority' => 'medium',
            ])->assertRedirect();
        $this->assertDatabaseCount('workspace_collaboration_members', 0);
        $this->assertDatabaseHas('workspace_tasks', [
            'task_id' => $this->todo->getKey(), 'visibility' => 'personal',
        ]);
        $this->actingAs($this->contributor, 'web')
            ->post(route('todos.collaboration.entry', ['todo' => $this->todo->getKey()]), [
                'entry_type' => 'progress', 'body' => 'Should be blocked',
            ])->assertNotFound();
        // Switching back to Work must NOT resurrect revoked invitations.
        $this->actingAs($this->owner, 'web')
            ->put(route('todos.update', ['todo' => $this->todo->getKey()]), [
                'title' => 'Work Again', 'visibility' => 'work',
                'status' => 'todo', 'priority' => 'medium',
            ])->assertRedirect();
        $this->assertDatabaseCount('workspace_collaboration_members', 0);
        $this->actingAs($this->viewer, 'web')
            ->post(route('todos.collaboration.entry', ['todo' => $this->todo->getKey()]), [
                'entry_type' => 'comment', 'body' => 'No revived access',
            ])->assertNotFound();
    }

    public function test_viewer_and_contributor_cannot_download_task_submission_files(): void
    {
        $this->invite($this->todo, $this->viewer, 'viewer');
        $this->invite($this->todo, $this->contributor, 'contributor');
        $fileId = (string) Str::uuid();
        foreach ([$this->viewer, $this->contributor] as $actor) {
            $this->actingAs($actor, 'web')
                ->get(route('todos.submission.files.download', [
                    'todo' => $this->todo->getKey(), 'file' => $fileId,
                ]))->assertNotFound();
        }
    }

    public function test_assignee_can_post_progress_without_an_explicit_collaborator_invite(): void
    {
        DB::table('workspace_task_assignments')->insert([
            'assignment_id' => (string) Str::uuid(),
            'task_id' => $this->todo->getKey(),
            'assigned_user_id' => $this->outsider->getKey(),
            'completion_required' => true,
            'submission_required' => false,
            'status' => 'assigned',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs($this->outsider, 'web')
            ->post(route('todos.collaboration.entry', ['todo' => $this->todo->getKey()]), [
                'entry_type' => 'comment', 'body' => 'Assignee report',
            ])->assertRedirect();
        $this->assertDatabaseHas('workspace_collaboration_entries', [
            'task_id' => $this->todo->getKey(), 'body' => 'Assignee report',
        ]);
    }

    public function test_viewer_role_is_explicit_and_assignee_takes_precedence(): void
    {
        $this->invite($this->todo, $this->viewer, 'viewer');
        $this->actingAs($this->viewer, 'web');
        $acl = app(\App\Services\WorkspaceCollaborationAccess::class);
        $this->assertSame('viewer', $acl->role($this->todo));

        DB::table('workspace_task_assignments')->insert([
            'assignment_id' => (string) Str::uuid(),
            'task_id' => $this->todo->getKey(),
            'assigned_user_id' => $this->viewer->getKey(),
            'status' => 'assigned',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->assertSame('assignee', $acl->role($this->todo));
    }

    public function test_guest_cannot_post_a_collaboration_update(): void
    {
        $this->post(route('activities.collaboration.entry', ['activity' => $this->activity->getKey()]), [
            'entry_type' => 'comment', 'body' => 'Guest should not write',
        ])->assertRedirect(route('login'));
        $this->assertDatabaseCount('workspace_collaboration_entries', 0);
    }

}
