@extends('layouts.app')

@section('title', 'Opportunity Detail')

@section('content')

<div class="page-head">

    <div>

        <h1>Opportunity Detail</h1>

        <p>
            View detailed information about this sales opportunity.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('opportunities.index') }}"
            class="btn"
        >
            ← Back
        </a>


        <a
            href="{{ route('opportunities.edit', $opportunity) }}"
            class="btn primary"
        >
            Edit Opportunity
        </a>

    </div>

</div>


@if(session('success'))

    <div class="alert success">
        {{ session('success') }}
    </div>

@endif


{{-- =========================================================
     OPPORTUNITY HEADER
========================================================= --}}

<div class="card opportunity-header-card">

    <div class="opportunity-header">

        <div>

            <div class="opportunity-code">

                {{ $opportunity->opportunity_code }}

            </div>


            <h2>

                {{ $opportunity->name }}

            </h2>


            <div class="opportunity-meta">

                Created
                {{ optional($opportunity->created_at)->format('d M Y H:i') }}

            </div>

        </div>


        <div class="header-statuses">

            <span
                class="status-badge stage"
            >
                {{ $opportunity->stage }}
            </span>


            <span
                class="status-badge
                {{ strtolower($opportunity->status) }}"
            >
                {{ $opportunity->status }}
            </span>

        </div>

    </div>

</div>


{{-- =========================================================
     MAIN INFORMATION
========================================================= --}}

<div class="detail-grid">


    {{-- =====================================================
         CUSTOMER
    ====================================================== --}}

    <div class="card">

        <div class="card-head">

            <div>

                <h3>Customer Information</h3>

                <p>
                    Customer associated with this opportunity.
                </p>

            </div>

        </div>


        <div class="card-body">

            @if($opportunity->customer)

                <div class="detail-row">

                    <span class="detail-label">
                        Customer
                    </span>

                    <span class="detail-value">
                        {{ $opportunity->customer->customer_name }}
                    </span>

                </div>


                <div class="detail-row">

                    <span class="detail-label">
                        Customer Code
                    </span>

                    <span class="detail-value">
                        {{ $opportunity->customer->customer_code ?? '-' }}
                    </span>

                </div>


                <div class="detail-row">

                    <span class="detail-label">
                        Phone
                    </span>

                    <span class="detail-value">
                        {{ $opportunity->customer->phone ?? '-' }}
                    </span>

                </div>


                <div class="detail-row">

                    <span class="detail-label">
                        Email
                    </span>

                    <span class="detail-value">
                        {{ $opportunity->customer->email ?? '-' }}
                    </span>

                </div>

            @else

                <div class="empty">

                    Customer information is unavailable.

                </div>

            @endif

        </div>

    </div>


    {{-- =====================================================
         LEAD
    ====================================================== --}}

    <div class="card">

        <div class="card-head">

            <div>

                <h3>Lead Information</h3>

                <p>
                    Lead that generated this opportunity.
                </p>

            </div>

        </div>


        <div class="card-body">

            @if($opportunity->lead)

                <div class="detail-row">

                    <span class="detail-label">
                        Lead Code
                    </span>

                    <span class="detail-value">
                        {{ $opportunity->lead->lead_code }}
                    </span>

                </div>


                <div class="detail-row">

                    <span class="detail-label">
                        Company
                    </span>

                    <span class="detail-value">
                        {{ $opportunity->lead->company_name ?? '-' }}
                    </span>

                </div>


                <div class="detail-row">

                    <span class="detail-label">
                        Contact
                    </span>

                    <span class="detail-value">
                        {{ $opportunity->lead->contact_name ?? '-' }}
                    </span>

                </div>

            @else

                <div class="empty">

                    This opportunity is not linked to a lead.

                </div>

            @endif

        </div>

    </div>


    {{-- =====================================================
         SALES INFORMATION
    ====================================================== --}}

    <div class="card">

        <div class="card-head">

            <div>

                <h3>Sales Information</h3>

                <p>
                    Sales representative responsible for this opportunity.
                </p>

            </div>

        </div>


        <div class="card-body">

            @if($opportunity->user)

                <div class="detail-row">

                    <span class="detail-label">
                        Sales
                    </span>

                    <span class="detail-value">
                        {{ $opportunity->user->name }}
                    </span>

                </div>


                <div class="detail-row">

                    <span class="detail-label">
                        Email
                    </span>

                    <span class="detail-value">
                        {{ $opportunity->user->email ?? '-' }}
                    </span>

                </div>

            @else

                <div class="empty">

                    Sales information is unavailable.

                </div>

            @endif

        </div>

    </div>


    {{-- =====================================================
         OPPORTUNITY VALUE
    ====================================================== --}}

    <div class="card">

        <div class="card-head">

            <div>

                <h3>Opportunity Summary</h3>

                <p>
                    Current value and closing information.
                </p>

            </div>

        </div>


        <div class="card-body">

            <div class="detail-row">

                <span class="detail-label">
                    Estimated Value
                </span>

                <span class="detail-value value-highlight">

                    @if($opportunity->estimated_value !== null)

                        Rp
                        {{ number_format(
                            (float) $opportunity->estimated_value,
                            0,
                            ',',
                            '.'
                        ) }}

                    @else

                        -

                    @endif

                </span>

            </div>


            <div class="detail-row">

                <span class="detail-label">
                    Expected Close
                </span>

                <span class="detail-value">

                    @if($opportunity->expected_close_date)

                        {{ $opportunity->expected_close_date->format('d M Y') }}

                    @else

                        -

                    @endif

                </span>

            </div>


            <div class="detail-row">

                <span class="detail-label">
                    Stage
                </span>

                <span class="detail-value">

                    {{ $opportunity->stage }}

                </span>

            </div>


            <div class="detail-row">

                <span class="detail-label">
                    Status
                </span>

                <span class="detail-value">

                    {{ $opportunity->status }}

                </span>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     DESCRIPTION
========================================================= --}}

<div class="card">

    <div class="card-head">

        <div>

            <h3>Description</h3>

            <p>
                Additional information about this opportunity.
            </p>

        </div>

    </div>


    <div class="card-body">

        @if($opportunity->description)

            <div class="description-content">

                {!! nl2br(e($opportunity->description)) !!}

            </div>

        @else

            <div class="empty">

                No description has been provided.

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     OPPORTUNITY ITEMS
========================================================= --}}

<div class="card">

    <div class="card-head">

        <div>

            <h3>Opportunity Items</h3>

            <p>
                Products associated with this opportunity.
            </p>

        </div>


        <div>

            <span class="item-count">

                {{ $opportunity->items->count() }}

                {{ $opportunity->items->count() === 1 ? 'item' : 'items' }}

            </span>

        </div>

    </div>


    <div class="table-wrap">

        @if($opportunity->items->count())

            <table>

                <thead>

                    <tr>

                        <th>
                            Product
                        </th>

                        <th>
                            Quantity
                        </th>

                        <th>
                            Estimated Price
                        </th>

                        <th>
                            Subtotal
                        </th>

                        <th>
                            Notes
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach($opportunity->items as $item)

                        <tr>

                            <td>

                                @if($item->product)

                                    <div class="product-name">

                                        {{ $item->product->product_name }}

                                    </div>

                                @else

                                    <span class="muted">
                                        Product unavailable
                                    </span>

                                @endif

                            </td>


                            <td>

                                {{ number_format(
                                    $item->quantity
                                ) }}

                            </td>


                            <td>

                                @if($item->estimated_price !== null)

                                    Rp
                                    {{ number_format(
                                        (float) $item->estimated_price,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                @else

                                    -

                                @endif

                            </td>


                            <td>

                                @if($item->estimated_price !== null)

                                    Rp
                                    {{ number_format(
                                        (float) $item->estimated_price *
                                        (int) $item->quantity,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                @else

                                    -

                                @endif

                            </td>


                            <td>

                                {{ $item->notes ?: '-' }}

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        @else

            <div class="empty">

                <strong>No opportunity items yet.</strong>

                <span>
                    Products can be added to this opportunity later.
                </span>

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     TIMESTAMPS
========================================================= --}}

<div class="card metadata-card">

    <div class="card-body">

        <div class="metadata-grid">

            <div>

                <span class="metadata-label">
                    Created At
                </span>

                <span class="metadata-value">

                    {{ optional($opportunity->created_at)
                        ->format('d M Y H:i:s') }}

                </span>

            </div>


            <div>

                <span class="metadata-label">
                    Last Updated
                </span>

                <span class="metadata-value">

                    {{ optional($opportunity->updated_at)
                        ->format('d M Y H:i:s') }}

                </span>

            </div>

        </div>

    </div>

</div>


<style>

/* =========================================================
   OPPORTUNITY HEADER
========================================================= */

.opportunity-header-card {
    margin-bottom: 20px;
}

.opportunity-header {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 20px;

    padding: 24px;
}

.opportunity-code {
    margin-bottom: 6px;

    color: #2ba7a0;

    font-size: 12px;
    font-weight: 700;

    letter-spacing: .04em;
}

.opportunity-header h2 {
    margin: 0;

    color: #17284f;

    font-size: 22px;
    font-weight: 700;
}

.opportunity-meta {
    margin-top: 7px;

    color: #8a94a6;

    font-size: 11px;
}

.header-statuses {
    display: flex;

    align-items: center;

    gap: 8px;

    flex-shrink: 0;
}


/* =========================================================
   STATUS BADGES
========================================================= */

.status-badge {
    display: inline-flex;

    align-items: center;

    min-height: 27px;

    padding: 0 10px;

    border-radius: 999px;

    font-size: 11px;
    font-weight: 600;
}

.status-badge.stage {
    background: #edf3fb;

    color: #31527e;
}

.status-badge.open {
    background: #edf7f3;

    color: #16815f;
}

.status-badge.active {
    background: #eaf7f7;

    color: #238a88;
}

.status-badge.won {
    background: #eaf7ef;

    color: #16815f;
}

.status-badge.lost {
    background: #fceeee;

    color: #bd4d4d;
}

.status-badge.closed {
    background: #eef0f4;

    color: #687284;
}


/* =========================================================
   DETAIL GRID
========================================================= */

.detail-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 20px;

    margin-bottom: 20px;
}


/* =========================================================
   DETAIL ROW
========================================================= */

.detail-row {
    display: flex;

    align-items: flex-start;
    justify-content: space-between;

    gap: 20px;

    padding: 12px 0;

    border-bottom: 1px solid #edf0f4;
}

.detail-row:first-child {
    padding-top: 0;
}

.detail-row:last-child {
    padding-bottom: 0;

    border-bottom: none;
}

.detail-label {
    color: #8a94a6;

    font-size: 11px;

    flex-shrink: 0;
}

.detail-value {
    color: #17284f;

    font-size: 12px;
    font-weight: 600;

    text-align: right;

    word-break: break-word;
}

.value-highlight {
    color: #15966f;

    font-size: 14px;
}


/* =========================================================
   DESCRIPTION
========================================================= */

.description-content {
    color: #46536a;

    font-size: 13px;

    line-height: 1.7;

    white-space: normal;
}


/* =========================================================
   ITEMS
========================================================= */

.item-count {
    display: inline-flex;

    align-items: center;

    min-height: 26px;

    padding: 0 9px;

    border-radius: 999px;

    background: #f1f4f8;

    color: #687284;

    font-size: 11px;
    font-weight: 600;
}

.product-name {
    color: #17284f;

    font-size: 12px;
    font-weight: 600;
}

.table-wrap table {
    width: 100%;
}

.table-wrap th {
    white-space: nowrap;
}

.table-wrap td {
    vertical-align: top;
}


/* =========================================================
   METADATA
========================================================= */

.metadata-card {
    margin-bottom: 10px;
}

.metadata-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 30px;
}

.metadata-grid > div {
    display: flex;

    flex-direction: column;

    gap: 5px;
}

.metadata-label {
    color: #8a94a6;

    font-size: 10px;
}

.metadata-value {
    color: #46536a;

    font-size: 11px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 800px) {

    .opportunity-header {
        align-items: flex-start;

        flex-direction: column;
    }

    .header-statuses {
        width: 100%;
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }

}


@media (max-width: 600px) {

    .metadata-grid {
        grid-template-columns: 1fr;
    }

    .detail-row {
        flex-direction: column;

        gap: 5px;
    }

    .detail-value {
        text-align: left;
    }

}

</style>

@endsection