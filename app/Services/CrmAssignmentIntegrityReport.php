<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A7.9.8 read-only assignment impact/integrity report.
 * Historical rows are NEVER reassigned or deleted. An ineligible existing
 * assignee is a REVIEW item, not an automatic data correction.
 */
final class CrmAssignmentIntegrityReport
{
    private const SOURCES = [
        'service_tickets' => ['label' => 'Service Tickets', 'key' => 'service_ticket_id', 'purpose' => 'technician'],
        'maintenance_schedules' => ['label' => 'Maintenance Schedules', 'key' => 'maintenance_schedule_id', 'purpose' => 'technician'],
        'maintenance_records' => ['label' => 'Maintenance Records', 'key' => 'maintenance_record_id', 'purpose' => 'technician'],
        'installations' => ['label' => 'Installations', 'key' => 'installation_id', 'purpose' => 'technician'],
        'leads' => ['label' => 'Leads', 'key' => 'lead_id', 'purpose' => 'sales'],
        'opportunities' => ['label' => 'Opportunities', 'key' => 'opportunity_id', 'purpose' => 'sales'],
    ];

    /** @return array{total:int,items:list<array{label:string,purpose:string,count:int,eligible_now:bool}>} */
    public function forUser(string $userId): array
    {
        $directory = app(CrmUserAssignmentEligibility::class);
        $eligible = [];
        foreach (['technician', 'sales'] as $purpose) {
            $eligible[$purpose] = $directory->eligibleQuery($purpose)->whereKey($userId)->exists();
        }
        $items = [];
        $total = 0;
        foreach (self::SOURCES as $table => $spec) {
            if (!Schema::hasTable($table)) continue;
            $count = DB::table($table)->where('user_id', $userId)->count();
            if ($count === 0) continue;
            $total += $count;
            $items[] = [
                'label' => $spec['label'], 'purpose' => $spec['purpose'],
                'count' => $count, 'eligible_now' => $eligible[$spec['purpose']],
            ];
        }
        return ['total' => $total, 'items' => $items];
    }

    /** @return list<array{module:string,purpose:string,assigned:int,unassigned:int,review:int,examples:array}> */
    public function summary(int $limit = 5): array
    {
        $limit = max(0, min(20, $limit));
        $directory = app(CrmUserAssignmentEligibility::class);
        $result = [];
        foreach (self::SOURCES as $table => $spec) {
            if (!Schema::hasTable($table)) continue;
            $purpose = $spec['purpose'];
            $eligibleIds = $directory->eligibleQuery($purpose)->select('users.user_id');
            $base = DB::table($table . ' as records');
            $reviewQuery = (clone $base)->whereNotNull('records.user_id')
                ->whereNotIn('records.user_id', $eligibleIds);
            $examples = [];
            if ($limit > 0) {
                $examples = (clone $reviewQuery)
                    ->leftJoin('users as staff', 'staff.user_id', '=', 'records.user_id')
                    ->orderBy('records.' . $spec['key'])
                    ->limit($limit)
                    ->get([
                        'records.' . $spec['key'] . ' as record_id',
                        'records.user_id as user_id',
                        'staff.name as user_name',
                        'staff.status as user_status',
                    ])->map(static fn ($row): array => (array) $row)->all();
            }
            $result[] = [
                'module' => $spec['label'], 'purpose' => $purpose,
                'assigned' => (clone $base)->whereNotNull('records.user_id')->count(),
                'unassigned' => (clone $base)->whereNull('records.user_id')->count(),
                'review' => $reviewQuery->count(),
                'examples' => $examples,
            ];
        }
        return $result;
    }
}
