<?php
/** A7.5.6 pure evaluation rules. No Laravel bootstrap or DB access. */
declare(strict_types=1);

final class A756EvidenceRules
{
    /**
     * @param list<array{item_id:string,warehouse_id:?string,product_id:?string,quantity:int,serial_id:?string}> $items
     * @param list<array{warehouse_id:?string,product_id:?string,quantity:int}> $stockouts
     * @return array<string,mixed>
     */
    public static function delivery(array $items, array $stockouts, string $targetItemId): array
    {
        $target = null;
        foreach ($items as $item) {
            if ($item['item_id'] === $targetItemId) {
                if ($target !== null) return ['decision' => 'HOLD_DUPLICATE_TARGET'];
                $target = $item;
            }
        }
        if ($target === null) return ['decision' => 'HOLD_ITEM_NOT_FOUND'];
        if ($target['warehouse_id'] !== null || !$target['product_id'] || $target['quantity'] !== 1 || !$target['serial_id']) {
            return ['decision' => 'HOLD_ITEM_INCONSISTENT'];
        }

        $warehouses = [];
        foreach ($stockouts as $row) {
            if ($row['product_id'] === $target['product_id']
                && is_string($row['warehouse_id']) && $row['warehouse_id'] !== ''
                && $row['quantity'] > 0) {
                $warehouses[$row['warehouse_id']] = true;
            }
        }
        $candidateCount = count($warehouses);
        if ($candidateCount !== 1) return ['decision' => 'HOLD_AMBIGUOUS_WAREHOUSE', 'candidate_warehouse_count' => $candidateCount];
        $candidateWarehouse = array_key_first($warehouses);

        $allocations = [];
        $seenSerials = [];
        foreach ($items as $item) {
            if (!$item['serial_id'] || !$item['product_id'] || $item['quantity'] !== 1
                || isset($seenSerials[$item['serial_id']])) {
                return ['decision' => 'HOLD_ITEM_INCONSISTENT', 'candidate_warehouse_count' => 1];
            }
            $seenSerials[$item['serial_id']] = true;
            $warehouse = $item['item_id'] === $targetItemId ? $candidateWarehouse : $item['warehouse_id'];
            if (!$warehouse) return ['decision' => 'HOLD_OTHER_ITEM_MISSING_WAREHOUSE', 'candidate_warehouse_count' => 1];
            $allocations[] = ['warehouse_id' => $warehouse, 'product_id' => $item['product_id'], 'quantity' => 1];
        }
        $moved = [];
        foreach ($stockouts as $stockout) {
            $moved[] = [
                'warehouse_id' => $stockout['warehouse_id'] ?? '',
                'product_id' => $stockout['product_id'] ?? '',
                'quantity' => $stockout['quantity'],
            ];
        }
        if (!self::matchQuantities($allocations, $moved)) {
            return ['decision' => 'HOLD_QUANTITY_OR_PRODUCT_MISMATCH', 'candidate_warehouse_count' => 1];
        }
        return [
            'decision' => 'MATCHES_IF_PROVENANCE_APPROVED',
            'candidate_warehouse_count' => 1,
            'simulated_allocation_matches_stockouts' => true,
            'source_document_verified' => false,
            'auto_apply_allowed' => false,
        ];
    }

    /** @param list<array{warehouse_id:string,product_id:string,quantity:int}> $left
     *  @param list<array{warehouse_id:string,product_id:string,quantity:int}> $right
     */
    public static function matchQuantities(array $left, array $right): bool
    {
        if (!$left || !$right) return false;
        $group = static function (array $rows): ?array {
            $totals = [];
            foreach ($rows as $row) {
                $w = $row['warehouse_id'] ?? null;
                $p = $row['product_id'] ?? null;
                $q = $row['quantity'] ?? null;
                if (!is_string($w) || trim($w) === '' || !is_string($p) || trim($p) === ''
                    || !is_int($q) || $q <= 0) return null;
                $key = $w.'|'.$p;
                $totals[$key] = ($totals[$key] ?? 0) + $q;
            }
            ksort($totals);
            return $totals;
        };
        $a = $group($left);
        $b = $group($right);
        return $a !== null && $b !== null && $a === $b;
    }

    /** @param list<string> $serialStatuses */
    public static function installation(string $status, ?string $deliveryStatus, int $checklistTotal,
        int $completedCount, array $serialStatuses): array
    {
        $formatOnly = $status !== 'Completed' && strtolower(trim($status)) === 'completed';
        $delivered = strtolower(trim((string) $deliveryStatus)) === 'delivered';
        $checklistComplete = $checklistTotal > 0 && $completedCount === $checklistTotal;
        $allInstalled = $serialStatuses !== [] && count(array_filter($serialStatuses,
            static fn (string $value): bool => $value === 'installed')) === count($serialStatuses);
        return [
            'decision' => ($formatOnly && $delivered && $checklistComplete && $allInstalled)
                ? 'FORMAT_ONLY_CANDIDATE_NEEDS_APPROVAL' : 'HOLD_PRECONDITIONS',
            'format_only' => $formatOnly,
            'delivery_delivered' => $delivered,
            'checklist_complete' => $checklistComplete,
            'all_serials_installed' => $allInstalled,
            'auto_apply_allowed' => false,
        ];
    }

    public static function invoice(string $status): array
    {
        $accepted = ['Draft', 'Sent', 'Paid', 'Cancelled'];
        if (in_array($status, $accepted, true)) return ['decision' => 'RECOGNIZED', 'auto_apply_allowed' => false];
        $canonical = array_values(array_filter($accepted,
            static fn (string $value): bool => strtolower($value) === strtolower(trim($status))));
        return [
            'decision' => count($canonical) === 1 ? 'FORMAT_ONLY_CANDIDATE_NEEDS_APPROVAL' : 'HOLD_BILLING_MEANING',
            'classification' => count($canonical) === 1 ? 'format_only' : 'unrecognized_business_state',
            'auto_apply_allowed' => false,
        ];
    }
}
