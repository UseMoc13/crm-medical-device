<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

/**
 * A7.6.2: read-only, permission- and row-scope-aware KPI query backend.
 * NOT wired to Dashboard/Reports yet. No raw records, identifiers or free text are returned.
 * Missing/unsupported scope means restricted, NEVER a global fallback.
 */
final class CrmScopedKpiQuery
{
    private const SCOPED_SUBJECTS = [
        'customers' => ['own', 'branch'],
        'leads' => ['own', 'branch'],
        'opportunities' => ['own', 'branch'],
        'service_tickets' => ['assigned', 'branch'],
        'installations' => ['assigned', 'branch'],
    ];

    private const COUNT_METRICS = [
        'customers.total', 'leads.active', 'opportunities.total',
        'service_tickets.total', 'deliveries.in_transit',
        'installations.in_progress', 'inventory.below_minimum',
        'warranties.expiring_30_days',
    ];

    private const FINANCIAL_METRICS = [
        'invoices.invoiced', 'invoices.paid',
        'invoices.outstanding', 'invoices.overdue',
    ];

    public function __construct(
        private readonly CrmPermissionResolver $permissions,
        private readonly CrmDataScope $dataScope,
    ) {}

    /**
     * Explicit data-scope grant is required, even if page middleware allows the page.
     * No implicit all for disabled pilot flags, missing/null grants or modules without
     * an audited ownership column. Unsupported scopes always fail closed.
     */
    public static function scopeDecision(string $table, array $grants): string
    {
        $grants = array_values(array_unique($grants));
        if ($grants === [] || array_diff($grants, ['all', 'own', 'assigned', 'branch'])) {
            return 'restricted';
        }
        if (in_array('all', $grants, true)) return 'all';
        if (array_intersect($grants, self::SCOPED_SUBJECTS[$table] ?? [])) return 'scoped';
        return 'restricted';
    }

    /**
     * Returns only counts and monetary SUM values for the authenticated web user.
     * Financial values are decimal strings (database SUM precision); counts are ints.
     * Unauthorized entries NEVER include a `value` key.
     */
    public function run(
        ?CarbonInterface $from = null,
        ?CarbonInterface $to = null,
        ?CarbonInterface $asOf = null,
    ): array {
        $actor = Auth::guard('web')->user();
        if (!$actor instanceof User) throw new AuthenticationException('Authentication required for CRM KPIs.');

        $asOfDate = ($asOf ?? CarbonImmutable::today())->toDateString();
        $fromDate = ($from ?? CarbonImmutable::parse($asOfDate)->startOfYear())->toDateString();
        $toDate = ($to ?? CarbonImmutable::parse($asOfDate))->toDateString();
        if ($fromDate > $toDate) throw new InvalidArgumentException('KPI period start cannot exceed end.');

        $metrics = [];
        $decisions = [];
        $finance = null;
        foreach (CrmKpiSourceReadiness::METRICS as $id => $contract) {
            $table = $contract['table'];
            $permission = $contract['permission'];
            $key = $table . '|' . $permission;

            if (!isset($decisions[$key])) {
                $decisions[$key] = $this->authorizeMetric($actor, $table, $permission);
            }
            $decision = $decisions[$key];
            if ($decision === 'restricted') {
                $metrics[$id] = ['status' => 'restricted'];
                continue;
            }
            if (!$this->schemaReady($table, $contract['columns'], $decision)) {
                $metrics[$id] = ['status' => 'unavailable_schema'];
                continue;
            }
            if (in_array($id, self::FINANCIAL_METRICS, true)) {
                // Four financial KPI values share one query and precisely match Reports.
                $finance ??= $this->invoiceTotals($actor, $decision, $fromDate, $toDate, $asOfDate);
                $metrics[$id] = [
                    'status' => 'visible', 'unit' => 'IDR', 'value' => (string) $finance[$id],
                ];
            } else {
                if (!in_array($id, self::COUNT_METRICS, true)) {
                    throw new InvalidArgumentException('KPI source contract has no query implementation: ' . $id);
                }
                $metrics[$id] = [
                    'status' => 'visible', 'unit' => 'count',
                    'value' => $this->countMetric($actor, $id, $table, $decision, $asOfDate),
                ];
            }
        }
        return [
            'stage' => 'A7.6.2', 'read_only' => true, 'viewer_scoped' => true,
            'period' => ['from' => $fromDate, 'to' => $toDate, 'as_of' => $asOfDate],
            'metrics' => $metrics,
        ];
    }

    private function authorizeMetric(User $user, string $table, string $permission): string
    {
        $page = explode('.', $permission)[1] ?? '';
        $base = CrmRoleAccessCatalog::PAGES[$page]['base'] ?? null;
        if (!$base || !$this->permissions->allows($user, $base)
            || !$this->permissions->allows($user, $permission)) return 'restricted';
        return self::scopeDecision($table, $this->permissions->scopes($user, $permission));
    }

    private function schemaReady(string $table, array $columns, string $decision): bool
    {
        if (!Schema::hasTable($table)) return false;
        // Financial totals share one grouped query that also references due_date.
        if ($table === 'invoices') $columns[] = 'due_date';
        if ($decision === 'scoped') $columns[] = 'user_id';
        foreach (array_unique($columns) as $column) {
            if (!Schema::hasColumn($table, $column)) return false;
        }
        return true;
    }

    private function scopedQuery(User $actor, string $table, string $decision): Builder
    {
        $query = DB::table($table);
        return $decision === 'scoped'
            ? $this->dataScope->apply($query, $actor, $table, $this->permissions->scopes(
                $actor, 'crm_page.' . match ($table) {
                    'customers' => 'customers', 'leads' => 'leads',
                    'opportunities' => 'opportunities',
                    'service_tickets' => 'service_tickets', 'installations' => 'installations',
                    default => throw new InvalidArgumentException('Unsupported row scope subject.'),
                } . '.view'
            ))
            : $query;
    }

    private function countMetric(User $actor, string $id, string $table, string $decision, string $asOfDate): int
    {
        $query = $this->scopedQuery($actor, $table, $decision);
        match ($id) {
            'leads.active' => $query->whereNotNull('status')
                ->whereRaw('LOWER(TRIM(status)) NOT IN (?, ?, ?)', ['closed', 'converted', 'lost']),
            'deliveries.in_transit' => $query->where('status', 'in_transit'),
            'installations.in_progress' => $query->where('status', 'In Progress'),
            'inventory.below_minimum' => $query->whereNotNull('minimum_stock')
                ->whereColumn('quantity', '<', 'minimum_stock'),
            'warranties.expiring_30_days' => $query->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
                ->whereDate('end_date', '>=', $asOfDate)
                ->whereDate('end_date', '<=', CarbonImmutable::parse($asOfDate)->addDays(30)->toDateString()),
            default => null,
        };
        return (int) $query->count();
    }

    /** @return array<string,string> DB decimal sums returned unchanged as numeric strings. */
    private function invoiceTotals(User $actor, string $decision, string $fromDate, string $toDate, string $asOfDate): array
    {
        $query = $this->scopedQuery($actor, 'invoices', $decision);
        $totals = $query->whereDate('invoice_date', '>=', $fromDate)
            ->whereDate('invoice_date', '<=', $toDate)
            ->selectRaw("COALESCE(SUM(CASE WHEN status IN ('Sent','Paid') THEN total_amount ELSE 0 END),0) AS invoiced,
                        COALESCE(SUM(CASE WHEN status = 'Paid' THEN total_amount ELSE 0 END),0) AS paid,
                        COALESCE(SUM(CASE WHEN status = 'Sent' THEN total_amount ELSE 0 END),0) AS outstanding,
                        COALESCE(SUM(CASE WHEN status = 'Sent' AND due_date < ? THEN total_amount ELSE 0 END),0) AS overdue",
                [$asOfDate])->first();

        return [
            'invoices.invoiced' => (string) ($totals->invoiced ?? '0'),
            'invoices.paid' => (string) ($totals->paid ?? '0'),
            'invoices.outstanding' => (string) ($totals->outstanding ?? '0'),
            'invoices.overdue' => (string) ($totals->overdue ?? '0'),
        ];
    }
}
