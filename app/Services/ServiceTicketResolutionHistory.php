<?php

namespace App\Services;

use App\Models\ServiceTicket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Append-only resolution log. Call while ticket row is locked, inside the same transaction. */
class ServiceTicketResolutionHistory
{
    public function record(ServiceTicket $ticket, string $previousStatus, string $previousResolution, array $changes): void
    {
        $nextStatus = (string) $ticket->status;
        $nextResolution = trim((string) ($ticket->resolution ?? ''));
        $previousResolution = trim($previousResolution);
        $statusChanged = strtolower($previousStatus) !== strtolower($nextStatus);
        $resolutionChanged = $previousResolution !== $nextResolution;

        // Record every Resolve/Close status event and every substantive resolution edit.
        // Don't append duplicates when unrelated ticket fields are edited.
        if (!$resolutionChanged && !($statusChanged && in_array($nextStatus, ['resolved', 'closed'], true))) {
            return;
        }
        if ($nextResolution === '') {
            return;
        }

        DB::table('service_ticket_resolution_logs')->insert([
            'resolution_log_id' => (string) Str::uuid(),
            'service_ticket_id' => $ticket->getKey(),
            'status_from' => $previousStatus,
            'status_to' => $nextStatus,
            'resolution' => $nextResolution,
            'created_at' => now(),
        ]);
    }
}
