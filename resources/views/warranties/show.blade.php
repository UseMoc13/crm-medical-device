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

@include('warranties._style', ['page' => 'show'])

@endsection