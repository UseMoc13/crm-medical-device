<?php

declare(strict_types=1);
$root = dirname(__DIR__);
$files = [
    'app/Support/CrmQueryPlanDigest.php',
    'tools/a7751_pg_baseline.php',
    'tools/a7751_plan_smoke.php',
    'tests/Unit/CrmQueryPlanDigestTest.php',
    'tests/Unit/CrmPerformanceBaselineSourceTest.php',
];
$count = 0;
foreach ($files as $file) {
    $p = $root.'/'.$file;
    if (!is_file($p)) { fwrite(STDERR, "[FAIL] Missing {$file}\n"); exit(1); }
    echo "[PASS] Exists {$file}\n"; $count++;
    $output = []; $code = 0;
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($p).' 2>&1', $output, $code);
    if ($code !== 0) { fwrite(STDERR, "[FAIL] Syntax {$file}\n"); exit(1); }
    echo "[PASS] Syntax {$file}\n"; $count++;
}
$source = file_get_contents($root.'/tools/a7751_pg_baseline.php');
$checks = [
 'Explicit --run opt-in' => str_contains($source, "'--run'"),
 'Local APP_ENV' => str_contains($source, "environment('local')"),
 'PostgreSQL required' => str_contains($source, "'pgsql'"),
 'Only local DB hosts' => str_contains($source, "['localhost','127.0.0.1','::1']"),
 'Read-only PostgreSQL transaction' => str_contains($source, 'SET TRANSACTION READ ONLY'),
 'Statement timeout' => str_contains($source, 'statement_timeout'),
 'Explain JSON no analyze' => str_contains($source, 'EXPLAIN (FORMAT JSON)'),
 'Rollback in finally' => str_contains($source, 'finally'),
 'No data mutation calls' => preg_match('/\$connection->(?:insert|update|delete|unprepared)\s*\(/', $source) !== 1,
 'No EXPLAIN ANALYZE' => preg_match('/EXPLAIN\s*\(\s*ANALYZE\b/i', $source) !== 1,
];
foreach($checks as $name => $ok){
    if (!$ok) { fwrite(STDERR, "[FAIL] {$name}\n"); exit(1); }
    echo "[PASS] {$name}\n"; $count++;
}
echo "A7.7.5.1 static: {$count}/{$count} PASS. No DB access.\n";
