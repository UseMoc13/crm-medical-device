<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MaintenanceWorkflowController extends Controller
{
    public function changeStatus(Request $request, MaintenanceSchedule $maintenance): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['In Progress', 'Completed', 'Cancelled'])],
        ]);

        DB::transaction(function () use ($maintenance, $data): void {
            $schedule = MaintenanceSchedule::query()
                ->whereKey($maintenance->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $allowed = [
                'Scheduled' => ['In Progress', 'Cancelled'],
                'In Progress' => ['Completed', 'Cancelled'],
            ];
            $next = $data['status'];
            if (!in_array($next, $allowed[$schedule->status] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => 'This maintenance status transition is not allowed.',
                ]);
            }

            $recordCount = MaintenanceRecord::query()
                ->where('maintenance_schedule_id', $schedule->getKey())->count();

            if ($next === 'In Progress' && !$schedule->user_id) {
                throw ValidationException::withMessages([
                    'status' => 'Assign a technician before starting maintenance.',
                ]);
            }
            if ($next === 'Completed' && $recordCount === 0) {
                throw ValidationException::withMessages([
                    'status' => 'Add at least one maintenance record before completing maintenance.',
                ]);
            }
            if ($next === 'Cancelled' && $recordCount > 0) {
                throw ValidationException::withMessages([
                    'status' => 'A schedule with maintenance records cannot be cancelled.',
                ]);
            }

            $schedule->status = $next;
            $schedule->save();
        }, 3);

        return redirect()->route('maintenance.show', $maintenance)
            ->with('success', 'Maintenance status updated successfully.');
    }
}
