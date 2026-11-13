<?php

namespace App\Services;

use App\Models\MaintenanceSchedule;
use App\Models\ServiceTicket;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * A6.11.6.6 - centralized opt-in downstream query filtering.
 * Shared by invoice tables, financial aggregates, CSV, and service history.
 * No effect when the corresponding pilot feature flag is disabled.
 */
final class CrmReportingDataScope
{
    public function salesOrders(Builder $query, string $column = 'sales_order_id'): Builder
    {
        if (! $this->salesScopeEnabled()) {
            return $query;
        }

        $orders = DB::table('sales_orders as scope_so')
            ->join('quotations as scope_q', 'scope_q.quotation_id', '=', 'scope_so.quotation_id')
            ->join('opportunities as scope_o', 'scope_o.opportunity_id', '=', 'scope_q.opportunity_id')
            ->select('scope_so.sales_order_id');

        $this->opportunity($orders, 'scope_o.opportunity_id');
        $this->customer($orders, 'scope_o.customer_id');

        return $query->whereIn($column, $orders);
    }

    public function invoices(Builder $query, string $column = 'sales_order_id'): Builder
    {
        return $this->salesOrders($query, $column);
    }

    public function customer(Builder $query, string $column): Builder
    {
        if (app(CrmReferenceDataScope::class)->enabled('customers')) {
            $query->whereIn($column, app(CrmReferenceDataScope::class)
                ->visible('customers')->select('customer_id'));
        }

        return $query;
    }

    public function opportunity(Builder $query, string $column): Builder
    {
        if (app(CrmReferenceDataScope::class)->enabled('opportunities')) {
            $query->whereIn($column, app(CrmReferenceDataScope::class)
                ->visible('opportunities')->select('opportunity_id'));
        }

        return $query;
    }

    public function pipeline(Builder $query, string $opportunityId = 'opportunity_id', string $customerId = 'customer_id'): Builder
    {
        $this->opportunity($query, $opportunityId);
        $this->customer($query, $customerId);

        return $query;
    }

    public function tickets(Builder $query, string $ticketId = 'service_ticket_id', string $customerId = 'customer_id'): Builder
    {
        if (app(CrmServiceDataScope::class)->enabled('service_tickets')) {
            $query->whereIn($ticketId, app(CrmServiceDataScope::class)
                ->visible(ServiceTicket::query(), 'service_tickets')->select('service_ticket_id'));
        }
        return $this->customer($query, $customerId);
    }

    public function schedules(Builder $query, string $scheduleId = 'maintenance_schedule_id'): Builder
    {
        if (app(CrmServiceDataScope::class)->enabled('maintenance_schedules')) {
            $query->whereIn($scheduleId, app(CrmServiceDataScope::class)
                ->visible(MaintenanceSchedule::query(), 'maintenance_schedules')->select('maintenance_schedule_id'));
        }

        // Keep maintenance KPIs and history consistent with Customer scope too.
        if (app(CrmReferenceDataScope::class)->enabled('customers')) {
            $scheduleIds = DB::table('maintenance_schedules as scope_ms')
                ->join('serial_numbers as scope_sn', 'scope_sn.serial_number_id', '=', 'scope_ms.serial_number_id')
                ->select('scope_ms.maintenance_schedule_id');
            $this->customer($scheduleIds, 'scope_sn.customer_id');
            $query->whereIn($scheduleId, $scheduleIds);
        }

        return $query;
    }

    public function warranty(Builder $query, string $customerId = 'customer_id'): Builder
    {
        return $this->customer($query, $customerId);
    }

    public function salesScopeEnabled(): bool
    {
        $ref = app(CrmReferenceDataScope::class);
        return $ref->enabled('customers') || $ref->enabled('opportunities');
    }
}
