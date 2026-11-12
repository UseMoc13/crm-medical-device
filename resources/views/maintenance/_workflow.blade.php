@php
    $mtStatus = (string) $maintenance->status;
    $mtRecordCount = $allRecords->count();
    $mtHasTechnician = filled($maintenance->user_id);
@endphp
<section class="card mt-workflow-card" aria-labelledby="mtWorkflowHeading">
    <div class="card-head">
        <div>
            <h3 id="mtWorkflowHeading">Maintenance Workflow</h3>
            <p>Track the schedule from planning to completion. Status changes are validated on the server.</p>
        </div>
        <span class="mt-badge mt-status-{{ \Illuminate\Support\Str::slug($mtStatus) }}">{{ $mtStatus }}</span>
    </div>
    <div class="card-body">
        @error('status') <div class="mt-workflow-error" role="alert">{{ $message }}</div> @enderror
        <div class="mt-workflow-steps" aria-label="Maintenance stages">
            <span class="mt-workflow-step {{ $mtStatus === 'Scheduled' ? 'is-current' : '' }}">Scheduled</span>
            <span class="mt-workflow-arrow" aria-hidden="true">→</span>
            <span class="mt-workflow-step {{ $mtStatus === 'In Progress' ? 'is-current' : '' }}">In Progress</span>
            <span class="mt-workflow-arrow" aria-hidden="true">→</span>
            <span class="mt-workflow-step {{ $mtStatus === 'Completed' ? 'is-current' : '' }}">Completed</span>
        </div>
        <div class="mt-workflow-actions">
            @if($mtStatus === 'Scheduled')
                @if($mtHasTechnician)
                    <button type="button" class="btn primary" data-mt-next="In Progress" data-mt-message="Start this maintenance assignment?">Start Maintenance</button>
                @else
                    <a href="{{ route('maintenance.edit', $maintenance) }}" class="btn primary">Assign Technician First</a>
                @endif
                @if($mtRecordCount === 0)
                    <button type="button" class="btn danger" data-mt-next="Cancelled" data-mt-message="Cancel this schedule? This action cannot be undone.">Cancel Schedule</button>
                @endif
            @elseif($mtStatus === 'In Progress')
                <a class="btn primary" href="{{ route('maintenance.records.create', $maintenance) }}">+ Add Maintenance Record</a>
                @if($mtRecordCount > 0)
                    <button type="button" class="btn primary" data-mt-next="Completed" data-mt-message="Complete this maintenance schedule? Ensure all required work has been documented.">Complete Maintenance</button>
                @else
                    <button type="button" class="btn danger" data-mt-next="Cancelled" data-mt-message="Cancel this schedule? This action cannot be undone.">Cancel Schedule</button>
                @endif
            @else
                <span class="mt-workflow-hint">This schedule is finalized. Existing records remain available for review.</span>
            @endif
            @if($maintenance->serial_number_id)
                <a class="btn" href="{{ route('service-history.show', $maintenance->serial_number_id) }}">View Device Service History</a>
            @endif
        </div>
        @if($mtStatus === 'Scheduled' && !$mtHasTechnician)
            <p class="mt-workflow-hint">Assign a technician before starting maintenance.</p>
        @elseif($mtStatus === 'In Progress' && $mtRecordCount === 0)
            <p class="mt-workflow-hint">Add at least one maintenance record before completing the schedule.</p>
        @elseif($mtStatus === 'Cancelled')
            <p class="mt-workflow-hint">Cancelled schedules cannot be restarted from this workflow.</p>
        @endif
    </div>
</section>

<div class="mt-workflow-modal" id="mtStatusModal" hidden>
    <div class="mt-workflow-dialog" role="dialog" aria-modal="true" aria-labelledby="mtStatusTitle">
        <h3 id="mtStatusTitle">Confirm Status Change</h3>
        <p id="mtStatusMessage"></p>
        <form action="{{ route('maintenance.change-status', $maintenance) }}" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" id="mtNextStatus">
            <div class="mt-workflow-modal-actions">
                <button type="button" class="btn" id="mtStatusCancel">Back</button>
                <button type="submit" class="btn primary" id="mtStatusConfirm">Confirm Change</button>
            </div>
        </form>
    </div>
</div>
<script>
(() => {
    const modal = document.getElementById('mtStatusModal');
    if (!modal) return;
    const next = document.getElementById('mtNextStatus');
    const msg = document.getElementById('mtStatusMessage');
    const confirm = document.getElementById('mtStatusConfirm');
    const close = () => { modal.hidden = true; next.value = ''; };
    document.querySelectorAll('[data-mt-next]').forEach(button => {
        button.addEventListener('click', () => {
            next.value = button.dataset.mtNext;
            msg.textContent = button.dataset.mtMessage || 'Continue with this status change?';
            confirm.classList.toggle('mt-workflow-danger', next.value === 'Cancelled');
            modal.hidden = false;
        });
    });
    document.getElementById('mtStatusCancel')?.addEventListener('click', close);
    modal.addEventListener('click', event => { if (event.target === modal) close(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });
})();
</script>
