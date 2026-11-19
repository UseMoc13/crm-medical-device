<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Fixture-only reference arithmetic, deliberately not a production DB query. */
final class CrmFinancialParityContractTest extends TestCase
{
    public function test_synthetic_invoice_semantics_exclude_historical_states_and_other_periods(): void
    {
        $rows = [
            ['status' => 'Sent', 'date' => '2026-11-02', 'due' => '2026-11-08', 'amount' => 120],
            ['status' => 'Paid', 'date' => '2026-11-03', 'due' => '2026-11-08', 'amount' => 80],
            ['status' => 'Sent', 'date' => '2026-11-10', 'due' => '2026-12-08', 'amount' => 50],
            ['status' => 'Draft', 'date' => '2026-11-05', 'due' => '2026-11-07', 'amount' => 300],
            ['status' => 'Cancelled', 'date' => '2026-11-05', 'due' => '2026-11-07', 'amount' => 900],
            ['status' => 'unpaid', 'date' => '2026-11-05', 'due' => '2026-11-07', 'amount' => 400],
            ['status' => 'Paid', 'date' => '2026-10-31', 'due' => '2026-11-07', 'amount' => 700],
        ];
        $actual = ['invoiced' => 0, 'paid' => 0, 'outstanding' => 0, 'overdue' => 0];
        foreach ($rows as $row) {
            if ($row['date'] < '2026-11-01' || $row['date'] > '2026-11-30') continue;
            if (in_array($row['status'], ['Sent', 'Paid'], true)) $actual['invoiced'] += $row['amount'];
            if ($row['status'] === 'Paid') $actual['paid'] += $row['amount'];
            if ($row['status'] === 'Sent') {
                $actual['outstanding'] += $row['amount'];
                if ($row['due'] < '2026-11-19') $actual['overdue'] += $row['amount'];
            }
        }
        self::assertSame(['invoiced' => 250, 'paid' => 80, 'outstanding' => 170, 'overdue' => 120], $actual);
    }

    public function test_controller_and_kpi_queries_keep_distinct_permission_policies(): void
    {
        $base = dirname(__DIR__, 2);
        $reports = file_get_contents($base.'/app/Http/Controllers/ReportController.php');
        $dashboard = file_get_contents($base.'/app/Services/CrmScopedKpiQuery.php');
        self::assertIsString($reports);
        self::assertIsString($dashboard);
        self::assertSame(2, substr_count($reports, '$this->assertReportsView();'));
        self::assertStringContainsString('CrmReportingDataScope::class', $reports);
        self::assertStringContainsString('authorizeMetric($actor, $table, $permission)', $dashboard);
        self::assertStringContainsString("status IN ('Sent','Paid')", $reports);
        self::assertStringContainsString("status IN ('Sent','Paid')", $dashboard);
        self::assertStringContainsString("status = 'Sent' AND due_date < ?", $reports);
        self::assertStringContainsString("status = 'Sent' AND due_date < ?", $dashboard);
    }
}
