<?php

declare(strict_types=1);

/**
 * A7.10.2: local CRM PostgreSQL SELECT-only role/user integrity review.
 * Starts PostgreSQL READ ONLY transaction, timeout and ALWAYS rolls back.
 * Counts only; no identities, passwords, row contents, migrations or writes.
 */
$root = dirname(__DIR__);
if (array_slice($argv, 1) !== ['--local-readonly']) {
    fwrite(STDERR, "Usage: php tools/a710_rbac_db_preflight.php --local-readonly\n"); exit(2);
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

if (!app()->environment('local') || DB::getDefaultConnection() !== 'pgsql') {
    fwrite(STDERR, "[STOP] Requires APP_ENV=local and default DB_CONNECTION=pgsql.\n"); exit(2);
}
$db = DB::connection('pgsql');
$cfg = $db->getConfig();
$host = strtolower(trim((string)($cfg['host'] ?? '')));
$database = (string) ($cfg['database'] ?? '');
if (!in_array($host, ['127.0.0.1', 'localhost', '::1'], true) || $database !== 'crm_medical_device') {
    fwrite(STDERR, "[STOP] Only loopback crm_medical_device is allowed; check .env.\n"); exit(2);
}
$failed = 0;
$review = 0;
$begun = false;
try {
    $db->beginTransaction();
    $begun = true;
    $db->statement('SET TRANSACTION READ ONLY');
    $db->statement("SET LOCAL statement_timeout = '8000ms'");
    $required = ['users','roles','user_roles','permissions','role_permissions','service_ticket_work_logs','service_tickets','maintenance_schedules','maintenance_records','installations','leads','opportunities'];
    $tables = array_fill_keys(array_map(static fn ($r): string => (string)$r->tablename,
        $db->select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'")), true);
    foreach ($required as $table) {
        if (!isset($tables[$table])) throw new RuntimeException('Missing required table: '.$table);
    }
    echo '[DATABASE] crm_medical_device (loopback, transaction READ ONLY)'.PHP_EOL;
    $q = static function (string $sql) use ($db): int {
        return (int) ($db->selectOne($sql)->n ?? 0);
    };
    $metrics = [
        'users_total' => 'SELECT count(*) AS n FROM users',
        'users_active' => "SELECT count(*) AS n FROM users WHERE lower(status)='active'",
        'users_invalid_status' => "SELECT count(*) AS n FROM users WHERE lower(coalesce(status,'')) NOT IN ('active','inactive')",
        'users_without_primary_pivot' => 'SELECT count(*) AS n FROM users u LEFT JOIN user_roles ur ON ur.user_id=u.user_id AND ur.is_primary=true WHERE ur.user_id IS NULL',
        'users_primary_role_mismatch' => 'SELECT count(*) AS n FROM users u INNER JOIN user_roles ur ON ur.user_id=u.user_id AND ur.is_primary=true WHERE u.role_id IS DISTINCT FROM ur.role_id',
        'users_multiple_primary_pivots' => 'SELECT count(*) AS n FROM (SELECT user_id FROM user_roles GROUP BY user_id HAVING count(*) FILTER (WHERE is_primary=true)>1) x',
        'role_assignments_missing_role_or_user' => 'SELECT count(*) AS n FROM user_roles ur LEFT JOIN roles r ON r.role_id=ur.role_id LEFT JOIN users u ON u.user_id=ur.user_id WHERE r.role_id IS NULL OR u.user_id IS NULL',
        'role_permissions_missing_catalog_reference' => 'SELECT count(*) AS n FROM role_permissions rp LEFT JOIN roles r ON r.role_id=rp.role_id LEFT JOIN permissions p ON p.permission_id=rp.permission_id WHERE r.role_id IS NULL OR p.permission_id IS NULL',
        'super_admin_active' => "SELECT count(DISTINCT u.user_id) AS n FROM users u JOIN user_roles ur ON ur.user_id=u.user_id JOIN roles r ON r.role_id=ur.role_id WHERE r.is_system=true AND lower(r.role_name)='super admin' AND lower(u.status)='active'",
        'work_logs_attributed' => 'SELECT count(*) AS n FROM service_ticket_work_logs WHERE created_by_user_id IS NOT NULL',
        'work_logs_legacy' => 'SELECT count(*) AS n FROM service_ticket_work_logs WHERE created_by_user_id IS NULL',
    ];
    $integrityFields = ['users_invalid_status','users_without_primary_pivot','users_primary_role_mismatch','users_multiple_primary_pivots','role_assignments_missing_role_or_user','role_permissions_missing_catalog_reference'];
    foreach ($metrics as $key => $sql) {
        $value = $q($sql);
        echo sprintf('[CHECK] %s=%d', $key, $value).PHP_EOL;
        if (in_array($key, $integrityFields, true) && $value !== 0) $failed++;
        if ($key === 'super_admin_active' && $value === 0) $review++;
        if ($key === 'work_logs_legacy' && $value !== 0) $review++;
    }
    echo sprintf('[SUMMARY] integrity_failures=%d review_indicators=%d', $failed, $review).PHP_EOL;
    echo '[NOTE] Historical work log null author is not an integrity failure. No user identities are displayed.'.PHP_EOL;
} catch (Throwable $e) {
    $failed++;
    fwrite(STDERR, '[FAIL] Read-only RBAC preflight: '.$e->getMessage().PHP_EOL);
} finally {
    if ($begun && $db->transactionLevel() > 0) {
        try { $db->rollBack(); } catch (Throwable $e) { $failed++; fwrite(STDERR, '[FAIL] DB rollback could not be confirmed.'.PHP_EOL); }
    }
}
if ($failed) { echo '[RESULT] A710_RBAC_DATABASE_HOLD'.PHP_EOL; exit(1); }
echo '[RESULT] '.($review ? 'A710_RBAC_DATABASE_REVIEW' : 'A710_RBAC_DATABASE_INTEGRITY_READY').PHP_EOL;
echo '[SAFE] SELECT-only transaction rolled back; no business data changed.'.PHP_EOL;
exit(0);
