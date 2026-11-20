<?php
declare(strict_types=1);

/** A7.8.1: route inventory, opt-in LOCAL PostgreSQL catalog read-only mode. */
$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

use App\Support\CrmIntegrationUatManifest as Manifest;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

$allowed = ['--routes', '--db-readonly'];
$args = array_slice($argv, 1);
if (count($args) !== count(array_unique($args)) || array_diff($args, $allowed)) {
    fwrite(STDERR, "Usage: php tools/a781_integration_preflight.php [--routes | --db-readonly]\n");
    exit(2);
}
$databaseMode = in_array('--db-readonly', $args, true);
if ($databaseMode && in_array('--routes', $args, true)) {
    fwrite(STDERR, "[STOP] Choose only one mode.\n");
    exit(2);
}

// Check local repository before loading Laravel or opening a DB connection.
$branch = trim((string) @shell_exec('git -C '.escapeshellarg($root).' branch --show-current 2>NUL'));
if ($branch !== 'Big-Changes') {
    fwrite(STDERR, "[STOP] Expected branch Big-Changes; no DB access attempted.\n");
    exit(2);
}
$app = require $root.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (!app()->environment('local')) {
    fwrite(STDERR, "[STOP] APP_ENV must be local; no DB access attempted.\n");
    exit(2);
}

echo "A7.8.1 Integration & UAT Preflight [LOCAL, ".($databaseMode ? 'CATALOG READ ONLY' : 'ROUTES ONLY')."]\n";
$actual = [];
foreach (Route::getRoutes() as $route) {
    $name = $route->getName();
    if (!is_string($name) || $name === '') continue;
    $actual[$name] ??= [];
    $actual[$name] = array_values(array_unique(array_merge($actual[$name], $route->methods())));
}
$routeTotal=0;
$routeMissing=0;
foreach (Manifest::routeGroups() as $group=>$entries) {
    $missing=[];
    foreach ($entries as [$name,$method]) {
        ++$routeTotal;
        if (!in_array($method, $actual[$name]??[], true)) $missing[]=$name.' ['.$method.']';
    }
    $routeMissing+=count($missing);
    echo '[ROUTES] '.$group.' checks='.count($entries).' missing='.count($missing)."\n";
    foreach ($missing as $name) echo '[MISSING ROUTE] '.$name."\n";
}
if (!$databaseMode) {
    echo '[RESULT] routes_checked='.$routeTotal.' missing='.$routeMissing.' | '.($routeMissing?'HOLD_ROUTE_MISMATCH':'ROUTE_BASELINE_READY')."\n";
    echo "[SAFE] No business HTTP requests, migrations, seeds, storage changes, or database queries.\n";
    exit($routeMissing===0 ? 0 : 1);
}

// DB mode is opt-in and never follows a cloud DB host.
if (DB::getDefaultConnection() !== 'pgsql') {
    fwrite(STDERR, "[STOP] Default connection must be pgsql; database untouched.\n");
    exit(2);
}
$db=DB::connection('pgsql');
$config=$db->getConfig();
$host = strtolower(trim((string)($config['host']??'')));
if (!in_array($host,['127.0.0.1','localhost','::1'], true)) {
    fwrite(STDERR, "[STOP] Only loopback PostgreSQL host is allowed; database untouched.\n");
    exit(2);
}
$requiredTables = [];
foreach (Manifest::schemaGroups() as $names) foreach ($names as $name) $requiredTables[$name]=true;
$missingTables=[];
$fkReview=[];
$begun=false;
$catalogError=false;
try {
    $db->beginTransaction();
    $begun=true;
    $db->statement('SET TRANSACTION READ ONLY');
    $db->statement("SET LOCAL statement_timeout = '5000ms'");
    // Only public catalog metadata. No SELECT against business tables.
    $rows=$db->select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'");
    $present=[];
    foreach ($rows as $row) $present[(string)$row->tablename]=true;
    foreach (Manifest::schemaGroups() as $group=>$names) {
        $absent=array_values(array_filter($names, static fn(string $n):bool=>!isset($present[$n])));
        $missingTables=array_merge($missingTables,$absent);
        echo '[SCHEMA] '.$group.' checked='.count($names).' missing='.count($absent)."\n";
        foreach($absent as $n) echo '[MISSING TABLE] '.$n."\n";
    }
    $fkRows=$db->select(<<<'SQL'
SELECT src.relname AS source_table, att.attname AS source_column, dest.relname AS target_table
FROM pg_catalog.pg_constraint con
JOIN pg_catalog.pg_class src ON src.oid=con.conrelid
JOIN pg_catalog.pg_namespace ns ON ns.oid=src.relnamespace
JOIN pg_catalog.pg_class dest ON dest.oid=con.confrelid
JOIN pg_catalog.pg_attribute att ON att.attrelid=con.conrelid AND att.attnum=con.conkey[1]
WHERE con.contype='f' AND ns.nspname='public' AND array_length(con.conkey,1)=1
SQL);
    $fkSet=[];
    foreach($fkRows as $row) $fkSet[$row->source_table.'|'.$row->source_column.'|'.$row->target_table]=true;
    foreach(Manifest::relationshipHints() as [$source,$column,$target]) {
        if (!isset($present[$source]) || !isset($present[$target])) {
            echo '[RELATION] '.$source.'.'.$column.' -> '.$target.' = TABLE_MISSING' ."\n";
            $fkReview[]=$source.'.'.$column;
        } elseif (isset($fkSet[$source.'|'.$column.'|'.$target])) {
            echo '[RELATION] '.$source.'.'.$column.' -> '.$target.' = FK_PRESENT' ."\n";
        } else {
            echo '[RELATION] '.$source.'.'.$column.' -> '.$target.' = FK_NOT_DETECTED_REVIEW' ."\n";
            $fkReview[]=$source.'.'.$column;
        }
    }
} catch (Throwable $e) {
    $catalogError=true;
    fwrite(STDERR, "[STOP] Catalog read failed; no business data modified. Check local PostgreSQL connection/schema.\n");
} finally {
    if ($begun && $db->transactionLevel()>0) {
        try { $db->rollBack(); }
        catch (Throwable $e) {
            $catalogError=true;
            fwrite(STDERR, "[STOP] Rollback could not be confirmed; DB session will terminate.\n");
        }
    }
}
if ($catalogError) exit(2);
$status=($routeMissing || $missingTables || $fkReview)?'REVIEW_INTEGRATION_READINESS':'STRUCTURAL_BASELINE_READY';
echo '[RESULT] routes_missing='.$routeMissing.' schema_missing='.count($missingTables).' fk_review='.count($fkReview).' decision='.$status."\n";
echo "[SAFE] PostgreSQL READ ONLY transaction and ROLLBACK; catalog only, no business rows, writes, migrations, or external HTTP.\n";
echo "[NOTE] Local tunnel to remote DB is NOT detected; verify .env target before --db-readonly.\n";
exit($routeMissing || $missingTables ? 1 : 0);
