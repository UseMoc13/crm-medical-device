<?php
/**
 * A7.7.4.1 — surgical source patch, not an installer.
 * Run from VS Code PowerShell: php tools/a7741_apply_csv_patch.php --dry-run
 * Then explicitly: php tools/a7741_apply_csv_patch.php --apply
 * No Artisan bootstrap, DB connection, migrations, grants or SQL.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$mode = $argv[1] ?? '--dry-run';
if (!in_array($mode, ['--dry-run', '--apply'], true) || count($argv) !== 2) {
    fwrite(STDERR, "Usage: php tools/a7741_apply_csv_patch.php --dry-run|--apply\n");
    exit(2);
}

function stopA7741(string $message): never
{
    fwrite(STDERR, '[STOP] '.$message."\n");
    exit(2);
}

function phpSyntax(string $content): bool
{
    $temp = tempnam(sys_get_temp_dir(), 'crm7741_');
    if ($temp === false) stopA7741('Unable to allocate temporary syntax check file.');
    try {
        if (file_put_contents($temp, $content) !== strlen($content)) return false;
        $output = [];
        $exitCode = 1;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($temp).' 2>&1', $output, $exitCode);
        return $exitCode === 0;
    } finally {
        @unlink($temp);
    }
}

function replaceExactly(string $subject, string $old, string $new, string $label): string
{
    $count = substr_count($subject, $old);
    if ($count !== 1) stopA7741("Source anchor changed or ambiguous for {$label} ({$count} matches). Nothing changed.");
    return str_replace($old, $new, $subject);
}

$branch = trim((string) @shell_exec('git -C '.escapeshellarg($root).' branch --show-current 2>&1'));
// Cross-platform stderr capture; branch must match exactly.
if ($branch !== 'Big-Changes') stopA7741("Required branch Big-Changes; current=".($branch ?: '(unknown)').'.');

$reportPath = $root.'/app/Http/Controllers/ReportController.php';
$salesPath = $root.'/app/Http/Controllers/SalesOrderController.php';
$helperPath = $root.'/app/Support/CrmCsvCellSanitizer.php';
if (!is_file($helperPath)) stopA7741('Missing app/Support/CrmCsvCellSanitizer.php. Extract ZIP into Laravel root.');
$files = [$reportPath, $salesPath];
$originals = [];
$normalized = [];
$eol = [];
foreach ($files as $path) {
    if (!is_file($path)) stopA7741('Missing controller: '.basename($path));
    $bytes = file_get_contents($path);
    if ($bytes === false) stopA7741('Controller cannot be read: '.basename($path));
    $originals[$path] = $bytes;
    $eol[$path] = str_contains($bytes, "\r\n") ? "\r\n" : "\n";
    $normalized[$path] = str_replace("\r\n", "\n", $bytes);
}
$alreadyReport = str_contains($normalized[$reportPath], 'fputcsv($out, \\App\\Support\\CrmCsvCellSanitizer::row($fields, [0, 3, 4, 5]));');
$alreadySales = str_contains($normalized[$salesPath], 'fputcsv($out, \\App\\Support\\CrmCsvCellSanitizer::row($fields, [0, 1, 2, 5, 10]));');
if ($alreadyReport && $alreadySales) {
    echo "[PASS] Both CSV exports already use the shared sanitizer. No files changed.\n";
    exit(0);
}
if ($alreadyReport !== $alreadySales) stopA7741('Partial patch detected. Restore one consistent pair or review manually.');

$invoiceOld = <<<'ANCHOR'
            foreach ($rows->cursor() as $row) {
                fputcsv($out, [
                    $row->invoice_number,
                    $row->invoice_date,
                    $row->due_date,
                    $row->customer_name,
                    $row->sales_order_number,
                    $row->status,
                    $row->subtotal,
                    $row->discount,
                    $row->tax,
                    $row->total_amount,
                ]);
            }
ANCHOR;
$invoiceNew = <<<'REPLACEMENT'
            foreach ($rows->cursor() as $row) {
                $fields = [
                    $row->invoice_number,
                    $row->invoice_date,
                    $row->due_date,
                    $row->customer_name,
                    $row->sales_order_number,
                    $row->status,
                    $row->subtotal,
                    $row->discount,
                    $row->tax,
                    $row->total_amount,
                ];
                fputcsv($out, \App\Support\CrmCsvCellSanitizer::row($fields, [0, 3, 4, 5]));
            }
REPLACEMENT;
$salesOld = <<<'ANCHOR'
                    // Keep spreadsheet viewers from treating arbitrary database text as formulas.
                    foreach ([0, 1, 2, 5, 10] as $column) {
                        if (is_string($fields[$column]) && preg_match('/^[\x00-\x20]*[=+@\-]/u', $fields[$column])) {
                            $fields[$column] = "'" . $fields[$column];
                        }
                    }
                    fputcsv($out, $fields);
ANCHOR;
$salesNew = <<<'REPLACEMENT'
                    // Apply the same safe CSV encoding as the Invoice export.
                    // Only untrusted text is escaped; numeric values stay numeric.
                    fputcsv($out, \App\Support\CrmCsvCellSanitizer::row($fields, [0, 1, 2, 5, 10]));
REPLACEMENT;

$reportPatched = replaceExactly($normalized[$reportPath], $invoiceOld, $invoiceNew, 'Invoice export');
$salesPatched = replaceExactly($normalized[$salesPath], $salesOld, $salesNew, 'Sales Order export');
// Ensure we did not silently remove the original authorization/privacy behavior.
foreach (['$this->assertReportsView();', 'CrmInvoiceCsvPolicy::class', 'CrmReportingDataScope::class'] as $guard) {
    if (!str_contains($reportPatched, $guard)) stopA7741('Report authorization/scope guard not found: '.$guard);
}
foreach (['foreach ($query->cursor() as $order)', "'format' => ['required', 'in:pdf,csv']"] as $guard) {
    if (!str_contains($salesPatched, $guard)) stopA7741('Sales Order export contract changed: '.$guard);
}
$patches = [$reportPath=>$reportPatched, $salesPath=>$salesPatched];
foreach ($patches as $path => &$value) {
    if ($eol[$path] === "\r\n") $value = str_replace("\n", "\r\n", $value);
    if (!phpSyntax($value)) stopA7741('Patched PHP syntax invalid for '.basename($path));
}
unset($value);
if (!phpSyntax((string) file_get_contents($helperPath))) stopA7741('Sanitizer PHP syntax invalid.');
echo "[CHECK] Big-Changes; two exact source anchors verified.\n";
echo "[CHECK] Invoice and Sales Order CSV protected without changing SQL, permissions, or response headers.\n";
echo "[CHECK] All three PHP files pass syntax checks; CRLF preserved.\n";
if ($mode === '--dry-run') {
    echo "[PASS] Dry run. No application files or database rows changed.\n";
    exit(0);
}

// Use TEMP for backups to avoid accidentally committing originals or PII.
$backupDir = rtrim(sys_get_temp_dir(), '/\\').DIRECTORY_SEPARATOR.'crm_a7741_'.date('Ymd_His').'_'.bin2hex(random_bytes(3));
if (!mkdir($backupDir, 0700, true) && !is_dir($backupDir)) stopA7741('Backup directory could not be created.');
foreach ($originals as $path => $bytes) {
    if (file_put_contents($backupDir.DIRECTORY_SEPARATOR.basename($path), $bytes, LOCK_EX) !== strlen($bytes)) {
        stopA7741('Backup failed. No controller changed.');
    }
}
// Refuse a concurrent edit between preflight and write.
foreach ($originals as $path => $bytes) {
    if (file_get_contents($path) !== $bytes) stopA7741('Controller was modified during preflight; no files changed.');
}
$written = [];
try {
    foreach ($patches as $path => $bytes) {
        if (file_put_contents($path, $bytes, LOCK_EX) !== strlen($bytes)) throw new RuntimeException('Failed writing '.basename($path));
        $written[] = $path;
    }
} catch (Throwable $e) {
    foreach ($written as $path) @file_put_contents($path, $originals[$path], LOCK_EX);
    stopA7741('Write failed; rollback attempted. Verify backups in '.$backupDir);
}
echo "[PASS] A7.7.4.1 CSV export source files patched successfully.\n";
echo "[BACKUP] {$backupDir}\n";
echo "[SAFE] No DB access, schema modification, permission changes, or sensitive business output.\n";
echo "[NEXT] php tools/a7741_quick_check.php\n";
