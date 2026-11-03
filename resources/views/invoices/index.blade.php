@extends('layouts.app')

@section('title', 'Invoices')

@section('content')

@php
    $rp = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
@endphp

<div class="page-head">
    <div>
        <h1>Invoices</h1>
        <p>Issue invoices from sales orders and track payment status and overdue amounts.</p>
    </div>

    <div class="actions">
        <a href="{{ route('reports.index') }}" class="btn">View Reports</a>

        <a href="{{ route('invoices.create') }}" class="btn primary">
            + New Invoice
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert error">{{ session('error') }}</div>
@endif


{{-- SUMMARY --}}
<div class="inv-summary">

    <a href="{{ route('invoices.index') }}" class="inv-summary-card">
        <span>Total Invoiced</span>
        <strong>{{ $rp($summary->issued) }}</strong>
        <small>{{ $summary->total }} invoices in total (Sent + Paid amounts)</small>
    </a>

    <a href="{{ route('invoices.index', ['status' => 'Paid']) }}" class="inv-summary-card success">
        <span>Paid</span>
        <strong>{{ $rp($summary->paid) }}</strong>
        <small>Payments received</small>
    </a>

    <a href="{{ route('invoices.index', ['status' => 'Sent']) }}" class="inv-summary-card">
        <span>Outstanding</span>
        <strong>{{ $rp($summary->outstanding) }}</strong>
        <small>Sent, awaiting payment</small>
    </a>

    <a href="{{ route('invoices.index', ['status' => 'overdue']) }}" class="inv-summary-card danger">
        <span>Overdue</span>
        <strong>{{ $rp($summary->overdue_amount) }}</strong>
        <small>{{ $summary->overdue_count }} invoice(s) past due date</small>
    </a>

</div>


<div class="card">

    <div class="card-head">
        <div>
            <h3>Invoice List</h3>
            <p>{{ $invoices->total() }} invoices found</p>
        </div>
    </div>

    <div class="card-body">

        {{-- FILTER BAR --}}
        <form method="GET" action="{{ route('invoices.index') }}" class="inv-filter-bar">

            <div class="inv-search">
                <span class="inv-search-icon">⌕</span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search invoice number, sales order, or customer..."
                    autocomplete="off"
                >

                @if(request('search'))
                    <a
                        href="{{ route('invoices.index', request()->except('search', 'page')) }}"
                        class="inv-search-clear"
                        title="Clear search"
                        aria-label="Clear search"
                    >×</a>
                @endif
            </div>

            <div class="inv-ffield">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All Statuses</option>

                    @foreach($statuses as $statusOption)
                        <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>
                            {{ $statusOption }}
                        </option>
                    @endforeach

                    <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
                </select>
            </div>

            <div class="inv-ffield">
                <label for="date_from">From</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="inv-ffield">
                <label for="date_to">To</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>

            <div class="inv-ffield">
                <label for="sort">Sort By</label>
                <select id="sort" name="sort">
                    <option value="invoice_date" @selected($sort === 'invoice_date')>Invoice Date</option>
                    <option value="due_date" @selected($sort === 'due_date')>Due Date</option>
                    <option value="invoice_number" @selected($sort === 'invoice_number')>Invoice Number</option>
                    <option value="total_amount" @selected($sort === 'total_amount')>Amount</option>
                    <option value="status" @selected($sort === 'status')>Status</option>
                    <option value="created_at" @selected($sort === 'created_at')>Created Date</option>
                </select>
            </div>

            <div class="inv-ffield">
                <label for="direction">Order</label>
                <select id="direction" name="direction">
                    <option value="desc" @selected($direction === 'desc')>↓ Descending</option>
                    <option value="asc" @selected($direction === 'asc')>↑ Ascending</option>
                </select>
            </div>

            <button type="submit" class="btn inv-btn">Filter</button>

            @if(request()->hasAny(['search', 'status', 'date_from', 'date_to', 'sort', 'direction']))
                <a href="{{ route('invoices.index') }}" class="btn inv-btn">Reset</a>
            @endif

        </form>

        @if(request('search') || request('status') || request('date_from') || request('date_to'))
            <div class="inv-filter-summary">
                <span>Showing filtered results</span>

                @if(request('search'))
                    <span class="inv-chip">Search: "{{ request('search') }}"</span>
                @endif

                @if(request('status'))
                    <span class="inv-chip">Status: {{ ucfirst(request('status')) }}</span>
                @endif

                @if(request('date_from') || request('date_to'))
                    <span class="inv-chip">
                        Date: {{ request('date_from') ?: '…' }} → {{ request('date_to') ?: '…' }}
                    </span>
                @endif
            </div>
        @endif


        {{-- TABLE --}}
        <div class="inv-table-wrap">
            <table class="inv-table">

                <thead>
                    <tr>
                        <th class="inv-col-no">No.</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th class="inv-right">Amount</th>
                        <th>Status</th>
                        <th style="min-width:120px">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($invoices as $invoice)

                        @php
                            $due = $invoice->due_date ? \Illuminate\Support\Carbon::parse($invoice->due_date) : null;
                            $overdue = $invoice->status === 'Sent' && $due && $due->lt(today());
                            $statusKey = $overdue ? 'overdue' : \Illuminate\Support\Str::slug($invoice->status);
                        @endphp

                        <tr>

                            <td class="inv-col-no">
                                {{ $invoices->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <a href="{{ route('invoices.show', $invoice->invoice_id) }}" class="inv-link">
                                    {{ $invoice->invoice_number }}
                                </a>
                                <div class="inv-sub">{{ $invoice->sales_order_number ?? '-' }}</div>
                            </td>

                            <td>{{ $invoice->customer_name ?? '-' }}</td>

                            <td>{{ \Illuminate\Support\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>

                            <td>
                                {{ $due?->format('d M Y') ?? '-' }}

                                @if($overdue)
                                    <div>
                                        <span class="inv-overdue-tag">
                                            OVERDUE {{ $due->diffInDays(today()) }}d
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td class="inv-right"><strong>{{ $rp($invoice->total_amount) }}</strong></td>

                            <td>
                                <span class="inv-badge inv-status-{{ $statusKey }}">
                                    {{ $overdue ? 'Overdue' : $invoice->status }}
                                </span>
                            </td>

                            <td>
                                <div class="inv-actions">

                                    <a
                                        href="{{ route('invoices.show', $invoice->invoice_id) }}"
                                        class="inv-icon-btn view"
                                        title="View"
                                        aria-label="View invoice"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    @if(in_array($invoice->status, $editable, true))
                                        <a
                                            href="{{ route('invoices.edit', $invoice->invoice_id) }}"
                                            class="inv-icon-btn edit"
                                            title="Edit"
                                            aria-label="Edit invoice"
                                        >
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                            </svg>
                                        </a>
                                    @endif

                                    @if(in_array($invoice->status, $deletable, true))
                                        <form
                                            action="{{ route('invoices.destroy', $invoice->invoice_id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Delete this invoice and all of its items?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="inv-icon-btn delete"
                                                title="Delete"
                                                aria-label="Delete invoice"
                                            >
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M3 6h18"/>
                                                    <path d="M8 6V4h8v2"/>
                                                    <path d="m19 6-1 14H6L5 6"/>
                                                    <path d="M10 11v5M14 11v5"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8">
                                <div class="inv-empty">
                                    <strong>No invoices found.</strong>
                                    <span>Create a new invoice or adjust your search and filters.</span>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        @if($invoices->hasPages())
            <div class="pagination">
                {{ $invoices->links() }}
            </div>
        @endif

        <div class="inv-footer">
            <div>
                Showing {{ $invoices->firstItem() ?? 0 }}
                to {{ $invoices->lastItem() ?? 0 }}
                of {{ $invoices->total() }} invoices
            </div>
        </div>

    </div>
</div>

@include('invoices._styles')

@endsection