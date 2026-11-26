<?php
/** A7.9.9 read-only regression gate. No record is updated or deleted. */
declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$limit = 12;
$strict = false;
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--strict') {
        $strict = true;
    } elseif (preg_match('/\A--limit=(\d{1,2})\z/D', $arg, $match) && (int) $match[1] <= 50) {
        $limit = (int) $match[1];
    } else {
        fwrite(STDERR, "Usage: php tools/a799_integration_gate.php [--limit=0..50] [--strict]\n");
        exit(2);
    }
}

try {
    $db = \Illuminate\Support\Facades\DB::connection();
    $schema = \Illuminate\Support\Facades\Schema::connection($db->getName());
    foreach (['users', 'user_roles', 'roles', 'service_ticket_work_logs', 'audit_logs',
        'service_tickets', 'maintenance_schedules', 'maintenance_records',
        'installations', 'leads', 'opportunities'] as $table) {
        if (!$schema->hasTable($table)) {
            throw new RuntimeException('Missing table: ' . $table);
        }
    }
    if (!$schema->hasColumn('service_ticket_work_logs', 'created_by_user_id')) {
        throw new RuntimeException('A798 work log author migration is missing.');
    }
    echo '[DATABASE] ' . $db->getDatabaseName() . PHP_EOL;
    echo '[CHECK] A798 work-log author column: OK' . PHP_EOL;
    $roles = app(\App\Services\CrmUserAssignmentEligibility::class);
    foreach (['technician', 'sales'] as $purpose) {
        echo sprintf('[ELIGIBLE] %s=%d', $purpose, $roles->eligibleQuery($purpose)->count()) . PHP_EOL;
    }

    $sources = [
        'service_tickets' => ['key'=>'service_ticket_id', 'purpose'=>'technician'],
        'maintenance_schedules' => ['key'=>'maintenance_schedule_id', 'purpose'=>'technician'],
        'maintenance_records' => ['key'=>'maintenance_record_id', 'purpose'=>'technician'],
        'installations' => ['key'=>'installation_id', 'purpose'=>'technician'],
        'leads' => ['key'=>'lead_id', 'purpose'=>'sales'],
        'opportunities' => ['key'=>'opportunity_id', 'purpose'=>'sales'],
    ];
    $total = $operational = $history = 0;
    foreach ($sources as $table => $spec) {
        $eligibleIds = $roles->eligibleQuery($spec['purpose'])->select('users.user_id');
        $ineligible = \Illuminate\Support\Facades\DB::table($table.' as r')
            ->whereNotNull('r.user_id')->whereNotIn('r.user_id', $eligibleIds);
        $count = (clone $ineligible)->count();
        echo sprintf('[MODULE] %s | role=%s | review=%d', $table, $spec['purpose'], $count) . PHP_EOL;
        $total += $count;
        $examples = (clone $ineligible)->leftJoin('users as u','u.user_id','=','r.user_id')
            ->orderBy('r.'.$spec['key'])->limit($limit)
            ->get(array_merge([
                'r.'.$spec['key'].' as id',
                'r.user_id as assigned_id',
                'u.name as assigned_name',
            ], $table === 'maintenance_records' ? [] : ['r.status']));
        foreach ($examples as $item) {
            $status = $table === 'maintenance_records' ? null : (string) $item->status;
            $classification = in_array($table, ['leads','opportunities'], true)
                ? 'SALES_OWNER_REVIEW'
                : \App\Services\CrmOperationalAssignmentPolicy::classification($table, $status);
            echo sprintf('  [%s] id=%s | status=%s | assigned=%s | user=%s',
                $classification, $item->id, $status ?? '(performed record)', $item->assigned_id,
                $item->assigned_name ?? '(missing)') . PHP_EOL;
        }
        if (in_array($table, ['leads', 'opportunities'], true)) {
            $operational += $count;
            continue;
        }
        if ($table === 'maintenance_records') {
            $history += $count;
            continue;
        }
        // Count ALL records, not only preview examples.
        $records = (clone $ineligible)->get(['r.status']);
        foreach ($records as $row) {
            if (\App\Services\CrmOperationalAssignmentPolicy::mutable($table, (string) $row->status)) {
                $operational++;
            } else {
                $history++;
            }
        }
    }

    $logs = \Illuminate\Support\Facades\DB::table('service_ticket_work_logs');
    echo sprintf('[WORKLOG] total=%d | author_attributed=%d | legacy_unknown=%d',
        (clone $logs)->count(), (clone $logs)->whereNotNull('created_by_user_id')->count(),
        (clone $logs)->whereNull('created_by_user_id')->count()) . PHP_EOL;
    echo sprintf('[SUMMARY] review=%d | operational=%d | historical=%d', $total, $operational, $history) . PHP_EOL;
    echo '[NOTE] Report is read-only. Historical performer/finalized records must not be rewritten.' . PHP_EOL;
    if ($strict && $operational > 0) {
        echo '[RESULT] A799_GATE_ACTIONABLE_REVIEW_REQUIRED' . PHP_EOL;
        exit(3);
    }
    echo '[RESULT] ' . ($operational > 0 ? 'A799_GATE_REVIEW_REQUIRED' : 'A799_GATE_NO_ACTIONABLE_FINDINGS') . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, '[FAIL] Integration gate: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
