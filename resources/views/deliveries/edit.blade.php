@extends('layouts.app')

@section('title', 'Edit Delivery')

@section('content')

{{-- PAGE HEADER --}}
<div class="page-head">
    <div>
        <h1>Edit Delivery</h1>
        <p>Update delivery information and shipment details.</p>
    </div>

    <div class="actions">
        <a href="{{ route('deliveries.index') }}" class="btn">
            Back to Deliveries
        </a>
    </div>
</div>

{{-- VALIDATION ERRORS --}}
@if ($errors->any())
    <div class="alert error">
        <strong>Please correct the following errors:</strong>

        <ul style="margin: 8px 0 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $previewNumber = $delivery->delivery_number;
    $previewOrder = ($delivery->salesOrder->sales_order_number ?? '—');
    $previewStatus = ucwords(str_replace('_', ' ', $delivery->status));
@endphp

<div class="delivery-editor-layout">
{{-- EDIT DELIVERY FORM --}}
<div class="card">
    <div class="card-head">
        <div>
            <h3>Delivery Information</h3>
            <p>Update the delivery and shipment details below.</p>
        </div>
    </div>

    <div class="card-body">
        <form
            action="{{ route('deliveries.update', $delivery->delivery_id) }}"
            method="POST"
        >
            @csrf
            @method('PUT')

            <div class="delivery-form-grid">

                {{-- DELIVERY NUMBER --}}
                <div class="form-group">
                    <label for="delivery_number">Delivery Number</label>

                    <input
                        type="text"
                        id="delivery_number"
                        value="{{ $delivery->delivery_number }}"
                        readonly
                    >

                    <small>
                        Delivery number dibuat otomatis oleh sistem
                        dan tidak dapat diubah.
                    </small>
                </div>

                {{-- SALES ORDER --}}
                <div class="form-group">
                    <label for="sales_order_id">
                        Sales Order <span class="required">*</span>
                    </label>

                    <input type="text" value="{{ $delivery->salesOrder->sales_order_number ?? $delivery->sales_order_id }}" readonly>
                    <a class="delivery-origin-link" href="{{ route('sales-orders.show', $delivery->sales_order_id) }}">View Original Sales Order</a>

                    @error('sales_order_id')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- DELIVERY DATE --}}
                <div class="form-group">
                    <label for="delivery_date">Delivery Date</label>

                    <input
                        type="date"
                        name="delivery_date"
                        id="delivery_date"
                        value="{{ old(
                            'delivery_date',
                            $delivery->delivery_date
                                ? $delivery->delivery_date->format('Y-m-d')
                                : ''
                        ) }}"
                    >

                    @error('delivery_date')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- STATUS --}}
                <div class="form-group">
                    <label for="status">
                        Status <span class="required">*</span>
                    </label>

                    <input type="text" id="status" value="{{ ucwords(str_replace('_', ' ', $delivery->status)) }}" readonly>
                    <small class="form-hint">Use the status actions on Delivery Details to update this status.</small>

                    @error('status')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- DELIVERY ADDRESS --}}
                <div class="form-group full-width">
                    <label for="delivery_address">Delivery Address</label>

                    <textarea
                        name="delivery_address"
                        id="delivery_address"
                        rows="3"
                        placeholder="Enter the delivery destination address"
                    >{{ old(
                        'delivery_address',
                        $delivery->delivery_address
                    ) }}</textarea>

                    @include('deliveries._map')

                    @error('delivery_address')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- NOTES --}}
                <div class="form-group full-width">
                    <label for="notes">Notes</label>

                    <textarea
                        name="notes"
                        id="notes"
                        rows="3"
                        placeholder="Add delivery notes if necessary"
                    >{{ old('notes', $delivery->notes) }}</textarea>

                    @error('notes')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- FORM ACTIONS --}}
            <div class="form-actions">
                <a href="{{ route('deliveries.index') }}" class="btn">
                    Cancel
                </a>

                <button type="submit" class="btn primary">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@include('deliveries._preview')
</div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzxwpeoID7pFtHNj6ncewuQmk=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@include('deliveries._interaction')
@include('deliveries._style')

@endsection