@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Create Warranty</h1>
        <p>Register a new medical device warranty.</p>
    </div>

    <div class="actions">
        <a href="{{ route('warranties.index') }}" class="btn">
            Back to Warranties
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert danger">
        <strong>Please check the following errors:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-head">
        <h2>Warranty Information</h2>
    </div>

    <div class="card-body">
        <form
            action="{{ route('warranties.store') }}"
            method="POST"
            class="warranty-form"
        >
            @csrf

            {{-- WARRANTY NUMBER --}}
                <div class="form-group">
                    <label for="warranty_number">
                        Warranty Number
                    </label>

                    <div class="auto-number-field">
                        <input
                            type="text"
                            id="warranty_number"
                            class="form-control"
                            value="{{ $warrantyNumber }}"
                            readonly
                        >

                        <span class="auto-number-badge">
                            Auto
                        </span>
                    </div>

                    <small class="form-hint">
                        The warranty number is generated automatically
                        by the system.
                    </small>
                </div>

                {{-- Serial Number --}}
                <div class="form-group">
                    <label for="serial_number_id">
                        Serial Number <span class="required">*</span>
                    </label>

                    <select
                        id="serial_number_id"
                        name="serial_number_id"
                        required
                    >
                        <option value="">Select Serial Number</option>

                        @foreach ($serialNumbers as $serial)
                            <option
                                value="{{ $serial->serial_number_id }}"
                                data-customer-id="{{ $serial->customer_id }}"
                                @selected(
                                    old('serial_number_id') ===
                                    $serial->serial_number_id
                                )
                            >
                                {{ $serial->serial_number }}
                                — {{ $serial->product_name ?? 'Unknown Product' }}
                            </option>
                        @endforeach
                    </select>

                    @error('serial_number_id')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Customer --}}
                <div class="form-group">
                    <label for="customer_id">
                        Customer <span class="required">*</span>
                    </label>

                    <select
                        id="customer_id"
                        name="customer_id"
                        required
                    >
                        <option value="">Select Customer</option>

                        @foreach ($customers as $customer)
                            <option
                                value="{{ $customer->customer_id }}"
                                @selected(
                                    old('customer_id') ===
                                    $customer->customer_id
                                )
                            >
                                {{ $customer->customer_name }}
                            </option>
                        @endforeach
                    </select>

                    @error('customer_id')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Warranty Type --}}
                <div class="form-group">
                    <label for="warranty_type">
                        Warranty Type
                    </label>

                    <input
                        type="text"
                        id="warranty_type"
                        name="warranty_type"
                        value="{{ old('warranty_type') }}"
                        placeholder="Example: Standard, Extended"
                        maxlength="255"
                    >

                    @error('warranty_type')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Start Date --}}
                <div class="form-group">
                    <label for="start_date">
                        Start Date <span class="required">*</span>
                    </label>

                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        value="{{ old('start_date') }}"
                        required
                    >

                    @error('start_date')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                {{-- End Date --}}
                <div class="form-group">
                    <label for="end_date">
                        End Date <span class="required">*</span>
                    </label>

                    <input
                        type="date"
                        id="end_date"
                        name="end_date"
                        value="{{ old('end_date') }}"
                        required
                    >

                    @error('end_date')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <label for="status">
                        Status <span class="required">*</span>
                    </label>

                    <select id="status" name="status" required>
                        <option value="">Select Status</option>

                        @foreach ($statuses as $status)
                            <option
                                value="{{ $status }}"
                                @selected(old('status', 'Active') === $status)
                            >
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>

                    @error('status')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Terms --}}
                <div class="form-group full-width">
                    <label for="terms">
                        Warranty Terms and Conditions
                    </label>

                    <textarea
                        id="terms"
                        name="terms"
                        rows="5"
                        placeholder="Enter warranty coverage, limitations, and terms..."
                    >{{ old('terms') }}</textarea>

                    @error('terms')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('warranties.index') }}" class="btn">
                    Cancel
                </a>

                <button type="submit" class="btn primary">
                    Save Warranty
                </button>
            </div>
        </form>
    </div>
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
        padding: 24px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        color: #374151;
        font-size: 14px;
        font-weight: 600;
    }

    .required {
        color: #dc2626;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 11px 12px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #374151;
        font-size: 14px;
        font-family: inherit;
    }

    .form-group input,
    .form-group select {
        min-height: 42px;
    }

    .form-group textarea {
        resize: vertical;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #15966f;
        box-shadow: 0 0 0 3px rgb(21 150 111 / 10%);
    }

    .full-width {
        grid-column: 1 / -1;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
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

    .alert {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 8px;
    }

    .alert.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .error-text {
        color: #dc2626;
        font-size: 12px;
    }

    @media (max-width: 768px) {
        .page-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .full-width {
            grid-column: auto;
        }

        .card-body {
            padding: 16px;
        }
    }
    /* AUTOMATIC WARRANTY NUMBER */
    .auto-number-field {
        position: relative;
    }

    .auto-number-field .form-control {
        padding-right: 65px;
    }

    .auto-number-field .form-control[readonly] {
        background: var(--surface-secondary, #f8fafc);
        color: var(--text-muted, #64748b);
        cursor: default;
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

    .form-hint {
        font-size: 11px;
        line-height: 1.6;
        color: var(--text-muted, #64748b);
    }

</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const serialSelect = document.getElementById('serial_number_id');
        const customerSelect = document.getElementById('customer_id');

        function syncCustomer() {
            const selectedOption =
                serialSelect.options[serialSelect.selectedIndex];

            if (!selectedOption || !selectedOption.value) {
                return;
            }

            const customerId = selectedOption.dataset.customerId;

            if (customerId) {
                customerSelect.value = customerId;
            }
        }

        serialSelect.addEventListener('change', syncCustomer);
    });
</script>
@endsection