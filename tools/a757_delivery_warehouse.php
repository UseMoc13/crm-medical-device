<?php
/**
 * A7.5.7 one-row, human-approved Delivery warehouse provenance repair.
 * DEFAULT = preview/read-only. No changes without --execute + external approval + confirmation.
 * Intentionally does not modify inventory, stock_movements, serials, deliveries or statuses.
 */
declare(strict_types=1);
require __DIR__.'/a757_delivery_rules.php';

$root = dirname(__DIR__);
$print = static fn(string $s) => print($s.PHP_EOL);
$stop = static function (string $s): never {
    fwrite(STDERR, '[STOP] '.$s.PHP_EOL);
    exit(2);
};
$args = getopt('', ['delivery:','item:','warehouse:','execute','confirm:','approval-file:','help']);
if (isset($args['help']) || !isset($args['delivery'], $args['item'], $args['warehouse'])) {
    echo <<<'HELP'
A7.5.7 — one-row delivery warehouse reconciliation (LOCAL PostgreSQL)
Preview (read-only):
  php tools/a757_delivery_warehouse.php --delivery=UUID --item=UUID --warehouse=UUID

Approved execute (ONLY after original dispatch proof, independent review, and backup):
  php tools/a757_delivery_warehouse.php --delivery=UUID --item=UUID --warehouse=UUID --execute --approval-file="C:\secure\approval.json" --confirm=APPLY_APPROVED_WAREHOUSE_CORRECTION

Default is READ-ONLY. The approval JSON is private, outside the Git repository.
This tool cannot independently authenticate original business documents or reviewer authority.
HELP;
    echo PHP_EOL;
    exit(0);
}
$deliveryId = (string)$args['delivery'];
$itemId = (string)$args['item'];
$warehouseId = (string)$args['warehouse'];
$uuid = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/Di';
foreach ([$deliveryId,$itemId,$warehouseId] as $id) {
    if (preg_match($uuid, $id) !== 1) $stop('Expected three valid UUIDs; no database operation attempted.');
}
$execute = array_key_exists('execute', $args);
if ($execute && (($args['confirm'] ?? '') !== 'APPLY_APPROVED_WAREHOUSE_CORRECTION'
        || !is_string($args['approval-file'] ?? null))) {
    $stop('Execution requires --approval-file and exact --confirm; no database operation attempted.');
}
if (!$execute && (isset($args['approval-file']) || isset($args['confirm']))) {
    $stop('Approval options are only valid in explicit execute mode.');
}

// Approval gate before Laravel or any DB reads.
$approval = null; $approvalSha = null;
if ($execute) {
    $file = $args['approval-file'];
    if (!is_file($file) || !is_readable($file) || filesize($file) > 8192) {
        $stop('External approval file absent, unreadable or too large.');
    }
    // Keep approval outside the source tree and .git commits.
    $absolute = realpath($file);
    $repoRoot = realpath($root);
    if ($absolute === false || $repoRoot === false
        || str_starts_with(strtolower(str_replace('\\','/',$absolute)), strtolower(str_replace('\\','/',$repoRoot)).'/')) {
        $stop('Approval must be stored outside the Git repository.');
    }
    $rawApproval = file_get_contents($absolute);
    $approval = is_string($rawApproval) ? json_decode($rawApproval, true) : null;
    if (!is_array($approval) || !A757DeliveryRules::approval($approval, $deliveryId,$itemId,$warehouseId)) {
        $stop('Approval metadata incomplete/mismatched or lacks independent verification.');
    }
    $approvalSha = hash('sha256', $rawApproval);
}
if (!is_file($root.'/vendor/autoload.php') || !is_file($root.'/bootstrap/app.php')) {
    $stop('Laravel bootstrap/vendor missing. Extract tools into CRM repository root.');
}
// Fail closed if user accidentally runs from main or another branch.
$branchResult=[]; $branchCode=1;
exec('git -C '.escapeshellarg($root).' branch --show-current 2>&1', $branchResult, $branchCode);
if ($branchCode!==0 || trim(implode('', $branchResult))!=='Big-Changes') {
    $stop('Expected git branch Big-Changes.');
}
require_once $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!app()->environment('local')) $stop('APP_ENV must be local; tool is not authorized for production or staging.');
if (DB::connection()->getDriverName() !== 'pgsql') $stop('PostgreSQL connection required.');
if ($execute) {
    // A local APP_ENV pointing to a remote/production DB must NEVER get an apply path.
    $host = trim((string) config('database.connections.pgsql.host', ''));
    if (!in_array(strtolower($host), ['localhost','127.0.0.1','::1'], true)) {
        $stop('Execution blocked: DB_HOST is not local loopback. No writes performed.');
    }
}
$requirements = [
    'deliveries'=>['delivery_id','sales_order_id','status'],
    'delivery_items'=>['delivery_item_id','delivery_id','serial_number_id','warehouse_id','quantity'],
    'serial_numbers'=>['serial_number_id','product_id','status'],
    'stock_movements'=>['reference_id','reference_type','movement_type','warehouse_id','product_id','quantity'],
    'warehouses'=>['warehouse_id'],
    'sales_order_items'=>['sales_order_id','product_id','quantity'],
];
foreach ($requirements as $table=>$columns) {
    if (!Schema::hasTable($table)) $stop('Missing required table; no changes made.');
    foreach ($columns as $column) if (!Schema::hasColumn($table,$column)) $stop('Missing required column; no changes made.');
}
// Prevent a future accidental new write path while previewing. Also use a
// PostgreSQL READ ONLY transaction to enforce server-side read-only mode.
if (!$execute) {
    DB::connection()->beforeExecuting(static function(string $query): void {
        $trim = ltrim(preg_replace('/\/\*.*?\*\/|--[^\n]*(?:\n|$)/s', '', $query) ?? $query);
        if (preg_match('/^(?:INSERT|UPDATE|DELETE|MERGE|ALTER|DROP|TRUNCATE|CREATE|GRANT|REVOKE|COPY|CALL|DO|VACUUM)\b/i', $trim)) {
            throw new RuntimeException('Write query intercepted during preview.');
        }
    });
}

$journalPath = null;
try {
    $result = DB::transaction(static function () use ($deliveryId,$itemId,$warehouseId,$execute,$approval,$approvalSha,&$journalPath): array {
        // Must be the first command in this transaction.
        DB::statement($execute ? 'SET TRANSACTION ISOLATION LEVEL SERIALIZABLE' : 'SET TRANSACTION READ ONLY');
        $deliveryQ = DB::table('deliveries')->where('delivery_id',$deliveryId);
        if ($execute) $deliveryQ->lockForUpdate();
        $delivery = $deliveryQ->first(['delivery_id','sales_order_id','status']);
        if (!$delivery) throw new RuntimeException('Delivery not found');
        $rawItemsQ = DB::table('delivery_items')->where('delivery_id',$deliveryId)->orderBy('delivery_item_id');
        if ($execute) $rawItemsQ->lockForUpdate();
        $rawItems = $rawItemsQ->get(['delivery_item_id','warehouse_id','quantity','serial_number_id']);
        if ($rawItems->isEmpty()) throw new RuntimeException('Delivery has no allocated items');
        $serialIds=$rawItems->pluck('serial_number_id')->filter()->all();
        $serials=DB::table('serial_numbers')->whereIn('serial_number_id',$serialIds)
            ->get(['serial_number_id','product_id','status'])->keyBy('serial_number_id');
        $items=$rawItems->map(static function($item) use ($serials): array {
            $serial=$serials->get($item->serial_number_id);
            return [
                'item_id'=>(string)$item->delivery_item_id,
                'warehouse_id'=>$item->warehouse_id === null ? null : (string)$item->warehouse_id,
                'product_id'=>$serial?->product_id === null ? null : (string)$serial->product_id,
                'serial_id'=>$item->serial_number_id === null ? null : (string)$item->serial_number_id,
                'serial_status'=>$serial?->status === null ? null : (string)$serial->status,
                'quantity'=>(int)$item->quantity,
            ];
        })->all();
        $moveQ=DB::table('stock_movements')
            ->where('reference_type','delivery')->where('movement_type','stock_out')
            ->whereRaw('CAST(reference_id AS TEXT) = ?', [$deliveryId]);
        if ($execute) $moveQ->lockForUpdate();
        $movements=$moveQ->get(['warehouse_id','product_id','quantity'])->map(static fn($r): array => [
            'warehouse_id'=>$r->warehouse_id === null ? null : (string)$r->warehouse_id,
            'product_id'=>$r->product_id === null ? null : (string)$r->product_id,
            'quantity'=>(int)$r->quantity,
        ])->all();
        $ordered=DB::table('sales_order_items')->where('sales_order_id',$delivery->sales_order_id)
            ->groupBy('product_id')->select('product_id')->selectRaw('SUM(quantity) AS total_qty')
            ->get()->mapWithKeys(static fn($r): array => [(string)$r->product_id=>(int)$r->total_qty])->all();
        $candidateExists=DB::table('warehouses')->where('warehouse_id',$warehouseId)->exists();
        $decision=A757DeliveryRules::evaluate([
            'delivery_id'=>(string)$delivery->delivery_id,
            'sales_order_id'=>(string)$delivery->sales_order_id,
            'status'=>(string)$delivery->status,
        ],$items,$movements,$ordered,$candidateExists?[$warehouseId]:[],$itemId,$warehouseId);
        if (!$decision['technical_ready']) return $decision;
        if (!$execute) return $decision;

        // Unrelated active Delivery with same serial is a hard stop.
        $target = null;
        foreach ($items as $row) if ($row['item_id']===$itemId) $target=$row;
        if ($target===null) throw new RuntimeException('Target disappeared');
        $taken = DB::table('delivery_items as di')
            ->join('deliveries as d', 'd.delivery_id','=','di.delivery_id')
            ->where('di.serial_number_id',$target['serial_id'])
            ->where('di.delivery_id','!=',$deliveryId)
            ->where('d.status','!=','cancelled')->exists();
        if ($taken) throw new RuntimeException('Serial allocated to another active delivery');

        // Write a local sensitive BEFORE snapshot prior to the transaction update.
        $dir = rtrim(sys_get_temp_dir(),'/\\').DIRECTORY_SEPARATOR.'crm_a757_provenance';
        if (!is_dir($dir) && !mkdir($dir,0700,true) && !is_dir($dir)) {
            throw new RuntimeException('Cannot create private evidence directory');
        }
        $journalPath = $dir.DIRECTORY_SEPARATOR.date('Ymd-His').'-'.bin2hex(random_bytes(6)).'.json';
        $snapshot = [
            'stage'=>'A7.5.7', 'recorded_at'=>date(DATE_ATOM), 'action'=>'single_warehouse_id_backfill',
            'delivery_id'=>$deliveryId,'delivery_item_id'=>$itemId,
            'before_warehouse_id'=>null,'proposed_warehouse_id'=>$warehouseId,
            'approval_sha256'=>$approvalSha,
            'approval_reference'=>$approval['original_dispatch_document_reference'] ?? null,
            'technical_precondition'=>'MATCH',
            'note'=>'Local snapshot, not a database audit event. Keep private.',
        ];
        if (file_put_contents($journalPath,json_encode($snapshot,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),LOCK_EX)===false) {
            throw new RuntimeException('Unable to store pre-change snapshot');
        }
        @chmod($journalPath,0600);

        // The only business table write in this tool; compare-and-swap on NULL.
        $changed=DB::table('delivery_items')->where('delivery_item_id',$itemId)
            ->where('delivery_id',$deliveryId)->whereNull('warehouse_id')
            ->update(['warehouse_id'=>$warehouseId]);
        if ($changed !== 1) throw new RuntimeException('Expected exactly one changed row');

        foreach ($items as &$row) if ($row['item_id']===$itemId) $row['warehouse_id']=$warehouseId;
        unset($row);
        if (!A757DeliveryRules::postcondition($items,$movements)) {
            throw new RuntimeException('Post-update reconciliation failed, transaction rolled back');
        }
        return ['decision'=>'APPLIED_ONE_FIELD', 'technical_ready'=>true, 'approval_required'=>false];
    }, 1); // Fail closed on serialization conflicts; no automatic retry of an approved mutation.
    $print('A7.5.7 Delivery Warehouse Reconciliation ['.($execute?'APPROVED WRITE':'READ-ONLY PREVIEW').']');
    $print('Decision: '.$result['decision']);
    if (!$execute) {
        $print('No changes. Original dispatch document and independent approval are STILL REQUIRED.');
        $print('No warehouse is inferred merely from one stock-out movement.');
    } elseif ($result['decision']==='APPLIED_ONE_FIELD') {
        $print('Updated exactly one delivery_items.warehouse_id after checks; no inventory/stock-out/serial/status changes.');
        $print('Private pre-change snapshot stored in system temporary directory; retain the approval document separately.');
        $print('Next: php artisan crm:integration:regression');
    }
    exit($result['technical_ready'] ? 0 : 3);
} catch (Throwable $e) {
    $stop('Transaction aborted or evidence incomplete ('.get_class($e).'). No success claim; check local database and private snapshot if execute was attempted.');
}
