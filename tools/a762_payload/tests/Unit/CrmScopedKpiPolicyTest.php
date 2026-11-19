<?php

namespace Tests\Unit;

use App\Services\CrmKpiSourceReadiness;
use App\Services\CrmScopedKpiQuery;
use PHPUnit\Framework\TestCase;

final class CrmScopedKpiPolicyTest extends TestCase
{
    public function test_all_twelve_source_contracts_have_a_consistent_access_decision(): void
    {
        $sources = CrmKpiSourceReadiness::METRICS;
        self::assertCount(12, $sources);
        foreach ($sources as $source) {
            self::assertSame('restricted', CrmScopedKpiQuery::scopeDecision($source['table'], []));
            self::assertSame('all', CrmScopedKpiQuery::scopeDecision($source['table'], ['all']));
            self::assertSame('restricted', CrmScopedKpiQuery::scopeDecision($source['table'], ['team']));
        }
    }

    public function test_owned_and_assigned_scopes_work_only_for_supported_subjects(): void
    {
        foreach (['customers', 'leads', 'opportunities'] as $table) {
            self::assertSame('scoped', CrmScopedKpiQuery::scopeDecision($table, ['own']));
            self::assertSame('restricted', CrmScopedKpiQuery::scopeDecision($table, ['assigned']));
        }
        foreach (['service_tickets', 'installations'] as $table) {
            self::assertSame('scoped', CrmScopedKpiQuery::scopeDecision($table, ['assigned']));
            self::assertSame('restricted', CrmScopedKpiQuery::scopeDecision($table, ['own']));
        }
        foreach (['deliveries', 'inventories', 'invoices', 'warranties'] as $table) {
            self::assertSame('restricted', CrmScopedKpiQuery::scopeDecision($table, ['own', 'branch']));
        }
        self::assertSame('restricted', CrmScopedKpiQuery::scopeDecision('invoices', ['all', 'team']));
    }

    public function test_source_has_no_unscoped_fallback_or_mutation_statements(): void
    {
        $src = file_get_contents(__DIR__.'/../../app/Services/CrmScopedKpiQuery.php');
        self::assertStringContainsString("Auth::guard('web')->user()", $src);
        self::assertStringContainsString('CrmRoleAccessCatalog::PAGES', $src);
        self::assertStringContainsString('CrmKpiSourceReadiness::METRICS', $src);
        self::assertStringContainsString('whereColumn', $src);
        self::assertStringNotContainsString("->update(", $src);
        self::assertStringNotContainsString("->insert(", $src);
        self::assertStringNotContainsString("->delete(", $src);
        self::assertStringNotContainsString('customer_name', $src);
    }
}
