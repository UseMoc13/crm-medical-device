<?php

namespace Tests\Feature;

use App\Services\CrmStagingInvoiceParity;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Disposable SQLite-only synthetic test. Does NOT run the staging command or touch CRM DB. */
final class CrmStagingInvoiceParitySqliteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::assertSame('testing', app()->environment(), 'Refuse to run fixture outside APP_ENV=testing.');
        config()->set('database.connections.a7655_memory', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('a7655_memory');
        $schema = Schema::connection('a7655_memory');
        $schema->create('invoices', static function (Blueprint $t) {
            $t->string('invoice_id')->primary();
            $t->string('sales_order_id')->nullable();
            $t->date('invoice_date');
            $t->date('due_date')->nullable();
            $t->string('status');
            $t->decimal('total_amount', 18, 2);
        });
        $schema->create('sales_orders', static function (Blueprint $t) {
            $t->string('sales_order_id'); // no unique constraint to test drift detection
            $t->string('quotation_id')->nullable();
        });
        $schema->create('quotations', static function (Blueprint $t) {
            $t->string('quotation_id');
            $t->string('opportunity_id')->nullable();
        });
        $schema->create('opportunities', static function (Blueprint $t) {
            $t->string('opportunity_id');
            $t->string('customer_id')->nullable();
        });
        $schema->create('customers', static function (Blueprint $t) {
            $t->string('customer_id');
        });

        // Identical keys in every inserted row: prevents the earlier SQLite bulk insert bug.
        DB::connection('a7655_memory')->table('invoices')->insert([
            ['invoice_id'=>'1', 'sales_order_id'=>'A', 'invoice_date'=>'2026-11-01', 'due_date'=>'2026-11-05', 'status'=>'Sent',      'total_amount'=>'100.25'],
            ['invoice_id'=>'2', 'sales_order_id'=>'B', 'invoice_date'=>'2026-11-02', 'due_date'=>'2026-11-20', 'status'=>'Paid',      'total_amount'=>'300.50'],
            ['invoice_id'=>'3', 'sales_order_id'=>'C', 'invoice_date'=>'2026-11-03', 'due_date'=>'2026-11-04', 'status'=>'Draft',     'total_amount'=>'900.00'],
            ['invoice_id'=>'4', 'sales_order_id'=>'D', 'invoice_date'=>'2026-11-04', 'due_date'=>'2026-11-04', 'status'=>'unpaid',    'total_amount'=>'700.00'],
            ['invoice_id'=>'5', 'sales_order_id'=>'E', 'invoice_date'=>'2026-11-15', 'due_date'=>'2026-12-01', 'status'=>'Sent',      'total_amount'=>'10.00'],
            ['invoice_id'=>'6', 'sales_order_id'=>'F', 'invoice_date'=>'2025-11-01', 'due_date'=>'2025-11-05', 'status'=>'Paid',      'total_amount'=>'900.00'],
            ['invoice_id'=>'7', 'sales_order_id'=>'G', 'invoice_date'=>'2026-11-04', 'due_date'=>'2026-11-04', 'status'=>'Cancelled', 'total_amount'=>'800.00'],
        ]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('a7655_memory');
        DB::purge('a7655_memory');
        parent::tearDown();
    }

    public function test_independent_report_dashboard_and_csv_aggregates_match_without_writes(): void
    {
        $db = DB::connection('a7655_memory');
        // Validate actual fixture amounts; comparisons alone could pass on empty data.
        self::assertSame(7, $db->table('invoices')->count());
        self::assertSame('410.75', CrmStagingInvoiceParity::decimalString((string)
            $db->table('invoices')->whereIn('status', ['Sent','Paid'])
                ->whereDate('invoice_date', '>=', '2026-01-01')
                ->sum('total_amount')));

        $writes = [];
        DB::listen(static function (QueryExecuted $q) use (&$writes) {
            if ($q->connectionName === 'a7655_memory'
                && preg_match('/\A\s*(?:insert|update|delete|create|drop|alter|truncate)\b/i', $q->sql)) {
                $writes[] = $q->sql;
            }
        });
        $result = app(CrmStagingInvoiceParity::class)->compare(
            $db, '2026-01-01', '2026-11-19', '2026-11-19');
        self::assertSame('UNSCOPED_FORMULA_PARITY', $result['status']);
        self::assertSame(['total'=>9, 'passed'=>9, 'failed'=>0], $result['summary']);
        self::assertFalse($result['contains_financial_amounts']);
        self::assertFalse($result['authenticated_viewer_parity_verified']);
        self::assertSame([], $writes);
        self::assertSame(7, $db->table('invoices')->count());
    }

    public function test_duplicate_join_rows_are_detected_instead_of_falsely_claiming_parity(): void
    {
        $db = DB::connection('a7655_memory');
        $db->table('sales_orders')->insert([
            ['sales_order_id'=>'A', 'quotation_id'=>null],
            ['sales_order_id'=>'A', 'quotation_id'=>null],
        ]);
        $result = app(CrmStagingInvoiceParity::class)->compare(
            $db, '2026-01-01', '2026-11-19', '2026-11-19');
        self::assertSame('NUMERIC_MISMATCH', $result['status']);
        self::assertGreaterThan(0, $result['summary']['failed']);
        self::assertContains(['id'=>'csv_left_join_preserves_invoice_rows','status'=>'fail'], $result['checks']);
    }
}
