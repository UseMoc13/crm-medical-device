<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTaskAssignment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * A7.4.9 reviewer/assignee HTTP regressions.
 * Disposable SQLite :memory: ONLY. Never migrate:fresh/refresh PostgreSQL.
 */
final class WorkspaceTaskReviewHttpTest extends TestCase
{
    private User $owner;
    private User $assignee;
    private User $viewer;
    private WorkspaceTask $todo;
    private WorkspaceTaskAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        if (!extension_loaded('pdo_sqlite') ||
            config('database.default') !== 'sqlite' ||
            config('database.connections.sqlite.database') !== ':memory:') {
            $this->markTestSkipped('A7.4.9 HTTP tests require SQLite :memory: (not CRM PostgreSQL).');
        }
        $this->withoutVite();
        $this->makeSchema();
        $this->owner = $this->makeUser('creator');
        $this->assignee = $this->makeUser('assignee');
        $this->viewer = $this->makeUser('viewer');
        $this->todo = WorkspaceTask::query()->create([
            'owner_user_id' => $this->owner->getKey(), 'title' => 'Review Workflow QA',
            'visibility' => 'work', 'status' => 'todo', 'priority' => 'medium',
        ]);
        $this->assignment = WorkspaceTaskAssignment::query()->create([
            'task_id' => $this->todo->getKey(),
            'assigned_user_id' => $this->assignee->getKey(),
            'completion_required' => true,
            'submission_required' => false,
            'status' => 'assigned',
        ]);
    }

    protected function tearDown(): void
    {
        if (extension_loaded('pdo_sqlite') && config('database.default') === 'sqlite' &&
            config('database.connections.sqlite.database') === ':memory:') {
            foreach (['workspace_task_reviews','workspace_task_submission_files',
                'workspace_task_assignments','workspace_tasks','users'] as $table) {
                Schema::dropIfExists($table);
            }
        }
        parent::tearDown();
    }

    private function makeSchema(): void
    {
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
            $t->boolean('completion_required')->default(true);
            $t->boolean('submission_required')->default(false);
            $t->string('status')->default('assigned');
            $t->timestamp('completed_at')->nullable();
            $t->string('review_status')->default('not_submitted');
            $t->uuid('reviewed_by_user_id')->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->text('review_note')->nullable();
            $t->timestamps();
        });
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
    }

    private function makeUser(string $label): User
    {
        $id = (string) Str::uuid();
        DB::table('users')->insert([
            'user_id' => $id, 'name' => 'A749 '.$label,
            'email' => $label.'@a749.invalid',
            'password' => bcrypt('testing-only'), 'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return User::query()->findOrFail($id);
    }

    private function review(string $decision, string $note = ''): string
    {
        return route('todos.assignments.review', [
            'todo'=>$this->todo->getKey(), 'assignment'=>$this->assignment->getKey(),
        ]);
    }

    private function submit(): void
    {
        $this->actingAs($this->assignee, 'web')
            ->patch(route('todos.submission.complete', ['todo'=>$this->todo->getKey()]))
            ->assertRedirect();
    }

    public function test_assignee_completion_moves_to_pending_review(): void
    {
        $this->submit();
        $this->assertDatabaseHas('workspace_task_assignments', [
            'assignment_id'=>$this->assignment->getKey(),
            'status'=>'completed', 'review_status'=>'pending_review',
        ]);
        $this->assertDatabaseCount('workspace_task_reviews', 0);
    }

    public function test_creator_approves_then_mandatory_task_can_close(): void
    {
        $this->submit();
        $this->actingAs($this->owner,'web')->patch(route('todos.complete', ['todo'=>$this->todo->getKey()]))
            ->assertSessionHasErrors('status');
        $this->actingAs($this->owner,'web')->post($this->review('approve'), [
            'decision'=>'approve', 'note'=>'Reviewed and accepted',
        ])->assertRedirect();
        $this->assertDatabaseHas('workspace_task_assignments', [
            'assignment_id'=>$this->assignment->getKey(), 'review_status'=>'approved',
        ]);
        $this->assertDatabaseHas('workspace_task_reviews', [
            'assignment_id'=>$this->assignment->getKey(), 'actor_user_id'=>$this->owner->getKey(),
            'decision'=>'approved', 'note'=>'Reviewed and accepted',
        ]);
        $this->actingAs($this->owner,'web')->patch(route('todos.complete', ['todo'=>$this->todo->getKey()]))
            ->assertRedirect();
        $this->assertDatabaseHas('workspace_tasks', ['task_id'=>$this->todo->getKey(), 'status'=>'completed']);
    }

    public function test_revision_keeps_evidence_and_allows_resubmission(): void
    {
        $fileId = (string) Str::uuid();
        DB::table('workspace_task_submission_files')->insert([
            'file_id'=>$fileId, 'assignment_id'=>$this->assignment->getKey(),
            'uploaded_by_user_id'=>$this->assignee->getKey(),
            'original_name'=>'report.pdf', 'stored_path'=>'crm-task-submissions/demo/report.pdf',
            'file_size'=>1024, 'created_at'=>now(), 'updated_at'=>now(),
        ]);
        $this->submit();
        $this->actingAs($this->owner,'web')->post($this->review('request_revision'), [
            'decision'=>'request_revision', 'note'=>'Please revise the device test results.',
        ])->assertRedirect();
        $this->assertDatabaseHas('workspace_task_assignments', [
            'assignment_id'=>$this->assignment->getKey(), 'status'=>'assigned',
            'review_status'=>'revision_requested', 'completed_at'=>null,
        ]);
        $this->assertDatabaseHas('workspace_task_submission_files', ['file_id'=>$fileId]);
        $this->submit();
        $this->assertDatabaseHas('workspace_task_assignments', [
            'assignment_id'=>$this->assignment->getKey(), 'status'=>'completed',
            'review_status'=>'pending_review', 'review_note'=>null,
        ]);
        $this->actingAs($this->owner,'web')->post($this->review('approve'), [
            'decision'=>'approve',
        ])->assertRedirect();
        $this->assertDatabaseCount('workspace_task_reviews', 2);
    }

    public function test_revision_requires_meaningful_note(): void
    {
        $this->submit();
        $this->actingAs($this->owner,'web')->post($this->review('request_revision'), [
            'decision'=>'request_revision', 'note'=>'Fix',
        ])->assertSessionHasErrors('note');
        $this->assertDatabaseHas('workspace_task_assignments', [
            'assignment_id'=>$this->assignment->getKey(), 'review_status'=>'pending_review',
        ]);
    }

    public function test_viewer_and_assignee_cannot_review(): void
    {
        $this->submit();
        foreach ([$this->viewer, $this->assignee] as $actor) {
            $this->actingAs($actor,'web')->post($this->review('approve'), [
                'decision'=>'approve',
            ])->assertForbidden();
        }
        $this->assertDatabaseCount('workspace_task_reviews', 0);
    }

    public function test_creator_cannot_review_unsubmitted_or_duplicate_decision(): void
    {
        $this->actingAs($this->owner,'web')->post($this->review('approve'), [
            'decision'=>'approve',
        ])->assertStatus(422);
        $this->submit();
        $this->actingAs($this->owner,'web')->post($this->review('approve'), [
            'decision'=>'approve',
        ])->assertRedirect();
        $this->actingAs($this->owner,'web')->post($this->review('approve'), [
            'decision'=>'approve',
        ])->assertStatus(422);
        $this->assertDatabaseCount('workspace_task_reviews', 1);
    }

    public function test_cross_task_assignment_cannot_be_reviewed(): void
    {
        $another = WorkspaceTask::query()->create([
            'owner_user_id'=>$this->owner->getKey(), 'title'=>'Other task',
            'visibility'=>'work', 'status'=>'todo', 'priority'=>'low',
        ]);
        $this->actingAs($this->owner,'web')->post(route('todos.assignments.review', [
            'todo'=>$another->getKey(), 'assignment'=>$this->assignment->getKey(),
        ]), ['decision'=>'approve'])->assertNotFound();
    }

    public function test_legacy_completed_assignment_does_not_break_existing_closure(): void
    {
        $this->assignment->update(['status'=>'completed','review_status'=>'legacy_accepted']);
        $this->actingAs($this->owner,'web')->patch(route('todos.complete', ['todo'=>$this->todo->getKey()]))
            ->assertRedirect();
        $this->assertDatabaseHas('workspace_tasks', ['task_id'=>$this->todo->getKey(), 'status'=>'completed']);
    }

    public function test_no_review_after_task_closes_or_becomes_personal(): void
    {
        $this->submit();
        $this->todo->update(['status'=>'cancelled']);
        $this->actingAs($this->owner,'web')->post($this->review('approve'), [
            'decision'=>'approve',
        ])->assertStatus(422);
        $this->todo->update(['status'=>'todo','visibility'=>'personal']);
        $this->actingAs($this->owner,'web')->post($this->review('approve'), [
            'decision'=>'approve',
        ])->assertNotFound();
        $this->assertDatabaseCount('workspace_task_reviews', 0);
    }
}
