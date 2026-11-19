<?php

namespace App\Services;

use App\Models\User;

/**
 * A7.6.5.8: opt-in full-scope gate for Reports' aggregate groups.
 * This service ONLY reads role-grant metadata. No business row queries or writes.
 * We intentionally require ALL rather than own/branch/assigned: the existing
 * Reports queries are not proven to enforce partial per-user row scopes.
 */
final class CrmReportAggregatePrivacyPilot
{
    public function __construct(
        private readonly CrmNavigationAccess $navigation,
        private readonly CrmPermissionResolver $permissions,
    ) {}

    /** @return array{enabled:bool,financial:bool,operations:bool,pipeline:bool,warranty:bool} */
    public function forViewer(?User $viewer): array
    {
        $enabled = (bool) config('crm_reporting.report_aggregate_privacy_pilot_enabled', false);
        if (!$enabled) {
            // Legacy behavior unchanged while pilot is OFF. Route still requires reports.view.
            return self::decide(false, []);
        }
        if (!$viewer || strtolower(trim((string) ($viewer->status ?? ''))) !== 'active'
            || !$this->navigation->canView($viewer, 'reports')) {
            return self::decide(true, []);
        }

        return self::decide(true, [
            'financial' => $this->hasFullScope($viewer, 'invoices', 'invoices'),
            'operations' => $this->hasFullScope($viewer, 'service_tickets', 'service_tickets')
                && $this->hasFullScope($viewer, 'maintenance', 'maintenance_schedules'),
            'pipeline' => $this->hasFullScope($viewer, 'opportunities', 'opportunities'),
            'warranty' => $this->hasFullScope($viewer, 'warranties', 'warranties'),
        ]);
    }

    /**
     * @param array<string,bool> $fullScopeGrants
     * @return array{enabled:bool,financial:bool,operations:bool,pipeline:bool,warranty:bool}
     */
    public static function decide(bool $enabled, array $fullScopeGrants): array
    {
        $result = ['enabled' => $enabled];
        foreach (['financial', 'operations', 'pipeline', 'warranty'] as $group) {
            $result[$group] = !$enabled || (($fullScopeGrants[$group] ?? false) === true);
        }
        return $result;
    }

    private function hasFullScope(User $viewer, string $page, string $table): bool
    {
        if (!$this->navigation->canView($viewer, $page)) return false;
        return CrmScopedKpiQuery::scopeDecision(
            $table,
            $this->permissions->scopes($viewer, CrmRoleAccessCatalog::key($page, 'view'))
        ) === 'all';
    }
}
