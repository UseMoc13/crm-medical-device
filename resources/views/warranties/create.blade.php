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
                            @selected(old('warranty_type') === 'Manufacturer')
                        >
                            Manufacturer Warranty
                        </option>

                        <option
                            value="Extended"
                            @selected(old('warranty_type') === 'Extended')
                        >
                            Extended Warranty
                        </option>

                        <option
                            value="Service"
                            @selected(old('warranty_type') === 'Service')
                        >
                            Service Warranty
                        </option>
                    </select>

                    @error('warranty_type')
                        <small class="field-error">{{ $message }}</small>
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

@include('warranties._style', ['page' => 'create'])


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