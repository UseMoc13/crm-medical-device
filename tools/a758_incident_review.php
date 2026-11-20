<?php
/**
 * A7.5.8 — local-only, read-only, scoped Installation + Invoice review.
 * Explicit IDs prevent accidentally inspecting every business record.
 * No IDs, customer names, invoice amounts, serials, or SQL printed.
 */
declare(strict_types=1);

require_once __DIR__ . '/a758_case_rules.php';

function a758Stop(string $message, int $code = 2): never
{
    fwrite(STDERR, '[STOP] ' . $message . PHP_EOL);
    exit($code);
}

$help = "A7.5.8 scoped case review (LOCAL, READ-ONLY)\n"
    . "Usage: php tools/a758_incident_review.php --installation=UUID --invoice=UUID\n"
    . "No update, repair, migration, approval, or write options exist.\n";

if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
    echo $help;
    exit(0);
}

$options = [];
foreach (array_slice($argv, 1) as $arg) {
    if (!preg_match('/^--(installation|invoice)=(.+)$/D', $arg, $m) || isset($options[$m[1]])) {
        a758Stop('Unexpected or duplicated option. Use --help for usage.');
    }
    $options[$m[1]] = $m[2];
}
if (count($options) !== 2) a758Stop('Both --installation and --invoice are required.');
foreach (['installation', 'invoice'] as $key) {
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/iD', $options[$key])) {
        a758Stop('Invalid UUID syntax for --' . $key . '.');
    }
}

$root = dirname(__DIR__);
if (!is_file($root . '/artisan') || !is_file($root . '/vendor/autoload.php') || !is_file($root . '/bootstrap/app.php')) {
    a758Stop('Run this file inside the Laravel project root with Composer dependencies installed.');
}
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!app()->environment('local')) a758Stop('For privacy, this review is permitted only when APP_ENV=local.');
if (\Illuminate\Support\Facades\DB::connection()->getDriverName() !== 'pgsql') {
    a758Stop('Expected PostgreSQL. This tool refuses to inspect another database driver.');
}

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$required = [
    'installations' => ['installation_id', 'delivery_id', 'status'],
    'deliveries' => ['delivery_id', 'status'],
    'installation_checklists' => ['installation_id', 'is_completed'],
    'delivery_items' => ['delivery_id', 'serial_number_id'],
    'serial_numbers' => ['serial_number_id', 'status'],
    'invoices' => ['invoice_id', 'status'],
];
try {
    foreach ($required as $table => $columns) {
        if (!Schema::hasTable($table)) a758Stop('Required schema is incomplete (missing table).');
        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) a758Stop('Required schema is incomplete (missing column).');
        }
    }
} catch (Throwable $error) {
    a758Stop('Schema check failed (' . class_basename($error) . ').');
}

$connection = DB::connection();
$begun = false;
try {
    $connection->beginTransaction();
    $begun = true;
    // PostgreSQL protects all downstream reads from accidental modifications.
    $connection->statement('SET TRANSACTION READ ONLY');

    $installation = DB::table('installations')
        ->where('installation_id', $options['installation'])
        ->select('delivery_id', 'status')->first();
    $invoice = DB::table('invoices')
        ->where('invoice_id', $options['invoice'])
        ->select('status')->first();
    if (!$installation || !$invoice) a758Stop('One or both case IDs were not found; no alternative records were selected.');

    $deliveryStatus = DB::table('deliveries')->where('delivery_id', $installation->delivery_id)->value('status');
    $checklistTotal = (int) DB::table('installation_checklists')
        ->where('installation_id', $options['installation'])->count();
    $checklistPending = (int) DB::table('installation_checklists')
        ->where('installation_id', $options['installation'])
        ->where(static function ($query): void {
            $query->where('is_completed', false)->orWhereNull('is_completed');
        })->count();

    $items = DB::table('delivery_items as di')
        ->leftJoin('serial_numbers as sn', 'sn.serial_number_id', '=', 'di.serial_number_id')
        ->where('di.delivery_id', $installation->delivery_id)
        ->select('di.serial_number_id', 'sn.status')
        ->get();
    $itemCount = $items->count();
    $serialIds = [];
    $stateCounts = ['installed' => 0, 'assigned' => 0, 'other' => 0, 'missing' => 0];
    foreach ($items as $item) {
        if ($item->serial_number_id !== null) $serialIds[] = (string) $item->serial_number_id;
        if ($item->status === null) {
            $stateCounts['missing']++;
        } elseif ($item->status === 'installed') {
            $stateCounts['installed']++;
        } elseif ($item->status === 'assigned') {
            $stateCounts['assigned']++;
        } else {
            $stateCounts['other']++;
        }
    }
    $uniqueSerials = count($serialIds) === $itemCount && count(array_unique($serialIds)) === $itemCount;

    $warrantyEvidence = 'not_available';
    if (Schema::hasTable('warranties') && Schema::hasColumn('warranties','serial_number_id')) {
        $warrantyEvidence = DB::table('warranties')
            ->whereIn('serial_number_id', $serialIds)->count();
    }

    $auditEvents = ['installation' => 'unavailable', 'invoice' => 'unavailable'];
    if (Schema::hasTable('workspace_audit_events')
        && Schema::hasColumn('workspace_audit_events', 'event_type')
        && Schema::hasColumn('workspace_audit_events', 'subject_type')
        && Schema::hasColumn('workspace_audit_events', 'subject_id')) {
        foreach (['installation' => $options['installation'], 'invoice' => $options['invoice']] as $entity => $id) {
            $auditEvents[$entity] = DB::table('workspace_audit_events')
                ->where('event_type', $entity . '.status_changed')
                ->where('subject_type', $entity)
                ->where('subject_id', $id)->count();
        }
    }

    $invoiceItemCount = 'unavailable';
    if (Schema::hasTable('invoice_items') && Schema::hasColumn('invoice_items', 'invoice_id')) {
        $invoiceItemCount = DB::table('invoice_items')->where('invoice_id', $options['invoice'])->count();
    }
    $factsInstallation = [
        'status' => (string) $installation->status,
        'delivery_delivered' => $deliveryStatus === 'delivered',
        'checklist_total' => $checklistTotal,
        'checklist_pending' => $checklistPending,
        'item_count' => $itemCount,
        'unique_serials' => $uniqueSerials,
        'missing_serials' => $stateCounts['missing'],
        'installed_serials' => $stateCounts['installed'],
        'assigned_serials' => $stateCounts['assigned'],
        'other_serials' => $stateCounts['other'],
    ];
    $reviewInstallation = A758CaseRules::installation($factsInstallation);
    $reviewInvoice = A758CaseRules::invoice(['status' => (string) $invoice->status]);

    echo "A7.5.8 Installation + Billing Decision Evidence [LOCAL, READ-ONLY]\n";
    echo "[INSTALLATION] decision=" . $reviewInstallation['decision'] . PHP_EOL;
    echo '  linked_delivery_delivered=' . ($factsInstallation['delivery_delivered'] ? 'YES' : 'NO')
        . ' | checklists=' . $checklistTotal
        . ' | pending_checklists=' . $checklistPending
        . ' | allocated_serials=' . $itemCount
        . ' | unique_serials=' . ($uniqueSerials ? 'YES' : 'NO') . PHP_EOL;
    echo '  serial_states=' . json_encode($stateCounts, JSON_THROW_ON_ERROR)
        . ' | warranty_rows=' . $warrantyEvidence
        . ' | status_audit_events=' . $auditEvents['installation'] . PHP_EOL;
    echo '  blockers=' . (implode(',', $reviewInstallation['issues']) ?: 'NONE_TECHNICAL_BUT_DOCUMENTS_REQUIRED') . PHP_EOL;
    echo "  Needed: signed customer handover, serial lifecycle evidence, independent approver.\n";

    echo "[BILLING] decision=" . $reviewInvoice['decision'] . PHP_EOL;
    echo '  invoice_items=' . $invoiceItemCount
        . ' | status_audit_events=' . $auditEvents['invoice'] . PHP_EOL;
    echo '  blockers=' . (implode(',', $reviewInvoice['issues']) ?: 'NONE') . PHP_EOL;
    echo "  Needed: original invoice issuance and payment/collection records, Finance decision.\n";
    echo "RESULT: HUMAN_DOCUMENTARY_REVIEW_REQUIRED | corrections_applied=0\n";
    echo "[SAFE] Transaction read only; no UUIDs, amounts, customer names, serial strings or raw statuses printed.\n";
} catch (Throwable $error) {
    // Deliberately omit exception messages: they can contain SQL, connection info or record values.
    a758Stop('Evidence query aborted (' . class_basename($error) . ').');
} finally {
    if ($begun && $connection->transactionLevel() > 0) $connection->rollBack();
}
