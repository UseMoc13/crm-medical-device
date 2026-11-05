<?php

namespace App\Services;

use App\Models\ServiceTicket;
use Illuminate\Validation\ValidationException;

/** Single source of truth for both Edit form and Show status actions. */
class ServiceTicketTransition
{
    private const TRANSITIONS = [
        'open' => ['in_progress', 'on_hold'],
        'in_progress' => ['on_hold', 'resolved'],
        'on_hold' => ['in_progress'],
        'resolved' => ['in_progress', 'closed'],
        'closed' => [],
    ];

    /** @return array<int,string> */
    public function availableStatuses(string $current): array
    {
        $current = strtolower(trim($current));
        return array_merge([$current], self::TRANSITIONS[$current] ?? []);
    }

    /**
     * Validate a transition and return all fields that must be persisted together.
     * Caller must lock the ticket row and execute in a DB transaction.
     *
     * @param array<string,mixed> $input
     * @return array<string,mixed>
     */
    public function prepare(ServiceTicket $ticket, array $input): array
    {
        $current = strtolower(trim((string) $ticket->status));
        $next = strtolower(trim((string) ($input['status'] ?? '')));
        if (!array_key_exists($current, self::TRANSITIONS)) {
            throw ValidationException::withMessages(['status' => 'Unknown current ticket status.']);
        }
        if ($current === 'closed') {
            throw ValidationException::withMessages(['status' => 'Closed service tickets cannot be modified.']);
        }
        if ($next !== $current && !in_array($next, self::TRANSITIONS[$current], true)) {
            throw ValidationException::withMessages(['status' => 'This service ticket status transition is not allowed.']);
        }

        // When editing, the new assignee is available in the same request.
        $assignee = array_key_exists('user_id', $input) ? $input['user_id'] : $ticket->user_id;
        if ($next === 'in_progress' && empty($assignee)) {
            throw ValidationException::withMessages(['user_id' => 'Assign a technician before starting or resuming service.']);
        }

        $resolution = array_key_exists('resolution', $input)
            ? trim((string) ($input['resolution'] ?? ''))
            : trim((string) ($ticket->resolution ?? ''));
        if (in_array($next, ['resolved', 'closed'], true) && $resolution === '') {
            throw ValidationException::withMessages(['resolution' => 'Enter a resolution before resolving or closing the ticket.']);
        }

        $changes = $input;
        $changes['status'] = $next;
        if (array_key_exists('resolution', $input)) {
            $changes['resolution'] = $resolution === '' ? null : $resolution;
        }
        $changes['closed_at'] = $next === 'closed' ? ($ticket->closed_at ?? now()) : null;
        return $changes;
    }
}
