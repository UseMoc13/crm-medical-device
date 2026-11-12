<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRecord;
use App\Services\MaintenanceWorkflowRules;
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

            $next = $data['status'];
            $recordCount = MaintenanceRecord::query()
                ->where('maintenance_schedule_id', $schedule->getKey())->count();
            MaintenanceWorkflowRules::assertTransition($schedule, $next, $recordCount);

            $schedule->status = $next;
            $schedule->save();
        }, 3);

        return redirect()->route('maintenance.show', $maintenance)
            ->with('success', 'Maintenance status updated successfully.');
    }
}
