@extends('layouts.app')

@section('title', 'Installation Details')
@section('content')
@php
    $status = strtolower(
        str_replace(' ', '_', $installation->status)
    );

    $checklistTotal = $installation->checklists->count();
    $checklistDone = $installation->checklists->where('is_completed', true)->count();
    $checklistPercent = $checklistTotal ? (int) round(100 * $checklistDone / $checklistTotal) : 0;
    $badgeClass = match ($status) {
        'scheduled' => 'warning',
        'in_progress' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        default => 'secondary',
    };
@endphp

<div class="page-head">
    <div>
        <h1>Installation Details</h1>
        <p>View installation information and checklist progress.</p>
    </div>

    <div class="actions">
        <a href="{{ route('installations.index') }}" class="btn">
            &larr; Back
        </a>

        @if(!in_array($installation->status, ['Completed', 'Cancelled']))
            <a href="{{ route('installations.edit', $installation->installation_id) }}" class="btn primary">Edit Installation</a>
        @endif
    </div>
</div>

@include('installations._toast')

<div class="card installation-progress-card">
    <div class="card-head"><div><h3>Installation Workflow</h3><p>Record installation progress after completing the physical work.</p></div><span class="installation-badge {{ $badgeClass }}">{{ $installation->status }}</span></div>
    <div class="card-body">
        <div class="installation-progress-heading"><strong>{{ $checklistDone }} / {{ $checklistTotal }} Checklist Completed</strong><strong>{{ $checklistPercent }}%</strong></div>
        <div class="installation-progress-track" role="progressbar" aria-valuenow="{{ $checklistPercent }}" aria-valuemin="0" aria-valuemax="100"><div class="installation-progress-fill" style="width: {{ $checklistPercent }}%"></div></div>
        <div class="installation-status-actions">
            @if($installation->status === 'Scheduled')
                <button type="button" class="btn primary" data-installation-next="In Progress">Start Installation</button>
                <button type="button" class="btn" data-installation-next="Cancelled">Cancel Installation</button>
            @elseif($installation->status === 'In Progress')
                <button type="button" class="btn primary" data-installation-next="Completed" @disabled($checklistTotal === 0 || $checklistDone !== $checklistTotal)>Complete Installation</button>
                @if($checklistTotal === 0 || $checklistDone !== $checklistTotal)<span class="form-hint">Finish all checklist items before completion.</span>@endif
            @else
                <span class="form-hint">This installation is finalized. Status changes are disabled.</span>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div><h3>Installation Information</h3><p>Installation, technician, and scheduling details.</p></div>

        <span class="installation-badge {{ $badgeClass }}">
            {{ str_replace('_', ' ', $installation->status) }}
        </span>
    </div>

    <div class="card-body">
        <div class="installation-info-grid">

            <div class="info-field">
                <span class="info-label">Installation ID</span>
                <span class="info-value">
                    {{ $installation->installation_id }}
                </span>
            </div>

            <div class="info-field">
                <span class="info-label">Delivery Number</span>
                <span class="info-value">
                    @if($installation->delivery)
                        <a href="{{ route('deliveries.show', $installation->delivery_id) }}">{{ $installation->delivery->delivery_number }}</a>
                    @else
                        —
                    @endif
                </span>
            </div>

            <div class="info-field">
                <span class="info-label">Installation Date</span>
                <span class="info-value">
                    {{ $installation->installation_date?->format('d M Y') ?? 'Not scheduled' }}
                </span>
            </div>

            <div class="info-field">
                <span class="info-label">Assigned User</span>
                <span class="info-value">
                    {{ $installation->user?->name
                        ?? $installation->user?->full_name
                        ?? $installation->user?->username
                        ?? $installation->user?->email
                        ?? '—' }}
                </span>
            </div>

            <div class="info-field full-width">
                <span class="info-label">Installation Location</span>
                <span class="info-value">
                    {{ $installation->location ?: 'No location provided.' }}
                </span>
            </div>

            <div class="info-field full-width">
                <span class="info-label">Notes</span>
                <span class="info-value">
                    {{ $installation->notes ?: 'No additional notes.' }}
                </span>
            </div>

            <div class="info-field">
                <span class="info-label">Created At</span>
                <span class="info-value">
                    {{ $installation->created_at?->format('d M Y H:i') ?? '—' }}
                </span>
            </div>

            <div class="info-field">
                <span class="info-label">Last Updated</span>
                <span class="info-value">
                    {{ $installation->updated_at?->format('d M Y H:i') ?? '—' }}
                </span>
            </div>

        </div>
    </div>
</div>

<div class="card checklist-card">
    <div class="card-head">
        <div><h3>Installation Checklist</h3><p>Update each task, including its notes, while installation is in progress.</p></div>
        <span class="checklist-count">
            {{ $installation->checklists->where('is_completed', true)->count() }}
            / {{ $installation->checklists->count() }} completed
        </span>
    </div>

    <div class="card-body">
        @if ($installation->checklists->isNotEmpty())
            <div class="table-wrap installation-table-wrap">
                <table class="installation-table">
                    <thead>
                        <tr>
                            <th class="col-number">No.</th>
                            <th>Checklist Item</th>
                            <th>Notes</th>
                            <th>Status</th>
                            <th>Completed At</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($installation->checklists as $checklist)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>{{ $checklist->item_name }}</td>

                                <td class="installation-checklist-note">
                                    @if($installation->status === 'In Progress')
                                        <form id="checklistForm{{ $checklist->checklist_id }}" action="{{ route('installations.checklists.update', [$installation, $checklist]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_completed" value="{{ $checklist->is_completed ? 1 : 0 }}" class="checklist-completed-value">
                                            <label for="checklistNotes{{ $checklist->checklist_id }}" class="sr-only">Notes for {{ $checklist->item_name }}</label>
                                            <textarea id="checklistNotes{{ $checklist->checklist_id }}" name="notes" maxlength="2000" rows="2" placeholder="Enter checklist findings or technician notes...">{{ old('notes', $checklist->notes) }}</textarea>
                                            <div class="note-actions">
                                              <button type="submit" class="btn">Save Notes</button>
                                              <button type="button" class="btn primary checklist-toggle" data-completed="{{ $checklist->is_completed ? '0' : '1' }}">{{ $checklist->is_completed ? 'Undo Completion' : 'Mark Done' }}</button>
                                            </div>
                                            <div class="note-hint">Saving notes does not change completion status.</div>
                                        </form>
                                    @else
                                        <span class="saved-note">{{ $checklist->notes ?: '—' }}</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($checklist->is_completed)
                                        <span class="installation-badge success">
                                            Completed
                                        </span>
                                    @else
                                        <span class="installation-badge warning">
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $checklist->completed_at?->format('d M Y H:i') ?? '—' }}

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <h3>No Checklist Items</h3>
                <p>
                    Checklist items have not been added to this installation.
                </p>
            </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-head"><div><h3>Devices from Delivery</h3><p>Serial numbers allocated to this installation.</p></div></div>
    <div class="card-body">
        @if($deliveryItems->isEmpty())
            <p>No serialized devices found in the originating Delivery.</p>
        @else
            <div class="table-wrap installation-table-wrap">
                <table class="installation-table">
                    <thead><tr><th>No.</th><th>Product</th><th>Serial Number</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($deliveryItems as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->serialNumber?->product?->product_name ?? '—' }}</td>
                                <td>{{ $item->serialNumber?->serial_number ?? '—' }}</td>
                                <td>{{ $item->serialNumber?->status ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@if($installation->status === 'Scheduled')
    <p class="form-hint">Change status to In Progress to start completing the installation checklist.</p>
@elseif($installation->status === 'In Progress')
    <p class="form-hint">Complete all {{ $installation->checklists->count() }} checklist items, then choose Complete Installation above.</p>
@endif

<div class="installation-confirm" id="installationStatusModal" hidden>
    <div class="installation-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="installationModalTitle">
        <div class="installation-confirm-symbol">?</div><h3 id="installationModalTitle">Confirm Status Change</h3>
        <p id="installationModalMessage"></p>
        <form method="POST" action="{{ route('installations.change-status', $installation) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" id="installationNextStatus">
            <div class="installation-confirm-actions">
                <button type="button" class="btn" id="installationModalCancel">Back</button>
                <button type="submit" class="btn primary">Confirm Change</button>
            </div>
        </form>
    </div>
</div>
@include('installations._warranty_activation')
@include('installations._style')
<script>
(() => {
    const modal = document.getElementById('installationStatusModal');
    const value = document.getElementById('installationNextStatus');
    const message = document.getElementById('installationModalMessage');
    document.querySelectorAll('[data-installation-next]').forEach(button => button.addEventListener('click', () => {
        value.value = button.dataset.installationNext;
        message.textContent = 'Change installation status to ' + value.value + '?';
        modal.hidden = false;
    }));
    const close = () => { modal.hidden = true; value.value = ''; };
    document.getElementById('installationModalCancel').addEventListener('click', close);
    modal.addEventListener('click', event => { if(event.target === modal) close(); });
    document.addEventListener('keydown', event => { if(event.key === 'Escape') close(); });
    document.querySelectorAll('.checklist-toggle').forEach(button => button.addEventListener('click', () => {
       const form = button.closest('form'); form.querySelector('.checklist-completed-value').value = button.dataset.completed; form.requestSubmit();
    }));
    const toast = document.getElementById('installationToast');
    if(toast) toast.addEventListener('animationend', event => { if(event.animationName === 'installation-toast-fade') toast.remove(); });
})();
</script>
@endsection
