<?php

namespace App\Services;

use App\Models\MaintenanceSchedule;
use Illuminate\Validation\ValidationException;

/** Shared rules for every Maintenance status mutation (workflow, schedule and records). */
final class MaintenanceWorkflowRules
{
    public static function assertTransition(MaintenanceSchedule $schedule, string $next, int $recordCount): void
    {
        if ($next === $schedule->status) {
            return;
        }

        $allowed = [
            'Scheduled' => ['In Progress', 'Cancelled'],
            'In Progress' => ['Completed', 'Cancelled'],
        ];

        if (!in_array($next, $allowed[$schedule->status] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => 'This maintenance status transition is not allowed. Use the workflow actions.',
            ]);
        }
        if ($next === 'In Progress' && !$schedule->user_id) {
            throw ValidationException::withMessages(['status' => 'Assign a technician before starting maintenance.']);
        }
        if ($next === 'Completed' && $recordCount < 1) {
            throw ValidationException::withMessages(['status' => 'Add at least one maintenance record before completing maintenance.']);
        }
        if ($next === 'Cancelled' && $recordCount > 0) {
            throw ValidationException::withMessages(['status' => 'Schedules with maintenance records cannot be cancelled.']);
        }
    }

    public static function assertRecordWritable(MaintenanceSchedule $schedule): void
    {
        if ($schedule->status !== 'In Progress') {
            throw ValidationException::withMessages([
                'status' => 'Maintenance records can only be modified while the schedule is In Progress.',
            ]);
        }
    }
}
