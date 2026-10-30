@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Warranty Details</h1>
        <p>View warranty information and coverage details.</p>
    </div>

    <div class="actions">
        <a href="{{ route('warranties.index') }}" class="btn">
            Back to Warranties
        </a>

        <a
            href="{{ route('warranties.edit', $warranty->warranty_id) }}"
            class="btn primary"
        >
            Edit Warranty
        </a>
    </div>
</div>

@if (session('success'))
    <div class="alert success">
        {{ session('success') }}
    </div>
@endif

@php
    $status = strtolower(trim($warranty->status));

    $isExpired = \Carbon\Carbon::parse($warranty->end_date)
        ->lt(now()->startOfDay());

    $badgeClass = match ($status) {
        'active' => $isExpired ? 'danger' : 'success',
        'expired' => 'danger',
        'claimed' => 'warning',
        'void' => 'secondary',
        default => 'secondary',
    };
@endphp

<div class="detail-grid">

    {{-- Warranty Information --}}
    <div class="card">
        <div class="card-head">
            <h2>Warranty Information</h2>
        </div>

        <div class="card-body">
            <div class="detail-item">
                <span class="detail-label">Warranty Number</span>
                <strong>{{ $warranty->warranty_number }}</strong>
            </div>

            <div class="detail-item">
                <span class="detail-label">Warranty Type</span>
                <span>{{ $warranty->warranty_type ?: '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Status</span>

                <span class="badge {{ $badgeClass }}">
                    {{ $warranty->status }}

                    @if ($status === 'active' && $isExpired)
                        (Date Passed)
                    @endif
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Start Date</span>
                <span>
                    {{ \Carbon\Carbon::parse($warranty->start_date)->format('d M Y') }}
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">End Date</span>
                <span>
                    {{ \Carbon\Carbon::parse($warranty->end_date)->format('d M Y') }}
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Coverage Period</span>
                <span>
                    {{ \Carbon\Carbon::parse($warranty->start_date)->diffInMonths(
                        \Carbon\Carbon::parse($warranty->end_date)
                    ) }} months
                </span>
            </div>
        </div>
    </div>

    {{-- Product Information --}}
    <div class="card">
        <div class="card-head">
            <h2>Product Information</h2>
        </div>

        <div class="card-body">
            <div class="detail-item">
                <span class="detail-label">Serial Number</span>
                <strong>{{ $warranty->serial_number }}</strong>
            </div>

            <div class="detail-item">
                <span class="detail-label">Product Name</span>
                <span>{{ $warranty->product_name ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- Customer Information --}}
    <div class="card">
        <div class="card-head">
            <h2>Customer Information</h2>
        </div>

        <div class="card-body">
            <div class="detail-item">
                <span class="detail-label">Customer Name</span>
                <strong>{{ $warranty->customer_name }}</strong>
            </div>

            <div class="detail-item">
                <span class="detail-label">Customer ID</span>
                <span>{{ $warranty->customer_id }}</span>
            </div>
        </div>
    </div>

    {{-- Warranty Terms --}}
    <div class="card">
        <div class="card-head">
            <h2>Warranty Terms and Conditions</h2>
        </div>

        <div class="card-body">
            @if ($warranty->terms)
                <div class="terms-content">{{ $warranty->terms }}</div>
            @else
                <p class="empty-state">
                    No warranty terms or conditions have been provided.
                </p>
            @endif
        </div>
    </div>

</div>

<div class="bottom-actions">
    <a
        href="{{ route('warranties.edit', $warranty->warranty_id) }}"
        class="btn primary"
    >
        Edit Warranty
    </a>

    <form
        action="{{ route('warranties.destroy', $warranty->warranty_id) }}"
        method="POST"
        onsubmit="return confirm('Are you sure you want to delete this warranty?')"
    >
        @csrf
        @method('DELETE')

        <button type="submit" class="btn danger">
            Delete Warranty
        </button>
    </form>
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

    .actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
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

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 7px;
        padding: 13px 0;
        border-bottom: 1px solid #f3f4f6;
        overflow-wrap: anywhere;
    }

    .detail-item:first-child {
        padding-top: 0;
    }

    .detail-item:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    .detail-label {
        color: #6b7280;
        font-size: 13px;
    }

    .detail-item strong,
    .detail-item > span:not(.detail-label) {
        color: #374151;
        font-size: 14px;
    }

    .badge {
        display: inline-block;
        width: fit-content;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge.success {
        background: #dcfce7;
        color: #166534;
    }

    .badge.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge.secondary {
        background: #e5e7eb;
        color: #374151;
    }

    .terms-content {
        color: #374151;
        font-size: 14px;
        line-height: 1.7;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .empty-state {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .bottom-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        margin-top: 24px;
    }

    .bottom-actions form {
        margin: 0;
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
        background: #15966f;
        border-color: #15966f;
        color: #fff;
    }

    .btn.danger {
        background: #dc2626;
        border-color: #dc2626;
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

    @media (max-width: 768px) {
        .page-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }

        .bottom-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .bottom-actions .btn {
            width: 100%;
            box-sizing: border-box;
        }
    }
</style>
@endsection