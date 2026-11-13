<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

/**
 * Read-only static triage. Heuristics deliberately avoid claiming an endpoint is secure.
 */
final class AuditCrmScopeEndpoints extends Command
{
    protected $signature = 'crm:rbac:audit-scope-endpoints
        {--details : Display guard evidence locations}
        {--limit=100 : Maximum endpoint rows to display}
        {--json : Print a machine-readable report}
        {--strict : Exit nonzero if a target Controller or route handler is missing}';

    protected $description = 'Read-only inventory of high-risk CRM routes and scope guard candidates';

    private const TARGETS = [
        'OpportunityItemController' => ['CrmSalesDataScope', 'assertParentInScope'],
        'QuotationController' => ['CrmReferenceDataScope', 'assertVisible', 'assertUrlVisible'],
        'InvoiceController' => ['CrmReportingDataScope', 'assertVisible', 'assertUrlVisible'],
        'ProductController' => ['CrmReferenceDataScope', 'CrmSalesDataScope', 'CrmReportingDataScope'],
        'LeadController' => ['CrmSalesDataScope', 'CrmReferenceDataScope'],
        'OpportunityController' => ['CrmSalesDataScope', 'CrmReferenceDataScope'],
    ];

    public function handle(): int
    {
        $rows = [];
        $missing = 0;
        $routes = 0;
        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();
            if (!preg_match('/\\\\([A-Za-z][A-Za-z0-9]*Controller)@([A-Za-z_][A-Za-z0-9_]*)$/', $action, $m)) {
                continue;
            }
            $class = $m[1];
            $method = $m[2];
            if (!isset(self::TARGETS[$class])) {
                continue;
            }
            if (!preg_match('/^(show|create|store|edit|update|destroy|delete|changeStatus|items|export|close|resolve)/i', $method) && $class !== 'ProductController') {
                continue;
            }
            $routes++;
            $path = app_path('Http/Controllers/' . $class . '.php');
            $source = is_file($path) ? file_get_contents($path) : false;
            $body = is_string($source) ? $this->methodBody($source, $method) : null;
            $location = $body !== null ? $this->lineNumber((string) $source, $method) : null;
            $evidence = [];
            if ($body !== null) {
                foreach (self::TARGETS[$class] as $needle) {
                    if (str_contains($body, $needle)) {
                        $evidence[] = $needle;
                    }
                }
            }
            $status = $body === null ? 'MISSING_HANDLER' : (count($evidence) ? 'DIRECT_CANDIDATE' : 'INDIRECT_REVIEW');
            if ($status === 'MISSING_HANDLER') {
                $missing++;
            }
            $rows[] = [
                'controller' => $class,
                'method' => $method,
                'verb' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'status' => $status,
                'line' => $location,
                'evidence' => $evidence,
            ];
        }
        usort($rows, static fn (array $a, array $b) => strcmp($a['controller'].'@'.$a['method'].' '.$a['uri'], $b['controller'].'@'.$b['method'].' '.$b['uri']));
        $report = [
            'notice' => 'Static review only. DIRECT_CANDIDATE does not establish authorization. INDIRECT_REVIEW may call a protected helper.',
            'routes' => $routes,
            'missing_handlers' => $missing,
            'runtime_verified' => false,
            'items' => $rows,
        ];
        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));
        } else {
            $this->info('A6.11.7.2 - Endpoint Scope Triage [READ ONLY]');
            $this->warn($report['notice']);
            $limit = max(0, min(2000, (int) $this->option('limit')));
            $this->table(['Handler', 'HTTP', 'URI', 'Guard evidence', 'Status'], array_map(static fn (array $r) => [
                $r['controller'].'@'.$r['method'], $r['verb'], $r['uri'], implode(', ', $r['evidence']), $r['status'],
            ], array_slice($rows, 0, $limit)));
            if ($this->option('details')) {
                foreach (array_slice($rows, 0, $limit) as $r) {
                    $this->line($r['controller'].'@'.$r['method'].' : '.($r['line'] ?? '?').' : '.$r['status']);
                }
            }
            $this->line('Routes reviewed: '.$routes.' | Missing handler: '.$missing.' | Runtime verified: NO');
            $this->line('Review indirect calls in Controller code and test 403/404 with accounts from distinct scopes.');
        }
        return $this->option('strict') && $missing > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function methodBody(string $source, string $name): ?string
    {
        $pattern = '/\\bfunction\\s+'.preg_quote($name, '/').'\\s*\\(/';
        if (!preg_match($pattern, $source, $match, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $start = $match[0][1];
        if (!preg_match('/\\bfunction\\s+[A-Za-z_][A-Za-z0-9_]*\\s*\\(/', $source, $next, PREG_OFFSET_CAPTURE, $start + strlen($match[0][0]))) {
            return substr($source, $start);
        }
        return substr($source, $start, $next[0][1] - $start);
    }

    private function lineNumber(string $source, string $name): ?int
    {
        if (!preg_match('/\\bfunction\\s+'.preg_quote($name, '/').'\\s*\\(/', $source, $match, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        return 1 + substr_count(substr($source, 0, $match[0][1]), "\n");
    }
}
