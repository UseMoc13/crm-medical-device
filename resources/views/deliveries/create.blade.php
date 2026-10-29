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
                                        old('sales_order_id') == $salesOrder->sales_order_id
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

                        <select
                            name="status"
                            id="status"
                            class="form-control @error('status') is-invalid @enderror"
                            required
                        >
                            <option
                                value="pending"
                                @selected(old('status', 'pending') === 'pending')
                            >
                                Pending
                            </option>

                            <option
                                value="in_transit"
                                @selected(old('status') === 'in_transit')
                            >
                                In Transit
                            </option>

                            <option
                                value="delivered"
                                @selected(old('status') === 'delivered')
                            >
                                Delivered
                            </option>

                            <option
                                value="cancelled"
                                @selected(old('status') === 'cancelled')
                            >
                                Cancelled
                            </option>
                        </select>

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
<style>
    /* VALIDATION */
    .validation-list {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .validation-list li {
        margin-bottom: 4px;
    }

    /* FORM GRID */
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
        margin-bottom: 4px;
    }

    .delivery-form-grid .full-width {
        grid-column: 1 / -1;
    }

    /* SECTION HEADINGS */
    .form-section + .form-section {
        margin-top: 30px;
        padding-top: 26px;
        border-top: 1px solid var(--border, #e2e8f0);
    }

    .section-heading {
        margin-bottom: 20px;
    }

    .section-heading h4 {
        margin: 0 0 6px;
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary, #1e293b);
    }

    .section-heading p {
        margin: 0;
        font-size: 13px;
        line-height: 1.6;
        color: var(--text-muted, #64748b);
    }

    /* LABELS */
    .delivery-form-grid label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary, #334155);
    }

    .required-mark {
        color: #dc2626;
        font-weight: 600;
    }

    .required-note {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: var(--text-muted, #64748b);
        white-space: nowrap;
    }

    /* INPUTS */
    .delivery-form-grid .form-control {
        box-sizing: border-box;
        display: block;
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid var(--border, #cbd5e1);
        border-radius: 7px;
        background: var(--surface, #ffffff);
        color: var(--text-primary, #1e293b);
        font-family: inherit;
        font-size: 13px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .delivery-form-grid textarea.form-control {
        min-height: auto;
        line-height: 1.6;
        resize: vertical;
    }

    .delivery-form-grid .form-control:focus {
        outline: none;
        border-color: var(--primary, #2563eb);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
    }

    .delivery-form-grid .form-control[readonly] {
        background: var(--surface-secondary, #f8fafc);
        color: var(--text-muted, #64748b);
        cursor: default;
    }

    .delivery-form-grid .form-control.is-invalid {
        border-color: #dc2626;
    }

    /* AUTOMATIC DELIVERY NUMBER */
    .auto-number-field {
        position: relative;
    }

    .auto-number-field .form-control {
        padding-right: 65px;
    }

    .auto-number-badge {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        padding: 4px 7px;
        border-radius: 5px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* HELP TEXT AND ERRORS */
    .delivery-form-grid .form-hint {
        font-size: 11px;
        line-height: 1.6;
        color: var(--text-muted, #64748b);
    }

    .delivery-form-grid .field-error {
        color: #dc2626;
        font-size: 12px;
        line-height: 1.5;
    }

    /* FORM ACTIONS */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 30px;
        padding-top: 22px;
        border-top: 1px solid var(--border, #e2e8f0);
    }

    /* DISABLED BUTTON */
    .form-actions button:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .delivery-form-grid {
            grid-template-columns: 1fr;
            gap: 18px;
        }

        .delivery-form-grid .full-width {
            grid-column: auto;
        }

        .page-head {
            flex-wrap: wrap;
            gap: 14px;
        }

        .page-head .actions {
            width: 100%;
        }

        .required-note {
            margin-top: 8px;
        }
    }

    @media (max-width: 480px) {
        .form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .form-actions .btn {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
    }
</style>

@endsection