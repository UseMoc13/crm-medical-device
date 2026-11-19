<?php

namespace Tests\Feature;

use App\Console\Commands\PublishDueCrmAnnouncements;
use App\Models\Announcement;
use App\Services\CrmDashboardAnnouncements;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

/** All fixtures and publication writes are on a disposable SQLite :memory: DB. */
final class CrmAnnouncementScheduleIsolatedTest extends TestCase
{
    private function isolated(callable $callback): void
    {
        if (!app()->environment('testing')) $this->markTestSkipped('Requires APP_ENV=testing');
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) $this->markTestSkipped('Requires PDO SQLite');
        $name = 'a764_memory';
        $previous = DB::getDefaultConnection();
        $configuration = config('database.connections.'.$name);
        config()->set('database.connections.'.$name, [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
        ]);
        DB::purge($name);
        DB::setDefaultConnection($name);
        Schema::clearResolvedInstance('db.schema');
        try {
            Schema::connection($name)->create('announcements', function (Blueprint $table): void {
                $table->uuid('announcement_id')->primary();
                $table->string('title');
                $table->string('status');
                $table->boolean('is_pinned')->default(false);
                $table->unsignedInteger('pin_order')->nullable();
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->uuid('created_by')->nullable();
                // Match announcements migration: Eloquent updates updated_at on publish.
                $table->timestamps();
            });
            $callback();
        } finally {
            DB::setDefaultConnection($previous);
            Schema::clearResolvedInstance('db.schema');
            DB::purge($name);
            config()->set('database.connections.'.$name, $configuration);
        }
    }

    public function test_dashboard_read_has_no_mutation_and_command_publishes_due_items(): void
    {
        $this->isolated(function (): void {
            $now = now();
            $ids = array_map(static fn () => (string) Str::uuid(), range(1, 3));
            // Every row in a bulk insert MUST contain the same columns.
            // Otherwise the first row's column list can misplace scheduled_at.
            DB::table('announcements')->insert([
                [
                    'announcement_id' => $ids[0], 'title' => 'Published', 'status' => 'published',
                    'scheduled_at' => null, 'published_at' => $now->copy()->subDay(),
                ],
                [
                    'announcement_id' => $ids[1], 'title' => 'Due', 'status' => 'scheduled',
                    'scheduled_at' => $now->copy()->subHour(), 'published_at' => null,
                ],
                [
                    'announcement_id' => $ids[2], 'title' => 'Future', 'status' => 'scheduled',
                    'scheduled_at' => $now->copy()->addDay(), 'published_at' => null,
                ],
            ]);
            // Validate the fixture BEFORE running any publisher command. A
            // silent wrong-column insert must never be mistaken for a bug in
            // the command, Eloquent connection, or scheduled task.
            self::assertTrue(
                DB::table('announcements')->where('announcement_id', $ids[1])
                    ->where('status', 'scheduled')->whereNotNull('scheduled_at')
                    ->whereNull('published_at')->where('scheduled_at', '<=', $now)->exists(),
                'Fixture error: due item must have a past scheduled_at, not published_at.'
            );
            self::assertTrue(
                DB::table('announcements')->where('announcement_id', $ids[2])
                    ->where('status', 'scheduled')->whereNotNull('scheduled_at')
                    ->whereNull('published_at')->where('scheduled_at', '>', $now)->exists(),
                'Fixture error: future item must have a future scheduled_at.'
            );
            $writes = [];
            DB::listen(static function ($event) use (&$writes): void {
                if (preg_match('/^\s*(insert|update|delete|alter|drop|create|truncate)\b/i', $event->sql)) $writes[] = $event->sql;
            });
            $initial = app(CrmDashboardAnnouncements::class)->latest();
            self::assertSame([$ids[0]], $initial->pluck('announcement_id')->all());
            self::assertSame([], $writes);
            // Do not invoke Artisan::call() here: a Console Kernel bootstrap may
            // change the default DB context used by the isolated fixture.
            // Validate the actual Eloquent connection before any publisher write.
            self::assertSame('a764_memory', DB::getDefaultConnection());
            self::assertSame('a764_memory', (new Announcement())->getConnection()->getName());
            $command = app(PublishDueCrmAnnouncements::class);
            $command->setLaravel(app());
            self::assertSame(0, $command->run(new ArrayInput([]), new BufferedOutput()));
            self::assertSame('published', DB::table('announcements')->where('announcement_id', $ids[1])->value('status'));
            self::assertSame('scheduled', DB::table('announcements')->where('announcement_id', $ids[2])->value('status'));
            $countAfterMutation = count($writes);
            self::assertGreaterThan(0, $countAfterMutation);
            self::assertCount(2, app(CrmDashboardAnnouncements::class)->latest());
            self::assertCount($countAfterMutation, $writes, 'Dashboard read must never add another SQL write.');
        });
    }
}
