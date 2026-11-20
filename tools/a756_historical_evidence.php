<?php
/**
 * A7.5.6 historical provenance preflight.
 * Laravel READ-ONLY diagnostic: never updates inventory, allocation, serial, invoice or status.
 * Prints no UUIDs, serial strings, customer names, invoice totals or stock movement notes.
 * Run locally: php tools/a756_historical_evidence.php
 */
declare(strict_types=1);

require __DIR__.'/a756_evidence_rules.php';
$root = dirname(__DIR__);
if (!is_file($root.'/vendor/autoload.php') || !is_file($root.'/bootstrap/app.php')) {
    fwrite(STDERR, "[STOP] Run from an installed Laravel project root with Composer vendor files.\n");
    exit(2);
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CrmIntegrationInvestigation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!app()->environment('local')) {
    fwrite(STDERR, "[STOP] This evidence tool is restricted to APP_ENV=local. No queries performed.\n");
    exit(2);
}
if (DB::connection()->getDriverName() !== 'pgsql') {
    fwrite(STDERR, "[STOP] A7.5.6 evidence analysis expects PostgreSQL, not a test fixture. No business queries performed.\n");
    exit(2);
}
$needed = [
    'deliveries'=>['delivery_id','sales_order_id','status'],
    'delivery_items'=>['delivery_item_id','delivery_id','serial_number_id','warehouse_id','quantity'],
    'serial_numbers'=>['serial_number_id','product_id','status'],
    'stock_movements'=>['warehouse_id','product_id','quantity','reference_type','reference_id','movement_type'],
    'warehouses'=>['warehouse_id'],
    'sales_order_items'=>['sales_order_id','product_id','quantity'],
    'installations'=>['installation_id','delivery_id','status'],
    'installation_checklists'=>['installation_id','is_completed'],
    'invoices'=>['invoice_id','status'],
];
$missing=[];
foreach ($needed as $table => $columns) {
    if (!Schema::hasTable($table)) { $missing[]=$table; continue; }
    foreach ($columns as $column) if (!Schema::hasColumn($table,$column)) $missing[]=$table.'.'.$column;
}
if ($missing) {
    fwrite(STDERR, '[STOP] Missing required schema: '.implode(', ', $missing).".\n");
    exit(2);
}

// Prevent any accidental persistence by this diagnostic, even if a future code
// change mistakenly introduces query builder INSERT/UPDATE/DELETE calls.
DB::connection()->beforeExecuting(static function (string $query): void {
    $normalized = ltrim(preg_replace('/(?:\/\*.*?\*\/|--[^\n]*\n)/s', '', $query) ?? $query);
    if (preg_match('/^(?:INSERT|UPDATE|DELETE|MERGE|ALTER|DROP|TRUNCATE|CREATE|GRANT|REVOKE|COPY|CALL|DO|VACUUM|REINDEX|REFRESH|SELECT\s+.+\s+FOR\s+(?:UPDATE|SHARE))\b/i', $normalized)) {
        throw new RuntimeException('A7.5.6 read-only guard blocked a possible mutation or locking query.');
    }
});

$print = static function (string $message): void { echo $message.PHP_EOL; };
$bool = static fn(bool $v): string => $v ? 'YES' : 'NO';
$groupSerials = static function (iterable $rows): array {
    $count = ['installed'=>0,'assigned'=>0,'available'=>0,'other'=>0,'missing'=>0];
    foreach ($rows as $row) {
        $raw = $row->serial_status ?? $row->status ?? null;
        $key = $raw === null ? 'missing' : strtolower(trim((string) $raw));
        if (!array_key_exists($key, $count)) $key = 'other';
        $count[$key]++;
    }
    return $count;
};

try {
    $report = app(CrmIntegrationInvestigation::class)->run(true,20);
    $summary = $report['summary'] ?? [];
    if (($summary['checked'] ?? null) !== 4 || ($summary['skipped'] ?? -1) !== 0 || ($summary['errors'] ?? -1) !== 0) {
        throw new RuntimeException('Source investigator incomplete or contains query errors.');
    }
    $checks = [];
    foreach ($report['checks'] as $entry) {
        $checks[$entry['id']] = $entry;
        if (($entry['details_limited'] ?? false) || ($entry['count'] ?? 0) > 20) {
            throw new RuntimeException('Finding details truncated; inspect locally with a larger secure process.');
        }
        if (($entry['status'] ?? '') === 'flagged' && count($entry['details'] ?? []) !== (int) ($entry['count'] ?? -1)) {
            throw new RuntimeException('Finding details do not cover the entire flagged set.');
        }
    }
    $ids = [
        'delivery.dispatched_item_missing_warehouse', 'delivery.stockout_quantity_mismatch',
        'installation.unknown_status', 'billing.unknown_status',
    ];
    foreach ($ids as $id) if (!isset($checks[$id])) throw new RuntimeException('Required investigation rule not found.');

    $print('A7.5.6 Historical Evidence Preflight [LOCAL, READ-ONLY]');
    $print('Source: '.($summary['checked'] ?? 0).' rules | flagged: '.($summary['flagged_rules'] ?? 0)
        .' | source errors: 0 | no IDs, amounts or customer data displayed');
    $print('WARNING: Simulated warehouse consistency is NOT proof of dispatch provenance.');

    foreach ($checks['delivery.dispatched_item_missing_warehouse']['details'] ?? [] as $index => $detail) {
        $deliveryId = (string) $detail['delivery_id'];
        $itemId = (string) $detail['delivery_item_id'];
        $items = DB::table('delivery_items as di')
            ->join('deliveries as d','d.delivery_id','=','di.delivery_id')
            ->leftJoin('serial_numbers as sn','sn.serial_number_id','=','di.serial_number_id')
            ->where('di.delivery_id',$deliveryId)
            ->select('di.delivery_item_id','di.warehouse_id','di.quantity','di.serial_number_id',
                'sn.product_id','sn.status as serial_status','d.status as delivery_status','d.sales_order_id')
            ->orderBy('di.delivery_item_id')->get();
        $movements = DB::table('stock_movements')
            ->where('reference_type','delivery')->where('reference_id',$deliveryId)->where('movement_type','stock_out')
            ->select('warehouse_id','product_id','quantity')->get();
        $normalizedItems = $items->map(static fn($row): array => [
            'item_id'=>(string)$row->delivery_item_id,
            'warehouse_id'=>$row->warehouse_id === null ? null : (string)$row->warehouse_id,
            'product_id'=>$row->product_id === null ? null : (string)$row->product_id,
            'quantity'=>(int)$row->quantity,
            'serial_id'=>$row->serial_number_id === null ? null : (string)$row->serial_number_id,
        ])->all();
        $normalizedMovements = $movements->map(static fn($row): array => [
            'warehouse_id'=>$row->warehouse_id === null ? null : (string)$row->warehouse_id,
            'product_id'=>$row->product_id === null ? null : (string)$row->product_id,
            'quantity'=>(int)$row->quantity,
        ])->all();
        $decision = A756EvidenceRules::delivery($normalizedItems,$normalizedMovements,$itemId);
        $allSerials = $groupSerials($items);
        $boundsOk = $items->isNotEmpty();
        $orderId = $items->first()?->sales_order_id;
        $ordered = $orderId ? DB::table('sales_order_items')->where('sales_order_id',$orderId)
            ->select('product_id')->selectRaw('SUM(quantity) AS ordered_qty')
            ->groupBy('product_id')->pluck('ordered_qty','product_id')->all() : [];
        $totals = [];
        foreach ($items as $item) {
            if ($item->product_id === null) { $boundsOk=false; continue; }
            $product = (string)$item->product_id;
            $totals[$product] = ($totals[$product] ?? 0) + (int)$item->quantity;
        }
        foreach ($totals as $product=>$qty) {
            if (!array_key_exists($product,$ordered) || $qty > (int)$ordered[$product]) $boundsOk = false;
        }
        $candidateExists = false;
        if (($decision['candidate_warehouse_count'] ?? 0) === 1) {
            $target = $items->firstWhere('delivery_item_id',$itemId);
            $candidateW = [];
            if ($target && $target->product_id !== null) foreach ($movements as $move) {
                if ((string)$move->product_id === (string)$target->product_id && $move->warehouse_id !== null
                    && (int)$move->quantity > 0) $candidateW[(string)$move->warehouse_id]=true;
            }
            if (count($candidateW) === 1) {
                $candidateExists = DB::table('warehouses')->where('warehouse_id',array_key_first($candidateW))->exists();
            }
        }
        $caseDecision = ($decision['decision'] === 'MATCHES_IF_PROVENANCE_APPROVED' && $boundsOk && $candidateExists)
            ? 'CANDIDATE_NEEDS_ORIGINAL_DISPATCH_PROOF_AND_APPROVAL' : 'HOLD_RECONCILIATION';
        $print('[DELIVERY case '.($index+1).'] item_count='.$items->count().' | stockout_rows='.$movements->count()
            .' | candidate_warehouse_count='.($decision['candidate_warehouse_count'] ?? 0)
            .' | simulated='.$decision['decision'].' | sales_order_bound='.$bool($boundsOk)
            .' | warehouse_exists='.$bool($candidateExists).' | decision='.$caseDecision);
        $print('  serial_state_counts='.json_encode($allSerials,JSON_UNESCAPED_SLASHES));
        $print('  Required: original dispatch/warehouse document, authorized reviewer, independent verification; NO stock movement creation.');
    }
    // Report any mismatch not tied to the missing-warehouse rule; do not falsely treat it as resolved.
    $missingDetails = $checks['delivery.dispatched_item_missing_warehouse']['details'] ?? [];
    $missingDeliveryIds = array_fill_keys(array_map(static fn($d): string=>(string)$d['delivery_id'],$missingDetails),true);
    $unpaired = 0;
    foreach ($checks['delivery.stockout_quantity_mismatch']['details'] ?? [] as $detail) {
        if (!isset($missingDeliveryIds[(string)$detail['delivery_id']])) $unpaired++;
    }
    $print('[DELIVERY] unmatched_stockout_mismatch_cases='.$unpaired.' | automatic_data_corrections=0');

    foreach ($checks['installation.unknown_status']['details'] ?? [] as $index => $detail) {
        $installation = DB::table('installations')->where('installation_id',(string)$detail['record_id'])
            ->first(['delivery_id','status']);
        if (!$installation) throw new RuntimeException('Historical installation record no longer exists.');
        $deliveryStatus = DB::table('deliveries')->where('delivery_id',$installation->delivery_id)->value('status');
        $checklistTotal = DB::table('installation_checklists')->where('installation_id',(string)$detail['record_id'])->count();
        $completeCount = DB::table('installation_checklists')->where('installation_id',(string)$detail['record_id'])
            ->where('is_completed',true)->count();
        $serialRows = DB::table('delivery_items as di')
            ->leftJoin('serial_numbers as sn','sn.serial_number_id','=','di.serial_number_id')
            ->where('di.delivery_id',$installation->delivery_id)->select('sn.status as serial_status')->get();
        $serialStatuses = $serialRows->map(static fn($row): string => $row->serial_status === null ? 'missing' : (string)$row->serial_status)->all();
        $decision = A756EvidenceRules::installation((string)$installation->status,
            $deliveryStatus === null ? null : (string)$deliveryStatus,(int)$checklistTotal,(int)$completeCount,$serialStatuses);
        $print('[INSTALLATION case '.($index+1).'] status_format_only='.$bool($decision['format_only'])
            .' | delivered='.$bool($decision['delivery_delivered'])
            .' | checklist_complete='.$bool($decision['checklist_complete'])
            .' | all_serials_installed='.$bool($decision['all_serials_installed'])
            .' | decision='.$decision['decision']);
        $print('  serial_state_counts='.json_encode($groupSerials($serialRows), JSON_UNESCAPED_SLASHES));
        $print('  Required: verify each device lifecycle and customer handover before any status normalization.');
    }

    $auditEnabled = Schema::hasTable('workspace_audit_events')
        && Schema::hasColumn('workspace_audit_events','event_type')
        && Schema::hasColumn('workspace_audit_events','subject_type')
        && Schema::hasColumn('workspace_audit_events','subject_id');
    foreach ($checks['billing.unknown_status']['details'] ?? [] as $index => $detail) {
        $status = DB::table('invoices')->where('invoice_id',(string)$detail['record_id'])->value('status');
        if ($status === null) throw new RuntimeException('Historical Invoice missing or has null status; human review required.');
        $decision = A756EvidenceRules::invoice((string)$status);
        $events = $auditEnabled ? DB::table('workspace_audit_events')
            ->where('event_type','invoice.status_changed')->where('subject_type','invoice')
            ->where('subject_id',(string)$detail['record_id'])->count() : null;
        $print('[BILLING case '.($index+1).'] classification='.($decision['classification'] ?? 'recognized')
            .' | status_change_audit_events='.($events === null ? 'unavailable' : (string)$events)
            .' | decision='.$decision['decision']);
        $print('  Required: confirm original invoice issuance, collection and payment history. No Draft/Sent/Paid guess.');
    }
    $print('RESULT: MANUAL_EVIDENCE_AND_APPROVAL_REQUIRED | No DB writes performed by this tool.');
    exit(0);
} catch (Throwable $e) {
    // Avoid echoing the exception text: database exceptions may contain SQL and record identifiers.
    fwrite(STDERR, '[STOP] Evidence incomplete or database validation failed ('.get_class($e).'). No correction attempted.'.PHP_EOL);
    exit(2);
}
