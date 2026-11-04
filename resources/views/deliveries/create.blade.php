@extends('layouts.app')

@section('title', 'Create Delivery')

@section('content')

{{-- PAGE HEADER --}}
<div class="page-head">
    <div>
        <h1>Create Delivery</h1>
        <p>Register a new product delivery for a sales order.</p>
    </div>

    <div class="actions">
        <a href="{{ route('deliveries.index') }}" class="btn">
            ← Back to Deliveries
        </a>
    </div>
</div>

{{-- VALIDATION ERRORS --}}
@if ($errors->any())
    <div class="alert danger">
        <strong>Please correct the following errors:</strong>

        <ul class="validation-list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $previewNumber = $deliveryNumber;
    $previewOrder = '—';
    $previewStatus = 'Pending';
@endphp

<div class="delivery-editor-layout">
{{-- CREATE DELIVERY FORM --}}
<div class="card">
    <div class="card-head">
        <div>
            <h3>Delivery Information</h3>
            <p>Enter the delivery and shipment details below.</p>
        </div>

        <span class="required-note">
            <span class="required-mark">*</span>
            Required fields
        </span>
    </div>

    <div class="card-body">

        <form action="{{ route('deliveries.store') }}" method="POST">
            @csrf

            {{-- SECTION 1: GENERAL INFORMATION --}}
            <div class="form-section">
                <div class="section-heading">
                    <h4>General Information</h4>
                    <p>Delivery identification and associated sales order.</p>
                </div>

                <div class="delivery-form-grid">

                    {{-- DELIVERY NUMBER --}}
                    <div class="form-group">
                        <label for="delivery_number">
                            Delivery Number
                        </label>

                        <div class="auto-number-field">
                            <input
                                type="text"
                                id="delivery_number"
                                class="form-control"
                                value="{{ $deliveryNumber }}"
                                readonly
                            >

                            <span class="auto-number-badge">
                                Auto
                            </span>
                        </div>

                        <small class="form-hint">
                            The delivery number is generated automatically
                            by the system.
                        </small>
                    </div>

                    {{-- SALES ORDER --}}
                    <div class="form-group">
                        <label for="sales_order_id">
                            Sales Order
                            <span class="required-mark">*</span>
                        </label>

                        <select
                            name="sales_order_id"
                            id="sales_order_id"
                            class="form-control @error('sales_order_id') is-invalid @enderror"
                            required
                        >
                            <option value="">-- Select Sales Order --</option>

                            @foreach ($salesOrders as $salesOrder)
                                <option
                                    value="{{ $salesOrder->sales_order_id }}"
                                    @selected(
                                        old('sales_order_id', request('sales_order_id')) == $salesOrder->sales_order_id
                                    )
                                >
                                    {{ $salesOrder->sales_order_number
                                        ?? $salesOrder->sales_order_id }}
                                </option>
                            @endforeach
                        </select>

                        @error('sales_order_id')
                            <small class="field-error">{{ $message }}</small>
                        @enderror

                        @if ($salesOrders->isEmpty())
                            <small class="field-error">
                                No Sales Orders are currently available.
                                Please create a Sales Order first.
                            </small>
                        @endif
                    </div>

                </div>
            </div>

            {{-- SECTION 2: DELIVERY DETAILS --}}
            <div class="form-section">
                <div class="section-heading">
                    <h4>Delivery Details</h4>
                    <p>Specify the delivery date and current shipment status.</p>
                </div>

                <div class="delivery-form-grid">

                    {{-- DELIVERY DATE --}}
                    <div class="form-group">
                        <label for="delivery_date">
                            Delivery Date
                        </label>

                        <input
                            type="date"
                            name="delivery_date"
                            id="delivery_date"
                            class="form-control @error('delivery_date') is-invalid @enderror"
                            value="{{ old('delivery_date') }}"
                        >

                        @error('delivery_date')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- STATUS --}}
                    <div class="form-group">
                        <label for="status">
                            Status
                            <span class="required-mark">*</span>
                        </label>

                        <input type="text" id="status" class="form-control" value="Pending" readonly>
                        <small class="form-hint">New deliveries start as Pending. Update status from the detail page.</small>

                        @error('status')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- SECTION 3: DELIVERY ADDRESS --}}
            <div class="form-section">
                <div class="section-heading">
                    <h4>Delivery Address</h4>
                    <p>Provide the destination address for this shipment.</p>
                </div>

                <div class="delivery-form-grid">

                    <div class="form-group full-width">
                        <label for="delivery_address">
                            Delivery Address
                        </label>

                        <textarea
                            name="delivery_address"
                            id="delivery_address"
                            rows="4"
                            class="form-control @error('delivery_address') is-invalid @enderror"
                            placeholder="Enter the delivery destination address"
                        >{{ old('delivery_address') }}</textarea>

                        @include('deliveries._map')

                        @error('delivery_address')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- NOTES --}}
                    <div class="form-group full-width">
                        <label for="notes">
                            Notes
                        </label>

                        <textarea
                            name="notes"
                            id="notes"
                            rows="3"
                            class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Add additional delivery instructions or notes"
                        >{{ old('notes') }}</textarea>

                        @error('notes')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- FORM ACTIONS --}}
            <div class="form-actions">
                <a
                    href="{{ route('deliveries.index') }}"
                    class="btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn primary"
                    @disabled($salesOrders->isEmpty())
                >
                    Save Delivery
                </button>
            </div>

        </form>
    </div>
</div>

{{-- PAGE STYLES --}}
@include('deliveries._preview')
</div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzxwpeoID7pFtHNj6ncewuQmk=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@include('deliveries._interaction')
@include('deliveries._style')

@endsection