<?php
namespace Tests\Unit;
use App\Support\CrmIntegrationUatManifest;
use App\Support\CrmUatDataReadiness;
use PHPUnit\Framework\TestCase;

/** Pure unit assertions; no Laravel DB or CRM data required. */
final class CrmUatDataReadinessTest extends TestCase
{
    public function test_incomplete_sample_chains_are_not_misrepresented_as_uat_pass(): void
    {
        self::assertSame('PARTIAL_SAMPLE_DATA_REVIEW', CrmUatDataReadiness::summarize(
            ['customers'=>'HAS_SAMPLE','invoices'=>'EMPTY'], ['invoice_sales_chain'=>'NO_LINKED_SAMPLE']
        )['decision']);
        self::assertSame('SYNTHETIC_DATA_REQUIRED', CrmUatDataReadiness::summarize(
            ['customers'=>'EMPTY'], ['invoice_sales_chain'=>'NO_LINKED_SAMPLE']
        )['decision']);
        self::assertSame('HOLD_SCHEMA_GAPS', CrmUatDataReadiness::summarize(
            ['customers'=>'MISSING'], ['invoice_sales_chain'=>'BLOCKED_MISSING_TABLE']
        )['decision']);
        self::assertSame('DATA_COVERAGE_CANDIDATE_NOT_UAT_PASS', CrmUatDataReadiness::summarize(
            ['customers'=>'HAS_SAMPLE'], ['invoice_sales_chain'=>'LINKED_SAMPLE']
        )['decision']);
    }

    public function test_chain_sql_is_fixed_select_exists_and_only_uses_manifest_tables(): void
    {
        $allowed = [];
        foreach (CrmIntegrationUatManifest::schemaGroups() as $tables) {
            foreach ($tables as $name) $allowed[$name] = true;
        }
        $chains = CrmUatDataReadiness::chains();
        self::assertCount(7, $chains);
        foreach ($chains as $id => $item) {
            self::assertNotEmpty($item['tables'], $id);
            self::assertStringStartsWith('SELECT EXISTS (SELECT 1 FROM public.', $item['sql']);
            self::assertStringEndsWith(' LIMIT 1) AS found', $item['sql']);
            self::assertDoesNotMatchRegularExpression('/\\b(?:INSERT|UPDATE|DELETE|TRUNCATE|ALTER|DROP|CREATE|ANALYZE)\\b/i', $item['sql']);
            foreach ($item['tables'] as $table) self::assertArrayHasKey($table, $allowed);
        }
    }
}
