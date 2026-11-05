@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')

@php
    $rp = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

    $invoiceDate = \Illuminate\Support\Carbon::parse($invoice->invoice_date);
    $due = $invoice->due_date ? \Illuminate\Support\Carbon::parse($invoice->due_date) : null;

    $statusKey = $isOverdue ? 'overdue' : \Illuminate\Support\Str::slug($invoice->status);

    $addressParts = array_filter([
        $customer->address ?? null,
        $customer->city ?? null,
        $customer->province ?? null,
    ]);
@endphp

<div class="page-head">
    <div>
        <h1>Invoice Details</h1>
        <p>{{ $invoice->invoice_number }} — view, print, and manage payment status.</p>
    </div>

    <div class="actions">
        <a href="{{ route('invoices.index') }}" class="btn">&larr; Back to Invoices</a>

        <button type="button" class="btn" onclick="window.print()">Print</button>

        @if($canEdit)
            <a href="{{ route('invoices.edit', $invoice->invoice_id) }}" class="btn primary">
                Edit Invoice
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert error">{{ session('error') }}</div>
@endif


{{-- STATUS ACTIONS --}}
<div class="card no-print" style="margin-bottom:18px">
    <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">

        <div style="display:flex;align-items:center;gap:12px">
            <span class="inv-party-label" style="margin:0">Status</span>
            <span class="inv-badge inv-status-{{ $statusKey }}">
                {{ $isOverdue ? 'Overdue' : $invoice->status }}
            </span>

            @if($isOverdue)
                <span class="inv-sub" style="margin:0">
                    {{ $due->diffInDays(today()) }} day(s) past due
                </span>
            @endif
        </div>

        <div class="inv-status-actions">

            @foreach($transitions as $target)
                <form
                    method="POST"
                    action="{{ route('invoices.status', $invoice->invoice_id) }}"
                    @if($target === 'Cancelled')
                        onsubmit="return confirm('Cancel this invoice?')"
                    @elseif($target === 'Paid')
                        onsubmit="return confirm('Mark this invoice as paid?')"
                    @endif
                >
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $target }}">

                    <button
                        type="submit"
                        class="inv-btn-sm {{ $target === 'Paid' ? 'success' : ($target === 'Cancelled' ? 'danger' : '') }}"
                    >
                        @switch($target)
                            @case('Sent') Mark as Sent @break
                            @case('Paid') Mark as Paid @break
                            @case('Cancelled') Cancel Invoice @break
                            @case('Draft') Reopen as Draft @break
                            @default {{ $target }}
                        @endswitch
                    </button>
                </form>
            @endforeach

            @if($canDelete)
                <form
                    method="POST"
                    action="{{ route('invoices.destroy', $invoice->invoice_id) }}"
                    onsubmit="return confirm('Delete this invoice and all of its items?')"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inv-btn-sm danger">Delete</button>
                </form>
            @endif

            @if(!count($transitions) && !$canDelete)
                <span class="inv-sub" style="margin:0">No further actions available.</span>
            @endif

        </div>
    </div>
</div>


{{-- INVOICE DOCUMENT --}}
<div class="card">
    <div class="inv-doc">

        <div class="inv-doc-top">
            <div>
                <h2 class="inv-doc-title">INVOICE</h2>
                <div class="inv-doc-number">{{ $invoice->invoice_number }}</div>
            </div>

            <div class="inv-doc-meta">
                <div>
                    <span>Invoice Date</span>
                    <strong>{{ $invoiceDate->format('d M Y') }}</strong>
                </div>

                <div>
                    <span>Due Date</span>
                    <strong>{{ $due?->format('d M Y') ?? '-' }}</strong>
                </div>

                <div>
                    <span>Sales Order</span>
                    <strong>{{ $invoice->sales_order_number ?? '-' }}</strong>
                </div>

                <div>
                    <span>Status</span>
                    <strong>{{ $isOverdue ? 'Overdue' : $invoice->status }}</strong>
                </div>
            </div>
        </div>

        <div class="inv-parties">
            <div class="inv-party">
                <span class="inv-party-label">Billed To</span>

                @if($customer)
                    <strong>{{ $customer->customer_name }}</strong>
                    <p>
                        @if(count($addressParts)){{ implode(', ', $addressParts) }}<br>@endif
                        @if(!empty($customer->phone)){{ $customer->phone }}<br>@endif
                        @if(!empty($customer->email)){{ $customer->email }}@endif
                    </p>
                @else
                    <strong>Unknown Customer</strong>
                    <p>No customer is linked to the sales order of this invoice.</p>
                @endif
            </div>

            <div class="inv-party">
                <span class="inv-party-label">Payment Summary</span>
                <strong>{{ $rp($invoice->total_amount) }}</strong>
                <p>
                    {{ $items->count() }} item(s)
                    @if($due)
                        · payable by {{ $due->format('d M Y') }}
                    @endif
                </p>
            </div>
        </div>

        <div class="inv-doc-table-wrap">
            <table class="inv-doc-table">
                <thead>
                    <tr>
                        <th style="width:50px">No.</th>
                        <th>Product</th>
                        <th style="width:80px">Qty</th>
                        <th class="inv-right" style="width:150px">Unit Price</th>
                        <th class="inv-right" style="width:140px">Discount</th>
                        <th class="inv-right" style="width:160px">Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $item->product_name ?? 'Product unavailable' }}</strong>
                                @if(!empty($item->product_code))
                                    <div class="inv-sub">{{ $item->product_code }}</div>
                                @endif
                            </td>
                            <td>{{ number_format((int) $item->quantity) }}{{ !empty($item->unit) ? ' ' . $item->unit : '' }}</td>
                            <td class="inv-right">{{ $rp($item->unit_price) }}</td>
                            <td class="inv-right">{{ (float) $item->discount > 0 ? '- ' . $rp($item->discount) : '-' }}</td>
                            <td class="inv-right"><strong>{{ $rp($item->subtotal) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="inv-empty">
                                    <strong>No items.</strong>
                                    <span>This invoice has no line items.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="inv-totals">
            <div class="inv-sum-row"><span>Subtotal</span><strong>{{ $rp($invoice->subtotal) }}</strong></div>
            <div class="inv-sum-row"><span>Discount</span><strong>- {{ $rp($invoice->discount ?? 0) }}</strong></div>
            <div class="inv-sum-row"><span>Tax</span><strong>{{ $rp($invoice->tax ?? 0) }}</strong></div>

            <div class="inv-sum-total">
                <span>Total</span>
                <strong>{{ $rp($invoice->total_amount) }}</strong>
            </div>
        </div>

        @if(filled($invoice->notes))
            <div class="inv-notes">
                <span class="inv-party-label">Notes</span>
                <p>{{ $invoice->notes }}</p>
            </div>
        @endif

    </div>
</div>

@include('invoices._styles')

@endsection