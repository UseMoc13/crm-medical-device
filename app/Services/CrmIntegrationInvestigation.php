<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * A7.5.2.1: forensic read-only view of four already-detected A7.5.1 findings.
 * Reuses the original rule queries so results cannot drift from the baseline audit.
 * Does not infer that a historic inconsistency is safe to auto-repair.
 */
final class CrmIntegrationInvestigation
{
    public const TARGETS = [
        'delivery.dispatched_item_missing_warehouse',
        'delivery.stockout_quantity_mismatch',
        'installation.unknown_status',
        'billing.unknown_status',
    ];

    public function __construct(private readonly CrmIntegrationAudit $audit) {}

    public function run(bool $details = false, int $limit = 10): array
    {
        $limit = max(1, min(20, $limit));
        $catalog = [];
        foreach ($this->audit->definitions() as $definition) {
            $catalog[$definition['id']] = $definition;
        }

        $checks = [];
        foreach (self::TARGETS as $id) {
            if (!isset($catalog[$id])) {
                $checks[] = ['id' => $id, 'status' => 'error', 'error_type' => 'RuleNotFound'];
                continue;
            }
            $rule = $catalog[$id];
            $missing = $this->missingSchema($rule['requires']);
            if ($missing !== []) {
                $checks[] = ['id' => $id, 'status' => 'skipped', 'missing_schema' => $missing];
                continue;
            }
            try {
                /** @var Builder $source */
                $source = ($rule['query'])();
                $count = (int) DB::query()->fromSub($source, 'a752_findings')->count();
                $check = [
                    'id' => $id,
                    'severity' => $rule['severity'],
                    'status' => $count ? 'flagged' : 'ok',
                    'count' => $count,
                ];
                if ($details && $count) {
                    $ids = DB::query()->fromSub(($rule['query'])(), 'a752_samples')
                        ->select('entity_id')->distinct()->orderBy('entity_id')->limit($limit)
                        ->pluck('entity_id')->map(static fn ($value) => (string) $value)->all();
                    $check['details'] = $this->detailsFor($id, $ids);
                    $check['details_limited'] = $count > $limit;
                }
                $checks[] = $check;
            } catch (Throwable $e) {
                // No SQL strings, DB connection details or raw data are exposed.
                $checks[] = ['id' => $id, 'status' => 'error', 'error_type' => class_basename($e)];
            }
        }
        $overlap = $this->overlappingDeliveryCount($catalog, $checks);
        return [
            'stage' => 'A7.5.2.1',
            'read_only' => true,
            'generated_at' => now()->toIso8601String(),
            'summary' => [
                'checked' => count($checks),
                'flagged_rules' => count(array_filter($checks, static fn ($c) => $c['status'] === 'flagged')),
                'skipped' => count(array_filter($checks, static fn ($c) => $c['status'] === 'skipped')),
                'errors' => count(array_filter($checks, static fn ($c) => $c['status'] === 'error')),
                'overlapping_delivery_count' => $overlap,
            ],
            'checks' => $checks,
        ];
    }

    /**
     * If both Delivery rules flag a delivery, one legacy missing warehouse may
     * explain a stock-out mismatch. This is a correlation, not an auto-fix.
     * Returns null if either required schema is missing or the query fails.
     * @param array<string,array<string,mixed>> $catalog
     * @param array<int,array<string,mixed>> $checks
     */
    private function overlappingDeliveryCount(array $catalog, array $checks): ?int
    {
        $byId = [];
        foreach ($checks as $check) $byId[$check['id']] = $check;
        foreach (self::TARGETS as $id) {
            if (!str_starts_with($id, 'delivery.')) continue;
            if (in_array($byId[$id]['status'] ?? 'error', ['skipped', 'error'], true)) return null;
        }
        try {
            $missingWarehouse = DB::table('delivery_items as di')
                ->join('deliveries as d', 'd.delivery_id', '=', 'di.delivery_id')
                ->whereIn('d.status', ['in_transit', 'delivered'])
                ->whereNull('di.warehouse_id')->select('di.delivery_id')->distinct();
            $mismatch = ($catalog['delivery.stockout_quantity_mismatch']['query'])();
            return (int) DB::query()->fromSub($mismatch, 'm')
                ->joinSub($missingWarehouse, 'w', static function ($join) {
                    $join->on('w.delivery_id', '=', 'm.entity_id');
                })->distinct()->count('m.entity_id');
        } catch (Throwable) {
            return null;
        }
    }

    /** @param array<string,list<string>> $requirements */
    private function missingSchema(array $requirements): array
    {
        $missing = [];
        foreach ($requirements as $table => $columns) {
            if (!Schema::hasTable($table)) {
                $missing[] = $table;
                continue;
            }
            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) $missing[] = $table . '.' . $column;
            }
        }
        return $missing;
    }

    /** @param list<string> $ids */
    private function detailsFor(string $id, array $ids): array
    {
        return match ($id) {
            'delivery.dispatched_item_missing_warehouse' => $this->missingWarehouseDetails($ids),
            'delivery.stockout_quantity_mismatch' => $this->stockoutDetails($ids),
            'installation.unknown_status' => $this->statusDetails('installations', 'installation_id', $ids, ['Scheduled','In Progress','Completed','Cancelled']),
            'billing.unknown_status' => $this->statusDetails('invoices', 'invoice_id', $ids, ['Draft','Sent','Paid','Cancelled']),
            default => [],
        };
    }

    /** @param list<string> $ids */
    private function missingWarehouseDetails(array $ids): array
    {
        $items = DB::table('delivery_items as di')
            ->join('deliveries as d', 'd.delivery_id', '=', 'di.delivery_id')
            ->leftJoin('serial_numbers as sn', 'sn.serial_number_id', '=', 'di.serial_number_id')
            ->whereIn('di.delivery_item_id', $ids)
            ->select('di.delivery_item_id', 'di.delivery_id', 'di.quantity', 'd.status', 'sn.product_id')
            ->orderBy('di.delivery_item_id')->get();
        $results = [];
        foreach ($items as $item) {
            $query = DB::table('stock_movements as sm')
                ->where('sm.reference_type', 'delivery')->where('sm.movement_type', 'stock_out')
                ->whereRaw('CAST(sm.reference_id AS TEXT) = ?', [(string) $item->delivery_id]);
            if ($item->product_id !== null) $query->where('sm.product_id', $item->product_id);
            $movements = $query->select('sm.warehouse_id')
                ->selectRaw('SUM(sm.quantity) AS stockout_qty')
                ->groupBy('sm.warehouse_id')->orderBy('sm.warehouse_id')->get();
            $results[] = [
                'delivery_item_id' => (string) $item->delivery_item_id,
                'delivery_id' => (string) $item->delivery_id,
                'delivery_status' => (string) $item->status,
                'allocated_qty' => (int) $item->quantity,
                'product_id' => $item->product_id ? (string) $item->product_id : null,
                'observed_stockout_warehouses' => $movements->map(static fn ($row) => [
                    'warehouse_id' => $row->warehouse_id ? (string) $row->warehouse_id : null,
                    'stockout_qty' => (int) $row->stockout_qty,
                ])->all(),
                'interpretation' => 'Warehouse is missing on allocation; stockout warehouse is observational evidence, not proof for automatic backfill.',
            ];
        }
        return $results;
    }

    /** @param list<string> $ids */
    private function stockoutDetails(array $ids): array
    {
        $results = [];
        foreach ($ids as $deliveryId) {
            $allocated = DB::table('delivery_items as di')
                ->join('serial_numbers as sn', 'sn.serial_number_id', '=', 'di.serial_number_id')
                ->where('di.delivery_id', $deliveryId)
                ->groupBy('di.warehouse_id', 'sn.product_id')
                ->select('di.warehouse_id', 'sn.product_id')
                ->selectRaw('SUM(di.quantity) AS allocated_qty')->get();
            $moved = DB::table('stock_movements as sm')
                ->where('sm.reference_type', 'delivery')->where('sm.movement_type', 'stock_out')
                ->whereRaw('CAST(sm.reference_id AS TEXT) = ?', [$deliveryId])
                ->groupBy('sm.warehouse_id', 'sm.product_id')
                ->select('sm.warehouse_id', 'sm.product_id')
                ->selectRaw('SUM(sm.quantity) AS stockout_qty')->get();
            $groups = [];
            foreach ($allocated as $row) {
                $key = self::groupKey($row->warehouse_id, $row->product_id);
                $groups[$key] = [
                    'warehouse_id' => $row->warehouse_id ? (string) $row->warehouse_id : null,
                    'product_id' => (string) $row->product_id,
                    'allocated_qty' => (int) $row->allocated_qty,
                    'stockout_qty' => 0,
                ];
            }
            foreach ($moved as $row) {
                $key = self::groupKey($row->warehouse_id, $row->product_id);
                if (!isset($groups[$key])) {
                    $groups[$key] = [
                        'warehouse_id' => $row->warehouse_id ? (string) $row->warehouse_id : null,
                        'product_id' => (string) $row->product_id,
                        'allocated_qty' => 0,
                        'stockout_qty' => 0,
                    ];
                }
                $groups[$key]['stockout_qty'] += (int) $row->stockout_qty;
            }
            $mismatch = [];
            foreach ($groups as $group) {
                if ($group['allocated_qty'] === $group['stockout_qty']) continue;
                $group['difference_allocated_minus_stockout'] = $group['allocated_qty'] - $group['stockout_qty'];
                $mismatch[] = $group;
            }
            $missingWarehouseCount = (int) DB::table('delivery_items')
                ->where('delivery_id', $deliveryId)->whereNull('warehouse_id')->count();
            $results[] = [
                'delivery_id' => $deliveryId,
                'missing_warehouse_item_count' => $missingWarehouseCount,
                'mismatched_warehouse_product_groups' => $mismatch,
                'interpretation' => 'Compare historic movement and allocation provenance before deciding any transaction guard or data correction.',
            ];
        }
        return $results;
    }

    private static function groupKey(?string $warehouseId, ?string $productId): string
    {
        return ($warehouseId ?? '(null)') . '|' . ($productId ?? '(null)');
    }

    /** @param list<string> $ids @param list<string> $accepted */
    private function statusDetails(string $table, string $primaryKey, array $ids, array $accepted): array
    {
        return DB::table($table)->whereIn($primaryKey, $ids)->select($primaryKey, 'status')
            ->orderBy($primaryKey)->get()->map(static function ($row) use ($primaryKey, $accepted) {
                $classification = self::classifyStatus($row->status, $accepted);
                return [
                    'record_id' => (string) $row->{$primaryKey},
                    'stored_status' => $row->status,
                    'classification' => $classification['classification'],
                    'canonical_if_format_only' => $classification['canonical_if_format_only'],
                ];
            })->all();
    }

    /**
     * Classification is diagnostic only; never automatically reclassify business states.
     * @param list<string> $accepted
     * @return array{classification:string,canonical_if_format_only:?string}
     */
    public static function classifyStatus(?string $status, array $accepted): array
    {
        if ($status === null) return ['classification' => 'null', 'canonical_if_format_only' => null];
        if (in_array($status, $accepted, true)) return ['classification' => 'recognized', 'canonical_if_format_only' => $status];
        $normalized = strtolower(trim($status));
        $matches = array_values(array_filter($accepted, static fn (string $candidate) => strtolower($candidate) === $normalized));
        if (count($matches) === 1) return ['classification' => 'case_or_outer_whitespace_only', 'canonical_if_format_only' => $matches[0]];
        return ['classification' => 'unrecognized_business_state', 'canonical_if_format_only' => null];
    }
}
