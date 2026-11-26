<?php
/**
 * A7.10.7: single-permission recovery; read-only by default.
 * No migration reruns, no reset/seed, and NO role or user grants.
 * Apply is allowed ONLY for local PostgreSQL crm_medical_device with explicit consent.
 */
declare(strict_types=1);

use App\Support\CrmAuditPermissionSeedPolicy as Policy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

$root = dirname(__DIR__);
$args = array_slice($argv, 1);
$usage = "Usage: php tools/a7107_workspace_audit_permission.php [--check | --apply --confirm=A7107-ONE-PERMISSION]\n";
if ($args === [] || $args === ['--check']) {
    $apply = false;
} elseif (count($args) === 2 && in_array('--apply', $args, true) && in_array('--confirm=A7107-ONE-PERMISSION', $args, true)) {
    $apply = true;
} else {
    fwrite(STDERR, $usage);
    exit(2);
}

try {
    if (!is_file($root.'/vendor/autoload.php') || !is_file($root.'/bootstrap/app.php')) {
        throw new RuntimeException('Laravel project root or vendor/autoload.php is missing.');
    }
    require $root.'/vendor/autoload.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    // Defense-in-depth: forbid running this repair against nonlocal and production DBs.
    if (!app()->environment('local')) {
        throw new RuntimeException('Requires APP_ENV=local; no changes performed.');
    }
    $db = DB::connection();
    if ($db->getDriverName() !== 'pgsql') {
        throw new RuntimeException('Requires PostgreSQL; no changes performed.');
    }
    if ((string) $db->getDatabaseName() !== 'crm_medical_device') {
        throw new RuntimeException('Unexpected database name; requires crm_medical_device.');
    }
    $host = trim((string) ($db->getConfig('host') ?? ''));
    if (!in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true)) {
        throw new RuntimeException('DB host must be localhost/loopback. Found a nonlocal or unsupported host; refusing.');
    }
    $url = (string) ($db->getConfig('url') ?? '');
    if ($url !== '') {
        $urlHost = parse_url($url, PHP_URL_HOST);
        if (!is_string($urlHost) || !in_array(strtolower($urlHost), ['localhost', '127.0.0.1', '::1'], true)) {
            throw new RuntimeException('Nonlocal DB_URL is not permitted.');
        }
    }
    $endpoint = $db->selectOne('select inet_server_addr()::text as server_addr, current_database()::text as db_name');
    if (!$endpoint || (string) ($endpoint->db_name ?? '') !== 'crm_medical_device' ||
        !in_array((string) ($endpoint->server_addr ?? ''), ['127.0.0.1', '::1'], true)) {
        throw new RuntimeException('PostgreSQL actual server address is not loopback; refusing.');
    }

    foreach (['migrations', 'permissions', 'role_permissions', 'workspace_audit_events'] as $table) {
        if (!Schema::hasTable($table)) throw new RuntimeException('Missing required table '.$table.'. Stop and review migrations.');
    }
    foreach (['permission_id', 'permission_name', 'module', 'action'] as $column) {
        if (!Schema::hasColumn('permissions', $column)) {
            throw new RuntimeException('Missing permissions column '.$column.'. Stop and review schema.');
        }
    }
    $migrationRan = DB::table('migrations')->where('migration', Policy::MIGRATION)->exists();
    if (!$migrationRan) {
        throw new RuntimeException('Workspace Audit migration is NOT recorded as run. Inspect migrations before any repair.');
    }

    $inspect = static function () {
        $rows = DB::table('permissions')->where('permission_name', Policy::PERMISSION)
            ->get(['permission_id', 'permission_name', 'module', 'action']);
        if ($rows->count() > 1) throw new RuntimeException('Duplicate audit permission names found; no automated repair allowed.');
        $state = Policy::classify($rows->first());
        if ($state === 'CONFLICT') {
            throw new RuntimeException('Existing workspace_audit.view has conflicting metadata. No automated overwrite allowed.');
        }
        return $state;
    };

    echo '[DATABASE] crm_medical_device | APP_ENV=local | PostgreSQL loopback verified' . PHP_EOL;
    echo '[CHECK] Workspace Audit migration recorded: Ran' . PHP_EOL;
    $status = $inspect();
    echo '[CHECK] workspace_audit.view = ' . $status . PHP_EOL;

    if (!$apply) {
        if ($status === 'READY') {
            echo '[RESULT] A7107_PERMISSION_PRESENT (read-only; no changes)' . PHP_EOL;
        } else {
            echo '[RESULT] A7107_READY_TO_REPAIR (one catalogue permission missing; no changes)' . PHP_EOL;
            echo '[NEXT] Back up PostgreSQL, then run --apply --confirm=A7107-ONE-PERMISSION' . PHP_EOL;
        }
        exit(0);
    }

    if ($status === 'READY') {
        echo '[RESULT] A7107_ALREADY_READY (no changes)' . PHP_EOL;
        exit(0);
    }

    // The write is strictly one permission catalog row; no permissions are granted.
    $inserted = DB::transaction(static function () use ($inspect): bool {
        if ($inspect() === 'READY') return false;
        $fields = Policy::insertFields((string) Str::uuid(), now()->toDateTimeString());
        // Old schema variants can omit description or created_at.
        foreach (array_keys($fields) as $name) {
            if (!Schema::hasColumn('permissions', $name)) unset($fields[$name]);
        }
        $count = DB::table('permissions')->insertOrIgnore($fields);
        if ($inspect() !== 'READY') throw new RuntimeException('Post-insert validation failed. Transaction rolled back.');
        return $count === 1;
    }, 3);
    echo '[CHECK] Permission metadata after repair: READY' . PHP_EOL;
    echo '[SAFE] No role_permissions, user_roles, roles, or users have been written.' . PHP_EOL;
    echo '[RESULT] ' . ($inserted ? 'A7107_AUDIT_PERMISSION_CREATED' : 'A7107_ALREADY_READY') . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, '[STOP] ' . $error->getMessage() . PHP_EOL);
    fwrite(STDERR, '[RESULT] A7107_REPAIR_NOT_COMPLETED' . PHP_EOL);
    exit(1);
}
