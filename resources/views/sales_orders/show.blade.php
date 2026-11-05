@extends('layouts.app')

@section('title', 'Sales Order Details')

@section('content')
@php
    $customer = $order->quotation?->opportunity?->customer;
    $formatMoney = fn ($value) => 'Rp ' . number_format((float)$value, 0, ',', '.');
@endphp
<div class="page-head so-detail-head">
    <div>
        <h1>Sales Order Details</h1>
        <p>Review order information, items, totals, and source quotation.</p>
    </div>
    <div class="actions so-header-actions">
        <a href="{{ route('sales_orders.index') }}" class="btn">Back</a>
        @if($order->quotation)
            <a href="{{ route('quotations.show', $order->quotation) }}" class="btn">View Quotation</a>
        @endif
        <a href="{{ route('sales-orders.download', $order) }}" class="btn primary">Download PDF</a>
    </div>
</div>
@if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert error">{{ session('error') }}</div>@endif

<div class="so-detail-layout">
    <div class="so-detail-main">
        <div class="card so-card">
            <div class="card-head">
                <div><h3>Sales Order Information</h3><p>General transaction details</p></div>
                <span class="so-status so-status-{{ strtolower((string)($order->status ?? 'draft')) }}">{{ ucfirst(str_replace('_', ' ', $order->status ?? 'draft')) }}</span>
            </div>
            <div class="so-info-grid">
                <div class="so-field"><span>Sales Order Number</span><strong>{{ $order->sales_order_number }}</strong></div>
                <div class="so-field"><span>Order Date</span><strong>{{ $order->order_date?->format('d M Y') ?? '-' }}</strong></div>
                <div class="so-field"><span>Original Quotation</span><strong>@if($order->quotation)<a href="{{ route('quotations.show', $order->quotation) }}" class="so-text-link">{{ $order->quotation->quotation_number }} ↗</a>@else - @endif</strong></div>
                <div class="so-field"><span>Expected Delivery</span><strong>{{ $order->delivery_date?->format('d M Y') ?? 'Not scheduled' }}</strong></div>
                <div class="so-field"><span>Customer</span><strong>{{ $customer?->customer_name ?? '-' }}</strong></div>
                <div class="so-field"><span>Contract</span><strong>{{ $order->contract?->contract_number ?? 'Not linked' }}</strong></div>
                <div class="so-field"><span>Created At</span><strong>{{ $order->created_at?->format('d M Y, H:i') ?? '-' }}</strong></div>
                <div class="so-field"><span>Updated At</span><strong>{{ $order->updated_at?->format('d M Y, H:i') ?? '-' }}</strong></div>
                <div class="so-field so-field-full"><span>Notes</span><strong>{{ $order->notes ?: '-' }}</strong></div>
            </div>
        </div>
        <div class="card so-card">
            <div class="card-head"><div><h3>Order Items</h3><p>{{ $order->items->count() }} product lines in this sales order</p></div></div>
            <div class="so-table-scroll">
                <table class="so-table so-items-table">
                    <thead><tr><th>No.</th><th>Product</th><th>Qty</th><th class="so-right">Unit Price</th><th class="so-right">Discount</th><th class="so-right">Subtotal</th></tr></thead>
                    <tbody>
                    @forelse($order->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $item->product?->product_name ?? 'Unknown Product' }}</strong><span class="so-muted so-block">{{ $item->product?->product_code ?? '-' }}</span></td>
                            <td>{{ $item->quantity }}</td>
                            <td class="so-right">{{ $formatMoney($item->unit_price) }}</td>
                            <td class="so-right">{{ $formatMoney($item->discount ?? 0) }}</td>
                            <td class="so-right so-number">{{ $formatMoney($item->subtotal) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="so-empty">No products attached to this Sales Order.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <aside class="so-detail-side">
        <div class="card so-card so-preview-card">
            <div class="so-preview-heading">
                <div><h3>Sales Order Preview</h3><p>Transaction summary</p></div>
                <span class="so-status so-status-{{ strtolower((string)($order->status ?? 'draft')) }}">{{ ucfirst($order->status ?? 'draft') }}</span>
            </div>
            <div class="so-preview-title">{{ $order->sales_order_number }}</div>
            <div class="so-preview-meta">
                <div><span>Customer</span><strong>{{ $customer?->customer_name ?? '-' }}</strong></div>
                <div><span>Quotation</span><strong>{{ $order->quotation?->quotation_number ?? '-' }}</strong></div>
                <div><span>Order Date</span><strong>{{ $order->order_date?->format('d M Y') ?? '-' }}</strong></div>
            </div>
            <div class="so-preview-items">
                <h4>Items</h4>
                @foreach($order->items as $item)
                    <div class="so-preview-item">
                        <div><strong>{{ $item->product?->product_name ?? 'Product' }}</strong><span>{{ $item->quantity }} × {{ $formatMoney($item->unit_price) }}</span></div>
                        <strong>{{ $formatMoney($item->subtotal) }}</strong>
                    </div>
                @endforeach
            </div>
            <div class="so-totals">
                <div><span>Subtotal</span><strong>{{ $formatMoney($order->subtotal) }}</strong></div>
                @php($effectiveDiscount = max(0, (float) $order->subtotal + (float) $order->tax - (float) $order->total_amount))
                @if($effectiveDiscount > 0.009)
                    <div><span>Quotation Discount*</span><strong>- {{ $formatMoney($effectiveDiscount) }}</strong></div>
                @endif
                <div><span>Tax</span><strong>+ {{ $formatMoney($order->tax) }}</strong></div>
                <div class="so-grand-total"><span>Total Amount</span><strong>{{ $formatMoney($order->total_amount) }}</strong></div>
            </div>
            @if($effectiveDiscount > 0.009)
                <p class="so-summary-note">*Discount inferred from stored subtotal, tax, and total because Sales Orders have no header discount field.</p>
            @endif
            <a href="{{ route('sales-orders.download', $order) }}" class="btn primary so-download-wide">Download PDF</a>
            @if($order->quotation)
                <a href="{{ route('quotations.show', $order->quotation) }}" class="btn so-download-wide so-secondary-link">View Original Quotation</a>
            @endif
        </div>
    </aside>
</div>
@endsection

@push('styles')
    @include('sales_orders._style')
@endpush
