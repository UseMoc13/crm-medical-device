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

/** Disposable sqlite :memory: only — no PostgreSQL reset or production fixtures. */
final class WorkspaceFloatingNoteHttpTest extends TestCase
{
    private User $owner;
    private User $other;
    private string $activity;
    private string $todo;
    private string $event;

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
        Schema::create('activities', function (Blueprint $t) {
            $t->uuid('activity_id')->primary(); $t->uuid('user_id'); $t->string('subject');
            $t->timestamp('activity_date');
        });
        Schema::create('workspace_calendar_events', function (Blueprint $t) {
            $t->uuid('calendar_event_id')->primary(); $t->uuid('owner_user_id');
            $t->string('title'); $t->string('visibility')->default('personal');
            $t->timestamp('starts_at'); $t->timestamp('ends_at')->nullable();
            $t->boolean('is_all_day')->default(false); $t->string('location')->nullable();$t->timestamps();
        });
        Schema::create('workspace_tasks', function (Blueprint $t) {
            $t->uuid('task_id')->primary(); $t->uuid('owner_user_id');
            $t->string('title'); $t->string('visibility'); $t->string('status')->default('todo');
            $t->timestamp('due_at')->nullable(); $t->timestamp('starts_at')->nullable(); $t->timestamps();
        });
        Schema::create('workspace_task_assignments', function (Blueprint $t) {
            $t->uuid('assignment_id')->primary(); $t->uuid('task_id'); $t->uuid('assigned_user_id');
        });
        Schema::create('workspace_collaboration_members', function (Blueprint $t) {
            $t->uuid('member_id')->primary(); $t->uuid('activity_id')->nullable();
            $t->uuid('task_id')->nullable(); $t->uuid('user_id'); $t->string('access_role');
        });
        Schema::create('workspace_personal_notes', function (Blueprint $t) {
            $t->uuid('note_id')->primary(); $t->uuid('owner_user_id');
            $t->string('title'); $t->text('body')->nullable();
            $t->boolean('is_pinned')->default(false); $t->timestamp('archived_at')->nullable();
            $t->timestamp('remind_at')->nullable(); $t->uuid('task_id')->nullable();
            $t->uuid('calendar_event_id')->nullable(); $t->uuid('activity_id')->nullable();
            $t->unsignedInteger('revision')->default(0); $t->timestamps();
        });
        Schema::create('workspace_note_mentions', function (Blueprint $t) {
            $t->uuid('mention_id')->primary(); $t->uuid('note_id');
            $t->string('target_type'); $t->uuid('target_id'); $t->timestamps();
        });
        $this->owner = $this->makeUser('owner');
        $this->other = $this->makeUser('other');
        $this->activity = (string)Str::uuid(); $this->todo = (string)Str::uuid(); $this->event = (string)Str::uuid();
        DB::table('activities')->insert(['activity_id'=>$this->activity, 'user_id'=>$this->owner->getKey(),
            'subject'=>'Owner Activity', 'activity_date'=>now()]);
        DB::table('workspace_tasks')->insert(['task_id'=>$this->todo, 'owner_user_id'=>$this->owner->getKey(),
            'title'=>'Owner TO-DO', 'visibility'=>'work', 'status'=>'todo', 'created_at'=>now(), 'updated_at'=>now()]);
        DB::table('workspace_calendar_events')->insert(['calendar_event_id'=>$this->event, 'owner_user_id'=>$this->owner->getKey(),
            'title'=>'Personal Calendar Event', 'starts_at'=>now(), 'is_all_day'=>false,
            'visibility'=>'personal', 'created_at'=>now(), 'updated_at'=>now()]);
    }

    private function makeUser(string $name): User
    {
        $id=(string)Str::uuid();
        DB::table('users')->insert(['user_id'=>$id,'name'=>$name,'email'=>$name.'@floating.invalid',
            'password'=>bcrypt('test-only'),'status'=>'active','created_at'=>now(),'updated_at'=>now()]);
        return User::query()->findOrFail($id);
    }

    protected function tearDown(): void
    {
        if (extension_loaded('pdo_sqlite') && config('database.default') === 'sqlite'
            && config('database.connections.sqlite.database') === ':memory:') {
            foreach (['workspace_note_mentions','workspace_personal_notes','workspace_collaboration_members',
                'workspace_task_assignments','workspace_tasks','activities','workspace_calendar_events',
                'user_roles','roles','users'] as $table) Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    private function create(): array
    {
        $result = $this->actingAs($this->owner, 'web')
            ->postJson(route('notes.floating.create'))->assertCreated()->json('note');
        $this->assertNotEmpty($result['id']);
        return $result;
    }

    public function test_global_create_autosave_and_revision_conflict(): void
    {
        $note = $this->create();
        $url=route('notes.floating.save',['note'=>$note['id']]);
        $this->actingAs($this->owner,'web')->patchJson($url,['revision'=>0,'title'=>'Meeting','body'=>'Prepare agenda'])
            ->assertOk()->assertJsonPath('note.revision',1);
        $this->actingAs($this->owner,'web')->patchJson($url,['revision'=>0,'title'=>'Overwritten','body'=>'Wrong'])
            ->assertStatus(409);
        $this->assertDatabaseHas('workspace_personal_notes',['note_id'=>$note['id'],'title'=>'Meeting', 'revision'=>1]);
    }

    public function test_mention_search_and_links_are_limited_to_accessible_records(): void
    {
        $note=$this->create();
        foreach (['activity'=>'Owner Activity','todo'=>'Owner TO-DO','calendar'=>'Personal Calendar Event'] as $type=>$title) {
            $this->actingAs($this->owner,'web')->getJson(route('notes.mention-options', ['type'=>$type,'q'=>'Owner']))
                ->assertOk();
        }
        $body="Discuss @[Activity:Owner Activity](activity:{$this->activity})\n".
            "@[TO-DO:Owner TO-DO](todo:{$this->todo})\n".
            "@[Calendar:Personal Calendar Event](calendar:{$this->event})";
        $this->actingAs($this->owner,'web')->patchJson(route('notes.floating.save',['note'=>$note['id']]),
            ['revision'=>0,'title'=>'Follow-up','body'=>$body])->assertOk()
            ->assertSee('notes-mention-link');
        $this->assertDatabaseCount('workspace_note_mentions',3);
        $this->actingAs($this->other,'web')->getJson(route('notes.mention-options', ['type'=>'all','q'=>'Owner']))
            ->assertOk()->assertJsonCount(0,'items');
        $this->actingAs($this->other,'web')->getJson(route('notes.floating.index'))
            ->assertOk()->assertJsonCount(0,'notes');
        $this->actingAs($this->other,'web')->getJson(route('notes.floating.show',['note'=>$note['id']]))->assertNotFound();
        $this->actingAs($this->other,'web')->patchJson(route('notes.floating.save',['note'=>$note['id']]),
            ['revision'=>1,'title'=>'Forbidden','body'=>''])->assertNotFound();
    }

    public function test_forged_mention_is_rejected_and_not_persisted(): void
    {
        $note=$this->actingAs($this->other,'web')->postJson(route('notes.floating.create'))
            ->assertCreated()->json('note');
        $body="Sneak @[Activity:Hidden](activity:{$this->activity})";
        $this->actingAs($this->other,'web')->patchJson(route('notes.floating.save',['note'=>$note['id']]),
            ['revision'=>0,'title'=>'Bad','body'=>$body])->assertUnprocessable()->assertJsonValidationErrors('body');
        $this->assertDatabaseHas('workspace_personal_notes',['note_id'=>$note['id'],'revision'=>0]);
        $this->assertDatabaseCount('workspace_note_mentions',0);
    }

    public function test_private_notes_are_never_visible_to_other_accounts(): void
    {
        $note=$this->create();
        $this->actingAs($this->other,'web')->deleteJson(route('notes.floating.destroy',['note'=>$note['id']]))
            ->assertNotFound();
        $this->assertDatabaseHas('workspace_personal_notes',['note_id'=>$note['id']]);
        $this->actingAs($this->owner,'web')->deleteJson(route('notes.floating.destroy',['note'=>$note['id']]))
            ->assertOk();
        $this->assertDatabaseMissing('workspace_personal_notes',['note_id'=>$note['id']]);
    }

    public function test_untrusted_markup_is_escaped_in_preview(): void
    {
        $note = $this->create();
        $body = '<script>alert(1)</script>\n<img src=x onerror=alert(2)>';
        // The API deliberately returns the raw body for round-trip plain-text editing.
        // Escape assertions MUST target preview_html, not the complete JSON response.
        $response = $this->actingAs($this->owner, 'web')
            ->patchJson(route('notes.floating.save', ['note' => $note['id']]), [
                'revision' => 0, 'title' => 'Safety', 'body' => $body,
            ])->assertOk()->assertJsonPath('note.body', $body);

        $preview = (string) $response->json('note.preview_html');
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $preview);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(2)&gt;', $preview);
        $this->assertStringNotContainsString('<script', $preview);
        $this->assertStringNotContainsString('<img', $preview);
    }

    public function test_mention_label_markup_is_escaped_but_link_remains_clickable(): void
    {
        $note = $this->create();
        $body = '@[Activity:<svg/onload=alert(3)>](activity:' . $this->activity . ')';
        $response = $this->actingAs($this->owner, 'web')
            ->patchJson(route('notes.floating.save', ['note' => $note['id']]), [
                'revision' => 0, 'title' => 'Safe mention', 'body' => $body,
            ])->assertOk()->assertJsonPath('note.body', $body);

        $preview = (string) $response->json('note.preview_html');
        $this->assertStringContainsString('class="notes-mention-link"', $preview);
        $this->assertStringContainsString('&lt;svg/onload=alert(3)&gt;', $preview);
        $this->assertStringNotContainsString('<svg', $preview);
        $this->assertDatabaseCount('workspace_note_mentions', 1);
    }
}
