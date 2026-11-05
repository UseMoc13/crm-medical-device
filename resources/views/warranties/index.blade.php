@extends('layouts.app')

@section('title', 'Warranties')
@section('content')
<div class="page-head">
    <div>
        <h1>Warranties</h1>
        <p>Manage medical device warranties and coverage periods.</p>
    </div>

    <div class="actions">
        <a href="{{ route('warranties.create') }}" class="btn primary">
            + Create Warranty
        </a>
    </div>
</div>

@include('warranties._toast')

<div class="card">
    <div class="card-head">
        <div><h3>Warranty List</h3><p>Manage coverage and warranty records.</p></div>
    </div>

    <div class="card-body">
        <form method="GET"
              action="{{ route('warranties.index') }}"
              class="warranty-filter-bar">

            <div class="warranty-filter-field">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search warranty, serial number, customer..."
                >
            </div>

            <div class="warranty-filter-field">
                <select name="status">
                    <option value="">All Statuses</option>

                    @foreach ($statuses as $status)
                        <option
                            value="{{ $status }}"
                            @selected(request('status') === $status)
                        >
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="warranty-filter-field">
                <select name="customer_id">
                    <option value="">All Customers</option>

                    @foreach ($customers as $customer)
                        <option
                            value="{{ $customer->customer_id }}"
                            @selected(request('customer_id') === $customer->customer_id)
                        >
                            {{ $customer->customer_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn">
                Filter
            </button>

            <a href="{{ route('warranties.index') }}" class="btn">
                Reset
            </a>
        </form>

        <div class="table-wrap warranty-table-wrap">
            <table class="warranty-table">
                <thead>
                    <tr>
                        <th class="col-number">No.</th>
                        <th>Warranty Number</th>
                        <th>Serial Number</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($warranties as $warranty)
                        @php
                            $status = strtolower(trim($warranty->status));

                            $isExpired = \Carbon\Carbon::parse(
                                $warranty->end_date
                            )->lt(now()->startOfDay());

                            $badgeClass = match ($status) {
                                'active' => $isExpired ? 'danger' : 'success',
                                'expired' => 'danger',
                                'claimed' => 'warning',
                                'void' => 'secondary',
                                default => 'secondary',
                            };
                        @endphp

                        <tr>
                            <td>
                                {{ $warranties->firstItem() + $loop->index }}
                            </td>

                            <td>
                                {{ $warranty->warranty_number }}
                            </td>

                            <td>
                                {{ $warranty->serial_number }}
                            </td>

                            <td>
                                {{ $warranty->product_name ?? '—' }}
                            </td>

                            <td>
                                {{ $warranty->customer_name }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($warranty->start_date)->format('d M Y') }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($warranty->end_date)->format('d M Y') }}
                            </td>

                            <td>
                                <span class="warranty-status-badge warranty-status-{{ $badgeClass }}">
                                    {{ $warranty->status }}

                                    @if ($status === 'active' && $isExpired)
                                        (Date Passed)
                                    @endif
                                </span>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a
                                        href="{{ route('warranties.show', $warranty->warranty_id) }}"
                                        class="action-btn view"
                                        title="View"
                                        aria-label="View warranty"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    <a
                                        href="{{ route('warranties.edit', $warranty->warranty_id) }}"
                                        class="action-btn edit"
                                        title="Edit"
                                        aria-label="Edit warranty"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>
                                    </a>

                                    <form
                                        action="{{ route('warranties.destroy', $warranty->warranty_id) }}"
                                        method="POST"
                                        class="warranty-delete-form" data-number="{{ $warranty->warranty_number }}"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-btn delete"
                                            title="Delete"
                                            aria-label="Delete warranty"
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
                            <td colspan="9" class="empty-state">
                                No warranty records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div>
                Showing {{ $warranties->firstItem() ?? 0 }}
                to {{ $warranties->lastItem() ?? 0 }}
                of {{ $warranties->total() }} warranties
            </div>

            <div>
                {{ $warranties->links() }}
            </div>
        </div>
    </div>
</div>

@include('warranties._confirm')
@include('warranties._style')

@endsection
