<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Read-only triage of NULL scopes. Does not infer that a grant is vulnerable.
 */
final class AuditCrmScopeGrants extends Command
{
    protected $signature = 'crm:rbac:audit-scope-grants
        {--all-view : Include VIEW grants not matched to the seven scoped subjects}
        {--limit=100 : Maximum rows to display in the role/permission detail table}';

    protected $description = 'Read-only breakdown of NULL data_scope by role and permission type';

    private const SUBJECTS = [
        'customers' => 'customers_enabled',
        'leads' => 'leads_enabled',
        'opportunities' => 'opportunities_enabled',
        'service_tickets' => 'service_tickets_enabled',
        'maintenance_schedules' => 'maintenance_schedules_enabled',
        'installations' => 'installations_enabled',
        'activities' => 'activities_enabled',
    ];

    public function handle(): int
    {
        try {
            foreach (['role_permissions', 'permissions', 'roles'] as $table) {
                if (!Schema::hasTable($table)) {
                    $this->error("Missing table: {$table}");
                    return self::FAILURE;
                }
            }
            if (!Schema::hasColumn('role_permissions', 'data_scope')) {
                $this->error('Missing role_permissions.data_scope column.');
                return self::FAILURE;
            }

            $rows = DB::table('role_permissions as rp')
                ->join('roles as r', 'r.role_id', '=', 'rp.role_id')
                ->join('permissions as p', 'p.permission_id', '=', 'rp.permission_id')
                ->select('r.role_name', 'p.permission_name', 'p.module', 'p.action', 'rp.data_scope')
                ->orderBy('r.role_name')
                ->orderBy('p.permission_name')
                ->get();

            $count = ['all' => 0, 'null' => 0, 'nonview_null' => 0, 'view_other_null' => 0, 'scope_view_null' => 0, 'scope_view_set' => 0, 'enabled_scope_null' => 0, 'legacy_view_null' => 0];
            $details = [];
            $subjectCounts = [];
            $roleCounts = [];
            foreach (self::SUBJECTS as $subject => $flag) {
                $subjectCounts[$subject] = ['enabled' => (bool) config("crm_data_scope.{$flag}", false), 'null' => 0, 'set' => 0];
            }

            foreach ($rows as $row) {
                $count['all']++;
                $null = $row->data_scope === null;
                if ($null) $count['null']++;
                $name = strtolower((string) $row->permission_name);
                $action = strtolower((string) $row->action);
                $isView = (bool) preg_match('/(?:^|[._:-])view$/', $name) || $action === 'view';
                $subject = $this->matchingSubject($name, (string) $row->module);
                $legacy = $subject !== null && !str_starts_with($name, 'crm_page.');
                $type = !$isView ? 'NON_VIEW' : ($subject === null ? 'OTHER_VIEW' : 'SCOPED_VIEW');
                $role = (string) $row->role_name;
                $roleCounts[$role] ??= ['null' => 0, 'scoped_view_null' => 0];
                if ($null) $roleCounts[$role]['null']++;

                if ($type === 'NON_VIEW' && $null) $count['nonview_null']++;
                if ($type === 'OTHER_VIEW' && $null) $count['view_other_null']++;
                if ($type === 'SCOPED_VIEW') {
                    $count[$null ? 'scope_view_null' : 'scope_view_set']++;
                    $subjectCounts[$subject][$null ? 'null' : 'set']++;
                    if ($null) {
                        $roleCounts[$role]['scoped_view_null']++;
                        if ($subjectCounts[$subject]['enabled']) $count['enabled_scope_null']++;
                        if ($legacy) $count['legacy_view_null']++;
                    }
                }
                if ($type === 'SCOPED_VIEW' || ($this->option('all-view') && $isView)) {
                    $details[] = [$role, (string) $row->permission_name, $subject ?? '(other)', $null ? '<NULL>' : (string) $row->data_scope, $null && $type === 'SCOPED_VIEW' ? ($legacy ? 'LEGACY_REVIEW' : ($subjectCounts[$subject]['enabled'] ? 'ACTIVE_REVIEW' : 'DISABLED_REVIEW')) : 'INFO'];
                }
            }

            $this->info('A6.11.7.1 - Scope Grant Breakdown [READ ONLY]');
            $this->warn('NULL is not automatically a vulnerability; this is a classification, not an authorization test.');
            $this->table(['Metric', 'Count'], [
                ['All grants', $count['all']],
                ['All NULL grants', $count['null']],
                ['NULL on non-View actions', $count['nonview_null']],
                ['NULL on other View permissions', $count['view_other_null']],
                ['NULL on matched scoped View permissions', $count['scope_view_null']],
                ['Non-NULL on matched scoped View permissions', $count['scope_view_set']],
                ['NULL on enabled scoped View permissions', $count['enabled_scope_null']],
                ['NULL on legacy-name scoped View permissions', $count['legacy_view_null']],
            ]);
            $this->table(['Subject', 'Flag enabled', 'View grants NULL', 'View grants set'], array_map(
                static fn ($name, $s) => [$name, $s['enabled'] ? 'YES' : 'NO', $s['null'], $s['set']],
                array_keys($subjectCounts), array_values($subjectCounts)
            ));
            $roleRows = [];
            foreach ($roleCounts as $role => $stats) {
                if ($stats['null'] > 0) $roleRows[] = [$role, $stats['null'], $stats['scoped_view_null']];
            }
            $this->table(['Role', 'All NULL', 'Scoped View NULL'], $roleRows);
            $limit = max(0, min(1000, (int) $this->option('limit')));
            $this->table(['Role', 'Permission', 'Subject', 'Scope', 'Audit'], array_slice($details, 0, $limit));
            if (count($details) > $limit) {
                $this->warn('Detail truncated: use --limit=300 to see more.');
            }
            $this->line('Scope policy decision: configure missing View scopes in Roles UI; do not mass-update NULL grants.');
            $this->line('LEGACY_REVIEW marks old permission names; check which permission namespace authorization actually consumes.');
            $this->line('Static matching may miss custom permission naming. Check relevant permission names above.');
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Read-only audit failed: ' . get_class($e) . '. Check table/column permissions and Laravel logs.');
            return self::FAILURE;
        }
    }

    private function matchingSubject(string $permissionName, string $module): ?string
    {
        $name = strtolower(str_replace('-', '_', $permissionName));
        $module = strtolower(str_replace('-', '_', $module));
        // The Maintenance page represents the maintenance_schedules scope subject.
        if (preg_match('/(?:^|[._:])maintenance(?:[._:]|$)/', $name) || $module === 'maintenance') {
            return 'maintenance_schedules';
        }
        foreach (array_keys(self::SUBJECTS) as $subject) {
            // Require a token boundary. Avoid counting, e.g., customer_notifications.
            $pattern = '/(?:^|[._:])' . preg_quote($subject, '/') . '(?:[._:]|$)/';
            if (preg_match($pattern, $name) || preg_match($pattern, $module)) {
                return $subject;
            }
        }
        return null;
    }
}
