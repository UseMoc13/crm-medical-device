<?php

declare(strict_types=1);

// A7.7.5: PostgreSQL statistic inventory. Never mutate tables or run ANALYZE/EXPLAIN ANALYZE.
$root = dirname(__DIR__);
if (!is_file($root . '/bootstrap/app.php') || !is_file($root . '/vendor/autoload.php')) {
    fwrite(STDERR, "[ERROR] Run from the CRM Laravel project root; vendor/autoload.php required.\n");
    exit(2);
}
$limit = 15;
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--limit=([0-9]{1,2})$/D', $arg, $m) && (int)$m[1] >= 1 && (int)$m[1] <= 50) {
        $limit = (int)$m[1];
    } else {
        fwrite(STDERR, "Usage: php tools/a775_pg_performance.php [--limit=1..50]\n");
        exit(2);
    }
}
require $root . '/vendor/autoload.php';
require __DIR__ . '/a775_performance_policy.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!$app->environment('local')) {
    fwrite(STDERR, "[DENIED] APP_ENV must be local. No database reads performed.\n");
    exit(3);
}
if (Illuminate\Support\Facades\DB::connection()->getDriverName() !== 'pgsql') {
    fwrite(STDERR, "[DENIED] PostgreSQL connection required. No database reads performed.\n");
    exit(3);
}
$conn = Illuminate\Support\Facades\DB::connection();
$began = false;
$failed = false;
try {
    $conn->beginTransaction();
    $began = true;
    $conn->statement('SET TRANSACTION READ ONLY');
    $rows = $conn->select(<<<'SQL'
        SELECT s.schemaname, s.relname AS table_name,
            COALESCE(s.n_live_tup,0) AS live_rows,
            COALESCE(s.n_dead_tup,0) AS dead_rows,
            COALESCE(s.seq_scan,0) AS seq_scans,
            COALESCE(s.idx_scan,0) AS index_scans,
            COALESCE(ix.index_count,0) AS index_count
        FROM pg_stat_user_tables s
        LEFT JOIN (
            SELECT schemaname, tablename, COUNT(*)::integer AS index_count
            FROM pg_indexes GROUP BY schemaname, tablename
        ) ix ON ix.schemaname = s.schemaname AND ix.tablename = s.relname
        ORDER BY s.n_live_tup DESC, s.relname ASC
        LIMIT ?
    SQL, [$limit]);
    echo "A7.7.5 PostgreSQL Performance Preflight [LOCAL, READ-ONLY]\n";
    echo "Stats are approximate/historical. A flag is NOT proof of a slow query or a missing index.\n";
    echo 'tables_returned='.count($rows).' | max='.$limit."\n";
    $flagged = 0;
    foreach ($rows as $r) {
        $args = [(int)$r->live_rows,(int)$r->dead_rows,(int)$r->seq_scans,(int)$r->index_scans,(int)$r->index_count];
        $flags = CrmPerformancePreflightPolicy::reviewFlags(...$args);
        if ($flags) $flagged++;
        $name = (string)$r->schemaname.'.'.(string)$r->table_name;
        echo '[TABLE] '.preg_replace('/[^A-Za-z0-9_.-]/', '?', $name)
            .' | rows_est='.$args[0].' | dead_est='.$args[1]
            .' | seq_scan='.$args[2].' | idx_scan='.$args[3]
            .' | indexes='.$args[4].' | flags='.($flags ? implode(',', $flags) : 'NONE')."\n";
    }
    echo 'review_candidates='.$flagged." | decision=MEASURE_QUERY_PLANS_BEFORE_CHANGES\n";
    echo "[SAFE] No SQL writes, no migration, no EXPLAIN ANALYZE. Transaction READ ONLY.\n";
} catch (Throwable $e) {
    fwrite(STDERR, '[ERROR] Could not read PostgreSQL statistics: '.get_class($e)."\n");
    $failed = true;
} finally {
    if ($began) $conn->rollBack();
}
exit($failed ? 1 : 0);
