<?php

namespace App\Services;

use App\Models\Delivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * A7.5.2.2a: read-only integrity guard before in_transit -> delivered.
 * Call INSIDE the Delivery status transaction after locking the Delivery row.
 * This guard never attempts to repair legacy stock, serials, or allocations.
 */
final class CrmDeliveryCompletionGuard
{
    public function assertCanComplete(Delivery $delivery): void
    {
        if (! Schema::hasTable('delivery_items') || ! Schema::hasColumn('delivery_items', 'warehouse_id')) {
            $this->reject('Delivery warehouse provenance is unavailable. Run the warehouse allocation migration.');
        }

        $items = DB::table('delivery_items as di')
            ->leftJoin('serial_numbers as sn', 'sn.serial_number_id', '=', 'di.serial_number_id')
            ->where('di.delivery_id', $delivery->getKey())
            ->select('di.serial_number_id', 'di.warehouse_id', 'di.quantity',
                'sn.product_id', 'sn.status as serial_status')
            ->get();

        if ($items->isEmpty()) {
            $this->reject('Delivery cannot be finalized without allocated devices.');
        }

        $seenSerials = [];
        $allocations = [];
        $productTotals = [];
        foreach ($items as $item) {
            $serialId = (string) ($item->serial_number_id ?? '');
            $warehouseId = (string) ($item->warehouse_id ?? '');
            $productId = (string) ($item->product_id ?? '');
            if ($serialId === '' || $warehouseId === '' || $productId === ''
                || (int) $item->quantity !== 1 || isset($seenSerials[$serialId])) {
                $this->reject('Allocated devices need unique serial numbers, unit quantities and source warehouses.');
            }
            if ((string) $item->serial_status !== 'assigned') {
                $this->reject('All delivered devices must have an assigned serial status.');
            }
            $seenSerials[$serialId] = true;
            $allocations[] = ['warehouse_id' => $warehouseId, 'product_id' => $productId, 'quantity' => 1];
            $productTotals[$productId] = ($productTotals[$productId] ?? 0) + 1;
        }

        // The Sales Order is the authoritative bound on delivered products.
        $ordered = DB::table('sales_order_items')
            ->where('sales_order_id', $delivery->sales_order_id)
            ->select('product_id')
            ->selectRaw('SUM(quantity) as ordered_quantity')
            ->groupBy('product_id')
            ->pluck('ordered_quantity', 'product_id');
        foreach ($productTotals as $productId => $quantity) {
            if (! isset($ordered[$productId]) || $quantity > (int) $ordered[$productId]) {
                $this->reject('Delivery allocations no longer match the Sales Order.');
            }
        }

        $movements = DB::table('stock_movements')
            ->where('reference_type', 'delivery')
            ->where('reference_id', $delivery->getKey())
            ->where('movement_type', 'stock_out')
            ->select('warehouse_id', 'product_id', 'quantity')
            ->get()
            ->map(static fn ($row): array => [
                'warehouse_id' => (string) $row->warehouse_id,
                'product_id' => (string) $row->product_id,
                'quantity' => (int) $row->quantity,
            ])->all();

        if (! self::quantitiesMatch($allocations, $movements)) {
            $this->reject('Dispatch stock-out does not match allocated devices by warehouse and product. Investigate before finalizing.');
        }
    }

    /**
     * Pure matching logic. Both sides must have identical positive quantities
     * per warehouse + product; no unknown, missing or extra movement groups.
     *
     * @param list<array{warehouse_id:string, product_id:string, quantity:int}> $allocations
     * @param list<array{warehouse_id:string, product_id:string, quantity:int}> $movements
     */
    public static function quantitiesMatch(array $allocations, array $movements): bool
    {
        if ($allocations === [] || $movements === []) return false;
        $group = static function (array $rows): ?array {
            $totals = [];
            foreach ($rows as $row) {
                $warehouse = (string) ($row['warehouse_id'] ?? '');
                $product = (string) ($row['product_id'] ?? '');
                $quantity = $row['quantity'] ?? null;
                if ($warehouse === '' || $product === '' || ! is_int($quantity) || $quantity <= 0) {
                    return null;
                }
                $key = $warehouse . ':' . $product;
                $totals[$key] = ($totals[$key] ?? 0) + $quantity;
            }
            ksort($totals);
            return $totals;
        };
        $left = $group($allocations);
        $right = $group($movements);
        return $left !== null && $right !== null && $left === $right;
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['status' => $message]);
    }
}
