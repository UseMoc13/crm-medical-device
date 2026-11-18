<?php

namespace Tests\Unit;

use App\Http\Controllers\InvoiceController;
use App\Services\CrmInvoiceRowGuard;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

/** No schema migrations, seeders, or business-record writes. */
final class CrmInvoiceRowGuardTest extends TestCase
{
    public function test_guard_refuses_lock_outside_transaction(): void
    {
        DB::shouldReceive('transactionLevel')->once()->andReturn(0);
        $this->expectException(\LogicException::class);
        app(CrmInvoiceRowGuard::class)->lockedStatus('invoice-id');
    }

    public function test_guard_locks_actual_row_and_returns_latest_status(): void
    {
        DB::shouldReceive('transactionLevel')->once()->andReturn(1);
        $query = Mockery::mock();
        DB::shouldReceive('table')->once()->with('invoices')->andReturn($query);
        $query->shouldReceive('where')->once()->with('invoice_id', 'invoice-id')->andReturnSelf();
        $query->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $query->shouldReceive('first')->once()->with(['status'])->andReturn((object) ['status' => 'Sent']);

        $this->assertSame('Sent', app(CrmInvoiceRowGuard::class)->lockedStatus('invoice-id'));
    }

    public function test_guard_does_not_convert_legacy_unpaid_status(): void
    {
        DB::shouldReceive('transactionLevel')->once()->andReturn(1);
        $query = Mockery::mock();
        DB::shouldReceive('table')->once()->with('invoices')->andReturn($query);
        $query->shouldReceive('where')->once()->andReturnSelf();
        $query->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $query->shouldReceive('first')->once()->andReturn((object) ['status' => 'unpaid']);

        $this->assertSame('unpaid', app(CrmInvoiceRowGuard::class)->lockedStatus('invoice-id'));
    }

    public function test_all_three_write_paths_recheck_locked_status_inside_transactions(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/InvoiceController.php'));
        if (is_string($source)) $source = str_replace(["\r\n", "\r"], "\n", $source);
        $this->assertIsString($source);
        foreach (['update', 'updateStatus', 'destroy'] as $name) {
            $start = strpos($source, '    public function '.$name.'(');
            $this->assertNotFalse($start, 'Missing invoice '.$name.'()');
            $end = strpos($source, "\n    }\n", $start);
            $this->assertNotFalse($end);
            $method = substr($source, $start, $end - $start);
            $this->assertStringContainsString('DB::transaction(', $method);
            $this->assertStringContainsString('CrmInvoiceRowGuard::class', $method);
            $this->assertLessThan(
                strpos($method, 'lockedStatus('),
                strpos($method, 'DB::transaction('),
                'Invoice '.$name.'() must begin transaction before row lock.'
            );
        }
    }

    public function test_existing_transition_matrix_still_rejects_unknown_state(): void
    {
        $ref = new \ReflectionClass(InvoiceController::class);
        $matrix = $ref->getReflectionConstant('TRANSITIONS')->getValue();
        $this->assertSame(['Sent', 'Cancelled'], $matrix['Draft']);
        $this->assertSame(['Paid', 'Cancelled'], $matrix['Sent']);
        $this->assertSame([], $matrix['Paid']);
        $this->assertSame(['Draft'], $matrix['Cancelled']);
        $this->assertArrayNotHasKey('unpaid', $matrix);
        $this->assertSame(['Draft', 'Sent'], $ref->getReflectionConstant('EDITABLE')->getValue());
        $this->assertSame(['Draft', 'Cancelled'], $ref->getReflectionConstant('DELETABLE')->getValue());
    }
}
