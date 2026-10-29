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

                    <select
                        name="sales_order_id"
                        id="sales_order_id"
                        required
                    >
                        <option value="">-- Select Sales Order --</option>

                        @foreach ($salesOrders as $salesOrder)
                            <option
                                value="{{ $salesOrder->sales_order_id }}"
                                @selected(
                                    old(
                                        'sales_order_id',
                                        $delivery->sales_order_id
                                    ) == $salesOrder->sales_order_id
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

                    <select name="status" id="status" required>
                        <option value="pending"
                            @selected(
                                old('status', $delivery->status) === 'pending'
                            )>
                            Pending
                        </option>

                        <option value="in_transit"
                            @selected(
                                old('status', $delivery->status) === 'in_transit'
                            )>
                            In Transit
                        </option>

                        <option value="delivered"
                            @selected(
                                old('status', $delivery->status) === 'delivered'
                            )>
                            Delivered
                        </option>

                        <option value="cancelled"
                            @selected(
                                old('status', $delivery->status) === 'cancelled'
                            )>
                            Cancelled
                        </option>
                    </select>

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

<style>
    .delivery-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .delivery-form-grid .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .delivery-form-grid .full-width {
        grid-column: 1 / -1;
    }

    .delivery-form-grid label {
        font-size: 13px;
        font-weight: 600;
    }

    .delivery-form-grid input,
    .delivery-form-grid select,
    .delivery-form-grid textarea {
        box-sizing: border-box;
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border-color, #d1d5db);
        border-radius: 6px;
        background: #fff;
        font: inherit;
        font-size: 14px;
    }

    .delivery-form-grid textarea {
        resize: vertical;
    }

    .delivery-form-grid input:focus,
    .delivery-form-grid select:focus,
    .delivery-form-grid textarea:focus {
        outline: none;
        border-color: var(--primary-color, #2563eb);
    }

    .delivery-form-grid input[readonly] {
        background: var(--surface-secondary, #f8fafc);
        color: var(--text-muted, #64748b);
    }

    .delivery-form-grid small {
        font-size: 12px;
        color: var(--text-muted, #64748b);
    }

    .delivery-form-grid .required {
        color: #dc2626;
    }

    .delivery-form-grid .field-error {
        color: #dc2626;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color, #e5e7eb);
    }

    @media (max-width: 768px) {
        .delivery-form-grid {
            grid-template-columns: 1fr;
        }

        .delivery-form-grid .full-width {
            grid-column: auto;
        }
    }
</style>

@endsection