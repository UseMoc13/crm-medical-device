<?php
/** A7.9.9: strict one-record operational correction; defaults to READ ONLY. */
declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$values = [];
$apply = false;
$usage = "Usage: php tools/a799_assignment_reassign.php --table=service_tickets|maintenance_schedules|installations --record=UUID --from=UUID --to=UUID [--reason=TEXT] [--apply --confirm=A799-ONE-RECORD]\n";
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--apply') {
        $apply = true;
        continue;
    }
    if (!preg_match('/^--(table|record|from|to|reason|confirm)=(.+)$/sD', $arg, $match) || isset($values[$match[1]])) {
        fwrite(STDERR, $usage);
        exit(2);
    }
    $values[$match[1]] = $match[2];
}
foreach (['table', 'record', 'from', 'to'] as $key) {
    if (empty($values[$key])) {
        fwrite(STDERR, $usage);
        exit(2);
    }
}
$uuid = '/\A[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\z/iD';
foreach (['record', 'from', 'to'] as $key) {
    if (!preg_match($uuid, $values[$key])) {
        fwrite(STDERR, '[FAIL] Invalid UUID for ' . $key . PHP_EOL);
        exit(2);
    }
}
if ($apply && (($values['confirm'] ?? '') !== 'A799-ONE-RECORD' || empty($values['reason']))) {
    fwrite(STDERR, "[FAIL] Apply requires --reason and --confirm=A799-ONE-RECORD.\n");
    exit(2);
}
if (!$apply && isset($values['confirm'])) {
    fwrite(STDERR, "[FAIL] --confirm is only for --apply.\n");
    exit(2);
}

try {
    $db = \Illuminate\Support\Facades\DB::connection();
    $host = strtolower(trim((string) $db->getConfig('host')));
    $name = (string) $db->getDatabaseName();
    if (! $app->environment('local') || !in_array($host, ['127.0.0.1', 'localhost', '::1'], true)
        || $name !== 'crm_medical_device') {
        throw new RuntimeException('Fail closed: only local crm_medical_device database is permitted.');
    }
    $agent = app(\App\Services\CrmOperationalAssignmentRemediator::class);
    $data = $apply
        ? $agent->apply($values['table'], $values['record'], $values['from'], $values['to'], (string) $values['reason'])
        : $agent->preview($values['table'], $values['record'], $values['from'], $values['to']);
    echo '[DATABASE] ' . $name . PHP_EOL;
    echo '[RECORD] ' . $data['table'] . '/' . $data['record_id'] . ' | status=' . $data['status'] . PHP_EOL;
    echo '[FROM] ' . $data['previous_name'] . ' (' . $data['previous_id'] . ')' . PHP_EOL;
    echo '[TO] ' . $data['replacement_name'] . ' (' . $data['replacement_id'] . ')' . PHP_EOL;
    echo '[BRANCH] ' . ($data['branch_id'] ?: '(unassigned)') . PHP_EOL;
    echo $apply
        ? "[RESULT] A799_ONE_RECORD_REASSIGNED_WITH_AUDIT\n"
        : "[RESULT] A799_REASSIGN_DRY_RUN_ONLY (no rows modified)\n";
} catch (\Illuminate\Validation\ValidationException $e) {
    foreach ($e->errors() as $key => $errors) {
        fwrite(STDERR, '[FAIL] ' . $key . ': ' . implode('; ', $errors) . PHP_EOL);
    }
    exit(1);
} catch (Throwable $e) {
    fwrite(STDERR, '[FAIL] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
