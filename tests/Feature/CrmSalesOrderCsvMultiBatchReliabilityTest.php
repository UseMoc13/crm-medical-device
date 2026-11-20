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
 * A7.7.6.1: real CSV export with 405 distinct relation chains in an isolated,
 * disposable SQLite :memory: database. NEVER touches the CRM PostgreSQL DB.
 * No migrations, HTTP mutations, timers, or business data imports.
 */
final class CrmSalesOrderCsvMultiBatchReliabilityTest extends TestCase
{
    private const CONNECTION = 'crm_a7761_sqlite';
    private ?string $previousConnection = null;
    private mixed $previousDefinition = null;

    protected function setUp(): void
    {
        parent::setUp();
        self::assertSame('testing', app()->environment(), 'A7.7.6.1 runs only in APP_ENV=testing.');
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PDO SQLite extension required for this isolated test.');
        }

        $this->previousConnection = DB::getDefaultConnection();
        $this->previousDefinition = config('database.connections.'.self::CONNECTION);
        config()->set('database.connections.'.self::CONNECTION, [
            'driver' => 'sqlite', 'database' => ':memory:',
            'prefix' => '', 'foreign_key_constraints' => false,
        ]);
        DB::purge(self::CONNECTION);
        DB::setDefaultConnection(self::CONNECTION);
        self::assertSame(':memory:', DB::connection(self::CONNECTION)->getDatabaseName());

        $schema = Schema::connection(self::CONNECTION);
        $schema->create('sales_orders', static function (Blueprint $t): void {
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
        $schema->create('quotations', static function (Blueprint $t): void {
            $t->string('quotation_id')->primary();
            $t->string('opportunity_id');
            $t->string('quotation_number');
        });
        $schema->create('opportunities', static function (Blueprint $t): void {
            $t->string('opportunity_id')->primary();
            $t->string('customer_id');
        });
        $schema->create('customers', static function (Blueprint $t): void {
            $t->string('customer_id')->primary();
            $t->string('customer_name');
        });
        $this->seedSyntheticRows(405);
    }

    protected function tearDown(): void
    {
        if ($this->previousConnection !== null) {
            DB::setDefaultConnection($this->previousConnection);
            DB::disconnect(self::CONNECTION);
            DB::purge(self::CONNECTION);
            config()->set('database.connections.'.self::CONNECTION, $this->previousDefinition);
        }
        parent::tearDown();
    }

    private function seedSyntheticRows(int $count): void
    {
        $customers = $opportunities = $quotations = $orders = [];
        $db = DB::connection(self::CONNECTION);

        for ($i = 1; $i <= $count; $i++) {
            $id = str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $customers[] = [
                'customer_id' => 'c'.$id,
                'customer_name' => $i === 201 ? '=SUM(1,2)' : 'Synthetic Customer '.$id,
            ];
            $opportunities[] = ['opportunity_id' => 'o'.$id, 'customer_id' => 'c'.$id];
            $quotations[] = [
                'quotation_id' => 'q'.$id, 'opportunity_id' => 'o'.$id,
                'quotation_number' => 'Q-'.$id,
            ];
            $orders[] = [
                'sales_order_id' => 'so'.$id,
                'quotation_id' => 'q'.$id,
                'sales_order_number' => 'SO-'.$id,
                'order_date' => '2026-11-20',
                'delivery_date' => null,
                'status' => $i % 100 === 0 ? 'pending' : 'confirmed',
                'subtotal' => '120.00', 'tax' => '12.00', 'total_amount' => '132.00',
                'notes' => $i === 405 ? '@dangerous()' : 'Synthetic record',
                'created_at' => '2026-11-20 12:00:00',
                'updated_at' => '2026-11-20 12:00:00',
            ];
        }
        // Chunk inserts to respect SQLite's parameter limit and keep fixtures small.
        foreach (['customers' => $customers, 'opportunities' => $opportunities,
            'quotations' => $quotations, 'sales_orders' => $orders] as $table => $rows) {
            foreach (array_chunk($rows, 40) as $chunk) {
                $db->table($table)->insert($chunk);
            }
        }
        self::assertSame(405, $db->table('sales_orders')->count());
    }

    /** @return array{0: array<int,array<int,string|null>>, 1:int} */
    private function runCsv(array $filters): array
    {
        $db = DB::connection(self::CONNECTION);
        $db->flushQueryLog();
        $db->enableQueryLog();
        try {
            $response = app(SalesOrderController::class)->export(Request::create(
                '/sales-orders/export', 'GET', ['format' => 'csv'] + $filters
            ));
            self::assertInstanceOf(StreamedResponse::class, $response);
            ob_start();
            try {
                $response->sendContent();
                $contents = (string) ob_get_clean();
            } catch (\Throwable $exception) {
                ob_end_clean();
                throw $exception;
            }
            $queries = $db->getQueryLog();
        } finally {
            $db->disableQueryLog();
            $db->flushQueryLog();
        }

        $selects = 0;
        foreach ($queries as $query) {
            $sql = (string) ($query['query'] ?? '');
            self::assertDoesNotMatchRegularExpression(
                '/^\s*(?:insert|update|delete|alter|create|drop|truncate)\b/i',
                $sql, 'Export must never write to the SQLite fixture.'
            );
            if (preg_match('/^\s*select\b/i', $sql)) $selects++;
        }

        $handle = fopen('php://temp', 'w+');
        self::assertNotFalse($handle);
        fwrite($handle, $contents);
        rewind($handle);
        $rows = [];
        while (($fields = fgetcsv($handle)) !== false) $rows[] = $fields;
        fclose($handle);

        return [$rows, $selects];
    }

    public function test_405_rows_cross_three_batches_without_duplicates_or_n_plus_one(): void
    {
        [$rows, $selects] = $this->runCsv([]);
        self::assertCount(406, $rows, 'Header plus 405 rows expected.');
        self::assertGreaterThanOrEqual(10, $selects, 'Expected three batches and eager-loaded relations.');
        self::assertLessThanOrEqual(16, $selects, 'Excessive SELECTs indicate N+1 or unbounded query regression.');

        $identifiers = array_map(static fn (array $r): string => (string) $r[0], array_slice($rows, 1));
        self::assertCount(405, array_unique($identifiers), 'No duplicates between pages.');
        self::assertSame('SO-0405', $identifiers[0], 'Stable descending tie-break ordering.');
        self::assertSame('SO-0001', $identifiers[404]);
        self::assertSame('SO-0201', $identifiers[204], 'Middle batch boundary must not be skipped.');
        self::assertSame("'=SUM(1,2)", $rows[205][2], 'Middle-batch customer formula must be quoted.');
        self::assertSame("'@dangerous()", $rows[1][10], 'Notes formula must be quoted.');
        self::assertSame('132', (string) (float) $rows[1][9], 'Amounts remain numeric.');
        self::assertSame(405, DB::connection(self::CONNECTION)->table('sales_orders')->count());
    }

    public function test_filtered_export_maintains_expected_rows(): void
    {
        [$rows, $selects] = $this->runCsv(['status' => 'pending']);
        self::assertCount(5, $rows, 'Header plus four pending synthetic orders.');
        self::assertLessThanOrEqual(5, $selects, 'Filtered small export should load one batch.');
        $ids = array_map(static fn (array $r): string => (string) $r[0], array_slice($rows, 1));
        self::assertSame(['SO-0400','SO-0300','SO-0200','SO-0100'], $ids);
    }
}
