<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkspaceCollaborationMember;
use App\Models\WorkspaceTaskAssignment;
use App\Models\WorkspaceTaskReview;
use App\Services\WorkspaceNotificationCenter;
use App\Services\WorkspaceReminderSync;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * A7.4.11.2: disposable SQLite :memory: only, never a real CRM database.
 */
final class WorkspaceNotificationPreferencesHttpTest extends TestCase
{
    private User $owner;
    private User $worker;
    private User $outsider;
    private string $taskId;
    private string $activityId;
    private string $eventId;

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
        // A7109_REV2: disposable SQLite RBAC catalogue, no production migrations.
        Schema::create('permissions', function (Blueprint $t): void {
            $t->uuid('permission_id')->primary();
            $t->string('permission_name')->unique();
            $t->string('module')->nullable();
        });
        Schema::create('role_permissions', function (Blueprint $t): void {
            $t->uuid('role_permission_id')->primary();
            $t->uuid('role_id');
            $t->uuid('permission_id');
            $t->string('data_scope')->nullable();
            $t->unique(['role_id', 'permission_id']);
        });
        Schema::create('workspace_notifications', function (Blueprint $t) {
            $t->uuid('notification_id')->primary(); $t->uuid('recipient_user_id'); $t->uuid('actor_user_id')->nullable();
            $t->string('kind'); $t->string('title'); $t->string('message'); $t->string('target_type');
            $t->uuid('target_id'); $t->string('dedupe_key'); $t->timestamp('read_at')->nullable();
            $t->timestamp('created_at')->nullable(); $t->unique(['recipient_user_id','dedupe_key']);
        });
        Schema::create('workspace_notification_preferences', function (Blueprint $t) {
            $t->uuid('user_id')->primary();
            $t->boolean('assignment_enabled')->default(true);
            $t->boolean('collaboration_enabled')->default(true);
            $t->boolean('submission_enabled')->default(true);
            $t->boolean('review_enabled')->default(true);
            $t->boolean('reminder_note_enabled')->default(true);
            $t->boolean('reminder_calendar_enabled')->default(true);
            $t->boolean('reminder_activity_enabled')->default(true);
            $t->boolean('reminder_todo_enabled')->default(true);
            $t->unsignedSmallInteger('reminder_lead_minutes')->default(1440);
            $t->timestamps();
        });
        Schema::create('workspace_tasks', function (Blueprint $t) {
            $t->uuid('task_id')->primary(); $t->uuid('owner_user_id'); $t->string('title');
            $t->string('visibility'); $t->string('status')->default('todo');
            $t->timestamp('due_at')->nullable(); $t->timestamp('starts_at')->nullable(); $t->timestamps();
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
            $t->uuid('activity_id')->primary(); $t->uuid('user_id'); $t->string('subject');
            $t->timestamp('activity_date');
        });
        Schema::create('workspace_calendar_events', function (Blueprint $t) {
            $t->uuid('calendar_event_id')->primary(); $t->uuid('owner_user_id'); $t->string('title');
            $t->timestamp('starts_at'); $t->timestamp('ends_at')->nullable();
            $t->string('visibility')->default('personal'); $t->timestamps();
        });
        Schema::create('workspace_personal_notes', function (Blueprint $t) {
            $t->uuid('note_id')->primary(); $t->uuid('owner_user_id'); $t->string('title');
            $t->text('body')->nullable(); $t->timestamp('remind_at')->nullable();
            $t->timestamp('archived_at')->nullable(); $t->timestamps();
        });
        $this->owner = $this->makeUser('owner');
        $this->worker = $this->makeUser('worker');
        $this->outsider = $this->makeUser('outsider');
        $this->taskId = (string) Str::uuid();
        $this->activityId = (string) Str::uuid();
        $this->eventId = (string) Str::uuid();
        DB::table('workspace_tasks')->insert([
            'task_id' => $this->taskId, 'owner_user_id' => $this->owner->getKey(),
            'title' => 'Internal task', 'visibility' => 'work', 'status' => 'todo',
            'created_at'=>now(), 'updated_at'=>now(),
        ]);
        DB::table('activities')->insert([
            'activity_id'=>$this->activityId,'user_id'=>$this->owner->getKey(),
            'subject'=>'Private activity','activity_date'=>now()->addHours(2),
        ]);
        DB::table('workspace_calendar_events')->insert([
            'calendar_event_id'=>$this->eventId,'owner_user_id'=>$this->owner->getKey(),
            'title'=>'Private calendar','visibility'=>'personal','starts_at'=>now()->addHours(2),
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function makeUser(string $name): User
    {
        $id = (string) Str::uuid();
        DB::table('users')->insert([
            'user_id'=>$id, 'name'=>$name, 'email'=>$name.'@notifications.invalid',
            'password'=>bcrypt('test-only'), 'status'=>'active',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        return User::query()->findOrFail($id);
    }

    protected function tearDown(): void
    {
        if (extension_loaded('pdo_sqlite') && config('database.default') === 'sqlite'
            && config('database.connections.sqlite.database') === ':memory:') {
            foreach (['workspace_notification_preferences','workspace_notifications','workspace_task_reviews','workspace_task_assignments',
                'workspace_collaboration_members','workspace_personal_notes',
                'workspace_calendar_events','activities','workspace_tasks',
                'role_permissions','permissions','user_roles','roles','users'] as $table) Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    private function notice(string $recipient, string $type = 'todo', ?string $id = null, string $key='manual'): string
    {
        $sent = app(WorkspaceNotificationCenter::class)->send(
            $recipient, 'test', $type, $id ?? $this->taskId,
            'Security test', 'Generic notification text', $key,
        );
        $this->assertTrue($sent);
        return (string) DB::table('workspace_notifications')->where('recipient_user_id',$recipient)
            ->where('dedupe_key',$key)->value('notification_id');
    }

    public function test_inbox_is_per_user_and_unread_can_be_marked(): void
    {
        $workerId=$this->notice((string)$this->worker->getKey());
        $this->actingAs($this->owner,'web')->get(route('workspace-notifications.index'))
            ->assertOk()->assertDontSee('Security test');
        $this->actingAs($this->owner,'web')->patch(route('workspace-notifications.read', ['notification'=>$workerId]))->assertNotFound();
        $this->actingAs($this->worker,'web')->get(route('workspace-notifications.index'))
            ->assertOk()->assertSee('Security test');
        $this->actingAs($this->worker,'web')->patch(route('workspace-notifications.read', ['notification'=>$workerId]))->assertRedirect();
        $this->assertDatabaseHas('workspace_notifications', ['notification_id'=>$workerId]);
        $this->assertNotNull(DB::table('workspace_notifications')->where('notification_id',$workerId)->value('read_at'));
    }

    public function test_open_only_redirects_to_currently_authorized_target(): void
    {
        $this->notice((string)$this->worker->getKey());
        $this->actingAs($this->worker,'web')->post(route('workspace-notifications.open', [
            'notification'=>DB::table('workspace_notifications')->value('notification_id')]))
            ->assertRedirect(route('workspace-notifications.index'));

        $assignment = WorkspaceTaskAssignment::query()->create([
            'task_id'=>$this->taskId,'assigned_user_id'=>$this->worker->getKey(), 'status'=>'assigned',
        ]);
        $id = $this->notice((string)$this->worker->getKey(), 'todo', null, 'link-check');
        $this->actingAs($this->worker,'web')->post(route('workspace-notifications.open',['notification'=>$id]))
            ->assertRedirect(route('todos.show', ['todo'=>$this->taskId]));
        $assignment->delete();
        $this->actingAs($this->worker,'web')->post(route('workspace-notifications.open',['notification'=>$id]))
            ->assertRedirect(route('workspace-notifications.index'));
    }

    public function test_observers_create_assignment_invitation_and_review_notifications(): void
    {
        $assignment = WorkspaceTaskAssignment::query()->create([
            'task_id'=>$this->taskId, 'assigned_user_id'=>$this->worker->getKey(), 'status'=>'assigned',
        ]);
        $this->assertDatabaseHas('workspace_notifications', [
            'recipient_user_id'=>$this->worker->getKey(), 'kind'=>'assignment',
        ]);
        WorkspaceCollaborationMember::query()->create([
            'activity_id'=>$this->activityId, 'user_id'=>$this->worker->getKey(), 'access_role'=>'viewer',
        ]);
        $this->assertDatabaseHas('workspace_notifications', [
            'recipient_user_id'=>$this->worker->getKey(), 'kind'=>'collaboration',
        ]);
        $assignment->update(['status'=>'completed','completed_at'=>now(),'review_status'=>'pending_review']);
        $this->assertDatabaseHas('workspace_notifications', [
            'recipient_user_id'=>$this->owner->getKey(), 'kind'=>'submission',
        ]);
        WorkspaceTaskReview::query()->create([
            'task_id'=>$this->taskId,'assignment_id'=>$assignment->getKey(),
            'actor_user_id'=>$this->owner->getKey(), 'decision'=>'revision_requested',
        ]);
        $this->assertDatabaseHas('workspace_notifications', [
            'recipient_user_id'=>$this->worker->getKey(), 'kind'=>'revision',
        ]);
    }

    public function test_reminders_do_not_duplicate_and_preserve_personal_scope(): void
    {
        $note = (string)Str::uuid();
        DB::table('workspace_personal_notes')->insert([
            'note_id'=>$note, 'owner_user_id'=>$this->owner->getKey(),
            'title'=>'Private note', 'remind_at'=>now()->addMinutes(15),
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        $sync=app(WorkspaceReminderSync::class);
        $first=$sync->run();
        $this->assertGreaterThanOrEqual(1, $first['note']);
        $after=DB::table('workspace_notifications')->count();
        $second=$sync->run();
        $this->assertSame(['note'=>0,'calendar'=>0,'activity'=>0,'todo'=>0],$second);
        $this->assertSame($after, DB::table('workspace_notifications')->count());
        $this->assertDatabaseHas('workspace_notifications', [
            'recipient_user_id'=>$this->owner->getKey(), 'target_type'=>'note','target_id'=>$note,
        ]);
        $this->assertDatabaseMissing('workspace_notifications', [
            'recipient_user_id'=>$this->outsider->getKey(), 'target_type'=>'note','target_id'=>$note,
        ]);
    }

    public function test_guest_cannot_read_inbox_or_mark_notifications(): void
    {
        $id = $this->notice((string)$this->owner->getKey());
        $this->getJson(route('workspace-notifications.index'))->assertUnauthorized();
        $this->patchJson(route('workspace-notifications.read',['notification'=>$id]))->assertUnauthorized();
    }

    public function test_sender_deduplicates_and_does_not_notify_inactive_users(): void
    {
        $recipient=(string)$this->worker->getKey();
        $this->notice($recipient);
        $this->assertFalse(app(WorkspaceNotificationCenter::class)->send(
            $recipient,'test','todo',$this->taskId,'Same title','Same body','manual'));
        $this->assertDatabaseCount('workspace_notifications',1);
        DB::table('users')->where('user_id',$recipient)->update(['status'=>'inactive']);
        $this->assertFalse(app(WorkspaceNotificationCenter::class)->send(
            $recipient,'test','todo',$this->taskId,'New title','New body','no-send'));
        $this->assertDatabaseCount('workspace_notifications',1);
    }

    public function test_poll_does_not_expose_other_users_notifications(): void
    {
        $this->notice((string) $this->worker->getKey(), 'todo', null, 'worker-only');
        $response = $this->actingAs($this->owner, 'web')->getJson(route('workspace-notifications.poll'))
            ->assertOk()->assertJsonPath('unread_count', 0)
            ->assertJsonCount(0, 'notifications');
        // Cache-Control directives are order independent (RFC 9111).
        // Verify that user-scoped polling is private and never stored,
        // rather than comparing the order Symfony/Laravel serializes them.
        $cacheDirectives = array_map('trim', explode(',', strtolower(
            (string) $response->headers->get('Cache-Control')
        )));
        $this->assertContains('private', $cacheDirectives);
        $this->assertContains('no-store', $cacheDirectives);
        $this->assertNotContains('public', $cacheDirectives);
        $this->actingAs($this->worker, 'web')->getJson(route('workspace-notifications.poll'))
            ->assertOk()->assertJsonPath('unread_count', 1)
            ->assertJsonCount(1, 'notifications');
        $this->getJson(route('workspace-notifications.poll'))
            ->assertOk()->assertJsonStructure(['unread_count', 'notifications']);
    }

    public function test_preferences_are_saved_per_user_and_invalid_lead_is_rejected(): void
    {
        $owner=(string) $this->owner->getKey();
        $this->actingAs($this->owner, 'web')->get(route('workspace-notifications.settings'))
            ->assertOk()->assertSee('Notification Settings');
        $this->actingAs($this->owner, 'web')->patch(route('workspace-notifications.settings.update'), [
            'assignment_enabled' => '1', 'reminder_activity_enabled' => '1',
            'reminder_lead_minutes' => 15,
        ])->assertRedirect(route('workspace-notifications.settings'));
        $this->assertDatabaseHas('workspace_notification_preferences', [
            'user_id' => $owner, 'assignment_enabled' => 1,
            'collaboration_enabled' => 0, 'reminder_lead_minutes' => 15,
        ]);
        $this->assertDatabaseCount('workspace_notification_preferences', 1);
        $this->actingAs($this->worker, 'web')->get(route('workspace-notifications.settings'))
            ->assertOk()->assertSee('1 day before');
        $this->actingAs($this->owner, 'web')->patch(route('workspace-notifications.settings.update'), [
            'reminder_lead_minutes' => 17,
        ])->assertSessionHasErrors('reminder_lead_minutes');
        $this->assertDatabaseHas('workspace_notification_preferences', [
            'user_id' => $owner, 'reminder_lead_minutes' => 15,
        ]);
    }

    public function test_disabled_category_blocks_new_notifications_but_keeps_existing(): void
    {
        $worker=(string) $this->worker->getKey();
        $id=$this->notice($worker, 'todo', null, 'existing-event');
        $this->actingAs($this->worker, 'web')->patch(route('workspace-notifications.settings.update'), [
            'reminder_lead_minutes' => 1440, // no flag => all disabled
        ])->assertRedirect();
        $this->assertFalse(app(WorkspaceNotificationCenter::class)->send(
            $worker, 'assignment', 'todo', $this->taskId,
            'New task', 'Work assigned', 'assignment-disabled'
        ));
        $this->assertDatabaseCount('workspace_notifications', 1);
        $this->assertDatabaseHas('workspace_notifications', ['notification_id' => $id]);
    }

    public function test_reminder_lead_time_respects_each_user_and_deduplicates(): void
    {
        $this->actingAs($this->owner, 'web')->patch(route('workspace-notifications.settings.update'), [
            'reminder_calendar_enabled' => 1, 'reminder_lead_minutes' => 15,
        ])->assertRedirect();
        // Existing owner calendar event is in 2 hours: not due at 15-min lead.
        $scan = app(WorkspaceReminderSync::class);
        $this->assertSame(0, $scan->run()['calendar']);
        $this->actingAs($this->owner, 'web')->patch(route('workspace-notifications.settings.update'), [
            'reminder_calendar_enabled' => 1, 'reminder_lead_minutes' => 180,
        ])->assertRedirect();
        $this->assertSame(1, $scan->run()['calendar']);
        $this->assertSame(0, $scan->run()['calendar']);
        $this->assertDatabaseHas('workspace_notifications', [
            'recipient_user_id' => (string) $this->owner->getKey(),
            'target_type' => 'calendar', 'target_id' => $this->eventId,
        ]);
    }

    public function test_explicit_personal_note_reminder_is_not_shifted_by_lead_setting(): void
    {
        $noteId=(string) Str::uuid();
        DB::table('workspace_personal_notes')->insert([
            'note_id'=>$noteId, 'owner_user_id'=>$this->owner->getKey(),
            'title'=>'Note', 'remind_at'=>now()->addMinutes(10),
            'created_at'=>now(), 'updated_at'=>now(),
        ]);
        $this->actingAs($this->owner, 'web')->patch(route('workspace-notifications.settings.update'), [
            'reminder_note_enabled' => 1, 'reminder_lead_minutes' => 0,
        ])->assertRedirect();
        $this->assertSame(1, app(WorkspaceReminderSync::class)->run()['note']);
        $this->assertSame(0, app(WorkspaceReminderSync::class)->run()['note']);
    }

    public function test_guest_cannot_poll_or_change_preferences(): void
    {
        $this->getJson(route('workspace-notifications.poll'))->assertUnauthorized();
        $this->patchJson(route('workspace-notifications.settings.update'), [
            'reminder_lead_minutes' => 60,
        ])->assertUnauthorized();
    }
}
