<?php
/** A7.5.7 pure, deterministic delivery warehouse correction eligibility. */
declare(strict_types=1);
require_once __DIR__.'/a756_evidence_rules.php';

final class A757DeliveryRules
{
    /**
     * @param array{delivery_id:string,sales_order_id:string,status:string} $delivery
     * @param list<array{item_id:string,warehouse_id:?string,product_id:?string,quantity:int,serial_id:?string,serial_status:?string}> $items
     * @param list<array{warehouse_id:?string,product_id:?string,quantity:int}> $movements
     * @param array<string,int> $ordered Product ID => ordered quantity
     * @param list<string> $existingWarehouses
     * @return array{decision:string,technical_ready:bool,approval_required:bool}
     */
    public static function evaluate(array $delivery, array $items, array $movements,
        array $ordered, array $existingWarehouses, string $itemId, string $warehouseId): array
    {
        $no = static fn(string $decision): array => [
            'decision'=>$decision, 'technical_ready'=>false, 'approval_required'=>true,
        ];
        if (!in_array($delivery['status'] ?? '', ['in_transit','delivered'], true)) {
            return $no('HOLD_DELIVERY_NOT_DISPATCHED');
        }
        if (empty($delivery['sales_order_id']) || !$items || !$movements) {
            return $no('HOLD_MISSING_SOURCE');
        }
        if ($itemId === '' || $warehouseId === '' || !in_array($warehouseId, $existingWarehouses, true)) {
            return $no('HOLD_PROPOSED_WAREHOUSE_NOT_FOUND');
        }
        $seenItems = []; $seenSerials = []; $totals = []; $target = null;
        foreach ($items as $item) {
            $id = $item['item_id'] ?? '';
            $serial = $item['serial_id'] ?? null;
            $product = $item['product_id'] ?? null;
            $qty = $item['quantity'] ?? null;
            if (!is_string($id) || $id === '' || isset($seenItems[$id])
                || !is_string($serial) || $serial === '' || isset($seenSerials[$serial])
                || !is_string($product) || $product === '' || $qty !== 1
                || !in_array($item['serial_status'] ?? '', ['assigned', 'installed'], true)) {
                return $no('HOLD_ALLOCATION_OR_SERIAL_STATE');
            }
            $seenItems[$id] = true; $seenSerials[$serial] = true;
            $totals[$product] = ($totals[$product] ?? 0) + 1;
            if ($id === $itemId) $target = $item;
        }
        if (!$target) return $no('HOLD_TARGET_NOT_FOUND');
        if (($target['warehouse_id'] ?? null) !== null) return $no('HOLD_ALREADY_HAS_WAREHOUSE');
        foreach ($totals as $product => $quantity) {
            if (!array_key_exists($product, $ordered) || $quantity > $ordered[$product]) {
                return $no('HOLD_SALES_ORDER_BOUND');
            }
        }
        $warehouses = [];
        foreach ($movements as $movement) {
            if (($movement['product_id'] ?? null) === $target['product_id']
                && is_string($movement['warehouse_id'] ?? null)
                && $movement['warehouse_id'] !== '' && ($movement['quantity'] ?? 0) > 0) {
                $warehouses[$movement['warehouse_id']] = true;
            }
        }
        if (count($warehouses) !== 1 || !isset($warehouses[$warehouseId])) {
            return $no('HOLD_STOCKOUT_WAREHOUSE_AMBIGUOUS_OR_DIFFERENT');
        }
        $simulation = A756EvidenceRules::delivery($items, $movements, $itemId);
        if (($simulation['decision'] ?? '') !== 'MATCHES_IF_PROVENANCE_APPROVED') {
            return $no('HOLD_SIMULATED_ALLOCATION_MISMATCH');
        }
        return ['decision'=>'TECHNICAL_MATCH_PENDING_HUMAN_PROOF', 'technical_ready'=>true, 'approval_required'=>true];
    }

    /** Validate an external approval record; not proof that a document is authentic. */
    public static function approval(array $approval, string $deliveryId, string $itemId, string $warehouseId): bool
    {
        foreach ([
            'decision'=>'approve_single_delivery_item_warehouse_correction',
            'delivery_id'=>$deliveryId,
            'delivery_item_id'=>$itemId,
            'warehouse_id'=>$warehouseId,
        ] as $key=>$expected) {
            if (($approval[$key] ?? null) !== $expected) return false;
        }
        foreach (['original_dispatch_document_reference','approved_by','independently_verified_by','approved_at'] as $key) {
            $s = $approval[$key] ?? null;
            if (!is_string($s) || trim($s) === '' || strlen($s) > 240 || preg_match('/[\x00-\x1F]/', $s)) return false;
        }
        if ($approval['approved_by'] === $approval['independently_verified_by']) return false;
        if (($approval['original_dispatch_document_verified'] ?? null) !== true
            || ($approval['warehouse_provenance_independently_verified'] ?? null) !== true) return false;
        $ts = strtotime($approval['approved_at']);
        if ($ts === false || $ts > time() + 300 || $ts < strtotime('-90 days')) return false;
        return true;
    }

    /** Verify the sole targeted field update really reconciles ALL stock-out rows. */
    public static function postcondition(array $items, array $movements): bool
    {
        $allocation = [];
        foreach ($items as $item) {
            $allocation[] = [
                'warehouse_id' => (string) ($item['warehouse_id'] ?? ''),
                'product_id' => (string) ($item['product_id'] ?? ''),
                'quantity' => (int) ($item['quantity'] ?? 0),
            ];
        }
        return A756EvidenceRules::matchQuantities($allocation, array_map(static fn(array $row): array => [
            'warehouse_id'=>(string) ($row['warehouse_id'] ?? ''),
            'product_id'=>(string) ($row['product_id'] ?? ''),
            'quantity'=>(int) ($row['quantity'] ?? 0),
        ], $movements));
    }
}
