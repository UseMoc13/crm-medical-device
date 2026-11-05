@extends('layouts.app')

@section('title', 'Warranty Details')

@section('content')
@php
    $status = strtolower(trim((string) ($warranty->status ?? '')));
    $startDate = $warranty->start_date ? \Illuminate\Support\Carbon::parse($warranty->start_date)->startOfDay() : null;
    $endDate = $warranty->end_date ? \Illuminate\Support\Carbon::parse($warranty->end_date)->startOfDay() : null;
    $today = today();
    $datePassed = $endDate && $endDate->lt($today);
    $notYetStarted = $startDate && $startDate->gt($today);
    $displayStatus = $status === 'active' && $datePassed ? 'Expired' : ($warranty->status ?: 'Unknown');
    $statusClass = match (strtolower($displayStatus)) {
        'active' => 'success',
        'expired' => 'danger',
        'claimed' => 'warning',
        'void' => 'secondary',
        default => 'secondary',
    };
    $coverageMonths = $startDate && $endDate
        ? (int) $startDate->diffInMonths($endDate)
        : null;
    $daysRemaining = $endDate && !$datePassed ? (int) $today->diffInDays($endDate) : 0;
    $daysTotal = $startDate && $endDate && $endDate->gte($startDate)
        ? max(1, (int) $startDate->diffInDays($endDate))
        : 0;
    $daysElapsed = $startDate && $daysTotal > 0
        ? max(0, min($daysTotal, (int) $startDate->diffInDays($today, false)))
        : 0;
    $coverageProgress = $daysTotal > 0 ? (int) round(($daysElapsed / $daysTotal) * 100) : 0;
@endphp

{{-- Page Header: follows the existing Delivery Details layout --}}
<div class="page-head">
    <div>
        <h1>{{ $warranty->warranty_number }}</h1>
        <p>View warranty coverage, registered device, and customer information.</p>
    </div>
    <div class="actions">
        <a href="{{ route('warranties.edit', $warranty->warranty_id) }}" class="btn primary">✎ Edit Warranty</a>
        <a href="{{ route('warranties.index') }}" class="btn">← Back</a>
    </div>
</div>

@include('warranties._toast')

{{-- Warranty Information: full width like the Delivery information card --}}
<div class="card warranty-show-card warranty-show-primary">
    <div class="card-head">
        <div>
            <h3>Warranty Information</h3>
            <p>Coverage identification, warranty period, and current status.</p>
        </div>
        <span class="warranty-status-badge warranty-status-{{ $statusClass }}">{{ $displayStatus }}</span>
    </div>
    <div class="card-body">
        <div class="warranty-show-grid">
            <div class="warranty-show-field">
                <span class="warranty-show-label">Warranty Number</span>
                <strong class="warranty-show-value">{{ $warranty->warranty_number }}</strong>
            </div>
            <div class="warranty-show-field">
                <span class="warranty-show-label">Warranty Type</span>
                <strong class="warranty-show-value">{{ $warranty->warranty_type ?: '—' }}</strong>
            </div>
            <div class="warranty-show-field">
                <span class="warranty-show-label">Start Date</span>
                <strong class="warranty-show-value">{{ $startDate?->format('d M Y') ?? '—' }}</strong>
            </div>
            <div class="warranty-show-field">
                <span class="warranty-show-label">End Date</span>
                <strong class="warranty-show-value">{{ $endDate?->format('d M Y') ?? '—' }}</strong>
            </div>
            <div class="warranty-show-field">
                <span class="warranty-show-label">Coverage Period</span>
                <strong class="warranty-show-value">{{ $coverageMonths !== null ? $coverageMonths . ' months' : '—' }}</strong>
            </div>
            <div class="warranty-show-field">
                <span class="warranty-show-label">Warranty Status</span>
                <span class="warranty-status-badge warranty-status-{{ $statusClass }}">{{ $displayStatus }}</span>
            </div>
        </div>

        @if($status === 'active' && $startDate && $endDate && $daysTotal > 0)
            <div class="warranty-coverage-panel">
                <div class="warranty-coverage-heading">
                    <strong>Coverage Timeline</strong>
                    <span>
                        @if($datePassed)
                            Coverage period has ended
                        @elseif($notYetStarted)
                            Coverage has not started
                        @else
                            {{ $daysRemaining }} day{{ $daysRemaining === 1 ? '' : 's' }} remaining
                        @endif
                    </span>
                </div>
                <div class="warranty-coverage-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $coverageProgress }}" aria-label="Warranty coverage timeline">
                    <div class="warranty-coverage-fill" style="width: {{ $coverageProgress }}%"></div>
                </div>
                <div class="warranty-coverage-dates"><span>{{ $startDate->format('d M Y') }}</span><span>{{ $endDate->format('d M Y') }}</span></div>
            </div>
        @endif
    </div>
</div>

{{-- Related data cards share Delivery's compact information field appearance --}}
<div class="warranty-show-related">
    <div class="card warranty-show-card">
        <div class="card-head">
            <div><h3>Product Information</h3><p>Registered medical device and its serial number.</p></div>
        </div>
        <div class="card-body">
            <div class="warranty-show-grid warranty-show-related-fields">
                <div class="warranty-show-field">
                    <span class="warranty-show-label">Serial Number</span>
                    <strong class="warranty-show-value">{{ $warranty->serial_number ?? '—' }}</strong>
                </div>
                <div class="warranty-show-field">
                    <span class="warranty-show-label">Product Name</span>
                    <strong class="warranty-show-value">{{ $warranty->product_name ?? '—' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card warranty-show-card">
        <div class="card-head">
            <div><h3>Customer Information</h3><p>Warranty ownership and customer details.</p></div>
        </div>
        <div class="card-body">
            <div class="warranty-show-grid warranty-show-related-fields">
                <div class="warranty-show-field">
                    <span class="warranty-show-label">Customer Name</span>
                    <strong class="warranty-show-value">{{ $warranty->customer_name ?? '—' }}</strong>
                </div>
                <div class="warranty-show-field">
                    <span class="warranty-show-label">Customer ID</span>
                    <strong class="warranty-show-value warranty-show-id">{{ $warranty->customer_id }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card warranty-show-card warranty-show-terms-card">
    <div class="card-head">
        <div><h3>Warranty Terms and Conditions</h3><p>Coverage provisions and additional conditions.</p></div>
    </div>
    <div class="card-body">
        <p class="warranty-show-terms">{{ $warranty->terms ?: 'No warranty terms or conditions have been provided.' }}</p>
    </div>
</div>

<div class="warranty-show-bottom-actions">
    <a href="{{ route('warranties.index') }}" class="btn">Back to Warranties</a>
    <form action="{{ route('warranties.destroy', $warranty->warranty_id) }}" method="POST" class="warranty-delete-form" data-number="{{ $warranty->warranty_number }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn warranty-danger-btn">Delete Warranty</button>
    </form>
</div>

@include('warranties._confirm')
@include('warranties._style')
@endsection
