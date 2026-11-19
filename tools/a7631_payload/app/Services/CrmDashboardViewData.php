<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

/**
 * A7.6.3: Dashboard read model. Never runs global KPI counts, never grants
 * a permission, and never exposes free-text activity outside an explicit viewer scope.
 * Announcement publication is deliberately NOT handled here.
 */
final class CrmDashboardViewData
{
    public function __construct(
        private readonly CrmScopedKpiQuery $kpis,
        private readonly CrmPermissionResolver $permissions,
        private readonly CrmDataScope $dataScope,
    ) {}

    public function forCurrentUser(): array
    {
        $actor = Auth::guard('web')->user();
        if (!$actor instanceof User) {
            throw new AuthenticationException('Sign in before opening the CRM dashboard.');
        }

        $metrics = $this->kpis->run()['metrics'];
        $cards = self::present($metrics);

        return [
            'stats' => $cards['stats'],
            'statHints' => $cards['statHints'],
            'salesCards' => $cards['salesCards'],
            'recentActivities' => $this->recentActivities($actor),
        ];
    }

    /** Pure presentation mapper: values are never read from restricted metrics. */
    public static function present(array $metrics): array
    {
        $map = [
            'customers' => ['customers.total', 'Customer database'],
            'leads' => ['leads.active', 'Current leads'],
            'opportunities' => ['opportunities.total', 'Current opportunities'],
            'service_tickets' => ['service_tickets.total', 'Current service tickets'],
        ];
        $stats = [];
        $hints = [];
        foreach ($map as $key => [$metricId, $label]) {
            $metric = $metrics[$metricId] ?? [];
            $visible = ($metric['status'] ?? null) === 'visible'
                && ($metric['unit'] ?? null) === 'count'
                && is_int($metric['value'] ?? null) && $metric['value'] >= 0;
            $stats[$key] = $visible ? number_format($metric['value'], 0, ',', '.') : '—';
            $hints[$key] = $visible ? $label : 'Not available for your access';
        }

        $definitions = [
            ['opportunities.total', 'Opportunities', 'Current visible opportunities'],
            ['deliveries.in_transit', 'Deliveries In Transit', 'Current in-transit deliveries'],
            ['invoices.invoiced', 'Total Invoiced', 'Sent + Paid · invoice-date period'],
            ['invoices.paid', 'Paid', 'Paid invoices · invoice-date period'],
            ['invoices.outstanding', 'Outstanding', 'Sent invoices · invoice-date period'],
            ['invoices.overdue', 'Overdue', 'Sent invoices past due date'],
        ];
        $sales = [];
        foreach ($definitions as [$metricId, $label, $hint]) {
            $metric = $metrics[$metricId] ?? [];
            if (($metric['status'] ?? null) !== 'visible') continue;
            $value = null;
            if (($metric['unit'] ?? null) === 'count' && is_int($metric['value'] ?? null) && $metric['value'] >= 0) {
                $value = number_format($metric['value'], 0, ',', '.');
            } elseif (($metric['unit'] ?? null) === 'IDR' && is_string($metric['value'] ?? null)) {
                $amount = self::formatDecimalIdr($metric['value']);
                if ($amount !== null) $value = 'Rp ' . $amount;
            }
            if ($value !== null) {
                $sales[] = ['label' => $label, 'value' => $value, 'hint' => $hint];
            }
        }

        return ['stats' => $stats, 'statHints' => $hints, 'salesCards' => $sales];
    }

    /** Format a decimal *string* without float conversion or currency rounding. */
    public static function formatDecimalIdr(string $decimal): ?string
    {
        if (!preg_match('/^-?\d+(?:\.\d+)?$/D', $decimal)) return null;
        $negative = str_starts_with($decimal, '-');
        $parts = explode('.', ltrim($decimal, '-'), 2);
        $whole = ltrim($parts[0], '0');
        $whole = $whole === '' ? '0' : $whole;
        $fraction = rtrim($parts[1] ?? '', '0');
        $formatted = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $whole);
        return ($negative && ($whole !== '0' || $fraction !== '') ? '-' : '')
            . $formatted . ($fraction !== '' ? ',' . $fraction : '');
    }

    /**
     * No guest, no page permission or no audited owner column => no free text.
     * With explicit all scope, all rows may be listed. Otherwise owner, explicit
     * collaborator and optionally branch rows only; active opt-in scope narrows further.
     */
    private function recentActivities(User $actor): Collection
    {
        $page = CrmRoleAccessCatalog::PAGES['activities'];
        $viewPermission = CrmRoleAccessCatalog::key('activities', 'view');
        if (!$this->permissions->allows($actor, $page['base'])
            || !$this->permissions->allows($actor, $viewPermission)
            || !Schema::hasTable('activities')) {
            return collect();
        }
        foreach (['activity_id', 'user_id', 'subject', 'description', 'created_at'] as $column) {
            if (!Schema::hasColumn('activities', $column)) return collect();
        }

        $scope = $this->permissions->scopes($actor, $viewPermission);
        $viewer = (string) $actor->getKey();
        $query = DB::table('activities');
        if (!in_array('all', $scope, true)) {
            $collaboratorsAvailable = Schema::hasTable('workspace_collaboration_members')
                && Schema::hasColumn('workspace_collaboration_members', 'activity_id')
                && Schema::hasColumn('workspace_collaboration_members', 'user_id');
            $branchAllowed = in_array('branch', $scope, true)
                && $actor->branch_id !== null && Schema::hasTable('users')
                && Schema::hasColumn('users', 'branch_id');
            $query->where(function ($visible) use ($viewer, $collaboratorsAvailable, $branchAllowed, $actor) {
                $visible->where('activities.user_id', $viewer);
                if ($collaboratorsAvailable) {
                    $visible->orWhereExists(function ($members) use ($viewer) {
                        $members->selectRaw('1')->from('workspace_collaboration_members')
                            ->whereColumn('workspace_collaboration_members.activity_id', 'activities.activity_id')
                            ->where('workspace_collaboration_members.user_id', $viewer);
                    });
                }
                if ($branchAllowed) {
                    $visible->orWhereIn('activities.user_id', DB::table('users')
                        ->select('user_id')->where('branch_id', $actor->branch_id));
                }
            });
        }
        // Additional opt-in row scopes must narrow, never expand, this predicate.
        if ((bool) config('crm_data_scope.activities_enabled', false)) {
            $query = $this->dataScope->apply($query, $actor, 'activities', $scope);
        }
        return $query->select('activity_id', 'subject as title', 'description', 'created_at')
            ->orderByDesc('created_at')->limit(5)->get();
    }
}
