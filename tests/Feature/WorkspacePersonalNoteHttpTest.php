<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkspacePersonalNote;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Disposable in-memory SQLite tests, never run migrations against the CRM database. */
final class WorkspacePersonalNoteHttpTest extends TestCase
{
    private User $owner;
    private User $other;
    private string $activityId;
    private string $calendarId;

    protected function setUp(): void
    {
        parent::setUp();
        if (!extension_loaded('pdo_sqlite') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            $this->markTestSkipped('Personal Note HTTP tests require disposable SQLite :memory: only.');
        }
        $this->withoutVite();
        $this->makeSchema();
        $this->owner = $this->makeUser('owner');
        $this->other = $this->makeUser('other');
        $this->activityId = (string) Str::uuid();
        $this->calendarId = (string) Str::uuid();
        DB::table('activities')->insert([
            'activity_id' => $this->activityId, 'user_id' => $this->owner->getKey(),
            'subject' => 'Owner Meeting', 'activity_date' => now(),
        ]);
        DB::table('workspace_calendar_events')->insert([
            'calendar_event_id' => $this->calendarId, 'owner_user_id' => $this->owner->getKey(),
            'title' => 'Private Appointment', 'starts_at' => now(), 'visibility' => 'personal',
            'is_all_day' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        if (config('database.default') === 'sqlite' &&
            config('database.connections.sqlite.database') === ':memory:' && extension_loaded('pdo_sqlite')) {
            foreach (['workspace_personal_notes','workspace_calendar_day_exclusions','workspace_task_assignments','workspace_tasks','activities','workspace_calendar_events','user_roles','roles','users'] as $table) {
                Schema::dropIfExists($table);
            }
        }
        parent::tearDown();
    }

    private function makeSchema(): void
    {
        Schema::create('users', function (Blueprint $t): void {
            $t->uuid('user_id')->primary(); $t->uuid('role_id')->nullable();
            $t->string('name'); $t->string('email'); $t->string('password');
            $t->string('status')->default('active'); $t->timestamps();
        });
        Schema::create('roles', function (Blueprint $t): void {
            $t->uuid('role_id')->primary(); $t->string('role_name'); $t->boolean('is_system')->default(false);
        });
        Schema::create('user_roles', function (Blueprint $t): void {
            $t->uuid('user_role_id')->primary(); $t->uuid('user_id'); $t->uuid('role_id');
            $t->boolean('is_primary')->default(false); $t->uuid('assigned_by')->nullable(); $t->timestamp('created_at')->nullable();
        });
        Schema::create('activities', function (Blueprint $t): void {
            $t->uuid('activity_id')->primary(); $t->uuid('user_id');
            $t->string('subject'); $t->timestamp('activity_date');
        });
        Schema::create('workspace_calendar_events', function (Blueprint $t): void {
            $t->uuid('calendar_event_id')->primary(); $t->uuid('owner_user_id');
            $t->string('title'); $t->string('visibility'); $t->timestamp('starts_at');
            $t->timestamp('ends_at')->nullable(); $t->boolean('is_all_day');
            $t->string('location')->nullable(); $t->timestamps();
        });
        Schema::create('workspace_calendar_day_exclusions', function (Blueprint $t): void {
            $t->uuid('exclusion_id')->primary(); $t->uuid('calendar_event_id');
            $t->date('excluded_date');
        });
        Schema::create('workspace_tasks', function (Blueprint $t): void {
            $t->uuid('task_id')->primary(); $t->uuid('owner_user_id');
            $t->string('visibility')->default('personal');
            $t->timestamp('starts_at')->nullable(); $t->timestamp('due_at')->nullable();
            $t->string('status')->default('todo'); $t->string('title');
        });
        Schema::create('workspace_task_assignments', function (Blueprint $t): void {
            $t->uuid('assignment_id')->primary(); $t->uuid('task_id');
            $t->uuid('assigned_user_id');
        });
        Schema::create('workspace_personal_notes', function (Blueprint $t): void {
            $t->uuid('note_id')->primary(); $t->uuid('owner_user_id');
            $t->string('title'); $t->text('body')->nullable();
            $t->boolean('is_pinned')->default(false); $t->timestamp('archived_at')->nullable();
            $t->timestamp('remind_at')->nullable(); $t->uuid('task_id')->nullable();
            $t->uuid('calendar_event_id')->nullable(); $t->uuid('activity_id')->nullable();
            $t->timestamps();
        });
    }

    private function makeUser(string $name): User
    {
        $id = (string) Str::uuid();
        DB::table('users')->insert([
            'user_id' => $id, 'name' => 'A7410 '.$name, 'email' => $name.'@notes.invalid',
            'password' => bcrypt('testing-only'), 'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return User::query()->findOrFail($id);
    }

    private function makeNote(?User $owner = null, array $attributes = []): WorkspacePersonalNote
    {
        return WorkspacePersonalNote::create(array_merge([
            'owner_user_id' => ($owner ?? $this->owner)->getKey(),
            'title' => 'Private Meeting Notes', 'body' => 'My private discussion notes',
        ], $attributes));
    }

    public function test_owner_can_create_read_and_update_note(): void
    {
        $this->actingAs($this->owner, 'web')->post(route('notes.store'), [
            'title' => 'Client follow-up', 'body' => 'Prepare the talking points',
            'activity_id' => $this->activityId, 'calendar_event_id' => $this->calendarId,
            'remind_day' => '2026-11-20', 'remind_time' => '10:30', 'is_pinned' => '1',
        ])->assertRedirect();
        $note = WorkspacePersonalNote::query()->sole();
        $this->assertSame((string)$this->owner->getKey(), (string)$note->owner_user_id);
        $this->assertSame('2026-11-20 10:30', $note->remind_at?->format('Y-m-d H:i'));
        $this->assertTrue($note->is_pinned);
        $this->actingAs($this->owner, 'web')->get(route('notes.show', ['note'=>$note->getKey()]))->assertOk();
        $this->actingAs($this->owner, 'web')->put(route('notes.update', ['note'=>$note->getKey()]), [
            'title'=>'Updated Follow-up', 'body'=>'Updated private content',
        ])->assertRedirect();
        $this->assertDatabaseHas('workspace_personal_notes', [
            'note_id'=>$note->getKey(), 'title'=>'Updated Follow-up', 'is_pinned'=>0,
        ]);
    }

    public function test_another_user_cannot_view_edit_pin_archive_or_delete_private_note(): void
    {
        $note = $this->makeNote();
        $id = $note->getKey();
        $this->actingAs($this->other, 'web')->get(route('notes.show', ['note'=>$id]))->assertNotFound();
        $this->actingAs($this->other, 'web')->get(route('notes.edit', ['note'=>$id]))->assertNotFound();
        $this->actingAs($this->other, 'web')->put(route('notes.update', ['note'=>$id]), ['title'=>'Stolen'])->assertNotFound();
        $this->actingAs($this->other, 'web')->patch(route('notes.pin', ['note'=>$id]))->assertNotFound();
        $this->actingAs($this->other, 'web')->patch(route('notes.archive', ['note'=>$id]))->assertNotFound();
        $this->actingAs($this->other, 'web')->delete(route('notes.destroy', ['note'=>$id]))->assertNotFound();
        $this->assertDatabaseHas('workspace_personal_notes', ['note_id'=>$id, 'title'=>'Private Meeting Notes']);
    }

    public function test_linking_to_another_users_activity_and_calendar_event_is_rejected(): void
    {
        $this->actingAs($this->other, 'web')->post(route('notes.store'), [
            'title'=>'Outsider', 'activity_id'=>$this->activityId,
            'calendar_event_id'=>$this->calendarId,
        ])->assertSessionHasErrors(['activity_id','calendar_event_id']);
        $this->assertDatabaseCount('workspace_personal_notes', 0);
    }

    public function test_only_owner_sees_note_in_personal_calendar(): void
    {
        $note = $this->makeNote($this->owner, ['remind_at' => now()->startOfMonth()->addDays(4)]);
        $this->actingAs($this->owner, 'web')->get(route('calendar.index', [
            'view'=>'personal', 'month'=>now()->format('Y-m'),
        ]))->assertOk()->assertSee($note->title);
        $this->actingAs($this->other, 'web')->get(route('calendar.index', [
            'view'=>'personal', 'month'=>now()->format('Y-m'),
        ]))->assertOk()->assertDontSee($note->title);
    }

    public function test_archive_excludes_reminder_from_calendar(): void
    {
        $note = $this->makeNote($this->owner, ['remind_at' => now()->startOfMonth()->addDays(4)]);
        $this->actingAs($this->owner, 'web')->patch(route('notes.archive', ['note'=>$note->getKey()]))->assertRedirect();
        $this->actingAs($this->owner, 'web')->get(route('calendar.index', [
            'view'=>'personal', 'month'=>now()->format('Y-m'),
        ]))->assertOk()->assertDontSee($note->title);
    }

    public function test_owner_can_pin_archive_restore_and_delete(): void
    {
        $note = $this->makeNote();
        $id = $note->getKey();
        $this->actingAs($this->owner, 'web')->patch(route('notes.pin',['note'=>$id]))->assertRedirect();
        $this->assertDatabaseHas('workspace_personal_notes', ['note_id'=>$id,'is_pinned'=>1]);
        $this->actingAs($this->owner, 'web')->patch(route('notes.archive',['note'=>$id]))->assertRedirect();
        $this->assertNotNull($note->fresh()->archived_at);
        $this->actingAs($this->owner, 'web')->patch(route('notes.restore',['note'=>$id]))->assertRedirect();
        $this->assertNull($note->fresh()->archived_at);
        $this->actingAs($this->owner, 'web')->delete(route('notes.destroy',['note'=>$id]))->assertRedirect();
        $this->assertDatabaseMissing('workspace_personal_notes',['note_id'=>$id]);
    }
}
