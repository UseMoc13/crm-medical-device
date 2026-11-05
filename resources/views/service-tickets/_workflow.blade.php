@php
    $workflowStatus = strtolower(trim((string) $serviceTicket->status));
    $workflowTransitions = [
        'open' => ['in_progress' => 'Start Work', 'on_hold' => 'Put On Hold'],
        'in_progress' => ['on_hold' => 'Put On Hold', 'resolved' => 'Resolve Ticket'],
        'on_hold' => ['in_progress' => 'Resume Work'],
        'resolved' => ['in_progress' => 'Reopen Work', 'closed' => 'Close Ticket'],
        'closed' => [],
    ];
    $workflowActions = $workflowTransitions[$workflowStatus] ?? [];
@endphp

@if(session('workflow_success'))
    <div class="ticket-workflow-toast" role="status" aria-live="polite">{{ session('workflow_success') }}</div>
@endif

@if($errors->has('status') || $errors->has('resolution') || $errors->has('user_id'))
    <div class="ticket-workflow-validation" role="alert">
        @foreach(['status','resolution','user_id'] as $workflowField)
            @error($workflowField)<p>{{ $message }}</p>@enderror
        @endforeach
    </div>
@endif

<div class="card ticket-workflow-card">
    <div class="card-head">
        <div>
            <h3>Service Ticket Workflow</h3>
            <p>Record service progress after the corresponding work has been performed.</p>
        </div>
        <span class="ticket-workflow-current">{{ ucwords(str_replace('_', ' ', $workflowStatus)) }}</span>
    </div>
    <div class="card-body">
        <div class="ticket-workflow-actions">
            @forelse($workflowActions as $nextStatus => $actionLabel)
                <button type="button" class="btn {{ in_array($nextStatus, ['in_progress','resolved','closed'], true) ? 'primary' : '' }}"
                        data-ticket-workflow-action="{{ $nextStatus }}"
                        data-ticket-workflow-label="{{ $actionLabel }}">
                    {{ $actionLabel }}
                </button>
            @empty
                <span class="ticket-workflow-hint">This ticket is closed. No further workflow transitions are available.</span>
            @endforelse
            @if($workflowStatus !== 'closed')
                <a href="{{ route('service-tickets.edit', $serviceTicket) }}" class="btn">Edit Assignment & Details</a>
            @endif
        </div>
        <p class="ticket-workflow-hint">
            An assigned technician is required to start work. Resolving or closing a ticket requires a documented resolution.
            Warranty coverage is informational and does not prevent out-of-warranty service.
        </p>
    </div>
</div>

<div class="ticket-workflow-modal" id="ticketWorkflowModal" hidden>
    <div class="ticket-workflow-dialog" role="dialog" aria-modal="true" aria-labelledby="ticketWorkflowTitle">
        <div class="ticket-workflow-symbol">?</div>
        <h3 id="ticketWorkflowTitle">Confirm Status Change</h3>
        <p id="ticketWorkflowDescription">Are you sure you want to change the ticket status?</p>
        <form action="{{ route('service-tickets.change-status', $serviceTicket) }}" method="POST" id="ticketWorkflowForm">
            @csrf
            <input type="hidden" name="_method" value="PATCH" id="ticketWorkflowMethod">
            <input type="hidden" name="status" id="ticketWorkflowNextStatus" value="">
            <div class="ticket-workflow-resolution" id="ticketWorkflowResolutionWrap" hidden>
                <label for="ticketWorkflowResolution">Service Resolution <span aria-hidden="true">*</span></label>
                <textarea name="resolution" id="ticketWorkflowResolution" rows="4" maxlength="5000"
                          placeholder="Describe the diagnosis, action taken and final service result...">{{ old('resolution') }}</textarea>
                <small>Add a resolution entry for this workflow event. Previous records remain in Resolution History.</small>
            </div>
            <div class="ticket-workflow-dialog-actions">
                <button type="button" class="btn" id="ticketWorkflowCancel">Cancel</button>
                <button type="submit" class="btn primary" id="ticketWorkflowConfirm">Confirm</button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('ticketWorkflowModal');
    const next = document.getElementById('ticketWorkflowNextStatus');
    const title = document.getElementById('ticketWorkflowTitle');
    const description = document.getElementById('ticketWorkflowDescription');
    const method = document.getElementById('ticketWorkflowMethod');
    const form = document.getElementById('ticketWorkflowForm');
    const wrap = document.getElementById('ticketWorkflowResolutionWrap');
    const resolution = document.getElementById('ticketWorkflowResolution');
    if (!modal || !form) return;
    const permitted = new Set(@json(array_keys($workflowActions)));
    let previouslyFocused = null;

    const close = () => {
        modal.hidden = true;
        next.value = '';
        previouslyFocused?.focus();
    };
    document.querySelectorAll('[data-ticket-workflow-action]').forEach(button => {
        button.addEventListener('click', () => {
            const status = button.dataset.ticketWorkflowAction;
            if (!permitted.has(status)) return;
            previouslyFocused = button;
            next.value = status;
            method.value = 'PATCH';
            title.textContent = button.dataset.ticketWorkflowLabel + '?';
            description.textContent = 'Confirm this change to ' + status.replace(/_/g, ' ') + '?';
            const requiresResolution = status === 'resolved' || status === 'closed';
            wrap.hidden = !requiresResolution;
            resolution.required = requiresResolution;
            modal.hidden = false;
            if (requiresResolution) { resolution.value = ''; resolution.focus(); }
            else document.getElementById('ticketWorkflowCancel').focus();
        });
    });
    form.addEventListener('submit', event => {
        method.value = 'PATCH';
        if (!permitted.has(next.value) || ((next.value === 'resolved' || next.value === 'closed') && !resolution.value.trim())) {
            event.preventDefault();
            if (!resolution.value.trim()) resolution.focus();
        }
    });
    document.getElementById('ticketWorkflowCancel').addEventListener('click', close);
    modal.addEventListener('click', event => { if (event.target === modal) close(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !modal.hidden) close(); });
    const toast = document.querySelector('.ticket-workflow-toast');
    if (toast) window.setTimeout(() => toast.remove(), 4500);
})();
</script>
