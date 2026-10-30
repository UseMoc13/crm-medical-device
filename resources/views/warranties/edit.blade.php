@extends('layouts.app')

@section('content')
<div class="page-container">
    <div class="page-head">
        <div>
            <h1>Edit Warranty</h1>
            <p class="page-subtitle">
                Update warranty information and coverage details.
            </p>
        </div>

        <div class="page-actions">
            <a href="{{ route('warranties.show', $warranty->warranty_id) }}"
               class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2>Warranty Information</h2>
            <p>Update the warranty details below.</p>
        </div>

        <form
            action="{{ route('warranties.update', $warranty->warranty_id) }}"
            method="POST"
        >
            @csrf
            @method('PUT')

            <div class="form-grid">

                {{-- Warranty Number --}}
                <div class="form-group">
                    <label for="warranty_number">
                        Warranty Number <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="warranty_number"
                        name="warranty_number"
                        value="{{ old('warranty_number', $warranty->warranty_number) }}"
                        class="form-control @error('warranty_number') is-invalid @enderror"
                        placeholder="e.g. WTY-2026-0001"
                        required
                    >

                    @error('warranty_number')
                        <small class="error-message">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Warranty Type --}}
                <div class="form-group">
                    <label for="warranty_type">
                        Warranty Type <span class="required">*</span>
                    </label>

                    <select
                        id="warranty_type"
                        name="warranty_type"
                        class="form-control @error('warranty_type') is-invalid @enderror"
                        required
                    >
                        <option value="">Select warranty type</option>

                        @foreach ([
                            'Manufacturer' => 'Manufacturer Warranty',
                            'Extended' => 'Extended Warranty',
                            'Service' => 'Service Warranty',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                {{ old('warranty_type', $warranty->warranty_type) == $value ? 'selected' : '' }}
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    @error('warranty_type')
                        <small class="error-message">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Serial Number --}}
                <div class="form-group">
                    <label for="serial_number_id">
                        Serial Number <span class="required">*</span>
                    </label>

                    <select
                        id="serial_number_id"
                        name="serial_number_id"
                        class="form-control @error('serial_number_id') is-invalid @enderror"
                        required
                    >
                        <option value="">Select serial number</option>

                        @foreach ($serialNumbers as $serial)
                            <option
                                value="{{ $serial->serial_number_id }}"
                                data-customer-id="{{ $serial->customer_id }}"
                                {{ old('serial_number_id', $warranty->serial_number_id) == $serial->serial_number_id ? 'selected' : '' }}
                            >
                                {{ $serial->serial_number }}
                            </option>
                        @endforeach
                    </select>

                    @error('serial_number_id')
                        <small class="error-message">{{ $message }}</small>
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
                        class="form-control @error('customer_id') is-invalid @enderror"
                        required
                    >
                        <option value="">Select customer</option>

                        @foreach ($customers as $customer)
                            <option
                                value="{{ $customer->customer_id }}"
                                {{ old('customer_id', $warranty->customer_id) == $customer->customer_id ? 'selected' : '' }}
                            >
                                {{ $customer->customer_name }}
                            </option>
                        @endforeach
                    </select>

                    @error('customer_id')
                        <small class="error-message">{{ $message }}</small>
                    @enderror

                    <small class="form-hint">
                        The customer should match the selected serial number.
                    </small>
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
                        value="{{ old('start_date', $warranty->start_date ? \Illuminate\Support\Carbon::parse($warranty->start_date)->format('Y-m-d') : '') }}"
                        class="form-control @error('start_date') is-invalid @enderror"
                        required
                    >

                    @error('start_date')
                        <small class="error-message">{{ $message }}</small>
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
                        value="{{ old('end_date', $warranty->end_date ? \Illuminate\Support\Carbon::parse($warranty->end_date)->format('Y-m-d') : '') }}"
                        class="form-control @error('end_date') is-invalid @enderror"
                        required
                    >

                    @error('end_date')
                        <small class="error-message">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <label for="status">
                        Status <span class="required">*</span>
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="form-control @error('status') is-invalid @enderror"
                        required
                    >
                        <option value="">Select status</option>

                        @foreach ($statuses as $status)
                            <option
                                value="{{ $status }}"
                                {{ old('status', $warranty->status) == $status ? 'selected' : '' }}
                            >
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>

                    @error('status')
                        <small class="error-message">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Terms --}}
                <div class="form-group form-group-full">
                    <label for="terms">Warranty Terms & Conditions</label>

                    <textarea
                        id="terms"
                        name="terms"
                        rows="5"
                        class="form-control @error('terms') is-invalid @enderror"
                        placeholder="Enter warranty terms and conditions..."
                    >{{ old('terms', $warranty->terms) }}</textarea>

                    @error('terms')
                        <small class="error-message">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <div class="form-footer">
                <a
                    href="{{ route('warranties.show', $warranty->warranty_id) }}"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .page-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px;
    }

    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .page-head h1 {
        margin: 0 0 8px;
        font-size: 26px;
        font-weight: 700;
        color: #1f2937;
    }

    .page-subtitle {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .page-actions {
        display: flex;
        gap: 10px;
    }

    .card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .card-header {
        padding: 22px 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-header h2 {
        margin: 0 0 6px;
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
    }

    .card-header p {
        margin: 0;
        color: #6b7280;
        font-size: 13px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
        padding: 24px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .form-group-full {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }

    .required {
        color: #dc2626;
    }

    .form-control {
        width: 100%;
        box-sizing: border-box;
        padding: 11px 12px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #111827;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    .is-invalid {
        border-color: #dc2626;
    }

    .error-message {
        color: #dc2626;
        font-size: 12px;
    }

    .form-hint {
        color: #6b7280;
        font-size: 12px;
    }

    .alert {
        padding: 16px 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        font-size: 14px;
    }

    .alert-danger {
        color: #991b1b;
        background: #fef2f2;
        border: 1px solid #fecaca;
    }

    .alert ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 20px 24px;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border: 1px solid transparent;
        border-radius: 7px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-primary {
        color: #fff;
        background: #2563eb;
    }

    .btn-primary:hover {
        background: #1d4ed8;
    }

    .btn-secondary {
        color: #374151;
        background: #fff;
        border-color: #d1d5db;
    }

    .btn-secondary:hover {
        background: #f3f4f6;
    }

    @media (max-width: 768px) {
        .page-container {
            padding: 16px;
        }

        .page-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .form-grid {
            grid-template-columns: 1fr;
            padding: 18px;
        }

        .form-group-full {
            grid-column: auto;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const serialNumberSelect = document.getElementById('serial_number_id');
    const customerSelect = document.getElementById('customer_id');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    // Set customer automatically when serial number changes.
    serialNumberSelect.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const customerId = selectedOption
            ? selectedOption.dataset.customerId
            : '';

        if (customerId) {
            customerSelect.value = customerId;
        } else {
            customerSelect.value = '';
        }
    });

    // End date cannot be earlier than start date.
    function updateEndDateMinimum() {
        endDateInput.min = startDateInput.value || '';
    }

    startDateInput.addEventListener('change', updateEndDateMinimum);

    updateEndDateMinimum();
});
</script>
@endsection