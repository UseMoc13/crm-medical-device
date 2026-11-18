<?php
/** A7.6.1 additive-only, content-aware installer; no migrations or SQL writes. */
$root = dirname(__DIR__);
$payload = __DIR__ . '/a761_payload';
$paths = [
    'app/Services/CrmKpiSourceReadiness.php',
    'app/Console/Commands/AuditCrmKpiSources.php',
    'tests/Unit/CrmKpiSourceCatalogTest.php',
    'tests/Feature/CrmKpiSourceBaselineCommandTest.php',
];
$dryRun = in_array('--dry-run', $argv, true);
$operations = [];
foreach ($paths as $relative) {
    $src = $payload . '/' . $relative;
    $dst = $root . '/' . $relative;
    if (!is_file($src)) {
        fwrite(STDERR, '[FAIL] Missing package file: ' . $relative . PHP_EOL);
        exit(1);
    }
    if (!is_file($dst)) {
        if (file_exists($dst)) {
            fwrite(STDERR, '[FAIL] Destination is not a regular file: '.$relative.PHP_EOL);
            exit(1);
        }
        $operations[] = ['add', $src, $dst, $relative];
    } elseif (hash_file('sha256', $src) === hash_file('sha256', $dst)) {
        $operations[] = ['same', $src, $dst, $relative];
    } else {
        fwrite(STDERR, '[FAIL] Refusing conflicting local file: '.$relative.PHP_EOL);
        exit(1);
    }
}
foreach ($operations as [$kind, $src, $dst, $relative]) {
    echo ($dryRun ? '[CHECK] ' : '[INFO] ') . ($kind === 'add' ? 'add: ' : 'already installed: ') . $relative . PHP_EOL;
}
if ($dryRun) {
    echo '[PASS] Dry run. No files or database changed.'.PHP_EOL;
    exit(0);
}
$added = [];
foreach ($operations as [$kind, $src, $dst, $relative]) {
    if ($kind !== 'add') continue;
    $parent = dirname($dst);
    if (!is_dir($parent) && !mkdir($parent, 0775, true) && !is_dir($parent)) {
        fwrite(STDERR, '[FAIL] Failed to create directory for '.$relative.PHP_EOL);
        foreach ($added as $path) @unlink($path);
        exit(1);
    }
    if (!copy($src, $dst) || hash_file('sha256', $src) !== hash_file('sha256', $dst)) {
        @unlink($dst);
        foreach ($added as $path) @unlink($path);
        fwrite(STDERR, '[FAIL] Failed copy/verification: '.$relative.PHP_EOL);
        exit(1);
    }
    $added[] = $dst;
    echo '[PASS] Added: '.$relative.PHP_EOL;
}
echo '[PASS] A7.6.1 installed: '.count($added).' new files; no existing files overwritten.'.PHP_EOL;
echo 'No migrations, business aggregates, database writes, or UI changes.'.PHP_EOL;
