<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class CrmReportPrivacyPilotSourceTest extends TestCase
{
    public function test_both_top_lists_are_query_gated_before_sql_and_blade_is_gated(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root . '/app/Http/Controllers/ReportController.php');
        $blade = file_get_contents($root . '/resources/views/reports/index.blade.php');
        self::assertStringContainsString('$topCustomers = $canViewReportDetails ? app(', $controller);
        self::assertStringContainsString('$topProducts = $canViewReportDetails ? app(', $controller);
        // A7109_REV2: bound each assertion to its own protected query block.
        foreach ([
            ['/* ---------- Top customers ---------- */', '/* ---------- Top products ---------- */', '$topCustomers'],
            ['/* ---------- Top products ---------- */', '/* ---------- Service & Maintenance ---------- */', '$topProducts'],
        ] as [$startAnchor, $endAnchor, $variable]) {
            $start = strpos($controller, $startAnchor);
            $end = strpos($controller, $endAnchor);
            self::assertNotFalse($start, $variable . ' section start missing');
            self::assertNotFalse($end, $variable . ' section end missing');
            self::assertGreaterThan($start, $end, $variable . ' section boundaries reversed');
            $section = substr($controller, $start, $end - $start);
            self::assertStringContainsString($variable . ' = $canViewReportDetails ? app(', $section);
            self::assertSame(1, substr_count($section, '->get() : collect();'), $variable . ' must deny SQL with an empty collection');
        }
        self::assertSame(2, substr_count($blade, '@if($canViewReportDetails)'));
        self::assertSame(2, substr_count($blade, 'Invoice details restricted for this account.'));
        self::assertStringContainsString('@if($canExportInvoiceCsv)', $blade);
        self::assertStringContainsString('Export Invoices (CSV) — Restricted', $blade);
    }

    public function test_csv_direct_route_and_existing_report_aggregation_stay_protected(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root . '/app/Http/Controllers/ReportController.php');
        $config = file_get_contents($root . '/config/crm_reporting.php');
        self::assertSame(2, substr_count($controller, '$this->assertReportsView();'));
        self::assertStringContainsString("config('crm_reporting.report_customer_product_privacy_pilot_enabled', false)", $controller);
        self::assertStringContainsString("config('crm_reporting.csv_invoice_all_scope_pilot_enabled', false)", $controller);
        self::assertStringContainsString('CrmInvoiceCsvPolicy::class', $controller);
        self::assertStringContainsString("'report_customer_product_privacy_pilot_enabled' => (bool) env('CRM_REPORT_DETAIL_PRIVACY_PILOT_ENABLED', false)", $config);
        self::assertStringContainsString('COALESCE(SUM(total_amount) FILTER', $controller);
        self::assertStringContainsString('CrmReportingDataScope::class', $controller);
    }
}
