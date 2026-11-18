<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Observers\CrmCrossModuleStatusAuditObserver;
use App\Services\WorkspaceNotificationCenter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PDO;
use Tests\TestCase;

/**
 * A7.5.4.2 transaction fixture. Always uses its OWN ephemeral SQLite database.
 * Does not migrate, create, truncate, seed, or update the configured CRM DB.
 * This proves the ledger + inbox share a DB transaction, NOT the HTTP workflow.
 */
final class CrmIntegrationAtomicityIsolatedTest extends TestCase
{
    private function withMemoryDatabase(\Closure $test): void
    {
        self::assertTrue(app()->environment('testing'), 'This fixture may run only under APP_ENV=testing.');
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PDO SQLite is required for the isolated memory-only fixture.');
        }

        $name = 'crm_a7542_isolated_memory';
        $original = DB::getDefaultConnection();
        $existing = config('database.connections.'.$name);
        config()->set('database.connections.'.$name, [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge($name);
        DB::setDefaultConnection($name);
        try {
            $schema = Schema::connection($name);
            $schema->create('workspace_audit_events', function (Blueprint $table): void {
                $table->uuid('audit_event_id')->primary();
                $table->uuid('actor_user_id')->nullable();
                $table->string('event_type', 70);
                $table->string('subject_type', 30);
                $table->uuid('subject_id')->nullable();
                $table->json('details')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
            $schema->create('users', function (Blueprint $table): void {
                $table->uuid('user_id')->primary();
                $table->string('status', 20);
            });
            $schema->create('workspace_notification_preferences', function (Blueprint $table): void {
                $table->uuid('user_id')->primary();
                foreach (\App\Services\WorkspaceNotificationPreferences::FLAGS as $flag) {
                    $table->boolean($flag)->default($flag !== 'crm_status_enabled');
                }
                $table->unsignedSmallInteger('reminder_lead_minutes')->default(1440);
            });
            $schema->create('workspace_notifications', function (Blueprint $table): void {
                $table->uuid('notification_id')->primary();
                $table->uuid('recipient_user_id');
                $table->uuid('actor_user_id')->nullable();
                $table->string('kind', 60);
                $table->string('title', 160);
                $table->string('message', 255);
                $table->string('target_type', 20);
                $table->uuid('target_id');
                $table->string('dedupe_key', 160);
                $table->timestamp('read_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['recipient_user_id', 'dedupe_key']);
            });

            $test();
        } finally {
            // Never leave a test-wide override of the application's DB connection.
            DB::setDefaultConnection($original);
            DB::disconnect($name);
            DB::purge($name);
            config()->set('database.connections.'.$name, $existing);
        }
        self::assertSame($original, DB::getDefaultConnection(), 'Default CRM connection must be restored.');
    }

    private function recordSimulatedDeliveryStatus(string $id): void
    {
        // Simulates the Eloquent updated event without updating a Delivery row.
        // This isolates audit behavior from unrelated order/stock constraints.
        $delivery = new Delivery();
        $delivery->setRawAttributes(['delivery_id' => $id, 'status' => 'pending'], true);
        $delivery->setAttribute('status', 'in_transit');
        $delivery->syncChanges();
        self::assertTrue($delivery->wasChanged('status'));
        (new CrmCrossModuleStatusAuditObserver())->updated($delivery);
    }

    private function prepareRecipient(bool $enabled): string
    {
        $recipient = (string) Str::uuid();
        DB::table('users')->insert(['user_id' => $recipient, 'status' => 'active']);
        DB::table('workspace_notification_preferences')->insert([
            'user_id' => $recipient, 'crm_status_enabled' => $enabled,
            'reminder_lead_minutes' => 1440,
        ]);
        return $recipient;
    }

    private function notify(string $recipient, string $subject, string $key): bool
    {
        // Exercise the existing inbox + opt-in preference, not the publisher's
        // recipient selection (which is covered by its own access tests).
        return app(WorkspaceNotificationCenter::class)->send(
            $recipient, 'crm_status', 'delivery', $subject,
            'Delivery status updated', 'A CRM record has changed status.', $key
        );
    }

    public function test_audit_and_inbox_are_both_rolled_back_when_transaction_fails(): void
    {
        $this->withMemoryDatabase(function (): void {
            $subject = (string) Str::uuid();
            $recipient = $this->prepareRecipient(true);
            $this->expectingOnlyMemoryConnection();

            try {
                DB::transaction(function () use ($subject, $recipient): void {
                    $this->recordSimulatedDeliveryStatus($subject);
                    self::assertTrue($this->notify($recipient, $subject, 'qa-failed:'.(string) Str::uuid()));
                    self::assertSame(1, DB::table('workspace_audit_events')->count());
                    self::assertSame(1, DB::table('workspace_notifications')->count());
                    // Roll back BOTH writes by triggering a failed transaction.
                    throw new \RuntimeException('A7.5.4.2 intentional rollback');
                });
            } catch (\RuntimeException $e) {
                self::assertSame('A7.5.4.2 intentional rollback', $e->getMessage());
            }
            self::assertSame(0, DB::table('workspace_audit_events')->count());
            self::assertSame(0, DB::table('workspace_notifications')->count());
        });
    }

    public function test_commit_persists_sanitized_status_event_and_opt_in_notice_only(): void
    {
        $this->withMemoryDatabase(function (): void {
            $subject = (string) Str::uuid();
            $enabled = $this->prepareRecipient(true);
            $disabled = $this->prepareRecipient(false);
            $this->expectingOnlyMemoryConnection();

            DB::transaction(function () use ($subject, $enabled, $disabled): void {
                $this->recordSimulatedDeliveryStatus($subject);
                self::assertTrue($this->notify($enabled, $subject, 'qa-yes:'.(string) Str::uuid()));
                self::assertFalse($this->notify($disabled, $subject, 'qa-no:'.(string) Str::uuid()));
            });
            $event = DB::table('workspace_audit_events')->first();
            self::assertNotNull($event);
            self::assertSame('delivery.status_changed', $event->event_type);
            self::assertSame('delivery', $event->subject_type);
            self::assertSame(['status_from' => 'pending', 'status_to' => 'in_transit'],
                json_decode((string) $event->details, true, 512, JSON_THROW_ON_ERROR));
            self::assertSame(1, DB::table('workspace_notifications')->count());
            self::assertSame($enabled, DB::table('workspace_notifications')->value('recipient_user_id'));
        });
    }

    private function expectingOnlyMemoryConnection(): void
    {
        self::assertSame('crm_a7542_isolated_memory', DB::getDefaultConnection());
        self::assertSame(':memory:', (string) DB::connection()->getDatabaseName());
    }
}
