<?php

declare(strict_types=1);

/** A7.9.1: PostgreSQL public-schema metadata ONLY; never resets or seeds. */
$root = dirname(__DIR__);
$args = array_slice($argv, 1);
if (PHP_SAPI !== 'cli' || count($args) !== count(array_unique($args))
    || array_diff($args, ['--schema-readonly', '--snapshot'])
    || !in_array('--schema-readonly', $args, true)) {
    fwrite(STDERR, "Usage: php tools/a791_schema_preflight.php --schema-readonly [--snapshot]\n");
    exit(2);
}
$save = in_array('--snapshot', $args, true);
$branchError = '2>'.(DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null');
$branch = trim((string) @shell_exec('git -C '.escapeshellarg($root).' branch --show-current '.$branchError));
if ($branch !== 'Big-Changes') {
    fwrite(STDERR, "[STOP] Big-Changes branch required. No DB access.\n");
    exit(2);
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!app()->environment('local')) {
    fwrite(STDERR, "[STOP] APP_ENV must be local. No DB access.\n");
    exit(2);
}
if (\Illuminate\Support\Facades\DB::getDefaultConnection() !== 'pgsql') {
    fwrite(STDERR, "[STOP] Local default DB must be PostgreSQL.\n");
    exit(2);
}
$db = \Illuminate\Support\Facades\DB::connection('pgsql');
$host = strtolower(trim((string) ($db->getConfig()['host'] ?? '')));
if (!in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
    fwrite(STDERR, "[STOP] PostgreSQL host must be loopback. No DB access.\n");
    exit(2);
}

echo "A7.9.1 Schema & Reset Safety Preflight [LOCAL, PUBLIC CATALOG READ ONLY]\n";
echo "[LIMIT] This tool cannot prove localhost is not a tunnel to a remote DB. Verify .env manually.\n";
$started = false;
$error = false;
$snapshot = null;
try {
    $db->beginTransaction();
    $started = true;
    $db->statement('SET TRANSACTION READ ONLY');
    $db->statement("SET LOCAL statement_timeout = '6500ms'");
    $db->statement("SET LOCAL lock_timeout = '1500ms'");

    // All five SELECTs below read ONLY PostgreSQL catalogs; no CRM table SELECTs.
    $tableRows = $db->select(<<<'SQL'
SELECT cls.relname AS table_name
FROM pg_catalog.pg_class cls
JOIN pg_catalog.pg_namespace ns ON ns.oid=cls.relnamespace
WHERE ns.nspname='public' AND cls.relkind IN ('r','p')
ORDER BY cls.relname
SQL);
    $columnRows = $db->select(<<<'SQL'
SELECT cls.relname AS table_name, attr.attname AS column_name,
       attr.attnum AS ordinal,
       pg_catalog.format_type(attr.atttypid, attr.atttypmod) AS data_type,
       attr.attnotnull AS not_null,
       (def.oid IS NOT NULL) AS has_default,
       (attr.attidentity <> '') AS is_identity,
       (attr.attgenerated <> '') AS is_generated
FROM pg_catalog.pg_attribute attr
JOIN pg_catalog.pg_class cls ON cls.oid=attr.attrelid
JOIN pg_catalog.pg_namespace ns ON ns.oid=cls.relnamespace
LEFT JOIN pg_catalog.pg_attrdef def ON def.adrelid=attr.attrelid AND def.adnum=attr.attnum
WHERE ns.nspname='public' AND cls.relkind IN ('r','p')
  AND attr.attnum > 0 AND NOT attr.attisdropped
ORDER BY cls.relname, attr.attnum
SQL);
    $constraintRows = $db->select(<<<'SQL'
SELECT cls.relname AS table_name, con.conname AS constraint_name,
       con.contype AS constraint_type,
       pg_catalog.pg_get_constraintdef(con.oid, true) AS definition
FROM pg_catalog.pg_constraint con
JOIN pg_catalog.pg_class cls ON cls.oid=con.conrelid
JOIN pg_catalog.pg_namespace ns ON ns.oid=cls.relnamespace
WHERE ns.nspname='public' AND cls.relkind IN ('r','p')
  AND con.contype IN ('p','f','u','c','x')
ORDER BY cls.relname, con.conname
SQL);
    $enumRows = $db->select(<<<'SQL'
SELECT typ.typname AS enum_name, val.enumlabel AS enum_label
FROM pg_catalog.pg_type typ
JOIN pg_catalog.pg_namespace ns ON ns.oid=typ.typnamespace
JOIN pg_catalog.pg_enum val ON val.enumtypid=typ.oid
WHERE ns.nspname='public'
ORDER BY typ.typname, val.enumsortorder
SQL);
    $triggerRows = $db->select(<<<'SQL'
SELECT cls.relname AS table_name, trg.tgname AS trigger_name,
       pg_catalog.pg_get_triggerdef(trg.oid, true) AS definition
FROM pg_catalog.pg_trigger trg
JOIN pg_catalog.pg_class cls ON cls.oid=trg.tgrelid
JOIN pg_catalog.pg_namespace ns ON ns.oid=cls.relnamespace
WHERE ns.nspname='public' AND cls.relkind IN ('r','p')
  AND NOT trg.tgisinternal
ORDER BY cls.relname, trg.tgname
SQL);
    $snapshot = \App\Support\CrmUatSchemaSnapshot::build(
        $tableRows, $columnRows, $constraintRows, $enumRows, $triggerRows
    );
} catch (\Throwable $e) {
    $error = true;
    fwrite(STDERR, "[STOP] Schema catalog audit failed; no CRM records changed. Check PostgreSQL access/schema.\n");
} finally {
    if ($started && $db->transactionLevel() > 0) {
        try { $db->rollBack(); }
        catch (\Throwable $e) {
            $error = true;
            fwrite(STDERR, "[STOP] Rollback confirmation failed. No snapshot exported.\n");
            try { $db->disconnect(); } catch (\Throwable $ignored) {}
        }
    }
}
if ($error || !is_array($snapshot)) exit(2);

$expected = [];
foreach (\App\Support\CrmIntegrationUatManifest::schemaGroups() as $names) {
    foreach ($names as $name) $expected[$name] = true;
}
$missing = \App\Support\CrmUatSchemaSnapshot::missingTables($snapshot, array_keys($expected));
$stats = $snapshot['summary'];
echo '[SCHEMA] tables='.$stats['tables'].' columns='.$stats['columns']
    .' required_without_default='.$stats['required_without_default']."\n";
echo '[CONSTRAINTS] foreign_keys='.$stats['foreign_keys'].' unique='.$stats['unique_constraints']
    .' checks='.$stats['checks'].' enum_types='.$stats['enum_types'].' triggers='.$stats['triggers']."\n";
echo '[MANIFEST] tables_expected='.count($expected).' missing='.count($missing)."\n";
foreach ($missing as $name) echo '[MISSING] '.$name."\n";
echo '[FINGERPRINT] sha256_prefix='.substr($snapshot['fingerprint_sha256'], 0, 16)."\n";

if ($save) {
    // Writes ONLY a local JSON file, and ONLY if --snapshot was explicitly supplied.
    // Never includes DB name, connection parameters, row data, or user records.
    $folder = $root.'/storage/app/private';
    if (!is_dir($folder) && !mkdir($folder, 0700, true) && !is_dir($folder)) {
        fwrite(STDERR, "[STOP] Cannot create local private snapshot folder.\n");
        exit(2);
    }
    $filename = 'a791_schema_snapshot_'.substr($snapshot['fingerprint_sha256'], 0, 16).'.json';
    $output = $folder.'/'.$filename;
    if (file_exists($output)) {
        echo '[SNAPSHOT] Already exists; not overwritten: storage/app/private/'.$filename."\n";
    } else {
        $snapshot['generated_at_utc'] = gmdate('c');
        $payload = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        $stream = @fopen($output, 'x'); // exclusive create: never overwrite a prior snapshot
        if ($stream === false) {
            fwrite(STDERR, "[STOP] Snapshot file cannot be created; no DB records changed.\n");
            exit(2);
        }
        $bytes = fwrite($stream, $payload);
        fclose($stream);
        if ($bytes !== strlen($payload)) {
            @unlink($output);
            fwrite(STDERR, "[STOP] Incomplete snapshot; removed incomplete local file.\n");
            exit(2);
        }
        echo '[SNAPSHOT] Schema metadata saved: storage/app/private/'.$filename."\n";
        echo "[NOTICE] Review before sharing. Contains database schema/constraint definitions, NOT data rows.\n";
    }
}
echo '[RESULT] '.($missing === [] ? 'SCHEMA_INVENTORY_COLLECTED_RESET_NOT_APPROVED' : 'HOLD_SCHEMA_MANIFEST_GAPS')."\n";
echo "[SAFE] Catalog SELECTs only, READ ONLY transaction rolled back. No reset, migrations, writes, or business-row reads.\n";
exit($missing === [] ? 0 : 1);
