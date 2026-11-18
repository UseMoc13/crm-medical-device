<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Observers\CrmCrossModuleStatusAuditObserver;
use App\Services\WorkspaceAuditTrail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Transaction-level smoke check. Inserts an event only inside a rollback. */
final class CrmCrossModuleStatusAuditTransactionTest extends TestCase
{
    public function test_eloquent_status_event_is_recorded_and_rolled_back(): void
    {
        if (!Schema::hasTable('workspace_audit_events')) {
            $this->markTestSkipped('Audit table is absent in this test database; run the migrated PostgreSQL integration suite.');
        }

        $id = (string) Str::uuid();
        $before = DB::table('workspace_audit_events')->where('subject_id', $id)->count();
        DB::beginTransaction();
        try {
            $model = new Delivery();
            // No Delivery row is created: simulate Eloquent's updated event state.
            $model->setRawAttributes(['delivery_id' => $id, 'status' => 'pending'], true);
            $model->setAttribute('status', 'in_transit');
            $model->syncChanges();
            self::assertTrue($model->wasChanged('status'));

            (new CrmCrossModuleStatusAuditObserver())->updated($model);
            $events = DB::table('workspace_audit_events')->where('subject_id', $id)->get();
            self::assertCount(1, $events);
            self::assertSame('delivery.status_changed', $events[0]->event_type);
            self::assertSame('delivery', $events[0]->subject_type);
            $details = json_decode((string) $events[0]->details, true);
            self::assertSame('pending', $details['status_from'] ?? null);
            self::assertSame('in_transit', $details['status_to'] ?? null);
        } finally {
            DB::rollBack();
        }
        self::assertSame($before, DB::table('workspace_audit_events')->where('subject_id', $id)->count());
    }

    public function test_invalid_event_is_rejected_without_database_write(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        app(WorkspaceAuditTrail::class)->record('invoice.unknown_event', 'invoice', (string) Str::uuid());
    }
}
