<?php

namespace Tests\Unit;

use App\Services\CrmIntegrationInvestigation;
use PHPUnit\Framework\TestCase;

final class CrmIntegrationInvestigationSourceTest extends TestCase
{
    public function test_status_classification_never_rewrites_unknown_business_states(): void
    {
        $statuses = ['Draft', 'Sent', 'Paid', 'Cancelled'];
        $this->assertSame('recognized', CrmIntegrationInvestigation::classifyStatus('Paid', $statuses)['classification']);
        $this->assertSame('case_or_outer_whitespace_only', CrmIntegrationInvestigation::classifyStatus(' paid ', $statuses)['classification']);
        $this->assertSame('Paid', CrmIntegrationInvestigation::classifyStatus(' paid ', $statuses)['canonical_if_format_only']);
        $this->assertSame('unrecognized_business_state', CrmIntegrationInvestigation::classifyStatus('Overdue', $statuses)['classification']);
        $this->assertNull(CrmIntegrationInvestigation::classifyStatus('Overdue', $statuses)['canonical_if_format_only']);
        $this->assertSame('null', CrmIntegrationInvestigation::classifyStatus(null, $statuses)['classification']);
    }

    public function test_scanner_has_no_mutation_operations_and_uses_existing_rules(): void
    {
        $source = file_get_contents(__DIR__ . '/../../app/Services/CrmIntegrationInvestigation.php');
        $this->assertNotFalse($source);
        foreach (['->insert(', '->update(', '->delete(', '->truncate(', '->save(', 'DB::statement(', 'Schema::create('] as $write) {
            $this->assertStringNotContainsString($write, $source);
        }
        $this->assertStringContainsString('$this->audit->definitions()', $source);
        $this->assertCount(4, CrmIntegrationInvestigation::TARGETS);
    }
}
