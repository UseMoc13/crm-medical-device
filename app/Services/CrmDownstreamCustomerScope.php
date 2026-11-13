<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;

/**
 * A6.11.6.5 reference-scope utility for legacy Query Builder modules.
 * Opt-in: when customers_enabled=false every query is unchanged.
 * Call for ALL list/count/detail/write paths of the affected module.
 */
final class CrmDownstreamCustomerScope
{
    public function enabled(): bool
    {
        return app(CrmReferenceDataScope::class)->enabled('customers');
    }

    public function apply(Builder $query, string $column, bool $includeUnassigned = false): Builder
    {
        if (! $this->enabled()) {
            return $query;
        }

        $visibleCustomers = app(CrmReferenceDataScope::class)
            ->visible('customers')
            ->select('customer_id');

        return $query->where(function (Builder $nested) use ($column, $includeUnassigned, $visibleCustomers): void {
            $nested->whereIn($column, $visibleCustomers);
            if ($includeUnassigned) {
                $nested->orWhereNull($column);
            }
        });
    }

    public function assertRecordCustomer(?string $customerId, bool $allowUnassigned = false): void
    {
        if (! $this->enabled()) {
            return;
        }

        if ($allowUnassigned && ($customerId === null || $customerId === '')) {
            return;
        }

        if (! $customerId) {
            abort(404);
        }

        // Prevent UUID existence disclosure in direct URL actions.
        app(CrmReferenceDataScope::class)->assertUrlVisible('customers', $customerId);
    }
}
