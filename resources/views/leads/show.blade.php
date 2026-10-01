@extends('layouts.app')

@section('title', 'Lead Detail')

@section('content')

<div class="page-head">

    <div>

        <h1>Lead Detail</h1>

        <p>
            View lead information and details.
        </p>

    </div>

    <div class="actions">

        <a
            href="{{ route('leads.index') }}"
            class="btn">
            Back to Leads
        </a>

        <a
            href="{{ route('leads.edit', $lead) }}"
            class="btn primary">
            Edit Lead
        </a>

    </div>

</div>


@if (session('success'))

<div class="alert success">

    {{ session('success') }}

</div>

@endif


<div class="card">

    <div class="card-head">

        <div>

            <h3>
                Lead Information
            </h3>

            <p>
                Details of the selected lead.
            </p>

        </div>

    </div>


    <div class="card-body">

        <div class="detail-grid">


            {{-- LEAD CODE --}}

            <div class="detail-item">

                <span class="detail-label">
                    Lead Code
                </span>

                <span class="detail-value">
                    {{ $lead->lead_code }}
                </span>

            </div>


            {{-- STATUS --}}

            <div class="detail-item">

                <span class="detail-label">
                    Status
                </span>

                <span class="detail-value">
                    {{ $lead->status ?: '-' }}
                </span>

            </div>


            {{-- COMPANY --}}

            <div class="detail-item">

                <span class="detail-label">
                    Company Name
                </span>

                <span class="detail-value">
                    {{ $lead->company_name }}
                </span>

            </div>


            {{-- CONTACT --}}

            <div class="detail-item">

                <span class="detail-label">
                    Contact Name
                </span>

                <span class="detail-value">
                    {{ $lead->contact_name ?: '-' }}
                </span>

            </div>


            {{-- PHONE --}}

            <div class="detail-item">

                <span class="detail-label">
                    Phone
                </span>

                <span class="detail-value">
                    {{ $lead->phone ?: '-' }}
                </span>

            </div>


            {{-- EMAIL --}}

            <div class="detail-item">

                <span class="detail-label">
                    Email
                </span>

                <span class="detail-value">
                    {{ $lead->email ?: '-' }}
                </span>

            </div>


            {{-- SOURCE --}}

            <div class="detail-item">

                <span class="detail-label">
                    Lead Source
                </span>

                <span class="detail-value">
                    {{ $lead->source ?: '-' }}
                </span>

            </div>


            {{-- SALES --}}

            <div class="detail-item">

                <span class="detail-label">
                    Assigned Sales
                </span>

                <span class="detail-value">

                    @if ($lead->user)

                        {{ $lead->user->name }}

                    @else

                        <span class="muted">
                            Not assigned
                        </span>

                    @endif

                </span>

            </div>


            {{-- CUSTOMER --}}

            <div class="detail-item detail-full">

                <span class="detail-label">
                    Customer
                </span>

                <span class="detail-value">

                    @if ($lead->customer)

                        {{ $lead->customer->customer_name }}

                        @if ($lead->customer->customer_code)

                            <span class="detail-secondary">
                                ({{ $lead->customer->customer_code }})
                            </span>

                        @endif

                    @else

                        <span class="muted">
                            Not linked to customer
                        </span>

                    @endif

                </span>

            </div>


            {{-- QUALIFICATION --}}

            <div class="detail-item detail-full">

                <span class="detail-label">
                    Qualification
                </span>

                <div class="detail-description">

                    @if ($lead->qualification)

                        {{ $lead->qualification }}

                    @else

                        <span class="muted">
                            No qualification information available.
                        </span>

                    @endif

                </div>

            </div>


            {{-- CREATED --}}

            <div class="detail-item">

                <span class="detail-label">
                    Created At
                </span>

                <span class="detail-value">

                    {{ $lead->created_at?->format('d M Y, H:i') ?? '-' }}

                </span>

            </div>


            {{-- UPDATED --}}

            <div class="detail-item">

                <span class="detail-label">
                    Last Updated
                </span>

                <span class="detail-value">

                    {{ $lead->updated_at?->format('d M Y, H:i') ?? '-' }}

                </span>

            </div>


        </div>

    </div>

</div>


<style>

    .detail-grid {

        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 22px 30px;

    }


    .detail-item {

        display: flex;

        flex-direction: column;

        gap: 7px;

    }


    .detail-full {

        grid-column: 1 / -1;

    }


    .detail-label {

        font-size: 12px;

        font-weight: 600;

        color: #8a94a6;

        text-transform: uppercase;

        letter-spacing: .04em;

    }


    .detail-value {

        font-size: 15px;

        font-weight: 600;

        color: #26344f;

    }


    .detail-secondary {

        color: #8a94a6;

        font-size: 13px;

        font-weight: 500;

    }


    .detail-description {

        min-height: 80px;

        padding: 14px;

        border:
            1px solid #e1e5ec;

        border-radius: 8px;

        background: #f8f9fb;

        color: #39465d;

        font-size: 14px;

        line-height: 1.7;

        white-space: pre-line;

    }


    .muted {

        color: #9aa3b2;

        font-style: italic;

        font-weight: 400;

    }


    @media (max-width: 700px) {

        .detail-grid {

            grid-template-columns: 1fr;

        }


        .detail-full {

            grid-column: auto;

        }

    }

</style>

@endsection