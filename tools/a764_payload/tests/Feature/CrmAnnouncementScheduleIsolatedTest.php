<?php

namespace Tests\Feature;

use App\Services\CrmDashboardAnnouncements;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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
            DB::table('announcements')->insert([
                ['announcement_id'=>$ids[0], 'title'=>'Published', 'status'=>'published', 'published_at'=>$now->copy()->subDay()],
                ['announcement_id'=>$ids[1], 'title'=>'Due', 'status'=>'scheduled', 'scheduled_at'=>$now->copy()->subHour()],
                ['announcement_id'=>$ids[2], 'title'=>'Future', 'status'=>'scheduled', 'scheduled_at'=>$now->copy()->addDay()],
            ]);
            $writes = [];
            DB::listen(static function ($event) use (&$writes): void {
                if (preg_match('/^\s*(insert|update|delete|alter|drop|create|truncate)\b/i', $event->sql)) $writes[] = $event->sql;
            });
            $initial = app(CrmDashboardAnnouncements::class)->latest();
            self::assertSame([$ids[0]], $initial->pluck('announcement_id')->all());
            self::assertSame([], $writes);
            self::assertSame(0, Artisan::call('crm:announcements:publish-due'));
            self::assertSame('published', DB::table('announcements')->where('announcement_id', $ids[1])->value('status'));
            self::assertSame('scheduled', DB::table('announcements')->where('announcement_id', $ids[2])->value('status'));
            $countAfterMutation = count($writes);
            self::assertGreaterThan(0, $countAfterMutation);
            self::assertCount(2, app(CrmDashboardAnnouncements::class)->latest());
            self::assertCount($countAfterMutation, $writes, 'Dashboard read must never add another SQL write.');
        });
    }
}
