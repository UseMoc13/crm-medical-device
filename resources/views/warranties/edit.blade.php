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

                {{-- WARRANTY TYPE --}}
                <div class="form-group">
                    <label for="warranty_type">
                        Warranty Type
                        <span class="required-mark">*</span>
                    </label>

                    <select
                        name="warranty_type"
                        id="warranty_type"
                        class="form-control @error('warranty_type') is-invalid @enderror"
                        required
                    >
                        <option value="">-- Select Warranty Type --</option>

                        <option
                            value="Manufacturer"
                            @selected(old('warranty_type', $warranty->warranty_type) === 'Manufacturer')
                        >
                            Manufacturer Warranty
                        </option>

                        <option
                            value="Extended"
                            @selected(old('warranty_type', $warranty->warranty_type) === 'Extended')
                        >
                            Extended Warranty
                        </option>

                        <option
                            value="Service"
                            @selected(old('warranty_type', $warranty->warranty_type) === 'Service')
                        >
                            Service Warranty
                        </option>
                    </select>

                    @error('warranty_type')
                        <small class="field-error">{{ $message }}</small>
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

@include('warranties._style', ['page' => 'edit'])


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