@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Installations</h1>
        <p>Manage medical device installation schedules and activities.</p>
    </div>

    <div class="actions">
        <a href="{{ route('installations.create') }}" class="btn primary">
            + Create Installation
        </a>
    </div>
</div>

@if (session('success'))
    <div class="alert success">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert danger">
        <strong>Please check the following errors:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-head">
        <h2>Installation List</h2>
    </div>

    <div class="card-body">
        <form method="GET"
              action="{{ route('installations.index') }}"
              class="filter-form">

            <div class="filter-field">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search delivery, location, or status..."
                >
            </div>

            <div class="filter-field">
                <select name="status">
                    <option value="">All Statuses</option>
                    @foreach ([
                        'Scheduled' => 'Scheduled',
                        'In Progress' => 'In Progress',
                        'Completed' => 'Completed',
                        'Cancelled' => 'Cancelled',
                    ] as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(request('status') === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn primary">
                Search
            </button>

            <a href="{{ route('installations.index') }}" class="btn">
                Reset
            </a>
        </form>

        <div class="table-wrap">
            <table class="installation-table">
                <thead>
                    <tr>
                        <th class="col-number">No.</th>
                        <th class="col-delivery">Delivery</th>
                        <th class="col-date">Installation Date</th>
                        <th class="col-location">Location</th>
                        <th class="col-user">Assigned User</th>
                        <th class="col-status">Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($installations as $installation)
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

                        <tr>
                            <td>
                                {{ $installations->firstItem() + $loop->index }}
                            </td>

                            <td>
                                {{ $installation->delivery?->delivery_number ?? '—' }}
                            </td>

                            <td>
                                {{ $installation->installation_date?->format('d M Y') ?? '—' }}
                            </td>

                            <td>
                                {{ $installation->location ?: '—' }}
                            </td>

                            <td>
                                {{ $installation->user?->name
                                    ?? $installation->user?->full_name
                                    ?? $installation->user?->username
                                    ?? $installation->user?->email
                                    ?? '—' }}
                            </td>

                            <td>
                                <span class="badge {{ $badgeClass }}">
                                    {{ str_replace('_', ' ', $installation->status) }}
                                </span>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a
                                        href="{{ route('installations.show', $installation->installation_id) }}"
                                        class="action-btn view"
                                        title="View"
                                        aria-label="View installation"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    <a
                                        href="{{ route('installations.edit', $installation->installation_id) }}"
                                        class="action-btn edit"
                                        title="Edit"
                                        aria-label="Edit installation"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>
                                    </a>

                                    <form
                                        action="{{ route('installations.destroy', $installation->installation_id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this installation?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-btn delete"
                                            title="Delete"
                                            aria-label="Delete installation"
                                        >
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M3 6h18"/>
                                                <path d="M8 6V4h8v2"/>
                                                <path d="m19 6-1 14H6L5 6"/>
                                                <path d="M10 11v5M14 11v5"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                No installation records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

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

    .actions,
    .filter-form,
    .table-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card {
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
    }

    .card-head {
        padding: 18px 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-head h2 {
        margin: 0;
        font-size: 18px;
    }

    .card-body {
        padding: 20px;
    }

    .btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #374151;
        text-decoration: none;
        cursor: pointer;
        font-size: 14px;
    }

    .btn.primary {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    .filter-form {
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .filter-field input,
    .filter-field select {
        min-height: 40px;
        padding: 9px 12px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #374151;
    }

    .filter-field input {
        width: 280px;
        max-width: 100%;
    }

    .table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .installation-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .installation-table th,
    .installation-table td {
        padding: 13px 14px;
        border: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: middle;
    }

    .installation-table th {
        background: #f9fafb;
        color: #374151;
        font-weight: 600;
        white-space: nowrap;
    }

    .installation-table tbody tr:hover {
        background: #f9fafb;
    }

    .col-number {
        width: 55px;
        text-align: center !important;
    }

    .col-date {
        min-width: 135px;
    }

    .col-location {
        min-width: 180px;
    }

    .col-status {
        min-width: 125px;
    }

    .col-actions {
        width: 130px;
        text-align: center !important;
    }

    .badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
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

    .table-actions {
        justify-content: center;
    }

    .table-actions form {
        display: inline-flex;
        margin: 0;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        padding: 7px;
        border: 1px solid #e5e7eb;
        border-radius: 7px;
        background: #fff;
        cursor: pointer;
        text-decoration: none;
    }

    .action-btn svg {
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .action-btn.view {
        color: #2563eb;
    }

    .action-btn.edit {
        color: #d97706;
    }

    .action-btn.delete {
        color: #dc2626;
    }

    .empty-state {
        padding: 32px !important;
        color: #6b7280;
        text-align: center !important;
    }

    .table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-top: 18px;
        color: #6b7280;
        font-size: 13px;
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

    .alert.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    @media (max-width: 768px) {
        .page-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .filter-form {
            align-items: stretch;
            flex-direction: column;
        }

        .filter-field input,
        .filter-field select {
            width: 100%;
        }

        .table-footer {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endsection