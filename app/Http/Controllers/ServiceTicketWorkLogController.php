<?php

namespace App\Http\Controllers;

use App\Models\ServiceTicket;
use App\Services\CrmServiceDataScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceTicketWorkLogController extends Controller
{
    private const TYPES = ['diagnosis', 'inspection', 'repair', 'testing', 'progress'];

    public function store(Request $request, ServiceTicket $serviceTicket): RedirectResponse
    {
        app(CrmServiceDataScope::class)->assertVisible(ServiceTicket::query(), 'service_tickets', (string) $serviceTicket->getKey());
        $data = $request->validate([
            'entry_type' => ['required', Rule::in(self::TYPES)],
            'notes' => ['required', 'string', 'min:3', 'max:5000'],
        ]);

        /* A798:worklog_actor_capture */
        $actor = auth('web')->user();
        abort_unless($actor instanceof \App\Models\User, 403, 'Sign in before writing work logs.');

        DB::transaction(function () use ($serviceTicket, $data, $actor): void {
            $ticket = ServiceTicket::query()->whereKey($serviceTicket->getKey())
                ->lockForUpdate()->firstOrFail();

            if (!in_array($ticket->status, ['in_progress', 'on_hold'], true)) {
                throw ValidationException::withMessages([
                    'work_log' => 'Work logs can only be added while the ticket is In Progress or On Hold.',
                ]);
            }
            if (empty($ticket->user_id)) {
                throw ValidationException::withMessages([
                    'work_log' => 'Assign a technician before recording work progress.',
                ]);
            }

            /* A798:worklog_actor_guard */
            abort_unless(app(\App\Services\CrmWorkLogAuthorPolicy::class)->canWrite($actor, $ticket),
                403, 'Only the assigned active technician or Super Admin may record a work log.');

            DB::table('service_ticket_work_logs')->insert([
                'work_log_id' => (string) Str::uuid(),
                'service_ticket_id' => $ticket->getKey(),
                'entry_type' => $data['entry_type'],
                'status_snapshot' => $ticket->status,
                /* A798:worklog_author_insert */
                'created_by_user_id' => (string) $actor->getKey(),
                'notes' => trim($data['notes']),
                'created_at' => now(),
            ]);
        }, 3);

        return redirect()->route('service-tickets.show', $serviceTicket)
            ->with('success', 'Technician work log saved successfully.');
    }
}
