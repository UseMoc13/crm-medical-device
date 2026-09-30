@extends('layouts.app')

@section('title', 'Contact Details')

@section('content')

<div class="page-head">

    <div>
        <h1>Contact Details</h1>

        <p>
            View contact information and customer relationship.
        </p>
    </div>

    <div class="actions">

        <a
            href="{{ route('contacts.edit', $contact) }}"
            class="btn primary">
            Edit Contact
        </a>

        <a
            href="{{ route('contacts.index') }}"
            class="btn">
            Back to Contacts
        </a>

    </div>

</div>


@if(session('success'))

<div class="alert success">
    {{ session('success') }}
</div>

@endif


<div class="card">

    <div class="card-head">

        <div>

            <h3>Contact Information</h3>

            <p>
                Detailed information about this contact.
            </p>

        </div>

    </div>


    <div class="card-body">

        <div class="detail-grid">


            {{-- =====================================================
                 CONTACT NAME
            ====================================================== --}}

            <div class="detail-group">

                <span class="detail-label">
                    Contact Name
                </span>

                <div class="detail-value">
                    {{ $contact->name }}
                </div>

            </div>


            {{-- =====================================================
                 CUSTOMER
            ====================================================== --}}

            <div class="detail-group">

                <span class="detail-label">
                    Customer
                </span>

                <div class="detail-value">

                    @if($contact->customer)

                    <a
                        href="{{ route(
                                'customers.show',
                                $contact->customer
                            ) }}"
                        class="customer-detail-link">
                        {{ $contact->customer->customer_name }}
                    </a>

                    <div class="detail-subvalue">
                        {{ $contact->customer->customer_code }}
                    </div>

                    @else

                    <span class="muted">
                        -
                    </span>

                    @endif

                </div>

            </div>


            {{-- =====================================================
                 POSITION
            ====================================================== --}}

            <div class="detail-group">

                <span class="detail-label">
                    Position
                </span>

                <div class="detail-value">

                    {{ $contact->position ?: '-' }}

                </div>

            </div>


            {{-- =====================================================
                 DEPARTMENT
            ====================================================== --}}

            <div class="detail-group">

                <span class="detail-label">
                    Department
                </span>

                <div class="detail-value">

                    {{ $contact->department ?: '-' }}

                </div>

            </div>


            {{-- =====================================================
                 PHONE
            ====================================================== --}}

            <div class="detail-group">

                <span class="detail-label">
                    Phone
                </span>

                <div class="detail-value">

                    @if($contact->phone)

                    <a
                        href="tel:{{ $contact->phone }}"
                        class="contact-detail-link">
                        {{ $contact->phone }}
                    </a>

                    @else

                    <span class="muted">
                        -
                    </span>

                    @endif

                </div>

            </div>


            {{-- =====================================================
                 EMAIL
            ====================================================== --}}

            <div class="detail-group">

                <span class="detail-label">
                    Email
                </span>

                <div class="detail-value">

                    @if($contact->email)

                    <a
                        href="mailto:{{ $contact->email }}"
                        class="contact-detail-link">
                        {{ $contact->email }}
                    </a>

                    @else

                    <span class="muted">
                        -
                    </span>

                    @endif

                </div>

            </div>


            {{-- =====================================================
                 CONTACT TYPE
            ====================================================== --}}

            <div class="detail-group">

                <span class="detail-label">
                    Contact Type
                </span>

                <div class="detail-value">

                    @if($contact->contact_type)

                    <span class="contact-type-badge">
                        {{ $contact->contact_type }}
                    </span>

                    @else

                    <span class="muted">
                        -
                    </span>

                    @endif

                </div>

            </div>


            {{-- =====================================================
                 PRIMARY CONTACT
            ====================================================== --}}

            <div class="detail-group">

                <span class="detail-label">
                    Primary Contact
                </span>

                <div class="detail-value">

                    @if($contact->is_primary)

                    <span class="status-badge status-primary">
                        Primary Contact
                    </span>

                    @else

                    <span class="muted">
                        No
                    </span>

                    @endif

                </div>

            </div>


            {{-- =====================================================
                 CREATED
            ====================================================== --}}

            <div class="detail-group">

                <span class="detail-label">
                    Created
                </span>

                <div class="detail-value">

                    {{ $contact->created_at?->format('d M Y, H:i') ?? '-' }}

                </div>

            </div>


            {{-- =====================================================
                 UPDATED
            ====================================================== --}}

            <div class="detail-group">

                <span class="detail-label">
                    Last Updated
                </span>

                <div class="detail-value">

                    {{ $contact->updated_at?->format('d M Y, H:i') ?? '-' }}

                </div>

            </div>


        </div>

    </div>

</div>


<style>
    /* =========================================================
   CONTACT DETAIL
========================================================= */

    .detail-grid {

        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 0;

        border: 1px solid #e1e5ec;

        border-radius: 10px;

        overflow: hidden;

        background: #fff;

    }


    .detail-group {

        padding: 18px 20px;

        border-bottom: 1px solid #e8ebf0;

    }


    .detail-group:nth-child(odd) {

        border-right: 1px solid #e8ebf0;

    }


    .detail-label {

        display: block;

        margin-bottom: 7px;

        color: #7d8797;

        font-size: 12px;

        font-weight: 600;

    }


    .detail-value {

        color: #17284f;

        font-size: 14px;

        line-height: 1.5;

    }


    .detail-subvalue {

        margin-top: 2px;

        color: #8a94a6;

        font-size: 11px;

    }


    .customer-detail-link,
    .contact-detail-link {

        color: #223a70;

        text-decoration: none;

        font-weight: 600;

    }


    .customer-detail-link:hover,
    .contact-detail-link:hover {

        color: #2ba7a0;

        text-decoration: underline;

    }


    .contact-type-badge {

        display: inline-flex;

        align-items: center;

        padding: 4px 8px;

        border-radius: 6px;

        background: #f1f5f7;

        color: #34415c;

        font-size: 11px;

        font-weight: 600;

    }


    .status-badge.status-primary {

        background: #e8f5f0;

        color: #16805f;

    }


    @media (max-width: 700px) {

        .detail-grid {

            grid-template-columns: 1fr;

        }

        .detail-group:nth-child(odd) {

            border-right: none;

        }

    }
</style>

@endsection