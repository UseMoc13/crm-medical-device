<?php

namespace Tests\Feature;

use App\Http\Controllers\SalesOrderController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

/**
 * A7.7.5.2: real controller CSV callback against synthetic, isolated SQLite
 * :memory:. No migrations, no CRM PostgreSQL access, no HTTP write actions.
 */
final class CrmSalesOrderCsvSqliteIsolationTest extends TestCase
{
    private ?string $previousConnection = null;
    private const CONNECTION = 'crm_a7752_memory';

    protected function setUp(): void
    {
        parent::setUp();
        self::assertTrue(app()->environment('testing'), 'Only execute in APP_ENV=testing');
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite PDO unavailable');
        }
        $this->previousConnection = DB::getDefaultConnection();
        config()->set('database.connections.' . self::CONNECTION, [
            'driver' => 'sqlite', 'database' => ':memory:',
            'prefix' => '', 'foreign_key_constraints' => true,
        ]);
        DB::purge(self::CONNECTION);
        DB::setDefaultConnection(self::CONNECTION);
        self::assertSame(':memory:', DB::connection(self::CONNECTION)->getDatabaseName());
        $s = Schema::connection(self::CONNECTION);
        $s->create('sales_orders', static function (Blueprint $t): void {
            $t->string('sales_order_id')->primary();
            $t->string('quotation_id');
            $t->string('sales_order_number');
            $t->date('order_date');
            $t->date('delivery_date')->nullable();
            $t->string('status');
            $t->decimal('subtotal', 18, 2);
            $t->decimal('tax', 18, 2);
            $t->decimal('total_amount', 18, 2);
            $t->text('notes')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->timestamp('updated_at')->nullable();
        });
        $s->create('quotations', static function (Blueprint $t): void {
            $t->string('quotation_id')->primary();
            $t->string('opportunity_id');
            $t->string('quotation_number');
        });
        $s->create('opportunities', static function (Blueprint $t): void {
            $t->string('opportunity_id')->primary();
            $t->string('customer_id');
        });
        $s->create('customers', static function (Blueprint $t): void {
            $t->string('customer_id')->primary();
            $t->string('customer_name');
        });

        // Eight orders with distinct relation chains reveal N+1 query behavior.
        for ($i = 1; $i <= 8; $i++) {
            $id = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            DB::table('customers')->insert([
                'customer_id' => 'c' . $id,
                'customer_name' => $i === 1 ? '=SUM(1,2)' : 'Synthetic Customer ' . $id,
            ]);
            DB::table('opportunities')->insert(['opportunity_id' => 'o' . $id, 'customer_id' => 'c' . $id]);
            DB::table('quotations')->insert([
                'quotation_id' => 'q' . $id,
                'opportunity_id' => 'o' . $id,
                'quotation_number' => 'Q-' . $id,
            ]);
            DB::table('sales_orders')->insert([
                'sales_order_id' => 'so' . $id,
                'quotation_id' => 'q' . $id,
                'sales_order_number' => 'SO-' . $id,
                'order_date' => '2026-11-20',
                'delivery_date' => null,
                'status' => 'confirmed',
                'subtotal' => '120.00',
                'tax' => '12.00',
                'total_amount' => '132.00',
                'notes' => 'Synthetic only',
                'created_at' => '2026-11-20 12:00:00', // same timestamp verifies tie-breaker
                'updated_at' => '2026-11-20 12:00:00',
            ]);
        }
    }

    protected function tearDown(): void
    {
        if ($this->previousConnection !== null) {
            DB::setDefaultConnection($this->previousConnection);
            DB::disconnect(self::CONNECTION);
            DB::purge(self::CONNECTION);
            config()->set('database.connections.' . self::CONNECTION, null);
        }
        parent::tearDown();
    }

    public function test_csv_export_loads_eight_relation_chains_with_bounded_queries(): void
    {
        $db = DB::connection(self::CONNECTION);
        $db->flushQueryLog();
        $db->enableQueryLog();
        $response = app(SalesOrderController::class)->export(Request::create(
            '/sales-orders/export', 'GET', ['format' => 'csv']
        ));
        self::assertInstanceOf(StreamedResponse::class, $response);
        ob_start();
        try {
            $response->sendContent();
            $contents = (string) ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        $queries = $db->getQueryLog();
        $db->disableQueryLog();
        $selects = array_filter($queries, static fn (array $q): bool =>
            preg_match('/^\s*select\b/i', (string) ($q['query'] ?? '')) === 1
        );
        self::assertLessThanOrEqual(6, count($selects), 'N+1: too many queries for eight Sales Orders');
        self::assertGreaterThanOrEqual(4, count($selects), 'Expected order, quotation, opportunity, customer batches');
        foreach ($queries as $q) {
            self::assertDoesNotMatchRegularExpression(
                '/^\s*(?:insert|update|delete|alter|create|drop|truncate)\b/i',
                (string) ($q['query'] ?? ''), 'CSV export must not mutate tables'
            );
        }
        self::assertSame(8, $db->table('sales_orders')->count());
        self::assertStringContainsString('SO-01', $contents);
        self::assertStringContainsString('Synthetic Customer 08', $contents);
        self::assertStringContainsString("'=SUM(1,2)", $contents, 'Formula safety must remain in export');
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, $contents);
        rewind($stream);
        $rows = [];
        while (($fields = fgetcsv($stream)) !== false) $rows[] = $fields;
        fclose($stream);
        self::assertCount(9, $rows, 'One CSV header plus eight Sales Order rows');
        self::assertSame('132', (string) (float) $rows[1][9], 'Amount must retain numeric meaning');
    }
}
