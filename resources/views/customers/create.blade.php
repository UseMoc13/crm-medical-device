@extends('layouts.app')

@section('title', 'Create Customer')

@section('content')

<div class="page-head">
    <div>
        <h1>New Customer</h1>
        <p>Add a new customer to the CRM database.</p>
    </div>

    <div class="actions">
        <a href="{{ route('customers.index') }}" class="btn">
            Back to Customers
        </a>
    </div>
</div>

@if ($errors->any())
<div class="alert">
    <strong>Please check the following errors:</strong>

    <ul style="margin: 8px 0 0 18px;">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card">
    <div class="card-head">
        <div>
            <h3>Customer Information</h3>
            <p>Enter the basic information for this customer.</p>
        </div>
    </div>

    <div class="card-body">

        <form
            method="POST"
            action="{{ route('customers.store') }}">
            @csrf

            <div class="form-grid">

                {{-- Customer Name --}}
                <div class="form-group">
                    <label for="customer_name">
                        Customer Name <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="customer_name"
                        name="customer_name"
                        value="{{ old('customer_name') }}"
                        placeholder="Enter customer name"
                        required>
                </div>


                {{-- Customer Type --}}
                <div class="form-group">
                    <label for="customer_type">
                        Customer Type
                    </label>

                    <select
                        id="customer_type"
                        name="customer_type">
                        <option value="">Select customer type</option>

                        <option
                            value="Hospital"
                            {{ old('customer_type') === 'Hospital' ? 'selected' : '' }}>
                            Hospital
                        </option>

                        <option
                            value="Clinic"
                            {{ old('customer_type') === 'Clinic' ? 'selected' : '' }}>
                            Clinic
                        </option>

                        <option
                            value="Laboratory"
                            {{ old('customer_type') === 'Laboratory' ? 'selected' : '' }}>
                            Laboratory
                        </option>

                        <option
                            value="Distributor"
                            {{ old('customer_type') === 'Distributor' ? 'selected' : '' }}>
                            Distributor
                        </option>

                        <option
                            value="Other"
                            {{ old('customer_type') === 'Other' ? 'selected' : '' }}>
                            Other
                        </option>
                    </select>
                </div>


                {{-- Phone --}}
                <div class="form-group">
                    <label for="phone">
                        Phone
                    </label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="{{ old('phone') }}"
                        placeholder="Enter phone number">
                </div>


                {{-- Email --}}
                <div class="form-group">
                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Enter email address">
                </div>


                {{-- City --}}
                <div class="form-group">
                    <label for="city">
                        City
                    </label>

                    <input
                        type="text"
                        id="city"
                        name="city"
                        value="{{ old('city') }}"
                        placeholder="Enter city">
                </div>


                {{-- Province --}}
                <div class="form-group">
                    <label for="province">
                        Province
                    </label>

                    <input
                        type="text"
                        id="province"
                        name="province"
                        value="{{ old('province') }}"
                        placeholder="Enter province">
                </div>


                {{-- Status --}}
                <div class="form-group">
                    <label for="status">
                        Status <span>*</span>
                    </label>

                    <select
                        id="status"
                        name="status"
                        required>
                        <option
                            value="active"
                            {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                            Active
                        </option>

                        <option
                            value="inactive"
                            {{ old('status') === 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                </div>


                {{-- Address --}}
                <div class="form-group full">
                    <label for="address">
                        Address
                    </label>

                    <textarea
                        id="address"
                        name="address"
                        rows="4"
                        placeholder="Enter customer address">{{ old('address') }}</textarea>
                </div>

            </div>


            {{-- Form Actions --}}
            <div class="form-actions">

                <a
                    href="{{ route('customers.index') }}"
                    class="btn">
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn primary">
                    Create Customer
                </button>

            </div>

        </form>

    </div>
</div>

@endsection