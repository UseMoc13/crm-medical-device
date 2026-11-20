<?php

namespace Tests\Unit;

use App\Support\CrmQueryPlanDigest;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class CrmQueryPlanDigestTest extends TestCase
{
    public function test_index_and_seq_nodes_are_counted_without_leaking_identifiers(): void
    {
        $raw = json_encode([['Plan' => [
            'Node Type'=>'Limit','Total Cost'=>10.231,'Plan Rows'=>20,
            'Plans'=>[['Node Type'=>'Index Scan','Relation Name'=>'sensitive_customer',
                'Index Name'=>'secret_idx','Filter'=>"email = 'hidden'"],
                ['Node Type'=>'Seq Scan','Relation Name'=>'other']]
        ]]], JSON_THROW_ON_ERROR);
        $summary = CrmQueryPlanDigest::summarize($raw);
        self::assertSame(1, $summary['index_scans']);
        self::assertSame(1, $summary['sequential_scans']);
        self::assertSame(['Limit','Index Scan','Seq Scan'], $summary['nodes']);
        self::assertSame(20, $summary['estimated_rows']);
        self::assertSame(10.23, $summary['cost']);
        self::assertStringNotContainsString('secret', json_encode($summary));
    }

    public function test_unknown_node_names_are_not_returned(): void
    {
        $value = CrmQueryPlanDigest::summarize('[{"Plan":{"Node Type":"Sensitive Table XYZ", "Total Cost":0}}]');
        self::assertSame(['Other'], $value['nodes']);
    }

    public function test_invalid_json_fails_closed(): void
    {
        $this->expectException(UnexpectedValueException::class);
        CrmQueryPlanDigest::summarize('{not valid}');
    }

    public function test_missing_plan_fails_closed(): void
    {
        $this->expectException(UnexpectedValueException::class);
        CrmQueryPlanDigest::summarize('[]');
    }

    public function test_repeated_node_types_are_unique_and_counts_preserved(): void
    {
        $summary = CrmQueryPlanDigest::summarize('[{"Plan":{"Node Type":"Append","Plans":[{"Node Type":"Seq Scan"},{"Node Type":"Seq Scan"}]}}]');
        self::assertSame(['Append','Seq Scan'], $summary['nodes']);
        self::assertSame(2, $summary['sequential_scans']);
    }
}
