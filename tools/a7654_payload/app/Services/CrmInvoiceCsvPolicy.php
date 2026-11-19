<?php

namespace App\Services;

use App\Models\User;

/**
 * A7.6.5.4: explicit least-privilege policy for detailed Invoice CSV, not
 * an authorization source for the aggregated Reports page. Read-only.
 * There is no audited Invoice own/branch row filter in Reports, so only
 * an explicit ALL Invoice grant can authorize detailed export in pilot mode.
 */
final class CrmInvoiceCsvPolicy
{
    public function __construct(
        private readonly CrmNavigationAccess $navigation,
        private readonly CrmPermissionResolver $permissions,
    ) {}

    public function canExport(?User $user): bool
    {
        if ($user === null || strtolower(trim((string) ($user->status ?? ''))) !== 'active') {
            return false;
        }
        if (!$this->navigation->canView($user, 'reports')
            || !$this->navigation->canView($user, 'invoices')) {
            return false;
        }
        // Reuse the exact same explicit Invoice data-scope decision as Dashboard.
        // NULL/own/branch/assigned/unknown cannot be promoted to ALL.
        return CrmScopedKpiQuery::scopeDecision('invoices',
            $this->permissions->scopes($user, 'crm_page.invoices.view')) === 'all';
    }
}
