<?php

declare(strict_types=1);

/** A7.7.5.2: patch one verified CSV export foreach; no Laravel bootstrap, no DB. */
function a7752Run(array $argv): int
{
    if (count($argv) !== 2 || !in_array($argv[1], ['--dry-run', '--apply'], true)) {
        fwrite(STDERR, "Usage: php tools/a7752_apply_sales_order_lazy_patch.php --dry-run|--apply\n");
        return 2;
    }

    $root = dirname(__DIR__);
    $target = $root . '/app/Http/Controllers/SalesOrderController.php';
    if (!is_file($target)) {
        fwrite(STDERR, "[HOLD] SalesOrderController.php is missing.\n");
        return 2;
    }

    $cmd = 'git -C ' . escapeshellarg($root) . ' branch --show-current 2>&1';
    $output = [];
    $exitCode = 0;
    exec($cmd, $output, $exitCode);
    if ($exitCode !== 0 || trim(implode("\n", $output)) !== 'Big-Changes') {
        fwrite(STDERR, "[HOLD] Required Git branch: Big-Changes. No changes made.\n");
        return 2;
    }

    $source = file_get_contents($target);
    if (!is_string($source)) {
        fwrite(STDERR, "[HOLD] Cannot read controller.\n");
        return 2;
    }

    $old = 'foreach ($query->cursor() as $order) {';
    $new = "foreach (\$query->orderByDesc('sales_order_id')->lazy(200) as \$order) {";
    if (substr_count($source, $new) === 1 && substr_count($source, $old) === 0) {
        fwrite(STDOUT, "[PASS] Already applied. No application files changed.\n");
        return 0;
    }

    // No rewriting if controllers have drifted from the checked A7.7.4.1 layout.
    $required = [
        "SalesOrder::with(['quotation.opportunity.customer'])",
        'if ($format === \'csv\')',
        'CrmCsvCellSanitizer::row($fields, [0, 1, 2, 5, 10])',
        "'format' => ['required', 'in:pdf,csv']",
        '$orders = $query->limit(2000)->get();',
    ];
    foreach ($required as $fragment) {
        if (!str_contains($source, $fragment)) {
            fwrite(STDERR, "[HOLD] Controller layout differs from checked source. No changes made.\n");
            return 2;
        }
    }
    if (substr_count($source, $old) !== 1 || substr_count($source, $new) !== 0) {
        fwrite(STDERR, "[HOLD] Export cursor anchor missing or ambiguous. No changes made.\n");
        return 2;
    }

    $candidate = str_replace($old, $new, $source);
    if (substr_count($candidate, $new) !== 1 || strlen($candidate) === 0) {
        fwrite(STDERR, "[HOLD] Unexpected patch output.\n");
        return 2;
    }
    $temp = tempnam(sys_get_temp_dir(), 'crm_a7752_lint_');
    if ($temp === false) {
        fwrite(STDERR, "[HOLD] Cannot allocate temporary lint file.\n");
        return 2;
    }

    try {
        if (file_put_contents($temp, $candidate, LOCK_EX) !== strlen($candidate)) {
            fwrite(STDERR, "[HOLD] Cannot stage candidate.\n");
            return 2;
        }
        $lintOutput = [];
        $lintStatus = 0;
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($temp) . ' 2>&1', $lintOutput, $lintStatus);
        if ($lintStatus !== 0) {
            fwrite(STDERR, "[HOLD] Staged PHP syntax validation failed.\n");
            return 2;
        }
        fwrite(STDOUT, "[CHECK] Branch Big-Changes, exact CSV anchor and PHP syntax verified.\n");
        fwrite(STDOUT, "[CHECK] Only the CSV iterator changes; CSV sanitizer, fields, filters, routes and permissions retained.\n");

        if ($argv[1] === '--dry-run') {
            fwrite(STDOUT, "[PASS] Dry run; no application or database files changed.\n");
            return 0;
        }

        // Compare the bytes immediately before writing to detect concurrent edits.
        if (file_get_contents($target) !== $source) {
            fwrite(STDERR, "[HOLD] Controller changed during staging. No changes made.\n");
            return 2;
        }
        $backupDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'crm_a7752_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
        if (!mkdir($backupDir, 0700, true) || !copy($target, $backupDir . DIRECTORY_SEPARATOR . 'SalesOrderController.php')) {
            fwrite(STDERR, "[HOLD] Cannot preserve source backup. No changes made.\n");
            return 2;
        }
        $written = file_put_contents($target, $candidate, LOCK_EX);
        if ($written !== strlen($candidate)) {
            // Best-effort restoration from the safely preserved source.
            copy($backupDir . DIRECTORY_SEPARATOR . 'SalesOrderController.php', $target);
            fwrite(STDERR, "[HOLD] Write incomplete. Original restored from backup where possible.\n");
            return 2;
        }
        fwrite(STDOUT, "[PASS] A7.7.5.2 CSV iterator patched successfully.\n");
        fwrite(STDOUT, "[BACKUP] " . $backupDir . "\n");
        fwrite(STDOUT, "[SAFE] No DB connections, migrations or business file deletions.\n");
        return 0;
    } finally {
        // Never exit inside the try block: PHP exit() bypasses finally.
        @unlink($temp);
    }
}

exit(a7752Run($argv));
