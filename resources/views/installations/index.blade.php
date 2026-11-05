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

    <div class="table-footer">
            <div>
                Showing {{ $installations->firstItem() ?? 0 }}
                to {{ $installations->lastItem() ?? 0 }}
                of {{ $installations->total() }} installations
            </div>

    </div>
</div>

@include('installations._style', ['page' => 'index'])
@endsection