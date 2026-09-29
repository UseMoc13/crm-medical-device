@extends('layouts.app')

@section('title', 'Customer Detail')

@section('content')

<div class="page-head">

    <div>
        <div class="breadcrumb">
            <a href="{{ route('customers.index') }}">
                Customers
            </a>

            <span>/</span>

            <span>Detail</span>
        </div>

        <h1>{{ $customer->customer_name }}</h1>

        <p>
            Customer detail and information
        </p>
    </div>

    <div class="actions">

        <a
            href="{{ route('customers.edit', $customer) }}"
            class="btn primary"
        >
            ✎ Edit Customer
        </a>

        <a
            href="{{ route('customers.index') }}"
            class="btn"
        >
            ← Back
        </a>

    </div>

</div>


@if(session('success'))

    <div class="alert success">
        {{ session('success') }}
    </div>

@endif


<div class="grid two">

    {{-- Customer Information --}}

    <div class="card">

        <div class="card-head">
            <div>
                <h3>Customer Information</h3>
                <p>Basic customer information</p>
            </div>
        </div>

        <div class="card-body">

            <div class="detail-grid">

                <div class="detail-item">
                    <span class="detail-label">
                        Customer Code
                    </span>

                    <strong>
                        {{ $customer->customer_code }}
                    </strong>
                </div>


                <div class="detail-item">
                    <span class="detail-label">
                        Customer Name
                    </span>

                    <strong>
                        {{ $customer->customer_name }}
                    </strong>
                </div>


                <div class="detail-item">
                    <span class="detail-label">
                        Customer Type
                    </span>

                    <span>
                        {{ $customer->customer_type ?: '-' }}
                    </span>
                </div>


                <div class="detail-item">
                    <span class="detail-label">
                        Status
                    </span>

                    <span class="status-badge">
                        {{ ucfirst($customer->status) }}
                    </span>
                </div>

            </div>

        </div>

    </div>


    {{-- Contact Information --}}

    <div class="card">

        <div class="card-head">
            <div>
                <h3>Contact Information</h3>
                <p>Customer contact details</p>
            </div>
        </div>

        <div class="card-body">

            <div class="detail-grid">

                <div class="detail-item">
                    <span class="detail-label">
                        Phone
                    </span>

                    <span>
                        {{ $customer->phone ?: '-' }}
                    </span>
                </div>


                <div class="detail-item">
                    <span class="detail-label">
                        Email
                    </span>

                    <span>
                        {{ $customer->email ?: '-' }}
                    </span>
                </div>


                <div class="detail-item full">
                    <span class="detail-label">
                        Address
                    </span>

                    <span>
                        {{ $customer->address ?: '-' }}
                    </span>
                </div>


                <div class="detail-item">
                    <span class="detail-label">
                        City
                    </span>

                    <span>
                        {{ $customer->city ?: '-' }}
                    </span>
                </div>


                <div class="detail-item">
                    <span class="detail-label">
                        Province
                    </span>

                    <span>
                        {{ $customer->province ?: '-' }}
                    </span>
                </div>

            </div>

        </div>

    </div>

</div>


{{-- Related Data --}}

<div class="card" style="margin-top: 16px;">

    <div class="card-head">

        <div>
            <h3>Customer Activity</h3>

            <p>
                Related contacts and leads will appear here.
            </p>
        </div>

    </div>

    <div class="card-body">

        <div class="empty">

            Contacts and Leads integration will be available
            after the Customer & Lead module is completed.

        </div>

    </div>

</div>

@endsection