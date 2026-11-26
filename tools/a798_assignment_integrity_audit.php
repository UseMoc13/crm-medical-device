<?php
/** A7.9.8 read-only operational assignment audit. Never mutates database. */
declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$limit = 5;
foreach (array_slice($argv, 1) as $arg) {
    if (!preg_match('/^--limit=(\d{1,2})$/D', $arg, $m)) {
        fwrite(STDERR, "Usage: php tools/a798_assignment_integrity_audit.php [--limit=0..20]\n");
        exit(2);
    }
    $limit = (int) $m[1];
    if ($limit > 20) exit(2);
}

try {
    echo '[DATABASE] ' . \Illuminate\Support\Facades\DB::connection()->getDatabaseName() . PHP_EOL;
    $summary = app(\App\Services\CrmAssignmentIntegrityReport::class)->summary($limit);
    $total = 0;
    foreach ($summary as $row) {
        $total += $row['review'];
        echo sprintf('[MODULE] %s | required=%s | assigned=%d | unassigned=%d | review=%d',
            $row['module'], $row['purpose'], $row['assigned'], $row['unassigned'], $row['review']) . PHP_EOL;
        foreach ($row['examples'] as $example) {
            echo sprintf('  [REVIEW] record=%s | assigned=%s | user=%s | status=%s',
                $example['record_id'], $example['user_id'], $example['user_name'] ?? '(deleted)',
                $example['user_status'] ?? '(missing)') . PHP_EOL;
        }
    }
    echo '[TOTAL] assignments_needing_review=' . $total . PHP_EOL;
    echo '[RESULT] A798_READ_ONLY_AUDIT_COMPLETE' . PHP_EOL;
    echo '[NOTE] Review findings may be historical; no database rows were changed.' . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, '[FAIL] Read-only audit: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
