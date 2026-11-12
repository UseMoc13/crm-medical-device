{{-- Technician Work Log: internal progress, separate from final resolution. --}}
<section class="card ticket-worklog-card" id="ticket-work-logs">
    <div class="card-head">
        <div>
            <h3>Technician Work Log</h3>
            <p>Record diagnosis, inspection, repair, and testing progress without overwriting Resolution History.</p>
        </div>
        <span class="ticket-worklog-count">{{ $workLogs->count() }} entries</span>
    </div>
    <div class="card-body">
        @if(in_array($serviceTicket->status, ['in_progress', 'on_hold'], true) && $serviceTicket->user_id)
            <form method="POST" action="{{ route('service-tickets.work-logs.store', $serviceTicket) }}" class="ticket-worklog-form" id="ticketWorkLogForm">
                @csrf
                <div class="ticket-worklog-inputs">
                    <div>
                        <label for="workLogType">Activity Type <span aria-hidden="true">*</span></label>
                        <select id="workLogType" name="entry_type" required>
                            @foreach(['diagnosis' => 'Diagnosis', 'inspection' => 'Inspection', 'repair' => 'Repair / Replacement', 'testing' => 'Testing', 'progress' => 'General Progress'] as $code => $label)
                                <option value="{{ $code }}" @selected(old('entry_type') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="workLogNotes">Work Notes <span aria-hidden="true">*</span></label>
                        <textarea id="workLogNotes" name="notes" minlength="3" maxlength="5000" rows="3" required placeholder="Describe actual work performed, findings, or test results...">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="ticket-worklog-actions">
                    <span>New entries are stored chronologically. This does not change ticket status or inventory.</span>
                    <button type="submit" class="btn primary">Save Work Log</button>
                </div>
            </form>
        @elseif($serviceTicket->status === 'closed' || $serviceTicket->status === 'resolved')
            <p class="ticket-worklog-hint">This ticket is {{ ucwords(str_replace('_', ' ', $serviceTicket->status)) }}. Its work log is read-only.</p>
        @elseif(!$serviceTicket->user_id)
            <p class="ticket-worklog-hint">Assign a technician and start work before recording progress.</p>
        @else
            <p class="ticket-worklog-hint">Start Work using the workflow above to record technician activity.</p>
        @endif

        <div class="ticket-worklog-list" aria-label="Technician work history">
            @forelse($workLogs as $entry)
                <div class="ticket-worklog-entry">
                    <div class="ticket-worklog-entry-head">
                        <span class="ticket-worklog-type">{{ ucwords(str_replace('_', ' ', $entry->entry_type)) }}</span>
                        <time>{{ \Illuminate\Support\Carbon::parse($entry->created_at)->format('d M Y, H:i') }}</time>
                    </div>
                    <p>{{ $entry->notes }}</p>
                    <small>Ticket status when recorded: {{ ucwords(str_replace('_', ' ', $entry->status_snapshot)) }}</small>
                </div>
            @empty
                <p class="ticket-worklog-hint">No work logs recorded yet.</p>
            @endforelse
        </div>
    </div>
</section>
