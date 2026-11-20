<?php
/**
 * A7.6.5.10 - Read-only HOLD triage using existing CRM Artisan audit commands.
 * Does not alter the CRM application, grant permissions, or change business data.
 * It DOES run existing read-only audit commands which may read DB metadata and rows.
 * Only aggregate counts/rule codes appear in the generated summary, no UUIDs, raw
 * invoice values, customer names, status free text or SQL statements.
 *
 * Usage: php tools/a76510_hold_triage.php [--json]
 */
declare(strict_types=1);

final class A76510HoldTriage
{
    public const TARGETS = [
        'delivery.dispatched_item_missing_warehouse',
        'delivery.stockout_quantity_mismatch',
        'installation.unknown_status',
        'billing.unknown_status',
    ];

    public const COMMANDS = [
        'entitlements' => 'crm:reporting:role-entitlements',
        'parity' => 'crm:reporting:parity-baseline',
        'integration' => 'crm:integration:regression',
        'remediation' => 'crm:integration:remediation-review',
    ];

    public const STAGES = [
        'entitlements' => 'A7.6.5.3',
        'parity' => 'A7.6.5.2',
        'integration' => 'A7.5.4.1',
        'remediation' => 'A7.5.2.2c',
    ];

    /** @return array{exit:int,payload:?array} */
    public static function collect(string $root, string $name): array
    {
        $command = self::COMMANDS[$name] ?? null;
        if ($command === null || !is_file($root.DIRECTORY_SEPARATOR.'artisan')) {
            return ['exit' => -1, 'payload' => null];
        }
        $parts = [PHP_BINARY, $root.DIRECTORY_SEPARATOR.'artisan', $command, '--json'];
        $pipes = [];
        $process = @proc_open($parts, [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes, $root, null, ['bypass_shell' => true]);
        if (!is_resource($process)) return ['exit' => -1, 'payload' => null];
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]); // intentionally never echoed
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);
        unset($error);
        if ($exit !== 0 || !is_string($output) || strlen($output) > 1048576) {
            return ['exit' => $exit, 'payload' => null];
        }
        try {
            $value = json_decode(trim($output), true, 128, JSON_THROW_ON_ERROR);
            return ['exit' => $exit, 'payload' => is_array($value) ? $value : null];
        } catch (JsonException) {
            return ['exit' => $exit, 'payload' => null];
        }
    }

    /**
     * Pure, DB-free reducer; accepts only the safe fields from known audit commands.
     * @param array<string,array{exit:int,payload:?array}> $reports
     * @return array<string,mixed>
     */
    public static function assess(array $reports): array
    {
        $missing = [];
        $normalized = [];
        foreach (self::COMMANDS as $name => $_command) {
            $item = $reports[$name] ?? null;
            if (!is_array($item) || ($item['exit'] ?? -1) !== 0
                || !is_array($item['payload'] ?? null)
                || ($item['payload']['stage'] ?? null) !== self::STAGES[$name]
                || !is_array($item['payload']['summary'] ?? null)) {
                $missing[] = $name;
            } else {
                $normalized[$name] = $item['payload'];
            }
        }
        $out = [
            'stage' => 'A7.6.5.10',
            'status' => 'EVIDENCE_INCOMPLETE',
            'source' => 'Existing CRM Artisan read-only audits (Big-Changes)',
            'commands_checked' => count($normalized),
            'commands_required' => count(self::COMMANDS),
            'unavailable' => $missing,
            'entitlements' => null,
            'parity' => null,
            'integration' => null,
            'findings' => [],
            'review_decisions' => [
                'Finance: approve a reporting visibility and export policy; do not grant Invoice all automatically.',
                'Management: decide no financial data, aggregate-only design, or explicitly authorized full Invoice access.',
                'Delivery: confirm original dispatch warehouse and stock movement provenance before any correction.',
                'Installation: compare delivery/checklist/serial state before approving a status normalization.',
                'Billing: confirm meaning of legacy invoice state and payment history; never guess Paid/Sent/Draft.',
            ],
            'no_automatic_changes' => true,
            'production_activation_authorized' => false,
        ];
        if ($missing !== []) return $out;

        $role = $normalized['entitlements'];
        $parity = $normalized['parity'];
        $integration = $normalized['integration'];
        $remediation = $normalized['remediation'];
        $roleSummary = $role['summary'];
        $paritySummary = $parity['summary'];
        $intSummary = $integration['summary'];
        $remSummary = $remediation['summary'];

        $out['entitlements'] = [
            'status' => self::safeStatus($role['status'] ?? null),
            'reports_without_invoice_kpi' => self::nonnegative($roleSummary['reports_without_invoice_kpi'] ?? null),
            'csv_without_invoice_kpi' => self::nonnegative($roleSummary['csv_without_invoice_kpi'] ?? null),
        ];
        $out['parity'] = [
            'status' => self::safeStatus($parity['status'] ?? null),
            'policy_reviews' => self::nonnegative($paritySummary['policy_reviews'] ?? null),
            'failed_checks' => self::nonnegative($paritySummary['failed'] ?? null),
        ];
        $out['integration'] = [
            'status' => self::safeStatus($integration['status'] ?? null),
            'flagged' => self::nonnegative($intSummary['audit_flagged'] ?? null),
            'critical' => self::nonnegative($intSummary['critical_findings'] ?? null),
            'query_errors' => self::nonnegative($intSummary['query_errors_across_reports'] ?? null),
            'scanner_disagreements' => self::nonnegative($intSummary['inconsistent_rules'] ?? null),
            'shared_delivery_records' => self::nonnegative($intSummary['shared_delivery_records'] ?? null),
        ];
        $targetMap = [];
        foreach (($integration['targets'] ?? []) as $target) {
            if (is_array($target) && in_array($target['rule'] ?? null, self::TARGETS, true)) {
                $targetMap[$target['rule']] = $target;
            }
        }
        $reviewMap = [];
        foreach (($remediation['cases'] ?? []) as $case) {
            if (is_array($case) && in_array($case['id'] ?? null, self::TARGETS, true)) {
                $reviewMap[$case['id']] = $case;
            }
        }
        $diverged = false;
        foreach (self::TARGETS as $rule) {
            $target = $targetMap[$rule] ?? null;
            $case = $reviewMap[$rule] ?? null;
            $validTarget = is_array($target) && isset($target['status'], $target['count']);
            $validCase = is_array($case) && isset($case['status'], $case['count']);
            if (!$validTarget || !$validCase) {
                $diverged = true;
                $out['findings'][] = ['rule' => $rule, 'status' => 'EVIDENCE_MISSING', 'count' => null];
                continue;
            }
            $same = ($target['status'] === $case['status']) && ((int) $target['count'] === (int) $case['count']);
            if (!$same) $diverged = true;
            $out['findings'][] = [
                'rule' => $rule,
                'status' => $same ? self::safeStatus($case['status']) : 'SCANNER_DISAGREEMENT',
                'count' => $same ? self::nonnegative($case['count']) : null,
            ];
        }
        $badCounts = $out['entitlements']['reports_without_invoice_kpi'] === null
            || $out['entitlements']['csv_without_invoice_kpi'] === null
            || $out['parity']['policy_reviews'] === null
            || $out['parity']['failed_checks'] === null
            || $out['integration']['flagged'] === null
            || $out['integration']['critical'] === null
            || $out['integration']['query_errors'] === null
            || $out['integration']['scanner_disagreements'] === null
            || !isset($remSummary['checked'], $remSummary['errors'], $remSummary['skipped']);
        if ($badCounts) {
            $out['status'] = 'EVIDENCE_INCOMPLETE';
        } elseif ($diverged || $out['integration']['scanner_disagreements'] > 0 || $out['integration']['query_errors'] > 0) {
            $out['status'] = 'SCAN_DISAGREEMENT';
        } elseif ($out['entitlements']['status'] !== 'ELIGIBLE_FOR_NUMERIC_COMPARISON'
            || $out['entitlements']['reports_without_invoice_kpi'] > 0
            || $out['entitlements']['csv_without_invoice_kpi'] > 0
            || $out['parity']['status'] === 'REVIEW_REQUIRED'
            || $out['parity']['policy_reviews'] > 0
            || $out['parity']['failed_checks'] > 0
            || $out['integration']['status'] !== 'READY'
            || $out['integration']['flagged'] > 0
            || $out['integration']['critical'] > 0
            || (int) $remSummary['errors'] > 0
            || (int) $remSummary['skipped'] > 0) {
            $out['status'] = 'HOLD_REVIEW_REQUIRED';
        } else {
            $out['status'] = 'ELIGIBLE_FOR_SEPARATE_MANUAL_REVIEW';
        }
        return $out;
    }

    private static function safeStatus(mixed $value): string
    {
        $s = is_string($value) ? strtoupper($value) : '';
        return preg_match('/^[A-Z_]{2,45}$/', $s) ? $s : 'UNKNOWN';
    }

    private static function nonnegative(mixed $value): ?int
    {
        return (is_int($value) && $value >= 0) ? $value : null;
    }

    public static function display(array $report): void
    {
        echo "A7.6.5.10 HOLD Triage [READ-ONLY; existing Artisan scanners may READ business data]\n";
        echo 'Status: '.$report['status'].' | Commands '.$report['commands_checked'].'/'.$report['commands_required']."\n";
        if ($report['unavailable']) echo '[INCOMPLETE] Unavailable: '.implode(', ', $report['unavailable'])."\n";
        foreach (['entitlements', 'parity', 'integration'] as $key) {
            if (!is_array($report[$key])) continue;
            echo strtoupper($key).': '.implode(' | ', array_map(
                static fn ($k, $v): string => $k.'='.($v === null ? 'unknown' : (string) $v),
                array_keys($report[$key]), array_values($report[$key])
            ))."\n";
        }
        foreach ($report['findings'] as $case) {
            echo '['.$case['status'].'] '.$case['rule'].' ('.($case['count'] ?? 'unknown').")\n";
        }
        echo "Next: collect original business evidence and stakeholder policy decisions; no auto-grants or data fixes.\n";
        echo "Do not enable the aggregate privacy pilot based on this diagnostic alone.\n";
    }
}

if (isset($_SERVER['SCRIPT_FILENAME']) && realpath((string) $_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $args = array_slice($argv, 1);
    if (count(array_diff($args, ['--json'])) > 0 || count($args) > 1) {
        fwrite(STDERR, "Usage: php tools/a76510_hold_triage.php [--json]\n");
        exit(64);
    }
    $root = dirname(__DIR__);
    if (!is_file($root.'/artisan')) {
        fwrite(STDERR, "CRM Laravel root not found; put this file under tools/.\n");
        exit(64);
    }
    $reports = [];
    foreach (A76510HoldTriage::COMMANDS as $name => $command) {
        if (!in_array('--json', $args, true)) fwrite(STDERR, '[RUN] '.$command."\n");
        $reports[$name] = A76510HoldTriage::collect($root, $name);
    }
    $out = A76510HoldTriage::assess($reports);
    if (in_array('--json', $args, true)) {
        echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    } else A76510HoldTriage::display($out);
    exit($out['status'] === 'ELIGIBLE_FOR_SEPARATE_MANUAL_REVIEW' ? 0 : 2);
}
