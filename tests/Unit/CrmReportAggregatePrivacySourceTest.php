<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class CrmReportAggregatePrivacySourceTest extends TestCase
{
    public function test_each_report_business_group_has_server_side_query_gate(): void
    {
        $root = dirname(__DIR__, 2);
        $src = file_get_contents($root.'/app/Http/Controllers/ReportController.php');
        self::assertStringContainsString('$kpi = $canViewFinancial ?', $src);
        self::assertStringContainsString('$statusBreakdown = $canViewFinancial ?', $src);
        self::assertStringContainsString('$monthly = $canViewFinancial ?', $src);
        self::assertStringContainsString('$tickets = $canViewOperations ?', $src);
        self::assertStringContainsString('$maintenance = $canViewOperations ?', $src);
        self::assertStringContainsString('$pipeline = $canViewPipeline ?', $src);
        self::assertStringContainsString('$warranty = $canViewWarranty ?', $src);
        self::assertStringContainsString("$".'canViewReportDetails = $reportPrivacy[\'customer_product_details\'] && $canViewFinancial;', $src);
        self::assertStringContainsString("config('crm_reporting.report_aggregate_privacy_pilot_enabled'", $src);
    }

    public function test_blade_makes_denial_explicit_and_old_pilots_survive(): void
    {
        $root = dirname(__DIR__, 2);
        $view = file_get_contents($root.'/resources/views/reports/index.blade.php');
        foreach (['canViewFinancial','canViewOperations','canViewPipeline','canViewWarranty'] as $flag) {
            self::assertStringContainsString('@if($'.$flag.')', $view);
        }
        self::assertStringContainsString('restricted for this account.', $view);
        self::assertStringContainsString('@if($canViewReportDetails)', $view);
        self::assertStringContainsString('@if($canExportInvoiceCsv)', $view);
        $config = file_get_contents($root.'/config/crm_reporting.php');
        self::assertStringContainsString('CRM_REPORT_AGGREGATE_PRIVACY_PILOT_ENABLED', $config);
        self::assertStringContainsString('CRM_REPORT_DETAIL_PRIVACY_PILOT_ENABLED', $config);
        self::assertStringContainsString('CRM_REPORT_CSV_INVOICE_ALL_SCOPE_ENABLED', $config);
    }
}
