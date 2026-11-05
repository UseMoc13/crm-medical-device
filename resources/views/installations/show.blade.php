@extends('layouts.app')

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

@if(session('success') || session('error') || $errors->any())
    <div class="installation-toast {{ session('success') ? 'success' : 'danger' }}" role="alert" id="installationToast">
        {{ session('success') ?? session('error') ?? $errors->first() }}
    </div>
@endif

<div class="card installation-progress-card">
    <div class="card-head"><h2>Installation Progress</h2><span class="badge {{ $badgeClass }}">{{ $installation->status }}</span></div>
    <div class="card-body">
        <div class="installation-progress-heading"><strong>{{ $checklistDone }} / {{ $checklistTotal }} Checklist Completed</strong><strong>{{ $checklistPercent }}%</strong></div>
        <div class="installation-progress-track" role="progressbar" aria-valuenow="{{ $checklistPercent }}" aria-valuemin="0" aria-valuemax="100"><div style="width: {{ $checklistPercent }}%"></div></div>
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
        <h2>General Information</h2>

        <span class="badge {{ $badgeClass }}">
            {{ str_replace('_', ' ', $installation->status) }}
        </span>
    </div>

    <div class="card-body">
        <div class="detail-grid">

            <div class="detail-item">
                <span class="detail-label">Installation ID</span>
                <span class="detail-value">
                    {{ $installation->installation_id }}
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Delivery Number</span>
                <span class="detail-value">
                    @if($installation->delivery)
                        <a href="{{ route('deliveries.show', $installation->delivery_id) }}">{{ $installation->delivery->delivery_number }}</a>
                    @else
                        —
                    @endif
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Installation Date</span>
                <span class="detail-value">
                    {{ $installation->installation_date?->format('d M Y') ?? 'Not scheduled' }}
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Assigned User</span>
                <span class="detail-value">
                    {{ $installation->user?->name
                        ?? $installation->user?->full_name
                        ?? $installation->user?->username
                        ?? $installation->user?->email
                        ?? '—' }}
                </span>
            </div>

            <div class="detail-item full-width">
                <span class="detail-label">Installation Location</span>
                <span class="detail-value">
                    {{ $installation->location ?: 'No location provided.' }}
                </span>
            </div>

            <div class="detail-item full-width">
                <span class="detail-label">Notes</span>
                <span class="detail-value">
                    {{ $installation->notes ?: 'No additional notes.' }}
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Created At</span>
                <span class="detail-value">
                    {{ $installation->created_at?->format('d M Y H:i') ?? '—' }}
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Last Updated</span>
                <span class="detail-value">
                    {{ $installation->updated_at?->format('d M Y H:i') ?? '—' }}
                </span>
            </div>

        </div>
    </div>
</div>

<div class="card checklist-card">
    <div class="card-head">
        <h2>Installation Checklist</h2>
        <span class="checklist-count">
            {{ $installation->checklists->where('is_completed', true)->count() }}
            / {{ $installation->checklists->count() }} completed
        </span>
    </div>

    <div class="card-body">
        @if ($installation->checklists->isNotEmpty())
            <div class="table-wrap">
                <table class="checklist-table">
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

                                <td>{{ $checklist->notes ?: '—' }}</td>

                                <td>
                                    @if ($checklist->is_completed)
                                        <span class="badge success">
                                            Completed
                                        </span>
                                    @else
                                        <span class="badge warning">
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $checklist->completed_at?->format('d M Y H:i') ?? '—' }}
                                    @if($installation->status === 'In Progress')
                                        <form method="POST" action="{{ route('installations.checklists.update', [$installation, $checklist]) }}" class="installation-checklist-action">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_completed" value="{{ $checklist->is_completed ? '0' : '1' }}">
                                            <button type="submit" class="btn">{{ $checklist->is_completed ? 'Undo' : 'Mark Done' }}</button>
                                        </form>
                                    @endif
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
    <div class="card-head"><h2>Devices from Delivery</h2></div>
    <div class="card-body">
        @if($deliveryItems->isEmpty())
            <p>No serialized devices found in the originating Delivery.</p>
        @else
            <div class="table-wrap">
                <table class="checklist-table">
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
    <p class="form-hint">Complete all {{ $installation->checklists->count() }} checklist items, then change status to Completed using Edit Installation.</p>
@endif

<div class="installation-modal-backdrop" id="installationStatusModal" hidden>
    <div class="installation-modal" role="dialog" aria-modal="true" aria-labelledby="installationModalTitle">
        <h3 id="installationModalTitle">Confirm Status Change</h3>
        <p id="installationModalMessage"></p>
        <form method="POST" action="{{ route('installations.change-status', $installation) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" id="installationNextStatus">
            <div class="installation-modal-actions">
                <button type="button" class="btn" id="installationModalCancel">Back</button>
                <button type="submit" class="btn primary">Confirm Change</button>
            </div>
        </form>
    </div>
</div>
@include('installations._style', ['page' => 'show'])
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
    const toast = document.getElementById('installationToast');
    if(toast) toast.addEventListener('animationend', event => { if(event.animationName === 'installation-toast-fade') toast.remove(); });
})();
</script>
@endsection