<?php
/** A7.10.7.1: diagnose actual PostgreSQL endpoint; STRICTLY READ ONLY. */
declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$root = dirname(__DIR__);
if (array_slice($argv, 1) !== []) {
    fwrite(STDERR, "Usage: php tools/a71071_db_endpoint_diagnose.php\n");
    exit(2);
}

try {
    require $root.'/vendor/autoload.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();

    $db = DB::connection();
    $driver = $db->getDriverName();
    $env = (string) app()->environment();
    $database = (string) $db->getDatabaseName();
    $configuredHost = trim((string) ($db->getConfig('host') ?? ''));
    $configuredPort = trim((string) ($db->getConfig('port') ?? ''));

    echo "[MODE] READ_ONLY_DIAGNOSIS; no migrations, modifications, permission grants or data writes\n";
    echo '[APP_ENV] '.$env.PHP_EOL;
    echo '[DRIVER] '.$driver.PHP_EOL;
    echo '[CONFIG_DB_HOST] '.($configuredHost !== '' ? $configuredHost : '(unset)').PHP_EOL;
    echo '[CONFIG_DB_PORT] '.($configuredPort !== '' ? $configuredPort : '(default)').PHP_EOL;
    echo '[CONFIG_DB_URL] '.((string) ($db->getConfig('url') ?? '') !== '' ? 'SET (value hidden)' : 'not set').PHP_EOL;
    echo '[DATABASE] '.$database.PHP_EOL;
    if ($driver !== 'pgsql') {
        echo "[RESULT] A71071_NON_POSTGRESQL_NO_WRITES\n";
        exit(1);
    }
    $r = $db->selectOne('SELECT current_database()::text AS database_name, inet_server_addr()::text AS server_addr, inet_client_addr()::text AS client_addr, inet_server_port() AS server_port');
    $actual = (string) ($r->server_addr ?? '');
    $remoteAddressType = $actual === '' ? 'UNIX_SOCKET_OR_NO_TCP_ADDRESS'
        : (in_array($actual, ['127.0.0.1', '::1'], true) ? 'LOOPBACK'
        : (str_starts_with(strtolower($actual), '::ffff:127.') ? 'IPV4_MAPPED_LOOPBACK'
        : 'NON_LOOPBACK'));
    echo '[SERVER_DB] '.(string) ($r->database_name ?? '(unavailable)').PHP_EOL;
    echo '[SERVER_ADDR] '.($actual !== '' ? $actual : '(NULL)').PHP_EOL;
    echo '[SERVER_PORT] '.(string) ($r->server_port ?? '(NULL)').PHP_EOL;
    echo '[CLIENT_ADDR] '.((string) ($r->client_addr ?? '') ?: '(NULL)').PHP_EOL;
    echo '[SERVER_ADDR_CLASS] '.$remoteAddressType.PHP_EOL;

    if (Schema::hasTable('permissions')) {
        $found = DB::table('permissions')->where('permission_name', 'workspace_audit.view')
            ->first(['permission_name', 'module', 'action']);
        $status = $found === null ? 'MISSING' : (
            $found->module === 'workspace_audit' && $found->action === 'view' ? 'READY' : 'CONFLICT');
        echo '[AUDIT_PERMISSION] '.$status.PHP_EOL;
    } else {
        echo "[AUDIT_PERMISSION] NO_PERMISSIONS_TABLE\n";
    }
    if (!app()->environment('local') || $database !== 'crm_medical_device') {
        echo "[STOP] Unexpected environment or database. Do not apply any repair.\n";
    } elseif ($remoteAddressType === 'NON_LOOPBACK') {
        echo "[NEXT] Inspect container, port-forward or proxy topology; verify this is disposable development DB. Do not bypass A7107 server guard.\n";
    } elseif ($remoteAddressType === 'IPV4_MAPPED_LOOPBACK') {
        echo "[NEXT] A7107 strict comparison does not normalize mapped IPv4. Request an explicit normalization patch.\n";
    } else {
        echo "[NEXT] Share the diagnostic output for a safe, environment-specific A7107 fix.\n";
    }
    echo "[RESULT] A71071_ENDPOINT_DIAGNOSIS_COMPLETE_READ_ONLY\n";
} catch (Throwable $e) {
    fwrite(STDERR, '[STOP] Diagnostic failed: '.$e->getMessage().PHP_EOL);
    exit(1);
}
