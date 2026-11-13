<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Read-only, line-oriented review report. This is NOT a penetration test or
 * proof that a controller is secure. No changes to records, roles or routes.
 */
final class AuditCrmReferenceScope extends Command
{
    protected $signature = 'crm:rbac:audit-references {--details : Show candidate source lines and methods} {--limit=12 : Maximum detail rows per controller}';

    protected $description = 'Audit CRM cross-module references (read-only; with optional line details)';

    public function handle(): int
    {
        $root = app_path('Http/Controllers');
        if (! File::isDirectory($root)) {
            $this->error('Controller directory not found.');
            return self::FAILURE;
        }

        $refs = [
            'customers' => ['customer_id', 'Customer::'],
            'leads' => ['lead_id', 'Lead::'],
            'opportunities' => ['opportunity_id', 'Opportunity::'],
        ];

        $patterns = [
            'EXISTS_RULE' => '~\bexists:(?:customers|leads|opportunities)\b|\bRule::exists\s*\(\s*[\'\"](?:customers|leads|opportunities)\b~i',
            'REF_GUARD' => '~\bCrmReferenceDataScope\b|->assertVisible\s*\(|->assertUrlVisible\s*\(|->assertLeadCustomerMatch\s*\(~',
            'REF_QUERY' => '~\b(?:Customer|Lead|Opportunity)::(?:query|all|where|select|orderBy|find|findOrFail)\s*\(|\b(?:customer_id|lead_id|opportunity_id)\b~',
            'WRITE' => '~\b(?:insert|update|create|save|delete|destroy|changeStatus|attach|sync)\s*\(~',
        ];

        $summary = [];
        $details = [];
        $limit = min(100, max(1, (int) $this->option('limit')));
        $files = File::allFiles($root);
        usort($files, fn ($a, $b) => strcmp($a->getFilename(), $b->getFilename()));

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $source = File::get($file->getPathname());
            $subjects = [];
            foreach ($refs as $subject => $needles) {
                foreach ($needles as $needle) {
                    if (str_contains($source, $needle)) {
                        $subjects[] = $subject;
                        break;
                    }
                }
            }
            if ($subjects === []) {
                continue;
            }

            $hits = array_fill_keys(array_keys($patterns), 0);
            $samples = [];
            $method = 'class level';
            $lines = preg_split('/\R/', $source) ?: [];
            foreach ($lines as $index => $line) {
                if (preg_match('/\bfunction\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $line, $fn)) {
                    $method = $fn[1];
                }
                // Source strings and comments also match. These are only review pointers.
                foreach ($patterns as $kind => $pattern) {
                    if (preg_match($pattern, $line) !== 1) {
                        continue;
                    }
                    $hits[$kind]++;
                    if (count($samples) < $limit && in_array($kind, ['EXISTS_RULE', 'REF_GUARD', 'REF_QUERY'], true)) {
                        $samples[] = [
                            'L' . ($index + 1), $method, $kind,
                            mb_strimwidth(trim($line), 0, 125, '…'),
                        ];
                    }
                }
            }
            $name = $file->getFilename();
            $summary[] = [
                $name,
                implode(', ', $subjects),
                (string) $hits['EXISTS_RULE'],
                (string) $hits['REF_GUARD'],
                $hits['EXISTS_RULE'] > 0 ? 'PRIORITY REVIEW' : 'REVIEW',
            ];
            $details[$name] = $samples;
        }

        $this->info('A6.11.6.1 — Cross-module reference inventory (READ ONLY)');
        $this->line('Findings are candidate code locations, NOT proof of access control or a vulnerability.');
        $this->table(['Controller', 'References', 'Exists lines', 'Guard lines', 'Status'], $summary);
        $this->line('Controllers for review: ' . count($summary));
        $this->warn('A guard mention may be unused or cover only part of a Controller. Review each endpoint.');

        if ($this->option('details')) {
            foreach ($summary as $row) {
                $name = $row[0];
                if (($details[$name] ?? []) === []) {
                    continue;
                }
                $this->newLine();
                $this->line("<comment>{$name}</comment>");
                $this->table(['Line', 'Method (approx.)', 'Pattern', 'Source excerpt'], $details[$name]);
            }
        }

        $this->line('Do not enable pilot flags for production based only on this report.');
        return self::SUCCESS;
    }
}
