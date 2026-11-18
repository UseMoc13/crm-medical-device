<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * A7.5.1 -- observational integration checks, no writes and no automatic repair.
 *
 * These checks flag potentially inconsistent existing rows; business rules remain
 * in the respective controllers. A REVIEW finding is not necessarily corrupt data.
 * No names, e-mails, notes, serial-number strings or financial details are emitted.
 */
final class CrmIntegrationAudit
{
    /**
     * @return array<int, array{id:string,area:string,label:string,severity:string,requires:array<string,list<string>>,query:\Closure}>
     */
    public function definitions(): array
    {
        return [
            $this->rule('sales.order_missing_quotation', 'Sales', 'Sales Order refers to a missing Quotation', 'critical',
                ['sales_orders'=>['sales_order_id','quotation_id'], 'quotations'=>['quotation_id']],
                fn () => DB::table('sales_orders as so')->leftJoin('quotations as q', 'q.quotation_id', '=', 'so.quotation_id')
                    ->whereNotNull('so.quotation_id')->whereNull('q.quotation_id')->select('so.sales_order_id as entity_id')),

            $this->rule('sales.confirmed_order_empty', 'Sales', 'Confirmed Sales Order has no order items', 'review',
                ['sales_orders'=>['sales_order_id','status'], 'sales_order_items'=>['sales_order_id']],
                fn () => DB::table('sales_orders as so')->where('so.status', 'confirmed')->whereNotExists(function (Builder $q) {
                    $q->selectRaw('1')->from('sales_order_items as si')->whereColumn('si.sales_order_id', 'so.sales_order_id');
                })->select('so.sales_order_id as entity_id')),

            $this->rule('delivery.order_missing', 'Delivery', 'Delivery refers to a missing Sales Order', 'critical',
                ['deliveries'=>['delivery_id','sales_order_id'], 'sales_orders'=>['sales_order_id']],
                fn () => DB::table('deliveries as d')->leftJoin('sales_orders as so', 'so.sales_order_id', '=', 'd.sales_order_id')
                    ->whereNull('so.sales_order_id')->select('d.delivery_id as entity_id')),

            $this->rule('delivery.cancelled_order_active', 'Delivery', 'Non-cancelled Delivery refers to cancelled/rejected Sales Order', 'review',
                ['deliveries'=>['delivery_id','sales_order_id','status'], 'sales_orders'=>['sales_order_id','status']],
                fn () => DB::table('deliveries as d')->join('sales_orders as so', 'so.sales_order_id', '=', 'd.sales_order_id')
                    ->whereIn('so.status', ['cancelled','rejected'])->where('d.status', '!=', 'cancelled')
                    ->select('d.delivery_id as entity_id')),

            $this->rule('delivery.item_missing_delivery', 'Delivery', 'Delivery Item refers to a missing Delivery', 'critical',
                ['delivery_items'=>['delivery_item_id','delivery_id'], 'deliveries'=>['delivery_id']],
                fn () => DB::table('delivery_items as di')->leftJoin('deliveries as d','d.delivery_id','=','di.delivery_id')
                    ->whereNull('d.delivery_id')->select('di.delivery_item_id as entity_id')),

            $this->rule('delivery.item_missing_serial', 'Delivery', 'Delivery Item refers to a missing Serial Number', 'critical',
                ['delivery_items'=>['delivery_item_id','serial_number_id'], 'serial_numbers'=>['serial_number_id']],
                fn () => DB::table('delivery_items as di')->leftJoin('serial_numbers as sn','sn.serial_number_id','=','di.serial_number_id')
                    ->whereNull('sn.serial_number_id')->select('di.delivery_item_id as entity_id')),

            $this->rule('delivery.dispatched_empty', 'Delivery', 'Dispatched/Delivered record has no allocated items', 'critical',
                ['deliveries'=>['delivery_id','status'], 'delivery_items'=>['delivery_id']],
                fn () => DB::table('deliveries as d')->whereIn('d.status',['in_transit','delivered'])
                    ->whereNotExists(function (Builder $q) {
                        $q->selectRaw('1')->from('delivery_items as di')->whereColumn('di.delivery_id','d.delivery_id');
                    })->select('d.delivery_id as entity_id')),

            $this->rule('delivery.dispatched_missing_stockout', 'Inventory / Delivery', 'Dispatched/Delivered Delivery has no stock-out movement', 'critical',
                ['deliveries'=>['delivery_id','status'], 'stock_movements'=>['reference_id','reference_type','movement_type']],
                fn () => DB::table('deliveries as d')->whereIn('d.status',['in_transit','delivered'])
                    ->whereNotExists(function (Builder $q) {
                        $q->selectRaw('1')->from('stock_movements as sm')->whereRaw('CAST(sm.reference_id AS TEXT) = CAST(d.delivery_id AS TEXT)')
                            ->where('sm.reference_type','delivery')->where('sm.movement_type','stock_out');
                    })->select('d.delivery_id as entity_id')),

            $this->rule('delivery.pending_has_stockout', 'Inventory / Delivery', 'Pending Delivery already has stock-out movement', 'critical',
                ['deliveries'=>['delivery_id','status'], 'stock_movements'=>['reference_id','reference_type','movement_type']],
                fn () => DB::table('deliveries as d')->where('d.status','pending')
                    ->whereExists(function (Builder $q) {
                        $q->selectRaw('1')->from('stock_movements as sm')->whereRaw('CAST(sm.reference_id AS TEXT) = CAST(d.delivery_id AS TEXT)')
                            ->where('sm.reference_type','delivery')->where('sm.movement_type','stock_out');
                    })->select('d.delivery_id as entity_id')),

            $this->rule('delivery.delivered_missing_date', 'Delivery', 'Delivered record has no Delivery Date', 'review',
                ['deliveries'=>['delivery_id','status','delivery_date']],
                fn () => DB::table('deliveries as d')->where('d.status','delivered')->whereNull('d.delivery_date')
                    ->select('d.delivery_id as entity_id')),

            $this->rule('delivery.item_nonunit_quantity', 'Delivery', 'Serial-numbered Delivery Item has quantity other than one', 'review',
                ['delivery_items'=>['delivery_item_id','quantity']],
                fn () => DB::table('delivery_items as di')->where('di.quantity','!=',1)
                    ->select('di.delivery_item_id as entity_id')),

            $this->rule('delivery.dispatched_item_missing_warehouse', 'Inventory / Delivery', 'Dispatched item has no source warehouse', 'critical',
                ['deliveries'=>['delivery_id','status'], 'delivery_items'=>['delivery_item_id','delivery_id','warehouse_id']],
                fn () => DB::table('delivery_items as di')->join('deliveries as d','d.delivery_id','=','di.delivery_id')
                    ->whereIn('d.status',['in_transit','delivered'])->whereNull('di.warehouse_id')
                    ->select('di.delivery_item_id as entity_id')),

            $this->rule('delivery.serial_allocated_twice', 'Inventory / Delivery', 'Serial Number allocated to multiple non-cancelled deliveries', 'critical',
                ['delivery_items'=>['serial_number_id','delivery_id'], 'deliveries'=>['delivery_id','status']],
                fn () => DB::table('delivery_items as di')->join('deliveries as d','d.delivery_id','=','di.delivery_id')
                    ->where('d.status','!=','cancelled')->whereNotNull('di.serial_number_id')->groupBy('di.serial_number_id')
                    ->havingRaw('COUNT(DISTINCT d.delivery_id) > 1')
                    ->select('di.serial_number_id as entity_id')),

            $this->rule('delivery.serial_product_not_ordered', 'Sales / Delivery', 'Allocated Serial product is absent from Sales Order items', 'critical',
                ['delivery_items'=>['delivery_item_id','delivery_id','serial_number_id'],
                 'deliveries'=>['delivery_id','sales_order_id'], 'serial_numbers'=>['serial_number_id','product_id'],
                 'sales_order_items'=>['sales_order_id','product_id','sales_order_item_id']],
                fn () => DB::table('delivery_items as di')->join('deliveries as d','d.delivery_id','=','di.delivery_id')
                    ->join('serial_numbers as sn','sn.serial_number_id','=','di.serial_number_id')
                    ->leftJoin('sales_order_items as soi', function ($join) {
                        $join->on('soi.sales_order_id','=','d.sales_order_id')->on('soi.product_id','=','sn.product_id');
                    })->whereNull('soi.sales_order_item_id')->select('di.delivery_item_id as entity_id')),

            $this->rule('delivery.overallocated_order_product', 'Sales / Delivery', 'Active Delivery allocations exceed ordered product quantity', 'critical',
                ['delivery_items'=>['delivery_id','serial_number_id','quantity'],
                 'deliveries'=>['delivery_id','sales_order_id','status'], 'serial_numbers'=>['serial_number_id','product_id'],
                 'sales_order_items'=>['sales_order_id','product_id','quantity']],
                function () {
                    $allocated = DB::table('delivery_items as di')->join('deliveries as d','d.delivery_id','=','di.delivery_id')
                        ->join('serial_numbers as sn','sn.serial_number_id','=','di.serial_number_id')
                        ->where('d.status','!=','cancelled')
                        ->groupBy('d.sales_order_id','sn.product_id')
                        ->select('d.sales_order_id','sn.product_id')->selectRaw('SUM(di.quantity) AS allocated_qty');
                    $ordered = DB::table('sales_order_items')->groupBy('sales_order_id','product_id')
                        ->select('sales_order_id','product_id')->selectRaw('SUM(quantity) AS ordered_qty');
                    return DB::query()->fromSub($allocated,'alloc')
                        ->leftJoinSub($ordered,'ord', function ($join) {
                            $join->on('ord.sales_order_id','=','alloc.sales_order_id')
                                ->on('ord.product_id','=','alloc.product_id');
                        })->whereRaw('alloc.allocated_qty > COALESCE(ord.ordered_qty,0)')
                        ->select('alloc.sales_order_id as entity_id');
                }),

            $this->rule('delivery.stockout_quantity_mismatch', 'Inventory / Delivery', 'Dispatched item quantities do not match stock-out totals per warehouse/product', 'critical',
                ['deliveries'=>['delivery_id','status'], 'delivery_items'=>['delivery_id','serial_number_id','warehouse_id','quantity'],
                 'serial_numbers'=>['serial_number_id','product_id'],
                 'stock_movements'=>['reference_id','reference_type','movement_type','warehouse_id','product_id','quantity']],
                function () {
                    $allocated = DB::table('delivery_items as di')->join('deliveries as d','d.delivery_id','=','di.delivery_id')
                        ->join('serial_numbers as sn','sn.serial_number_id','=','di.serial_number_id')
                        ->whereIn('d.status',['in_transit','delivered'])
                        ->groupBy('di.delivery_id','di.warehouse_id','sn.product_id')
                        ->select('di.delivery_id','di.warehouse_id','sn.product_id')
                        ->selectRaw('SUM(di.quantity) AS allocated_qty');
                    $moved = DB::table('stock_movements as sm')
                        ->where('sm.reference_type','delivery')->where('sm.movement_type','stock_out')
                        ->groupBy('sm.reference_id','sm.warehouse_id','sm.product_id')
                        ->select('sm.reference_id','sm.warehouse_id','sm.product_id')
                        ->selectRaw('SUM(sm.quantity) AS moved_qty');
                    return DB::query()->fromSub($allocated,'al')
                        ->leftJoinSub($moved,'mv', function ($join) {
                            $join->whereRaw('CAST(mv.reference_id AS TEXT) = CAST(al.delivery_id AS TEXT)')
                                ->on('mv.warehouse_id','=','al.warehouse_id')
                                ->on('mv.product_id','=','al.product_id');
                        })->whereRaw('al.allocated_qty <> COALESCE(mv.moved_qty,0)')
                        ->select('al.delivery_id as entity_id');
                }),

            $this->rule('delivery.unknown_status', 'Delivery', 'Delivery has a status outside the current workflow', 'review',
                ['deliveries'=>['delivery_id','status']],
                fn () => DB::table('deliveries as d')->where(function (Builder $q) {
                    $q->whereNotIn('d.status',['pending','in_transit','delivered','cancelled'])->orWhereNull('d.status');
                })->select('d.delivery_id as entity_id')),

            $this->rule('installation.unknown_status', 'Installation', 'Installation has a status outside the current workflow', 'review',
                ['installations'=>['installation_id','status']],
                fn () => DB::table('installations as ins')->where(function (Builder $q) {
                    $q->whereNotIn('ins.status',['Scheduled','In Progress','Completed','Cancelled'])->orWhereNull('ins.status');
                })->select('ins.installation_id as entity_id')),

            $this->rule('billing.unknown_status', 'Billing', 'Invoice has a status outside the current workflow', 'review',
                ['invoices'=>['invoice_id','status']],
                fn () => DB::table('invoices as i')->where(function (Builder $q) {
                    $q->whereNotIn('i.status',['Draft','Sent','Paid','Cancelled'])->orWhereNull('i.status');
                })->select('i.invoice_id as entity_id')),

            $this->rule('service.unknown_status', 'Service', 'Service Ticket has a status outside the current workflow', 'review',
                ['service_tickets'=>['service_ticket_id','status']],
                fn () => DB::table('service_tickets as st')->where(function (Builder $q) {
                    $q->whereNotIn('st.status',['open','in_progress','on_hold','resolved','closed'])->orWhereNull('st.status');
                })->select('st.service_ticket_id as entity_id')),

            $this->rule('inventory.negative_quantity', 'Inventory', 'Warehouse Inventory has negative quantity', 'critical',
                ['inventories'=>['inventory_id','quantity']],
                fn () => DB::table('inventories as i')->where('i.quantity','<',0)->select('i.inventory_id as entity_id')),

            $this->rule('inventory.negative_minimum', 'Inventory', 'Warehouse Inventory has negative minimum stock', 'review',
                ['inventories'=>['inventory_id','minimum_stock']],
                fn () => DB::table('inventories as i')->where('i.minimum_stock','<',0)->select('i.inventory_id as entity_id')),

            $this->rule('installation.delivery_missing', 'Installation', 'Installation refers to a missing Delivery', 'critical',
                ['installations'=>['installation_id','delivery_id'], 'deliveries'=>['delivery_id']],
                fn () => DB::table('installations as ins')->leftJoin('deliveries as d','d.delivery_id','=','ins.delivery_id')
                    ->whereNull('d.delivery_id')->select('ins.installation_id as entity_id')),

            $this->rule('installation.completed_delivery_not_delivered', 'Installation', 'Completed Installation refers to non-delivered Delivery', 'critical',
                ['installations'=>['installation_id','delivery_id','status'], 'deliveries'=>['delivery_id','status']],
                fn () => DB::table('installations as ins')->join('deliveries as d','d.delivery_id','=','ins.delivery_id')
                    ->where('ins.status','Completed')->where('d.status','!=','delivered')
                    ->select('ins.installation_id as entity_id')),

            $this->rule('installation.completed_no_checklist', 'Installation', 'Completed Installation has no checklist items', 'critical',
                ['installations'=>['installation_id','status'], 'installation_checklists'=>['installation_id']],
                fn () => DB::table('installations as ins')->where('ins.status','Completed')
                    ->whereNotExists(function (Builder $q) {
                        $q->selectRaw('1')->from('installation_checklists as ic')
                            ->whereColumn('ic.installation_id','ins.installation_id');
                    })->select('ins.installation_id as entity_id')),

            $this->rule('installation.completed_checklist_pending', 'Installation', 'Completed Installation has incomplete checklist items', 'critical',
                ['installations'=>['installation_id','status'], 'installation_checklists'=>['installation_id','is_completed']],
                fn () => DB::table('installations as ins')->where('ins.status','Completed')
                    ->whereExists(function (Builder $q) {
                        $q->selectRaw('1')->from('installation_checklists as ic')
                            ->whereColumn('ic.installation_id','ins.installation_id')
                            ->where(function (Builder $state) {
                                $state->where('ic.is_completed',false)->orWhereNull('ic.is_completed');
                            });
                    })->select('ins.installation_id as entity_id')),

            $this->rule('installation.completed_serial_not_installed', 'Installation', 'Completed Installation includes Serial Number no longer in installed state', 'review',
                ['installations'=>['installation_id','delivery_id','status'],
                 'delivery_items'=>['delivery_id','serial_number_id'], 'serial_numbers'=>['serial_number_id','status']],
                fn () => DB::table('installations as ins')->join('delivery_items as di','di.delivery_id','=','ins.delivery_id')
                    ->join('serial_numbers as sn','sn.serial_number_id','=','di.serial_number_id')
                    ->where('ins.status','Completed')->where('sn.status','!=','installed')
                    ->select('ins.installation_id as entity_id')->distinct()),

            $this->rule('warranty.serial_missing', 'Warranty', 'Warranty refers to a missing Serial Number', 'critical',
                ['warranties'=>['warranty_id','serial_number_id'], 'serial_numbers'=>['serial_number_id']],
                fn () => DB::table('warranties as w')->leftJoin('serial_numbers as sn','sn.serial_number_id','=','w.serial_number_id')
                    ->whereNull('sn.serial_number_id')->select('w.warranty_id as entity_id')),

            $this->rule('warranty.customer_mismatch', 'Warranty', 'Warranty customer differs from assigned Serial Number customer', 'review',
                ['warranties'=>['warranty_id','customer_id','serial_number_id'],
                 'serial_numbers'=>['serial_number_id','customer_id']],
                fn () => DB::table('warranties as w')->join('serial_numbers as sn','sn.serial_number_id','=','w.serial_number_id')
                    ->whereNotNull('sn.customer_id')->whereNotNull('w.customer_id')
                    ->whereColumn('w.customer_id','!=','sn.customer_id')->select('w.warranty_id as entity_id')),

            $this->rule('warranty.invalid_date_range', 'Warranty', 'Warranty end date is earlier than its start', 'critical',
                ['warranties'=>['warranty_id','start_date','end_date']],
                fn () => DB::table('warranties as w')->whereNotNull('w.end_date')
                    ->whereColumn('w.end_date','<','w.start_date')->select('w.warranty_id as entity_id')),

            $this->rule('warranty.active_after_expiry', 'Warranty', 'Expired warranty still has Active status', 'review',
                ['warranties'=>['warranty_id','status','end_date']],
                fn () => DB::table('warranties as w')->whereRaw('LOWER(TRIM(w.status)) = ?', ['active'])
                    ->whereDate('w.end_date','<',today()->toDateString())->select('w.warranty_id as entity_id')),

            $this->rule('service.serial_missing', 'Service', 'Service Ticket refers to a missing Serial Number', 'critical',
                ['service_tickets'=>['service_ticket_id','serial_number_id'], 'serial_numbers'=>['serial_number_id']],
                fn () => DB::table('service_tickets as st')->whereNotNull('st.serial_number_id')
                    ->leftJoin('serial_numbers as sn','sn.serial_number_id','=','st.serial_number_id')
                    ->whereNull('sn.serial_number_id')->select('st.service_ticket_id as entity_id')),

            $this->rule('service.customer_serial_mismatch', 'Service', 'Service Ticket customer differs from Serial Number customer', 'review',
                ['service_tickets'=>['service_ticket_id','serial_number_id','customer_id'],
                 'serial_numbers'=>['serial_number_id','customer_id']],
                fn () => DB::table('service_tickets as st')->join('serial_numbers as sn','sn.serial_number_id','=','st.serial_number_id')
                    ->whereNotNull('sn.customer_id')->whereNotNull('st.customer_id')
                    ->whereColumn('st.customer_id','!=','sn.customer_id')->select('st.service_ticket_id as entity_id')),

            $this->rule('service.closed_missing_date', 'Service', 'Closed Service Ticket has no closed_at timestamp', 'review',
                ['service_tickets'=>['service_ticket_id','status','closed_at']],
                fn () => DB::table('service_tickets as st')->where('st.status','closed')->whereNull('st.closed_at')
                    ->select('st.service_ticket_id as entity_id')),

            $this->rule('billing.order_missing', 'Billing', 'Invoice refers to a missing Sales Order', 'critical',
                ['invoices'=>['invoice_id','sales_order_id'], 'sales_orders'=>['sales_order_id']],
                fn () => DB::table('invoices as i')->leftJoin('sales_orders as so','so.sales_order_id','=','i.sales_order_id')
                    ->whereNull('so.sales_order_id')->select('i.invoice_id as entity_id')),

            $this->rule('billing.negative_total', 'Billing', 'Invoice has negative total amount', 'critical',
                ['invoices'=>['invoice_id','total_amount']],
                fn () => DB::table('invoices as i')->where('i.total_amount','<',0)->select('i.invoice_id as entity_id')),

            $this->rule('billing.due_before_issue', 'Billing', 'Invoice due date precedes invoice date', 'review',
                ['invoices'=>['invoice_id','due_date','invoice_date']],
                fn () => DB::table('invoices as i')->whereNotNull('i.due_date')
                    ->whereColumn('i.due_date','<','i.invoice_date')->select('i.invoice_id as entity_id')),
        ];
    }

    /** @param array<string, list<string>> $requires */
    private function rule(string $id, string $area, string $label, string $severity, array $requires, \Closure $query): array
    {
        return compact('id','area','label','severity','requires','query');
    }

    public function run(bool $includeSamples = false, int $sampleLimit = 5): array
    {
        $checks = [];
        $tableAvailable = [];
        $columnAvailable = [];
        $sampleLimit = max(1, min($sampleLimit, 5));
        foreach ($this->definitions() as $definition) {
            $record = [
                'id' => $definition['id'], 'area' => $definition['area'],
                'label' => $definition['label'], 'severity' => $definition['severity'],
                'status' => 'ok', 'count' => 0,
            ];
            $missing = [];
            foreach ($definition['requires'] as $table => $columns) {
                if (!array_key_exists($table, $tableAvailable)) {
                    $tableAvailable[$table] = Schema::hasTable($table);
                }
                if (!$tableAvailable[$table]) {
                    $missing[] = $table;
                    continue;
                }
                foreach ($columns as $column) {
                    $key = $table . '.' . $column;
                    if (!array_key_exists($key, $columnAvailable)) {
                        $columnAvailable[$key] = Schema::hasColumn($table, $column);
                    }
                    if (!$columnAvailable[$key]) $missing[] = $key;
                }
            }
            if ($missing) {
                $record['status'] = 'skipped';
                $record['missing_schema'] = array_values(array_unique($missing));
                $checks[] = $record;
                continue;
            }
            try {
                /** @var Builder $query */
                $query = ($definition['query'])();
                // All rules return entity_id only. Wrapping a subquery ensures
                // grouped / distinct checks count findings, not grouped rows.
                $record['count'] = (int) DB::query()->fromSub($query, 'crm_a751_findings')->count();
                if ($record['count'] > 0) {
                    $record['status'] = 'flagged';
                    if ($includeSamples) {
                        $record['sample_ids'] = DB::query()->fromSub($query, 'crm_a751_samples')
                            ->limit($sampleLimit)->pluck('entity_id')->map(fn ($id) => (string) $id)->all();
                    }
                }
            } catch (Throwable $exception) {
                // Do not leak connection credentials, query parameters, SQL or
                // sensitive record content in end-user diagnostics.
                $record['status'] = 'error';
                $record['error_type'] = class_basename($exception);
            }
            $checks[] = $record;
        }
        return [
            'stage' => 'A7.5.1', 'read_only' => true,
            'generated_at' => now()->toIso8601String(),
            'summary' => self::summarize($checks), 'checks' => $checks,
        ];
    }

    /** @param array<int,array<string,mixed>> $checks */
    public static function summarize(array $checks): array
    {
        $summary = [
            'total_checks' => count($checks), 'passed' => 0, 'flagged' => 0,
            'skipped' => 0, 'errors' => 0,
            'critical_findings' => 0, 'review_findings' => 0,
        ];
        foreach ($checks as $check) {
            if ($check['status'] === 'ok') $summary['passed']++;
            if ($check['status'] === 'skipped') $summary['skipped']++;
            if ($check['status'] === 'error') $summary['errors']++;
            if ($check['status'] === 'flagged') {
                $summary['flagged']++;
                if ($check['severity'] === 'critical') $summary['critical_findings'] += (int) $check['count'];
                else $summary['review_findings'] += (int) $check['count'];
            }
        }
        return $summary;
    }
}
