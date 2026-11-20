<?php
/**
 * A7.6.5.9 CRM Reporting Privacy Release Readiness Gate (standalone).
 *
 * READ-ONLY: does not bootstrap Laravel, connect to DB, read .env, spawn processes,
 * change role grants/feature flags, or write to repository. It inspects file names
 * and an operator-supplied console transcript of already-executed checks.
 *
 * Usage (from CRM root):
 *   php tools/a7659_release_gate.php --snapshot="C:\\path\\to\\results.txt"
 *   php tools/a7659_release_gate.php --snapshot="..." --json
 *
 * Exit codes: 0 = READY_FOR_MANUAL_REVIEW, 2 = HOLD_REVIEW_REQUIRED,
 *             3 = EVIDENCE_INCOMPLETE, 4 = SOURCE_OUTDATED, 64 = bad options.
 */

declare(strict_types=1);

final class A7659ReleaseGate
{
    public const REQUIRED_SOURCE = [
        'app/Http/Controllers/ReportController.php',
        'resources/views/reports/index.blade.php',
        'config/crm_reporting.php',
        'app/Services/CrmReportAggregatePrivacyPilot.php',
        'tests/Unit/CrmReportAggregatePrivacyPolicyTest.php',
        'tests/Unit/CrmReportAggregatePrivacySourceTest.php',
        'tests/Feature/CrmReportAggregatePrivacyIsolationTest.php',
        'tools/a7658_quick_check.php',
        'tools/a7658_runtime_preflight.php',
        'tools/a7658_install_state.json',
    ];

    public const REQUIRED_TESTS = [
        'CrmReportAggregatePrivacyPolicyTest',
        'CrmReportAggregatePrivacySourceTest',
        'CrmReportAggregatePrivacyIsolationTest',
        'CrmReportPrivacyPilotHttpTest',
        'CrmInvoiceCsvPilotHttpTest',
        'CrmMultiRoleReportsHttpIsolationTest',
        'CrmReportAuthorizationHttpTest',
    ];

    /** @return array<string,mixed> */
    public static function evaluate(string $root, ?string $snapshot): array
    {
        $sourceMissing = [];
        foreach (self::REQUIRED_SOURCE as $name) {
            if (!is_file(rtrim($root, '\\/').DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $name))) {
                $sourceMissing[] = $name;
            }
        }

        $out = [
            'stage' => 'A7.6.5.9',
            'status' => null,
            'source' => [
                'present' => count(self::REQUIRED_SOURCE) - count($sourceMissing),
                'required' => count(self::REQUIRED_SOURCE),
                'missing' => $sourceMissing,
            ],
            'evidence' => [],
            'blockers' => [],
            'notices' => [
                'Status READY hanya berarti layak untuk review manual; bukan izin menyalakan flag di production.',
                'Gate tidak melakukan query database, perubahan permission, maupun perubahan fitur.',
            ],
        ];

        if ($sourceMissing !== []) {
            $out['blockers'][] = 'Source proyek tidak cocok dengan A7.6.5.8; diperlukan kode versi terbaru.';
        }

        $evidenceMissing = [];
        $reviewBlockers = [];

        if ($snapshot === null) {
            $evidenceMissing[] = 'Transkrip pengujian belum disertakan (--snapshot=...).';
        } elseif (!is_file($snapshot) || !is_readable($snapshot)) {
            $evidenceMissing[] = 'Transkrip pengujian tidak ditemukan atau tidak dapat dibaca.';
        } else {
            $size = filesize($snapshot);
            // Limit copied/pasted logs to avoid unexpected multi-gigabyte reads.
            if ($size === false || $size > 8 * 1024 * 1024) {
                $evidenceMissing[] = 'Transkrip harus berukuran maksimal 8 MB.';
            } else {
                $raw = file_get_contents($snapshot);
                if ($raw === false) {
                    $evidenceMissing[] = 'Gagal membaca transkrip.';
                } else {
                    // Remove ANSI terminal codes and normalize line endings.
                    $raw = preg_replace('/\x1B\[[0-9;]*[A-Za-z]/', '', $raw) ?? $raw;
                    $raw = str_replace(["\r\n", "\r"], "\n", $raw);
                    self::parseEvidence($raw, $out['evidence'], $evidenceMissing, $reviewBlockers);
                }
            }
        }

        if ($sourceMissing !== []) {
            $out['status'] = 'SOURCE_OUTDATED';
        } elseif ($evidenceMissing !== []) {
            $out['status'] = 'EVIDENCE_INCOMPLETE';
        } elseif ($reviewBlockers !== []) {
            $out['status'] = 'HOLD_REVIEW_REQUIRED';
        } else {
            $out['status'] = 'READY_FOR_MANUAL_REVIEW';
        }
        $out['blockers'] = array_merge($out['blockers'], $evidenceMissing, $reviewBlockers);
        return $out;
    }

    /**
     * @param array<string,mixed> $evidence
     * @param list<string> $missing
     * @param list<string> $review
     */
    private static function parseEvidence(string $s, array &$evidence, array &$missing, array &$review): void
    {
        // A7.6.5.8 installer need not be rerun on every check. Its install manifest
        // is a REQUIRED_SOURCE file, checked before reading any evidence.
        $evidence['aggregate_install'] = true;
        if (preg_match('/(?:^|\n)A7659_EXIT_CODE=([1-9][0-9]*)\b/', $s)) {
            $review[] = 'Setidaknya satu pemeriksaan mengembalikan exit code nonzero.';
        }

        foreach (['static' => 'A7\.6\.5\.8 static', 'runtime' => 'A7\.6\.5\.8 runtime'] as $label => $prefix) {
            $regex = '/'. $prefix .'\s*:\s*(\d+)\s*\/\s*(\d+)\s+PASS\s*;\s*(\d+)\s+FAIL/i';
            $matches = [];
            if (preg_match_all($regex, $s, $matches, PREG_SET_ORDER) && $matches !== []) {
                $m = $matches[array_key_last($matches)];
                $done = (int) $m[1];
                $required = (int) $m[2];
                $failed = (int) $m[3];
                $evidence[$label] = ['passed' => $done, 'total' => $required, 'failed' => $failed];
                if ($required < ($label === 'static' ? 50 : 11) || $failed > 0 || $done !== $required) {
                    $review[] = 'A7.6.5.8 '.$label.' checks tidak seluruhnya PASS.';
                }
            } else {
                $missing[] = 'Ringkasan A7.6.5.8 '.$label.' checks tidak ditemukan.';
                $evidence[$label] = null;
            }
        }
        $evidence['flag_default_off'] = str_contains($s, '[PASS] Pilot flag default OFF');
        $evidence['flag_local_off'] = str_contains($s, '[PASS] Local aggregate pilot currently OFF');
        if (!$evidence['flag_default_off'] || !$evidence['flag_local_off']) {
            $missing[] = 'Bukti default dan local aggregate privacy flag OFF belum lengkap.';
        }

        $evidence['tests'] = [];
        foreach (self::REQUIRED_TESTS as $suite) {
            $result = self::extractTestOutcome($s, $suite);
            $evidence['tests'][$suite] = $result;
            if ($result === 'MISSING') {
                $missing[] = 'Hasil suite '.$suite.' tidak ditemukan.';
            } elseif ($result !== 'PASS') {
                $review[] = 'Suite '.$suite.' tidak PASS.';
            }
        }

        $role = self::extractCommandSection($s, 'A7.6.5.3 Financial Role Entitlement Review');
        $parity = self::extractCommandSection($s, 'A7.6.5.2 CRM Financial KPI & Scope Parity');
        $baseline = self::extractCommandSection($s, 'A7.6.1 CRM KPI Source Readiness');
        $integration = self::extractCommandSection($s, 'A7.5.4.1 CRM Integration Regression Gate');

        self::parseSummary($role, 'entitlements', ['Reports/no Invoice KPI', 'CSV/Invoice KPI mismatch'], $evidence, $missing, $review, ['CLEAR', 'OK', 'READY']);
        self::parseSummary($parity, 'parity', ['Failed', 'Policy reviews'], $evidence, $missing, $review, ['CLEAR', 'OK', 'READY', 'PASS']);
        self::parseSummary($baseline, 'kpi_sources', ['Missing', 'Query errors', 'Review'], $evidence, $missing, $review, ['SOURCE_READY']);
        self::parseSummary($integration, 'integration', ['Flagged', 'Query errors', 'Critical'], $evidence, $missing, $review, ['OK', 'READY', 'PASS', 'CLEAN']);

        // This is a known safety review, not permission to grant Invoice scope to roles.
        if ($role !== null && (str_contains($role, '[reports_csv_without_invoice_kpi] Finance') || str_contains($role, '[reports_csv_without_invoice_kpi] Management'))) {
            $review[] = 'Finance/Management masih memerlukan keputusan hak akses Invoice KPI dan CSV (jangan auto-grant).';
        }
        $known = [
            'delivery.dispatched_item_missing_warehouse',
            'delivery.stockout_quantity_mismatch',
            'installation.unknown_status',
            'billing.unknown_status',
        ];
        $evidence['integration_finding_codes'] = [];
        if ($integration !== null) {
            foreach ($known as $code) {
                if (str_contains($integration, '[FLAGGED] '.$code)) {
                    $evidence['integration_finding_codes'][] = $code;
                }
            }
        }
        if ($evidence['integration_finding_codes'] !== []) {
            $review[] = 'Masih ada '.count($evidence['integration_finding_codes']).' integration regression finding yang teridentifikasi.';
        }
    }

    private static function extractTestOutcome(string $log, string $suite): string
    {
        // Prefer the last occurrence, so an earlier PASS cannot override a later FAIL.
        $pattern = '/(?:^|\n)PS [^\n]*>\s*php artisan test --filter='.preg_quote($suite, '/').'\s*\n([\s\S]*?)(?=\nPS [^\n]*> |\z)/';
        $sections = [];
        if (!preg_match_all($pattern, $log, $sections, PREG_SET_ORDER) || $sections === []) {
            // Also support PowerShell transcripts that omit the interactive prompt.
            // Pick the LAST suite result to prevent stale successes masking failures.
            $loose = [];
            $suitePattern = '/\b(PASS|FAIL)\s+Tests\\\\(?:Unit|Feature)\\\\'.preg_quote($suite, '/').'\b/';
            if (!preg_match_all($suitePattern, $log, $loose, PREG_OFFSET_CAPTURE) || $loose[0] === []) {
                return 'MISSING';
            }
            $last = $loose[0][array_key_last($loose[0])];
            $body = substr($log, $last[1], 1600);
            if (str_starts_with($last[0], 'FAIL')) {
                return 'FAIL';
            }
            return preg_match('/Tests:\s*(\d+)\s+passed\b/', $body, $m) && (int)$m[1] > 0
                && !preg_match('/Tests:.*\bfailed\b/i', $body)
                ? 'PASS' : 'FAIL';
        }
        $body = $sections[array_key_last($sections)][1];
        if (preg_match('/\bFAIL\s+Tests\\\\(?:Unit|Feature)\\\\'.preg_quote($suite, '/').'\b/', $body)) {
            return 'FAIL';
        }
        if (preg_match('/\bPASS\s+Tests\\\\(?:Unit|Feature)\\\\'.preg_quote($suite, '/').'\b/', $body)
            && preg_match('/Tests:\s*(\d+)\s+passed\b/', $body, $m) && (int)$m[1] > 0
            && !preg_match('/Tests:.*\bfailed\b/i', $body)) {
            return 'PASS';
        }
        return 'FAIL';
    }

    private static function extractCommandSection(string $log, string $heading): ?string
    {
        $pos = strrpos($log, $heading);
        if ($pos === false) {
            return null;
        }
        $body = substr($log, $pos);
        $stop = strpos($body, "\nPS ");
        if ($stop !== false) {
            $body = substr($body, 0, $stop);
        }
        return $body;
    }

    /** @param list<string> $fields @param list<string> $statuses */
    private static function parseSummary(?string $section, string $label, array $fields,
        array &$evidence, array &$missing, array &$review, array $statuses): void
    {
        if ($section === null) {
            $missing[] = 'Ringkasan '.$label.' tidak ditemukan.';
            $evidence[$label] = null;
            return;
        }
        if (!preg_match('/\bStatus:\s*([A-Z_]+)/', $section, $m)) {
            $missing[] = 'Status '.$label.' tidak dapat dibaca.';
            $evidence[$label] = null;
            return;
        }
        $result = ['status' => $m[1]];
        foreach ($fields as $field) {
            if (preg_match('/'.preg_quote($field, '/').'\s*:?\s*(\d+)\b/', $section, $c)) {
                $result[$field] = (int)$c[1];
            } else {
                $missing[] = 'Kolom '.$label.' / '.$field.' tidak ditemukan.';
                $result[$field] = null;
            }
        }
        $evidence[$label] = $result;
        if (!in_array($result['status'], $statuses, true)) {
            $review[] = 'Status '.$label.' = '.$result['status'].'; memerlukan review.';
        }
        foreach ($fields as $field) {
            if ($result[$field] !== null && $result[$field] !== 0) {
                $review[] = 'Ringkasan '.$label.' / '.$field.' = '.$result[$field].' (harus nol).';
            }
        }
    }
}

/** @return array{root:string,snapshot:?string,json:bool,help:bool} */
function a7659_parse_arguments(array $argv): array
{
    $base = dirname(__DIR__);
    $args = ['root' => $base, 'snapshot' => null, 'json' => false, 'help' => false];
    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $args['help'] = true;
        } elseif ($argument === '--json') {
            $args['json'] = true;
        } elseif (str_starts_with($argument, '--root=')) {
            $args['root'] = substr($argument, 7);
        } elseif (str_starts_with($argument, '--snapshot=')) {
            $args['snapshot'] = substr($argument, 11);
        } else {
            throw new InvalidArgumentException('Parameter tidak dikenal: '.$argument);
        }
    }
    return $args;
}

function a7659_main(array $argv): int
{
    try {
        $opts = a7659_parse_arguments($argv);
    } catch (InvalidArgumentException $e) {
        fwrite(STDERR, '[ERROR] '.$e->getMessage()."\n");
        return 64;
    }
    if ($opts['help']) {
        echo "A7.6.5.9 Reporting Privacy Release Readiness Gate (read-only)\n";
        echo "php tools/a7659_release_gate.php --snapshot=PATH [--root=CRM_ROOT] [--json]\n";
        echo "EXIT 0 manual review ready; 2 hold; 3 incomplete evidence; 4 older source; 64 bad arguments.\n";
        return 0;
    }
    $result = A7659ReleaseGate::evaluate($opts['root'], $opts['snapshot']);
    if ($opts['json']) {
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
    } else {
        echo "A7.6.5.9 CRM Reporting Privacy Release Gate [READ-ONLY; NO DB]"."\n";
        echo 'Status: '.$result['status']."\n";
        echo 'Source: '.$result['source']['present'].'/'.$result['source']['required'].' required A7.6.5.8 files'."\n";
        $e = $result['evidence'];
        if (isset($e['static']) && is_array($e['static'])) {
            echo 'Static: '.$e['static']['passed'].'/'.$e['static']['total'].' PASS; '.$e['static']['failed'].' FAIL'."\n";
        }
        if (isset($e['runtime']) && is_array($e['runtime'])) {
            echo 'Runtime: '.$e['runtime']['passed'].'/'.$e['runtime']['total'].' PASS; '.$e['runtime']['failed'].' FAIL'."\n";
        }
        if (isset($e['tests'])) {
            $pass = count(array_filter($e['tests'], fn ($v) => $v === 'PASS'));
            echo 'Suites: '.$pass.'/'.count($e['tests']).' PASS'."\n";
        }
        foreach (['entitlements', 'parity', 'kpi_sources', 'integration'] as $label) {
            if (isset($e[$label])) {
                echo ucfirst($label).': '.$e[$label]['status']."\n";
            }
        }
        foreach (array_unique($result['blockers']) as $blocker) {
            echo '[HOLD] '.$blocker."\n";
        }
        if ($result['status'] === 'READY_FOR_MANUAL_REVIEW') {
            echo '[REVIEW] Bukti memenuhi kriteria gate; approval manusia dan testing end-to-end tetap wajib.' ."\n";
        }
        echo '[SAFE] No application changes, flag changes, grants, migrations or DB reads.' ."\n";
    }
    return match ($result['status']) {
        'READY_FOR_MANUAL_REVIEW' => 0,
        'HOLD_REVIEW_REQUIRED' => 2,
        'EVIDENCE_INCOMPLETE' => 3,
        'SOURCE_OUTDATED' => 4,
        default => 64,
    };
}

if (isset($_SERVER['SCRIPT_FILENAME']) && realpath((string) $_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    exit(a7659_main($argv));
}
