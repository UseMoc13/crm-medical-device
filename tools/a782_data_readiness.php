<?php
declare(strict_types=1);
/** A7.8.2: opt-in aggregate presence probes, PostgreSQL read-only; no business values. */

$root = dirname(__DIR__);
if (array_slice($argv, 1) !== ['--data-readonly']) {
    fwrite(STDERR, "Usage: php tools/a782_data_readiness.php --data-readonly\n");
    exit(2);
}
$branch = trim((string) @shell_exec('git -C '.escapeshellarg($root).' branch --show-current 2>NUL'));
if ($branch !== 'Big-Changes') {
    fwrite(STDERR, "[STOP] Expected Big-Changes branch. No DB access attempted.\n");
    exit(2);
}
require $root.'/vendor/autoload.php';
use App\Support\CrmIntegrationUatManifest;
use App\Support\CrmUatDataReadiness;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
$app = require $root.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (!app()->environment('local')) {
    fwrite(STDERR, "[STOP] APP_ENV must be local. No DB access attempted.\n");
    exit(2);
}
if (DB::getDefaultConnection() !== 'pgsql') {
    fwrite(STDERR, "[STOP] Default database must be pgsql.\n");
    exit(2);
}
$db = DB::connection('pgsql');
$host = strtolower(trim((string) ($db->getConfig()['host'] ?? '')));
if (!in_array($host, ['localhost','127.0.0.1','::1'], true)) {
    fwrite(STDERR, "[STOP] Database host must be loopback.\n");
    exit(2);
}
$groups = CrmIntegrationUatManifest::schemaGroups();
$manifestTables = [];
foreach ($groups as $names) foreach ($names as $name) {
    if (!preg_match('/\A[a-z][a-z0-9_]*\z/D', $name)) {
        fwrite(STDERR, "[STOP] Invalid source manifest table name.\n");
        exit(2);
    }
    $manifestTables[$name] = true;
}
$manifest = CrmUatDataReadiness::chains();
foreach ($manifest as $entry) {
    if (!isset($entry['sql'], $entry['tables'])
        || preg_match('/\ASELECT EXISTS \(SELECT 1 FROM public\./', $entry['sql']) !== 1
        || preg_match('/\b(?:INSERT|UPDATE|DELETE|TRUNCATE|ALTER|DROP|CREATE|ANALYZE)\b/i', $entry['sql'])) {
        fwrite(STDERR, "[STOP] Invalid read-only chain definition.\n");
        exit(2);
    }
    foreach ($entry['tables'] as $name) {
        if (!isset($manifestTables[$name])) {
            fwrite(STDERR, "[STOP] Chain references a table outside A7.8.1 manifest.\n");
            exit(2);
        }
    }
}
function a782_bool(mixed $value): bool {
    $s = strtolower(trim((string) $value));
    if (in_array($s, ['1','t','true'], true)) return true;
    if (in_array($s, ['0','f','false',''], true)) return false;
    throw new RuntimeException('Unexpected PostgreSQL EXISTS result.');
}

echo "A7.8.2 UAT Data Readiness [LOCAL, READ ONLY; AGGREGATES ONLY]\n";
echo "[NOTE] EXISTS reads business-table presence but never prints row values or identifiers.\n";
$begun = false;
$failed = false;
$tableStatuses = [];
$chainStatuses = [];
try {
    $db->beginTransaction();
    $begun = true;
    $db->statement('SET TRANSACTION READ ONLY');
    $db->statement("SET LOCAL statement_timeout = '5000ms'");
    $db->statement("SET LOCAL lock_timeout = '1500ms'");
    $existing = $db->select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'");
    $present = [];
    foreach ($existing as $entry) $present[(string) $entry->tablename] = true;
    foreach ($groups as $group => $tables) {
        $counts = ['HAS_SAMPLE'=>0, 'EMPTY'=>0, 'MISSING'=>0];
        foreach ($tables as $name) {
            if (!isset($present[$name])) {
                $status = 'MISSING';
            } else {
                $record = $db->selectOne('SELECT EXISTS (SELECT 1 FROM public."'.$name.'" LIMIT 1) AS found');
                $status = a782_bool($record->found ?? null) ? 'HAS_SAMPLE' : 'EMPTY';
            }
            $tableStatuses[$name] = $status;
            $counts[$status]++;
        }
        echo '[TABLE GROUP] '.$group.' checked='.count($tables)
            .' has_sample='.$counts['HAS_SAMPLE'].' empty='.$counts['EMPTY'].' missing='.$counts['MISSING']."\n";
    }
    foreach ($manifest as $id => $entry) {
        $valid = true;
        foreach ($entry['tables'] as $name) if (($tableStatuses[$name] ?? 'MISSING') === 'MISSING') {
            $valid = false;
            break;
        }
        if (!$valid) {
            $status = 'BLOCKED_MISSING_TABLE';
        } else {
            $record = $db->selectOne($entry['sql']);
            $status = a782_bool($record->found ?? null) ? 'LINKED_SAMPLE' : 'NO_LINKED_SAMPLE';
        }
        $chainStatuses[$id] = $status;
        echo '[CHAIN] '.$id.' = '.$status."\n";
    }
} catch (Throwable $e) {
    $failed = true;
    fwrite(STDERR, "[STOP] Read-only audit could not complete. Check PostgreSQL access/timeout. No records changed.\n");
} finally {
    if ($begun && $db->transactionLevel() > 0) {
        try { $db->rollBack(); }
        catch (Throwable $e) {
            $failed = true;
            fwrite(STDERR, "[STOP] Rollback not confirmed; close DB connection.\n");
        }
    }
}
if ($failed) exit(2);
try {
    $summary = CrmUatDataReadiness::summarize($tableStatuses, $chainStatuses);
} catch (Throwable $e) {
    fwrite(STDERR, "[STOP] Invalid coverage summary.\n");
    exit(2);
}
echo '[RESULT] tables_with_samples='.$summary['table_filled'].' empty='.$summary['table_empty']
    .' missing='.$summary['table_missing'].' chains_linked='.$summary['chain_ready']
    .' chain_gaps='.$summary['chain_gap'].' decision='.$summary['decision']."\n";
echo "[SAFE] READ ONLY transaction rolled back. No data changes. No row values, paths, names, emails, or UUIDs printed.\n";
echo "[LIMIT] Presence is not proof of correct status transitions, permissions, or complete UAT.\n";
echo "[NOTE] Loopback tunnels may target remote DBs. Verify .env database target first.\n";
exit($summary['decision'] === 'HOLD_SCHEMA_GAPS' ? 1 : 0);
