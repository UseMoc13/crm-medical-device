@extends('layouts.app')

@section('content')
@php
    $status = strtolower(
        str_replace(' ', '_', $installation->status)
    );

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

        <a
            href="{{ route('installations.edit', $installation->installation_id) }}"
            class="btn primary"
        >
            Edit Installation
        </a>
    </div>
</div>

@if (session('success'))
    <div class="alert success">
        {{ session('success') }}
    </div>
@endif

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
                    {{ $installation->delivery?->delivery_number ?? '—' }}
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

/**Css
<style>
    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .page-head h1 {
        margin: 0 0 6px;
        font-size: 26px;
    }

    .page-head p {
        margin: 0;
        color: #6b7280;
    }

    .actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card {
        overflow: hidden;
        margin-bottom: 22px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
    }

    .card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 18px 22px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-head h2 {
        margin: 0;
        font-size: 18px;
    }

    .card-body {
        padding: 22px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .detail-item.full-width {
        grid-column: 1 / -1;
    }

    .detail-label {
        color: #6b7280;
        font-size: 13px;
    }

    .detail-value {
        color: #111827;
        font-size: 14px;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }

    .badge {
        display: inline-block;
        width: fit-content;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge.info {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge.success {
        background: #dcfce7;
        color: #166534;
    }

    .badge.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge.secondary {
        background: #e5e7eb;
        color: #374151;
    }

    .checklist-count {
        color: #6b7280;
        font-size: 13px;
    }

    .table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .checklist-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .checklist-table th,
    .checklist-table td {
        padding: 13px 14px;
        border: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: middle;
    }

    .checklist-table th {
        background: #f9fafb;
        color: #374151;
        font-weight: 600;
        white-space: nowrap;
    }

    .col-number {
        width: 60px;
        text-align: center !important;
    }

    .empty-state {
        padding: 30px;
        text-align: center;
        color: #6b7280;
    }

    .empty-state h3 {
        margin: 0 0 8px;
        color: #374151;
    }

    .empty-state p {
        margin: 0;
    }

    .btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 10px 16px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #374151;
        text-decoration: none;
        cursor: pointer;
        font-size: 14px;
    }

    .btn.primary {
        background: #15966f;
        border-color: #15966f;
        color: #fff;
    }

    .alert {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 8px;
    }

    .alert.success {
        background: #dcfce7;
        color: #166534;
    }

    @media (max-width: 700px) {
        .page-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .actions {
            flex-wrap: wrap;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }

        .detail-item.full-width {
            grid-column: auto;
        }

        .card-body {
            padding: 16px;
        }
    }

    .detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    border-top: 1px solid #e5e7eb;
    border-left: 1px solid #e5e7eb;
}

.detail-item {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
    min-width: 0;
    padding: 18px 20px;
    border-right: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    box-sizing: border-box;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-label {
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
}

.detail-value {
    color: #111827;
    font-size: 14px;
    line-height: 1.6;
    overflow-wrap: anywhere;
    white-space: pre-line;
}

@media (max-width: 700px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }

    .detail-item.full-width {
        grid-column: auto;
    }

    .detail-item {
        padding: 15px 16px;
    }
}
</style>
@endsection