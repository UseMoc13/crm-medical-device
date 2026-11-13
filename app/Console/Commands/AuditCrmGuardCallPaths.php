<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

/**
 * Read-only static call-path inventory. Never equate a guard mention with verified access control.
 */
final class AuditCrmGuardCallPaths extends Command
{
    protected $signature = 'crm:rbac:audit-guard-paths
        {--controller= : Filter by Controller short name (without .php)}
        {--details : Include calls and evidence}
        {--json : Output machine-readable JSON}
        {--limit=150 : Max displayed rows}
        {--strict : Fail only if a listed Controller action cannot be located}';

    protected $description = 'Read-only controller helper call-path audit for CRM Data Scope';

    private const CONTROLLERS = [
        'CustomerController', 'LeadController', 'OpportunityController',
        'OpportunityItemController', 'QuotationController', 'InvoiceController',
        'ProductController', 'ServiceTicketController', 'MaintenanceController',
        'InstallationController', 'ContactController', 'SerialNumberController',
        'WarrantyController', 'Reportcontroller', 'ServiceHistoryController',
    ];

    private const GUARD_TERMS = [
        'assertVisible', 'assertUrlVisible', 'assertRecordCustomer',
        'assertParentInScope', 'assertTicketScope', 'CrmDataScope',
        'CrmSalesDataScope', 'CrmServiceDataScope',
        'CrmInstallationActivityDataScope', 'CrmReferenceDataScope',
        'CrmDownstreamCustomerScope', 'CrmReportingDataScope',
    ];

    public function handle(): int
    {
        $filter = strtolower(trim((string) $this->option('controller')));
        $rows = [];
        $missing = 0;
        $cache = [];

        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();
            if (!preg_match('/\\\\([A-Za-z][A-Za-z0-9]*Controller)@([A-Za-z_][A-Za-z0-9_]*)$/', $action, $match)) {
                continue;
            }
            [$whole, $controller, $method] = $match;
            if (!in_array($controller, self::CONTROLLERS, true)) {
                continue;
            }
            if ($filter !== '' && strtolower($controller) !== strtolower(str_ends_with($filter, 'controller') ? $filter : $filter.'Controller')) {
                continue;
            }
            $path = app_path('Http/Controllers/'.$controller.'.php');
            if (!isset($cache[$controller])) {
                $cache[$controller] = is_file($path) ? $this->extractMethods((string) file_get_contents($path)) : [];
            }
            $methods = $cache[$controller];
            $body = $methods[$method]['body'] ?? null;
            if ($body === null) {
                $missing++;
                $rows[] = compact('controller', 'method') + [
                    'http' => implode('|', $route->methods()), 'uri' => $route->uri(),
                    'status' => 'MISSING_HANDLER', 'calls' => [], 'evidence' => [], 'line' => null,
                ];
                continue;
            }
            $visited = [];
            $calls = [];
            $evidence = [];
            $this->walk($method, $methods, $visited, $calls, $evidence, 0);
            $directEvidence = $this->guardEvidence($body);
            $status = $directEvidence ? 'DIRECT_CANDIDATE' : ($evidence ? 'HELPER_CANDIDATE' : 'NO_GUARD_EVIDENCE');
            $rows[] = [
                'controller' => $controller, 'method' => $method,
                'http' => implode('|', $route->methods()), 'uri' => $route->uri(),
                'status' => $status, 'calls' => array_values(array_unique($calls)),
                'evidence' => array_values(array_unique($evidence)),
                'line' => $methods[$method]['line'],
            ];
        }
        usort($rows, static fn ($a, $b) => strcmp($a['controller'].'@'.$a['method'].' '.$a['uri'], $b['controller'].'@'.$b['method'].' '.$b['uri']));
        $summary = array_count_values(array_column($rows, 'status'));
        $report = [
            'mode' => 'static_read_only', 'runtime_verified' => false,
            'warning' => 'Service mentions and same-class helper calls are ONLY review evidence. A passing status is not an access-control guarantee.',
            'route_count' => count($rows), 'summary' => $summary, 'items' => $rows,
        ];
        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));
        } else {
            $this->info('A6.13 - CRM Guard Call-Path Audit [READ ONLY]');
            $this->warn($report['warning']);
            $limit = max(0, min(1000, (int) $this->option('limit')));
            $displayed = array_slice($rows, 0, $limit);
            $this->table(['Handler', 'HTTP', 'URI', 'Candidate', 'Line'], array_map(static fn ($row) => [
                $row['controller'].'@'.$row['method'], $row['http'], $row['uri'], $row['status'], $row['line'] ?? '-',
            ], $displayed));
            if ($this->option('details')) {
                foreach ($displayed as $row) {
                    if ($row['status'] === 'DIRECT_CANDIDATE' && !$row['calls']) {
                        continue;
                    }
                    $this->line($row['controller'].'@'.$row['method'].' | calls: '.implode(', ', $row['calls']).' | evidence: '.implode(', ', $row['evidence']));
                }
            }
            $this->line('Routes: '.count($rows).' | Missing handlers: '.$missing.' | No guard evidence: '.($summary['NO_GUARD_EVIDENCE'] ?? 0).' | Runtime verified: NO');
            $this->warn('Review product/quotation/invoice sensitive handlers and test denied UUID + submitted references before production.');
        }
        return $this->option('strict') && $missing > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** Return function bodies, ignoring braces inside PHP strings/comments via tokenizer. */
    private function extractMethods(string $source): array
    {
        $tokens = token_get_all($source);
        $offsets = [];
        $pos = 0;
        foreach ($tokens as $token) {
            $offsets[] = $pos;
            $pos += strlen(is_array($token) ? $token[1] : $token);
        }
        $methods = [];
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) {
                continue;
            }
            $j = $i + 1;
            while ($j < $count && (is_array($tokens[$j]) && in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true) || $tokens[$j] === '&')) {
                $j++;
            }
            if ($j >= $count || !is_array($tokens[$j]) || $tokens[$j][0] !== T_STRING) {
                continue;
            }
            $name = $tokens[$j][1];
            while ($j < $count && $tokens[$j] !== '{' && $tokens[$j] !== ';') {
                $j++;
            }
            if ($j >= $count || $tokens[$j] !== '{') {
                continue;
            }
            $depth = 1;
            $end = $j + 1;
            while ($end < $count && $depth > 0) {
                if ($tokens[$end] === '{') $depth++;
                if ($tokens[$end] === '}') $depth--;
                $end++;
            }
            $startPos = $offsets[$j];
            $endPos = $end < $count ? $offsets[$end] : strlen($source);
            $methods[$name] = [
                'body' => substr($source, $startPos, $endPos - $startPos),
                'line' => $tokens[$i][2],
            ];
            $i = $end - 1;
        }
        return $methods;
    }

    private function walk(string $method, array $methods, array &$visited, array &$calls, array &$evidence, int $depth): void
    {
        if ($depth > 4 || isset($visited[$method]) || !isset($methods[$method])) {
            return;
        }
        $visited[$method] = true;
        $body = $methods[$method]['body'];
        $evidence = array_merge($evidence, $this->guardEvidence($body));
        if (!preg_match_all('/\$this\s*->\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $body, $matches)) {
            return;
        }
        foreach ($matches[1] as $callee) {
            if (!isset($methods[$callee])) {
                continue;
            }
            $calls[] = $method.' -> '.$callee;
            $this->walk($callee, $methods, $visited, $calls, $evidence, $depth + 1);
        }
    }

    private function guardEvidence(string $body): array
    {
        $found = [];
        foreach (self::GUARD_TERMS as $term) {
            if (str_contains($body, $term)) {
                $found[] = $term;
            }
        }
        return $found;
    }
}
