<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Read-only coverage report. Does not guess actors for legacy data.
 * A populated column is not proof that every mutation route is covered.
 */
class AuditCrmCreatorAttribution extends Command
{
    protected $signature = 'crm:audit-creator-attribution
                            {--since= : Inclusive date YYYY-MM-DD for the new-record check}
                            {--limit=15 : Maximum number of recent rows checked per table}';

    protected $description = 'Read-only audit of CRM creator/updater UUID coverage and Query Builder hotspots';

    private const TABLES = [
        'brands', 'product_categories', 'products', 'warehouses',
        'customers', 'contacts', 'leads', 'activities',
        'opportunities', 'quotations', 'sales_orders', 'contracts',
        'deliveries', 'installations', 'warranties', 'service_tickets',
        'maintenance_schedules', 'invoices', 'serial_numbers',
        'stock_movements', 'announcements',
    ];

    public function handle(): int
    {
        $since = trim((string) $this->option('since'));
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);

        if ($since !== '' && ! $this->validDate($since)) {
            $this->error('Invalid --since date; expected YYYY-MM-DD (e.g. --since=2026-11-13).');
            return self::FAILURE;
        }

        if ($limit === false || $limit < 1 || $limit > 100) {
            $this->error('--limit must be an integer from 1 to 100.');
            return self::FAILURE;
        }

        $this->info('CRM A6.10.5 — Creator/Updater coverage (READ ONLY)');
        $this->line('Never backfills historical attribution; NULL does not imply a failed workflow.');
        $rows = [];
        $missingCount = 0;
        $checked = 0;

        foreach (self::TABLES as $table) {
            try {
                if (! Schema::hasTable($table)) {
                    $rows[] = [$table, 'missing', '—', '—', '—'];
                    $missingCount++;
                    continue;
                }

                $hasCreator = Schema::hasColumn($table, 'created_by');
                $hasUpdater = Schema::hasColumn($table, 'updated_by');
                $hasCreatedAt = Schema::hasColumn($table, 'created_at');

                if (! $hasCreator || ! $hasUpdater) {
                    $rows[] = [$table, 'audit columns absent', '—', '—', '—'];
                    $missingCount++;
                    continue;
                }

                // Scan only a bounded recent sample per table, including a date filter
                // when present. This avoids huge aggregation queries on production DBs.
                $query = DB::table($table);
                if ($since !== '' && $hasCreatedAt) {
                    $query->whereDate('created_at', '>=', $since);
                }

                if ($hasCreatedAt) {
                    $query->orderByDesc('created_at');
                } else {
                    // Order by a predictable audit attribute if there is no timestamp.
                    $query->orderByDesc('created_by');
                }

                $sample = $query->limit($limit)->get(['created_by', 'updated_by']);
                $checked += $sample->count();
                $withoutCreator = $sample->filter(static fn ($record) => $record->created_by === null)->count();
                $withoutUpdater = $sample->filter(static fn ($record) => $record->updated_by === null)->count();

                $rows[] = [
                    $table,
                    'OK',
                    (string) $sample->count(),
                    (string) $withoutCreator,
                    (string) $withoutUpdater,
                ];
            } catch (Throwable $exception) {
                // Surface a bounded error type, never display sensitive SQL parameters.
                $rows[] = [$table, 'read error: '.class_basename($exception), '—', '—', '—'];
                $missingCount++;
            }
        }

        $this->table(['Table', 'Columns', 'Sample', 'NULL creator', 'NULL updater'], $rows);
        $this->line('Sample: up to '.$limit.' newest rows per table'.($since === '' ? ' (all dates).' : ' with created_at >= '.$since.' where supported.'));
        $this->line('Rows inspected: '.$checked.'. Tables missing columns or inaccessible: '.$missingCount.'.');
        $this->newLine();

        $this->comment('Known code paths requiring targeted verification:');
        $this->line('• DeliveryController: DB::table(stock_movements)->insert() during stock dispatch.');
        $this->line('• All direct DB::table()/raw SQL writes and mass Model::query()->update() bypass Eloquent events.');
        $this->line('• InstallationChecklist is not one of the 20 primary tables in A6.10.2.');
        $this->line('A non-NULL value indicates attribution is stored, not that every route is audited.');

        return self::SUCCESS;
    }

    private function validDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
