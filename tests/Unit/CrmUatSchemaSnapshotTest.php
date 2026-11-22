<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\CrmUatSchemaSnapshot as Snapshot;
use PHPUnit\Framework\TestCase;

/** All fixtures are synthetic in-memory objects. No Laravel/DB bootstrap. */
final class CrmUatSchemaSnapshotTest extends TestCase
{
    private function build(array $tables = ['customers', 'orders']): array
    {
        $tableRows = array_map(static fn (string $t): object => (object) ['table_name' => $t], $tables);
        return Snapshot::build($tableRows, [
            (object) ['table_name'=>'customers','column_name'=>'customer_id','ordinal'=>1,
                'data_type'=>'uuid','not_null'=>true,'has_default'=>true,'is_identity'=>false,'is_generated'=>false],
            (object) ['table_name'=>'orders','column_name'=>'order_id','ordinal'=>1,
                'data_type'=>'uuid','not_null'=>'t','has_default'=>'f','is_identity'=>'f','is_generated'=>'f'],
            (object) ['table_name'=>'orders','column_name'=>'customer_id','ordinal'=>2,
                'data_type'=>'uuid','not_null'=>'t','has_default'=>'f','is_identity'=>'f','is_generated'=>'f'],
        ], [
            (object) ['table_name'=>'orders','constraint_name'=>'orders_customer_fk',
                'constraint_type'=>'f','definition'=>'FOREIGN KEY (customer_id) REFERENCES customers(customer_id)'],
        ], [], []);
    }

    public function test_summary_and_required_insert_columns_are_derived_from_only_metadata(): void
    {
        $data = $this->build();
        self::assertSame(Snapshot::FORMAT, $data['format']);
        self::assertSame('public', $data['schema']);
        self::assertSame(2, $data['summary']['tables']);
        self::assertSame(3, $data['summary']['columns']);
        self::assertSame(2, $data['summary']['required_without_default']);
        self::assertSame(1, $data['summary']['foreign_keys']);
        self::assertSame([], Snapshot::missingTables($data, ['customers','orders']));
        self::assertSame(['missing'], Snapshot::missingTables($data, ['missing','customers']));
        self::assertArrayNotHasKey('database', $data);
        self::assertArrayNotHasKey('connection', $data);
    }

    public function test_fingerprint_is_repeatable_even_if_table_rows_change_order(): void
    {
        self::assertSame($this->build(['customers','orders'])['fingerprint_sha256'],
            $this->build(['orders','customers'])['fingerprint_sha256']);
        self::assertSame(64, strlen($this->build()['fingerprint_sha256']));
    }

    public function test_empty_catalog_and_unknown_column_fail_closed(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Snapshot::build([],[],[],[],[]);
    }

    public function test_orphan_column_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->build(['customers']);
    }

    public function test_unsafe_catalog_identifier_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->build(['customers', 'orders; DROP TABLE users']);
    }
}
