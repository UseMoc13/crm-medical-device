<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * A7.5.4.1: zero-write cross-module regression baseline.
 * Executes existing read-only scanners without record IDs or sample evidence.
 */
final class CrmIntegrationRegressionGate
{
    public const REQUIRED_ROUTES = [
        'deliveries.show', 'installations.show', 'invoices.show',
        'workspace-notifications.index', 'workspace-notifications.open',
        'workspace-notifications.settings.update',
    ];
    public const REQUIRED_TABLES = [
        'workspace_audit_events', 'workspace_notifications',
        'workspace_notification_preferences',
        'deliveries', 'delivery_items', 'serial_numbers', 'stock_movements',
        'installations', 'installation_checklists', 'invoices', 'sales_orders',
    ];
    public const REQUIRED_EVENTS = [
        'delivery.status_changed', 'installation.status_changed', 'invoice.status_changed',
    ];

    public function __construct(
        private readonly CrmIntegrationAudit $audit,
        private readonly CrmIntegrationInvestigation $investigation,
        private readonly CrmHistoricalRemediationReview $review,
    ) {}

    /** @return array<string,mixed> Safe summary: no customer data, raw statuses, UUIDs or SQL. */
    public function run(): array
    {
        $missing = [];
        foreach (self::REQUIRED_TABLES as $table) {
            if (!Schema::hasTable($table)) $missing[] = $table;
        }
        if (Schema::hasTable('workspace_notification_preferences')
            && !Schema::hasColumn('workspace_notification_preferences', 'crm_status_enabled')) {
            $missing[] = 'workspace_notification_preferences.crm_status_enabled';
        }
        if (Schema::hasTable('delivery_items') && !Schema::hasColumn('delivery_items', 'warehouse_id')) {
            $missing[] = 'delivery_items.warehouse_id';
        }

        $missingRoutes = [];
        foreach (self::REQUIRED_ROUTES as $name) {
            if (!Route::has($name)) $missingRoutes[] = $name;
        }
        $missingEvents = array_values(array_diff(self::REQUIRED_EVENTS, WorkspaceAuditTrail::EVENTS));
        $missingSubjects = array_values(array_diff(['delivery', 'installation', 'invoice'], WorkspaceAuditTrail::SUBJECTS));

        $audit = $this->audit->run(false);
        $investigation = $this->investigation->run(false);
        $review = $this->review->run(false);

        // IDs here are rule IDs only: never source record identifiers.
        $auditCases = array_column($audit['checks'], null, 'id');
        $investigationCases = array_column($investigation['checks'], null, 'id');
        $reviewCases = array_column($review['cases'], null, 'id');
        $inconsistencies = [];
        foreach (CrmIntegrationInvestigation::TARGETS as $ruleId) {
            $a = $auditCases[$ruleId] ?? null;
            $i = $investigationCases[$ruleId] ?? null;
            $r = $reviewCases[$ruleId] ?? null;
            if (!$a || !$i || !$r) {
                $inconsistencies[] = $ruleId;
                continue;
            }
            if ($a['status'] !== $i['status'] || $i['status'] !== $r['status']) {
                $inconsistencies[] = $ruleId;
                continue;
            }
            if (in_array($a['status'], ['ok', 'flagged'], true)
                && ((int) ($a['count'] ?? -1) !== (int) ($i['count'] ?? -2)
                    || (int) ($i['count'] ?? -1) !== (int) ($r['count'] ?? -2))) {
                $inconsistencies[] = $ruleId;
            }
        }

        // Detect unexpectedly truncated scanners, not just mismatched individual rules.
        if (($audit['read_only'] ?? false) !== true
            || ($investigation['read_only'] ?? false) !== true
            || ($review['read_only'] ?? false) !== true) {
            $inconsistencies[] = 'source.read_only';
        }
        if ((int) ($review['summary']['automatic_corrections'] ?? -1) !== 0) {
            $inconsistencies[] = 'review.auto_corrections';
        }
        if ((int) ($audit['summary']['total_checks'] ?? 0) !== count($audit['checks'])
            || count($audit['checks']) < 37
            || (int) ($investigation['summary']['checked'] ?? 0) !== 4
            || (int) ($review['summary']['checked'] ?? 0) !== 4) {
            $inconsistencies[] = 'catalog.coverage';
        }

        $summary = self::assess([
            'audit' => $audit['summary'],
            'investigation' => $investigation['summary'],
            'review' => $review['summary'],
            'missing_schema' => $missing,
            'missing_routes' => $missingRoutes,
            'missing_events' => $missingEvents,
            'missing_subjects' => $missingSubjects,
            'inconsistent_rules' => $inconsistencies,
        ]);

        $targets = [];
        foreach (CrmIntegrationInvestigation::TARGETS as $id) {
            $check = $auditCases[$id] ?? [];
            $targets[] = [
                'rule' => $id,
                'status' => $check['status'] ?? 'error',
                'severity' => $check['severity'] ?? 'review',
                'count' => (int) ($check['count'] ?? 0),
            ];
        }

        return [
            'stage' => 'A7.5.4.1',
            'read_only' => true,
            'generated_at' => now()->toIso8601String(),
            'status' => $summary['status'],
            'summary' => $summary,
            'targets' => $targets,
            'missing_schema' => $missing,
            'missing_routes' => $missingRoutes,
            'missing_audit_events' => $missingEvents,
            'missing_audit_subjects' => $missingSubjects,
            'inconsistent_rules' => $inconsistencies,
            'manual_browser_verification_required' => true,
            'historical_fixes_applied' => 0,
        ];
    }

    /**
     * Pure decision function: suitable for no-DB unit tests.
     * @param array<string,mixed> $input
     * @return array<string,mixed>
     */
    public static function assess(array $input): array
    {
        $a = $input['audit']; $i = $input['investigation']; $r = $input['review'];
        $errors = (int) ($a['errors'] ?? 0) + (int) ($i['errors'] ?? 0) + (int) ($r['errors'] ?? 0);
        $skipped = (int) ($a['skipped'] ?? 0) + (int) ($i['skipped'] ?? 0) + (int) ($r['skipped'] ?? 0);
        $missing = array_sum(array_map('count', [
            $input['missing_schema'] ?? [], $input['missing_routes'] ?? [],
            $input['missing_events'] ?? [], $input['missing_subjects'] ?? [],
        ]));
        $mismatches = count($input['inconsistent_rules'] ?? []);
        $flagged = (int) ($a['flagged'] ?? 0);

        // BLOCKED: actual query errors or disagreeing scanners.
        // INCOMPLETE: schema/route unavailable; not a clean pass.
        // WATCH: checks succeeded but historical findings remain.
        $status = $errors || $mismatches ? 'BLOCKED'
            : ($skipped || $missing ? 'INCOMPLETE' : ($flagged ? 'WATCH' : 'READY'));
        return [
            'status' => $status,
            'audit_checks' => (int) ($a['total_checks'] ?? 0),
            'audit_passed' => (int) ($a['passed'] ?? 0),
            'audit_flagged' => $flagged,
            'critical_findings' => (int) ($a['critical_findings'] ?? 0),
            'review_findings' => (int) ($a['review_findings'] ?? 0),
            'skipped_across_reports' => $skipped,
            'query_errors_across_reports' => $errors,
            'missing_prerequisites' => $missing,
            'inconsistent_rules' => $mismatches,
            'shared_delivery_records' => $i['overlapping_delivery_count'] ?? null,
            'auto_corrections' => (int) ($r['automatic_corrections'] ?? 0),
        ];
    }
}
