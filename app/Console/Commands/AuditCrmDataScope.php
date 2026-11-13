<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * A6.11.7: read-only inventory. Presence of guard strings NEVER proves authorization.
 * Does not alter records, grants, config, or route caches.
 */
final class AuditCrmDataScope extends Command
{
    protected $signature = 'crm:rbac:audit-data-scope
        {--db : Also read role_permissions.data_scope statistics, if database is accessible}
        {--json : Output machine-readable JSON instead of formatted tables}
        {--strict : Return failure if hard gaps are detected (useful for CI)}';

    protected $description = 'Read-only inventory of CRM data scope config, services, controller guards, routes and optional grants';

    private const SUBJECTS = [
        'customers' => ['flag' => 'customers_enabled', 'service' => 'CrmDataScope', 'controller' => 'CustomerController', 'subject' => 'customers'],
        'leads' => ['flag' => 'leads_enabled', 'service' => 'CrmSalesDataScope', 'controller' => 'LeadController', 'subject' => 'leads'],
        'opportunities' => ['flag' => 'opportunities_enabled', 'service' => 'CrmSalesDataScope', 'controller' => 'OpportunityController', 'subject' => 'opportunities'],
        'service_tickets' => ['flag' => 'service_tickets_enabled', 'service' => 'CrmServiceDataScope', 'controller' => 'ServiceTicketController', 'subject' => 'service_tickets'],
        'maintenance_schedules' => ['flag' => 'maintenance_schedules_enabled', 'service' => 'CrmServiceDataScope', 'controller' => 'MaintenanceController', 'subject' => 'maintenance_schedules'],
        'installations' => ['flag' => 'installations_enabled', 'service' => 'CrmInstallationActivityDataScope', 'controller' => 'InstallationController', 'subject' => 'installations'],
        'activities' => ['flag' => 'activities_enabled', 'service' => 'CrmInstallationActivityDataScope', 'controller' => 'ActivityController', 'subject' => 'activities'],
    ];

    private const DEPENDENCIES = [
        'CrmDataScope', 'CrmSalesDataScope', 'CrmServiceDataScope',
        'CrmInstallationActivityDataScope', 'CrmReferenceDataScope',
        'CrmDownstreamCustomerScope', 'CrmReportingDataScope',
    ];

    private const CROSS_MODULE_CONTROLLERS = [
        'ContactController', 'LeadController', 'OpportunityController',
        'OpportunityItemController', 'QuotationController', 'ServiceTicketController',
        'SerialNumberController', 'WarrantyController', 'InvoiceController',
        'Reportcontroller', 'ServiceHistoryController', 'ProductController',
    ];

    private const REFERENCE_PATTERNS = [
        'CrmReferenceDataScope', 'CrmDownstreamCustomerScope',
        'CrmReportingDataScope', 'CrmSalesDataScope', 'CrmServiceDataScope',
    ];

    public function handle(): int
    {
        $report = [
            'version' => 'A6.11.7',
            'mode' => 'read_only_static_inventory',
            'notice' => 'Static guard detection is not proof of access control; runtime tests required.',
            'scope_subjects' => [],
            'dependencies' => [],
            'cross_module' => [],
            'route_inventory' => [],
            'database' => ['status' => 'NOT_REQUESTED'],
            'summary' => [],
        ];

        $gaps = 0;
        $reviews = 0;
        foreach (self::DEPENDENCIES as $service) {
            $file = app_path('Services/' . $service . '.php');
            $report['dependencies'][] = [
                'service' => $service,
                'status' => is_file($file) ? 'PRESENT' : 'MISSING',
            ];
            if (! is_file($file)) {
                $gaps++;
            }
        }

        foreach (self::SUBJECTS as $name => $spec) {
            $configFile = config_path('crm_data_scope.php');
            $keyDefined = is_file($configFile) && preg_match('/[\'\"]' . preg_quote($spec['flag'], '/') . '[\'\"]\s*=>/', (string) file_get_contents($configFile)) === 1;
            $enabled = $keyDefined ? (bool) config('crm_data_scope.' . $spec['flag'], false) : false;
            $controllerPath = app_path('Http/Controllers/' . $spec['controller'] . '.php');
            $source = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
            $present = $source !== '';
            $serviceMention = $present && str_contains($source, $spec['service']);
            $directGuard = $present && preg_match('/(?:assertVisible|assertUrlVisible|visible\s*\(|applyForPermission\s*\(|authorizeOwner\s*\()/i', $source) === 1;
            $status = ! $keyDefined ? 'MISSING_CONFIG' : (! is_file(app_path('Services/' . $spec['service'] . '.php')) ? 'MISSING_SERVICE' : (! $present ? 'NO_CONTROLLER' : (! $serviceMention || ! $directGuard ? 'REVIEW_REQUIRED' : 'GUARD_CANDIDATE')));
            if (str_starts_with($status, 'MISSING_')) {
                $gaps++;
            } elseif ($status !== 'GUARD_CANDIDATE') {
                $reviews++;
            }
            $report['scope_subjects'][] = [
                'subject' => $name, 'flag' => $spec['flag'],
                'configured' => $keyDefined, 'enabled' => $enabled,
                'controller' => $spec['controller'],
                'status' => $status,
            ];
        }

        foreach (self::CROSS_MODULE_CONTROLLERS as $controller) {
            $path = app_path('Http/Controllers/' . $controller . '.php');
            if (! is_file($path)) {
                $report['cross_module'][] = ['controller' => $controller, 'status' => 'MISSING_CONTROLLER', 'found_services' => [], 'write_methods' => [], 'review_methods' => []];
                $reviews++;
                continue;
            }
            $source = (string) file_get_contents($path);
            $found = array_values(array_filter(self::REFERENCE_PATTERNS, static fn (string $pattern): bool => str_contains($source, $pattern)));
            $methods = $this->methodBodies($source);
            $sensitive = [];
            $review = [];
            foreach ($methods as $method => $body) {
                if (! preg_match('/^(store|update|destroy|delete|show|edit|changeStatus|export|close|resolve|complete|dispatch)/i', $method)) {
                    continue;
                }
                if (! preg_match('/(?:customer_id|lead_id|opportunity_id|sales_order_id|quotation_id)/', $body)) {
                    continue;
                }
                $sensitive[] = $method;
                $hasGuard = false;
                foreach (self::REFERENCE_PATTERNS as $pattern) {
                    if (str_contains($body, $pattern)) {
                        $hasGuard = true;
                        break;
                    }
                }
                // Helper calls, query scopes, and shared private methods may be legitimate.
                // Missing a direct mention is a REVIEW pointer, not a vulnerability verdict.
                if (! $hasGuard) {
                    $review[] = $method;
                }
            }
            $status = count($found) === 0 ? 'REVIEW_REQUIRED' : ($review ? 'PARTIAL_REVIEW' : 'GUARD_CANDIDATE');
            if ($status !== 'GUARD_CANDIDATE') {
                $reviews++;
            }
            $report['cross_module'][] = ['controller' => $controller, 'status' => $status, 'found_services' => $found, 'write_methods' => $sensitive, 'review_methods' => $review];
        }

        // Route inventory is observational only; middleware and route model binding
        // must still be inspected during runtime authorization tests.
        try {
            foreach (Route::getRoutes() as $route) {
                $action = $route->getActionName();
                if (! str_contains($action, 'Controller@')) {
                    continue;
                }
                foreach (self::CROSS_MODULE_CONTROLLERS as $controller) {
                    if (str_contains($action, '\\' . $controller . '@')) {
                        $report['route_inventory'][$controller] = ($report['route_inventory'][$controller] ?? 0) + 1;
                        break;
                    }
                }
            }
            ksort($report['route_inventory']);
        } catch (Throwable $e) {
            $report['route_inventory'] = ['status' => 'UNAVAILABLE', 'reason' => get_class($e)];
            $reviews++;
        }

        if ($this->option('db')) {
            try {
                if (! Schema::hasTable('role_permissions') || ! Schema::hasColumn('role_permissions', 'data_scope')) {
                    $report['database'] = ['status' => 'MISSING_ROLE_SCOPE_COLUMN'];
                    $gaps++;
                } else {
                    $counts = DB::table('role_permissions')->selectRaw('COALESCE(data_scope, ? ) as scope_value, count(*) as total', ['<NULL>'])->groupBy('data_scope')->orderBy('data_scope')->get();
                    $report['database'] = ['status' => 'READ', 'grant_scope_counts' => $counts->map(static fn ($row) => ['scope' => $row->scope_value, 'count' => (int) $row->total])->all()];
                    $nullCount = (int) DB::table('role_permissions')->whereNull('data_scope')->count();
                    $report['database']['null_scope_grants'] = $nullCount;
                    if ($nullCount > 0) {
                        $reviews++;
                    }
                }
            } catch (Throwable $e) {
                $report['database'] = ['status' => 'UNAVAILABLE', 'reason' => get_class($e) . ': ' . $e->getMessage()];
                $reviews++;
            }
        }

        $report['summary'] = ['hard_gaps' => $gaps, 'review_groups' => $reviews, 'runtime_verified' => false];
        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
        } else {
            $this->info('A6.11.7 - CRM Data Scope Inventory [READ ONLY]');
            $this->warn('Guard detection != authorization proof. Test with roles, UUID routes and submitted IDs.');
            $this->table(['Module', 'Flag', 'Enabled', 'Controller', 'Status'], array_map(static fn ($r) => [$r['subject'], $r['configured'] ? 'DEFINED' : 'MISSING', $r['enabled'] ? 'YES' : 'NO', $r['controller'], $r['status']], $report['scope_subjects']));
            $this->table(['Service', 'Status'], array_map(static fn ($r) => [$r['service'], $r['status']], $report['dependencies']));
            $this->table(['Controller', 'Detected services', 'Methods to review', 'Status'], array_map(static fn ($r) => [$r['controller'], implode(', ', $r['found_services']), implode(', ', $r['review_methods']), $r['status']], $report['cross_module']));
            $this->line('Database: ' . $report['database']['status'] . ($this->option('db') && isset($report['database']['null_scope_grants']) ? ' | NULL grants: ' . $report['database']['null_scope_grants'] : ''));
            $this->line('Route actions inventoried: ' . array_sum(array_filter($report['route_inventory'], 'is_int')));
            $this->warn("Summary: {$gaps} hard gaps; {$reviews} review groups. Runtime verified: NO.");
            $this->line('Next: inspect flagged handlers and test 403/404 + allowed CRUD with Super Admin/Sales/Technician.');
        }
        return $this->option('strict') && $gaps > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** @return array<string,string> Methods segmented approximately for review inventory. */
    private function methodBodies(string $source): array
    {
        $matches = [];
        preg_match_all('/\bfunction\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $source, $matches, PREG_OFFSET_CAPTURE);
        $methods = [];
        foreach ($matches[1] ?? [] as $i => $item) {
            $start = $matches[0][$i][1];
            $next = $matches[0][$i + 1][1] ?? strlen($source);
            $methods[$item[0]] = substr($source, $start, $next - $start);
        }
        return $methods;
    }
}
