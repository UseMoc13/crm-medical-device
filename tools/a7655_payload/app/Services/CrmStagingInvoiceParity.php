<?php

namespace App\Services;

use Illuminate\Database\Connection;
use InvalidArgumentException;
use RuntimeException;

/**
 * A7.6.5.5: compares independent Report FILTER, Dashboard CASE, and CSV-join
 * aggregate query shapes. It returns match flags only, never invoice amounts.
 *
 * This is deliberately an UNSCOPED formula/SQL check, not a role-access test.
 * The Artisan entry point restricts actual PostgreSQL reads to explicit staging.
 */
final class CrmStagingInvoiceParity
{
    public const METRICS = ['invoiced', 'paid', 'outstanding', 'overdue'];

    /** Deterministic, pure policy gate. ALL conditions are necessary. */
    public static function decision(
        string $environment,
        string $driver,
        bool $enabled,
        bool $confirmed,
        bool $sourceContractPassed,
        bool $downstreamSalesScopeEnabled,
    ): array {
        $reasons = [];
        if ($environment !== 'staging') $reasons[] = 'APP_ENV must be staging';
        if ($driver !== 'pgsql') $reasons[] = 'DB driver must be pgsql';
        if (!$enabled) $reasons[] = 'CRM_A7655_STAGING_PARITY_ENABLED must be true';
        if (!$confirmed) $reasons[] = '--confirm-staging is required';
        if (!$sourceContractPassed) $reasons[] = 'A7.6.5.2 source contracts drifted';
        if ($downstreamSalesScopeEnabled) $reasons[] = 'opt-in Sales/Customer row scope is active; unscoped comparison is not valid';
        return ['allowed' => $reasons === [], 'reasons' => $reasons];
    }

    /** Normalize DB NUMERIC values WITHOUT float conversion. */
    public static function decimalString(string|int|float|null $value): string
    {
        $raw = trim((string) ($value ?? '0'));
        if (!preg_match('/\A([+-]?)(\d+)(?:\.(\d+))?\z/D', $raw, $m)) {
            throw new InvalidArgumentException('Unsupported SQL numeric representation.');
        }
        $whole = ltrim($m[2], '0');
        $whole = $whole === '' ? '0' : $whole;
        $fraction = rtrim($m[3] ?? '', '0');
        if ($whole === '0' && $fraction === '') return '0';
        return (($m[1] ?? '') === '-' ? '-' : '') . $whole . ($fraction !== '' ? '.' . $fraction : '');
    }

    /**
     * Use a caller-selected DB connection, allowing disposable SQLite fixtures.
     * The CLI owns the read-only PostgreSQL transaction; this method never writes.
     * @return array<string,mixed> Booleans/metadata only; no raw values or row IDs.
     */
    public function compare(Connection $db, string $from, string $to, string $asOf): array
    {
        foreach ([$from, $to, $asOf] as $date) {
            if (!self::validDate($date)) throw new InvalidArgumentException('Invalid YYYY-MM-DD date.');
        }
        if ($from > $to) throw new InvalidArgumentException('From date is after to date.');

        $schema = $db->getSchemaBuilder();
        $required = [
            'invoices' => ['sales_order_id', 'invoice_date', 'due_date', 'status', 'total_amount'],
            'sales_orders' => ['sales_order_id', 'quotation_id'],
            'quotations' => ['quotation_id', 'opportunity_id'],
            'opportunities' => ['opportunity_id', 'customer_id'],
            'customers' => ['customer_id'],
        ];
        foreach ($required as $table => $columns) {
            if (!$schema->hasTable($table)) throw new RuntimeException('Required staging schema unavailable.');
            foreach ($columns as $column) {
                if (!$schema->hasColumn($table, $column)) throw new RuntimeException('Required staging schema unavailable.');
            }
        }

        // Independent Report SQL semantics: PostgreSQL aggregate FILTER.
        $report = $db->table('invoices')
            ->whereDate('invoice_date', '>=', $from)
            ->whereDate('invoice_date', '<=', $to)
            ->selectRaw("COUNT(*) AS total_rows,
                COALESCE(SUM(total_amount) FILTER (WHERE status IN ('Sent','Paid')), 0) AS invoiced,
                COALESCE(SUM(total_amount) FILTER (WHERE status = 'Paid'), 0) AS paid,
                COALESCE(SUM(total_amount) FILTER (WHERE status = 'Sent'), 0) AS outstanding,
                COALESCE(SUM(total_amount) FILTER (WHERE status = 'Sent' AND due_date < ?), 0) AS overdue", [$asOf])
            ->first();

        // Independent Dashboard KPI SQL semantics: CASE expressions.
        $dashboard = $db->table('invoices')
            ->whereDate('invoice_date', '>=', $from)
            ->whereDate('invoice_date', '<=', $to)
            ->selectRaw("COALESCE(SUM(CASE WHEN status IN ('Sent','Paid') THEN total_amount ELSE 0 END),0) AS invoiced,
                COALESCE(SUM(CASE WHEN status = 'Paid' THEN total_amount ELSE 0 END),0) AS paid,
                COALESCE(SUM(CASE WHEN status = 'Sent' THEN total_amount ELSE 0 END),0) AS outstanding,
                COALESCE(SUM(CASE WHEN status = 'Sent' AND due_date < ? THEN total_amount ELSE 0 END),0) AS overdue", [$asOf])
            ->first();

        // Invoice export is a LEFT-JOIN query and intentionally includes ALL statuses.
        // Aggregate its row shape only; never stream or return any individual row.
        $csv = $db->table('invoices as i')
            ->leftJoin('sales_orders as so', 'so.sales_order_id', '=', 'i.sales_order_id')
            ->leftJoin('quotations as q', 'q.quotation_id', '=', 'so.quotation_id')
            ->leftJoin('opportunities as o', 'o.opportunity_id', '=', 'q.opportunity_id')
            ->leftJoin('customers as c', 'c.customer_id', '=', 'o.customer_id')
            ->whereDate('i.invoice_date', '>=', $from)
            ->whereDate('i.invoice_date', '<=', $to)
            ->selectRaw("COUNT(*) AS total_rows,
                COALESCE(SUM(CASE WHEN i.status IN ('Sent','Paid') THEN i.total_amount ELSE 0 END),0) AS invoiced,
                COALESCE(SUM(CASE WHEN i.status = 'Paid' THEN i.total_amount ELSE 0 END),0) AS paid,
                COALESCE(SUM(CASE WHEN i.status = 'Sent' THEN i.total_amount ELSE 0 END),0) AS outstanding,
                COALESCE(SUM(CASE WHEN i.status = 'Sent' AND i.due_date < ? THEN i.total_amount ELSE 0 END),0) AS overdue", [$asOf])
            ->first();

        $checks = [];
        foreach (self::METRICS as $metric) {
            $r = self::decimalString($report->{$metric} ?? null);
            $checks['reports_vs_dashboard_' . $metric] = $r === self::decimalString($dashboard->{$metric} ?? null);
            $checks['reports_vs_csv_join_' . $metric] = $r === self::decimalString($csv->{$metric} ?? null);
        }
        $checks['csv_left_join_preserves_invoice_rows'] =
            (string) ($report->total_rows ?? '-1') === (string) ($csv->total_rows ?? '-2');
        $failures = array_keys(array_filter($checks, static fn (bool $passed): bool => !$passed));

        return [
            'stage' => 'A7.6.5.5',
            'status' => $failures === [] ? 'UNSCOPED_FORMULA_PARITY' : 'NUMERIC_MISMATCH',
            'read_only' => true,
            'contains_financial_amounts' => false,
            'contains_identifiers' => false,
            'period' => ['from' => $from, 'to' => $to, 'as_of' => $asOf],
            'checks' => array_map(
                static fn (string $key, bool $passed): array => ['id' => $key, 'status' => $passed ? 'pass' : 'fail'],
                array_keys($checks), array_values($checks),
            ),
            'summary' => ['total' => count($checks), 'passed' => count($checks) - count($failures), 'failed' => count($failures)],
            'row_scope_parity_verified' => false,
            'authenticated_viewer_parity_verified' => false,
            'csv_download_verified' => false,
            'note' => 'Unscoped staging formula/LEFT JOIN comparison only. Role and downstream-scope entitlement reviews remain open.',
        ];
    }

    public static function validDate(string $date): bool
    {
        if (!preg_match('/\A\d{4}-\d{2}-\d{2}\z/D', $date)) return false;
        [$y, $m, $d] = array_map('intval', explode('-', $date));
        return checkdate($m, $d, $y);
    }
}
