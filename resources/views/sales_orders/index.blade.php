@extends('layouts.app')

@section('title', 'Sales Orders')

@section('content')
<div class="page-head">
    <div>
        <h1>Sales Orders</h1>
        <p>Monitor confirmed customer orders and access their source quotations.</p>
    </div>
    <div class="actions so-header-actions">
        <button type="button" class="btn primary" id="openSoExport">Download / Export</button>
    </div>
</div>

@if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert error">{{ session('error') }}</div>@endif

<div class="card so-card">
    <div class="card-head">
        <div>
            <h3>Sales Order List</h3>
            <p>{{ $orders->total() }} sales orders found</p>
        </div>
    </div>
    <div class="so-filter-wrap">
        <form action="{{ route('sales_orders.index') }}" method="GET" class="so-filter">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search sales order or quotation..." aria-label="Search sales orders">
            <select name="status" aria-label="Filter order status">
                <option value="">All Statuses</option>
                @foreach(['draft', 'confirmed', 'processing', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn primary">Search</button>
            <a class="btn" href="{{ route('sales_orders.index') }}">Reset</a>
        </form>
    </div>
    <div class="so-table-scroll">
        <table class="so-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Sales Order</th>
                    <th>Origin Quotation</th>
                    <th>Customer</th>
                    <th>Order Date</th>
                    <th class="so-right">Total Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $orders->firstItem() + $loop->index }}</td>
                        <td><a class="so-strong-link" href="{{ route('sales-orders.show', $order) }}">{{ $order->sales_order_number }}</a></td>
                        <td>
                            @if($order->quotation)
                                <a class="so-text-link" href="{{ route('quotations.show', $order->quotation) }}" title="Open original quotation">{{ $order->quotation->quotation_number }} <span aria-hidden="true">↗</span></a>
                            @else
                                <span class="so-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $order->quotation?->opportunity?->customer?->customer_name ?? '-' }}</td>
                        <td>{{ $order->order_date?->format('d M Y') ?? '-' }}</td>
                        <td class="so-right so-number">Rp {{ number_format((float)$order->total_amount, 0, ',', '.') }}</td>
                        <td><span class="so-status so-status-{{ strtolower((string)($order->status ?? 'draft')) }}">{{ ucfirst(str_replace('_', ' ', $order->status ?? 'draft')) }}</span></td>
                        <td>
                            <div class="so-actions">
                                <a class="so-action" href="{{ route('sales-orders.show', $order) }}" title="View sales order">View</a>
                                <a class="so-action" href="{{ route('sales-orders.download', $order) }}" title="Download PDF sales order">Download</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="so-empty">No Sales Orders Found. Try adjusting your filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="so-table-footer">
        <span>Showing {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} sales orders</span>
        @if($orders->hasPages())<div>{{ $orders->withQueryString()->links() }}</div>@endif
    </div>
</div>

<div id="soExportModal" class="so-export-backdrop" hidden role="dialog" aria-modal="true" aria-labelledby="soExportTitle">
    <div class="so-export-dialog">
        <div class="so-export-heading">
            <div><h3 id="soExportTitle">Export Sales Orders</h3><p>Download the current search and status-filtered results.</p></div>
            <button type="button" class="so-export-close" id="closeSoExport" aria-label="Close">&times;</button>
        </div>
        <form action="{{ route('sales-orders.export') }}" method="GET" id="soExportForm">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <label class="so-export-option"><input type="radio" name="format" value="pdf" checked><span><strong>PDF Report</strong><small>Formatted document with summary, tables, totals, and pagination. Maximum 2,000 records.</small></span></label>
            <label class="so-export-option"><input type="radio" name="format" value="csv"><span><strong>CSV Data (Excel compatible)</strong><small>Complete matching records with raw numbers for spreadsheet analysis.</small></span></label>
            <div class="so-export-actions"><button type="button" class="btn" id="cancelSoExport">Cancel</button><button type="submit" class="btn primary">Download File</button></div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('soExportModal');
    const open = document.getElementById('openSoExport');
    if (!modal || !open) return;
    const close = () => { modal.hidden = true; document.body.style.overflow = ''; };
    open.addEventListener('click', () => { modal.hidden = false; document.body.style.overflow = 'hidden'; });
    ['closeSoExport', 'cancelSoExport'].forEach(id => document.getElementById(id)?.addEventListener('click', close));
    modal.addEventListener('click', event => { if (event.target === modal) close(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !modal.hidden) close(); });
    document.getElementById('soExportForm')?.addEventListener('submit', () => { close(); });
});
</script>
@endsection

@push('styles')
    @include('sales_orders._style')
@endpush
