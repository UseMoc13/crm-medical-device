<?php
/** A7.7.5.1 — PostgreSQL performance BASELINE only. No query execution or DDL. */

declare(strict_types=1);

if (PHP_SAPI !== 'cli' || ($_SERVER['argv'] ?? []) !== [$_SERVER['argv'][0] ?? '', '--run']) {
    fwrite(STDERR, "Usage: php tools/a7751_pg_baseline.php --run\nThis performs metadata reads and EXPLAIN (not ANALYZE) on a LOCAL PostgreSQL database.\n");
    exit(2);
}

$root = dirname(__DIR__);
if (!is_file($root.'/vendor/autoload.php') || !is_file($root.'/bootstrap/app.php')) {
    fwrite(STDERR, "[ERROR] Run from the Laravel project with Composer dependencies installed.\n");
    exit(2);
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\CrmQueryPlanDigest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!app()->environment('local')) {
    fwrite(STDERR, "[DENIED] APP_ENV must be local. No database read performed.\n");
    exit(3);
}
$config = config('database.default');
if ($config !== 'pgsql') {
    fwrite(STDERR, "[DENIED] DB_CONNECTION must be pgsql. No database read performed.\n");
    exit(3);
}
$host = (string) config('database.connections.pgsql.host', '');
if (!in_array($host, ['localhost','127.0.0.1','::1'], true)) {
    fwrite(STDERR, "[DENIED] DB host must be localhost, 127.0.0.1 or ::1; no remote database reads.\n");
    exit(3);
}

$tracked = [
    'customers', 'leads', 'opportunities', 'products', 'inventories',
    'deliveries', 'installations', 'service_tickets', 'maintenance_schedules',
    'invoices', 'invoice_items', 'stock_movements',
];

// These are hardcoded read-only SELECT statements. No user input reaches SQL.
$plans = [
    'invoice_date_wrapped' => [
        'requires' => ['invoices' => ['invoice_id','invoice_date']],
        'sql' => "SELECT invoice_id FROM invoices WHERE DATE(invoice_date) >= CURRENT_DATE - INTERVAL '90 days' ORDER BY invoice_date DESC LIMIT 20",
    ],
    'invoice_date_range' => [
        'requires' => ['invoices' => ['invoice_id','invoice_date']],
        'sql' => "SELECT invoice_id FROM invoices WHERE invoice_date >= CURRENT_DATE - INTERVAL '90 days' ORDER BY invoice_date DESC LIMIT 20",
    ],
    'ticket_date_wrapped' => [
        'requires' => ['service_tickets' => ['service_ticket_id','opened_at']],
        'sql' => "SELECT service_ticket_id FROM service_tickets WHERE DATE(opened_at) >= CURRENT_DATE - INTERVAL '90 days' ORDER BY opened_at DESC LIMIT 20",
    ],
    'ticket_date_range' => [
        'requires' => ['service_tickets' => ['service_ticket_id','opened_at']],
        'sql' => "SELECT service_ticket_id FROM service_tickets WHERE opened_at >= CURRENT_DATE - INTERVAL '90 days' ORDER BY opened_at DESC LIMIT 20",
    ],
    'maintenance_date_wrapped' => [
        'requires' => ['maintenance_schedules' => ['maintenance_schedule_id','scheduled_date']],
        'sql' => "SELECT maintenance_schedule_id FROM maintenance_schedules WHERE DATE(scheduled_date) >= CURRENT_DATE - INTERVAL '90 days' ORDER BY scheduled_date DESC LIMIT 20",
    ],
    'maintenance_date_range' => [
        'requires' => ['maintenance_schedules' => ['maintenance_schedule_id','scheduled_date']],
        'sql' => "SELECT maintenance_schedule_id FROM maintenance_schedules WHERE scheduled_date >= CURRENT_DATE - INTERVAL '90 days' ORDER BY scheduled_date DESC LIMIT 20",
    ],
    'inventory_low_stock' => [
        'requires' => ['inventories' => ['inventory_id','quantity','minimum_stock']],
        'sql' => 'SELECT inventory_id FROM inventories WHERE quantity > 0 AND minimum_stock IS NOT NULL AND quantity <= minimum_stock ORDER BY inventory_id LIMIT 20',
    ],
    'delivery_recent' => [
        'requires' => ['deliveries' => ['delivery_id','status','created_at']],
        'sql' => "SELECT delivery_id FROM deliveries WHERE status IN ('in_transit','delivered') ORDER BY created_at DESC LIMIT 20",
    ],
];

$connection = DB::connection('pgsql');
$started = false;
$exitCode = 0;
try {
    $connection->beginTransaction();
    $started = true;
    // A PostgreSQL transaction rejects any SQL writes and DDL from this point.
    $connection->statement('SET TRANSACTION READ ONLY');
    $connection->statement("SET LOCAL statement_timeout = '3500ms'");

    echo "A7.7.5.1 PostgreSQL Performance Baseline [LOCAL, READ-ONLY]\n";
    echo "No EXPLAIN ANALYZE, no scans of business rows, no SQL mutations.\n";
    $known = [];
    foreach ($tracked as $table) {
        if (!Schema::hasTable($table)) {
            echo "[TABLE] {$table} schema=SKIPPED\n";
            continue;
        }
        $known[$table] = true;
        $row = $connection->selectOne(
            'SELECT st.n_live_tup AS estimated_rows, st.n_dead_tup AS estimated_dead, st.seq_scan, st.idx_scan, '
            .'(SELECT COUNT(*) FROM pg_indexes pi WHERE pi.schemaname = st.schemaname AND pi.tablename = st.relname) AS index_count '
            .'FROM pg_stat_user_tables st WHERE st.schemaname = current_schema() AND st.relname = ?',
            [$table]
        );
        if (!$row) {
            echo "[TABLE] {$table} stats=UNAVAILABLE\n";
            continue;
        }
        printf("[TABLE] %s estimated_rows=%d estimated_dead=%d indexes=%d seq_scan=%d idx_scan=%d\n",
            $table, (int) $row->estimated_rows, (int) $row->estimated_dead,
            (int) $row->index_count, (int) $row->seq_scan, (int) $row->idx_scan);
    }
    foreach ($plans as $id => $entry) {
        $valid = true;
        foreach ($entry['requires'] as $table => $columns) {
            if (!isset($known[$table]) || !Schema::hasColumns($table, $columns)) { $valid = false; break; }
        }
        if (!$valid) { echo "[PLAN] {$id} schema=SKIPPED\n"; continue; }
        // EXPLAIN WITHOUT ANALYZE: PostgreSQL estimates the plan; it does not run the SELECT.
        $result = $connection->selectOne('EXPLAIN (FORMAT JSON) '.$entry['sql']);
        $raw = get_object_vars($result)['QUERY PLAN'] ?? null;
        if (is_array($raw)) $raw = json_encode($raw);
        if (!is_string($raw)) throw new RuntimeException('Unexpected EXPLAIN response.');
        $digest = CrmQueryPlanDigest::summarize($raw);
        printf("[PLAN] %s cost=%.2f estimated_rows=%d seq_scans=%d index_scans=%d nodes=%s\n",
            $id, $digest['cost'], $digest['estimated_rows'],
            $digest['sequential_scans'], $digest['index_scans'], implode(',', $digest['nodes']));
    }
    echo "RESULT: BASELINE_COLLECTED | No recommendations or indexes applied.\n";
} catch (Throwable $e) {
    fwrite(STDERR, '[ERROR] Baseline could not finish: '.get_class($e)."; no query details disclosed.\n");
    $exitCode = 1;
} finally {
    if ($started && $connection->transactionLevel() > 0) $connection->rollBack();
}
exit($exitCode);
