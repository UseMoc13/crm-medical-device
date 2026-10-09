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
            class="btn">
            ← Back
        </a>

        <a
            href="{{ route('opportunities.edit', $opportunity) }}"
            class="btn primary">
            Edit Opportunity
        </a>

    </div>

</div>


@if(session('success'))

<div class="alert success">
    {{ session('success') }}
</div>

@endif


@if(session('error'))

<div class="alert error">
    {{ session('error') }}
</div>

@endif


{{-- =========================================================
OPPORTUNITY HEADER
========================================================= --}}

<div class="card opportunity-header-card">

    <div class="opportunity-header">

        <div class="opportunity-title-area">

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

            <span class="status-badge stage">
                {{ $opportunity->stage }}
            </span>

            <span class="status-badge {{ strtolower($opportunity->status) }}">
                {{ $opportunity->status }}
            </span>

        </div>

    </div>

</div>


{{-- =========================================================
VIEW INFORMATION
========================================================= --}}

<div class="section-heading">

    <div>
        <h3>View Information</h3>

        <p>
            Detailed information about this opportunity.
        </p>
    </div>

</div>


<div class="information-grid">


    {{-- =====================================================
    CUSTOMER INFORMATION
    ====================================================== --}}

    <div class="card information-card">

        <button
            type="button"
            class="information-toggle"
            data-target="customerInformation">

            <div class="information-heading">

                <div class="information-icon">
                    C
                </div>

                <div>

                    <h3>
                        Customer Information
                    </h3>

                    <p>
                        Associated customer
                    </p>

                </div>

            </div>

            <span class="toggle-icon">
                +
            </span>

        </button>


        <div
            id="customerInformation"
            class="information-content">

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
    LEAD INFORMATION
    ====================================================== --}}

    <div class="card information-card">

        <button
            type="button"
            class="information-toggle"
            data-target="leadInformation">

            <div class="information-heading">

                <div class="information-icon">
                    L
                </div>

                <div>

                    <h3>
                        Lead Information
                    </h3>

                    <p>
                        Lead source
                    </p>

                </div>

            </div>

            <span class="toggle-icon">
                +
            </span>

        </button>


        <div
            id="leadInformation"
            class="information-content">

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

    <div class="card information-card">

        <button
            type="button"
            class="information-toggle"
            data-target="salesInformation">

            <div class="information-heading">

                <div class="information-icon">
                    S
                </div>

                <div>

                    <h3>
                        Sales Information
                    </h3>

                    <p>
                        Responsible sales representative
                    </p>

                </div>

            </div>

            <span class="toggle-icon">
                +
            </span>

        </button>


        <div
            id="salesInformation"
            class="information-content">

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
    OPPORTUNITY SUMMARY
    ====================================================== --}}

    <div class="card information-card">

        <button
            type="button"
            class="information-toggle"
            data-target="opportunitySummary">

            <div class="information-heading">

                <div class="information-icon">
                    $
                </div>

                <div>

                    <h3>
                        Opportunity Summary
                    </h3>

                    <p>
                        Value and closing information
                    </p>

                </div>

            </div>

            <span class="toggle-icon">
                +
            </span>

        </button>


        <div
            id="opportunitySummary"
            class="information-content">

            <div class="detail-row">

                <span class="detail-label">
                    Opportunity Code
                </span>

                <span class="detail-value">
                    {{ $opportunity->opportunity_code }}
                </span>

            </div>


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

<div class="card collapsible-card">

    <button
        type="button"
        class="information-toggle"
        data-target="descriptionInformation">

        <div class="information-heading">

            <div class="information-icon">
                D
            </div>

            <div>

                <h3>
                    Description
                </h3>

                <p>
                    Additional opportunity information
                </p>

            </div>

        </div>

        <span class="toggle-icon">
            +
        </span>

    </button>


    <div
        id="descriptionInformation"
        class="information-content">

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

<div class="card opportunity-items-card">

    <div class="card-head">

        <div>

            <h3>
                Opportunity Items
            </h3>

            <p>
                Products associated with this opportunity.
            </p>

        </div>


        <div class="items-header-actions">

            <span class="items-count">

                {{ $items->total() }}

                {{ $items->total() === 1 ? 'Item' : 'Items' }}

            </span>


            <button
                type="button"
                class="btn-show-all"
                onclick="openOpportunityItemsModal()">

                Show All

            </button>


            <a
                href="{{ route(
                    'opportunities.items.create',
                    $opportunity
                ) }}"
                class="btn-add-item">

                + Add Item

            </a>

        </div>

    </div>


    <div class="table-wrap">

        @if($items->count())

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

                        <th class="action-column">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach($items as $item)

                        <tr>

                            <td>

                                @if($item->product)

                                    <div class="product-name">
                                        {{ $item->product->product_name }}
                                    </div>

                                    @if($item->product->product_code)

                                        <div class="product-code">
                                            {{ $item->product->product_code }}
                                        </div>

                                    @endif

                                @else

                                    <span class="muted">
                                        Product unavailable
                                    </span>

                                @endif

                            </td>


                            <td>
                                {{ number_format((int) $item->quantity) }}
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

                                    <strong class="subtotal">

                                        Rp
                                        {{ number_format(
                                            (float) $item->estimated_price *
                                            (int) $item->quantity,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                    </strong>

                                @else

                                    -

                                @endif

                            </td>


                            <td class="notes-cell">

                                {{ $item->notes ?: '-' }}

                            </td>


                            <td class="action-column">

                                <div class="table-actions">

                                    <a
                                        href="{{ route(
                                            'opportunities.items.edit',
                                            [
                                                'opportunity' => $opportunity,
                                                'item' => $item
                                            ]
                                        ) }}"
                                        class="action-btn"
                                        title="Edit Opportunity Item">

                                        Edit

                                    </a>


                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'opportunities.items.destroy',
                                            [
                                                'opportunity' => $opportunity,
                                                'item' => $item
                                            ]
                                        ) }}"
                                        onsubmit="return confirm('Delete this opportunity item?');">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-btn"
                                            title="Delete Opportunity Item">

                                            Delete

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>


            {{-- =================================================
            PAGINATION
            ================================================== --}}

            @if($items->hasPages())

                <div class="items-pagination">

                    <div class="pagination-info">

                        Showing

                        <strong>
                            {{ $items->firstItem() }}
                        </strong>

                        to

                        <strong>
                            {{ $items->lastItem() }}
                        </strong>

                        of

                        <strong>
                            {{ $items->total() }}
                        </strong>

                        items

                    </div>


                    <div class="pagination-links">

                        {{ $items
                            ->onEachSide(1)
                            ->links()
                        }}

                    </div>

                </div>

            @endif

        @else

            <div class="empty items-empty">

                <strong>
                    No opportunity items yet.
                </strong>

                <span>
                    Add products to this opportunity using the
                    <strong>+ Add Item</strong>
                    button above.
                </span>

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
METADATA
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


{{-- =========================================================
OPPORTUNITY ITEMS MODAL
========================================================= --}}

<div
    id="opportunityItemsModal"
    class="items-modal">

    <div
        class="items-modal-overlay"
        onclick="closeOpportunityItemsModal()">
    </div>


    <div class="items-modal-dialog">

        <div class="items-modal-header">

            <div>

                <div class="items-modal-title">
                    Opportunity Items
                </div>

                <div class="items-modal-subtitle">
                    Manage all products associated with this opportunity.
                </div>

            </div>


            <button
                type="button"
                class="modal-close"
                onclick="closeOpportunityItemsModal()"
                aria-label="Close">

                ×

            </button>

        </div>


        {{-- SEARCH + FILTER --}}

        <div class="items-modal-toolbar">

            <div class="items-search">

                <span class="search-icon">
                    ⌕
                </span>

                <input
                    type="text"
                    id="opportunityItemSearch"
                    placeholder="Search product, code, or notes..."
                    autocomplete="off">

            </div>


            <div class="items-filter">

                <select
                    id="opportunityItemFilter">

                    <option value="all">
                        All Items
                    </option>

                    @foreach($items as $item)

                        @if($item->product)

                            <option
                                value="{{ strtolower($item->product->product_name) }}">

                                {{ $item->product->product_name }}

                            </option>

                        @endif

                    @endforeach

                </select>

            </div>


            <a
                href="{{ route(
                    'opportunities.items.create',
                    $opportunity
                ) }}"
                class="modal-add-item">

                + Add Item

            </a>

        </div>


        {{-- MODAL TABLE --}}

        <div class="modal-table-wrap">

            <table
                id="opportunityItemsTable">

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

                        <th class="action-column">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody id="opportunityItemsModalBody">

                    @foreach($items as $item)

                        <tr
                            data-product="{{ strtolower(
                                $item->product->product_name ?? ''
                            ) }}">

                            <td>

                                @if($item->product)

                                    <div class="product-name">
                                        {{ $item->product->product_name }}
                                    </div>

                                    @if($item->product->product_code)

                                        <div class="product-code">
                                            {{ $item->product->product_code }}
                                        </div>

                                    @endif

                                @else

                                    <span class="muted">
                                        Product unavailable
                                    </span>

                                @endif

                            </td>


                            <td>
                                {{ number_format((int) $item->quantity) }}
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

                                    <strong class="subtotal">

                                        Rp
                                        {{ number_format(
                                            (float) $item->estimated_price *
                                            (int) $item->quantity,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                    </strong>

                                @else

                                    -

                                @endif

                            </td>


                            <td class="notes-cell">

                                {{ $item->notes ?: '-' }}

                            </td>


                            <td class="action-column">

                                <div class="table-actions">

                                    <a
                                        href="{{ route(
                                            'opportunities.items.edit',
                                            [
                                                'opportunity' => $opportunity,
                                                'item' => $item
                                            ]
                                        ) }}"
                                        class="action-btn"
                                        title="Edit Opportunity Item">

                                        Edit

                                    </a>


                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'opportunities.items.destroy',
                                            [
                                                'opportunity' => $opportunity,
                                                'item' => $item
                                            ]
                                        ) }}"
                                        onsubmit="return confirm('Delete this opportunity item?');">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-btn"
                                            title="Delete Opportunity Item">

                                            Delete

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>


            <div
                id="itemsSearchEmpty"
                class="modal-search-empty">

                No items match your search.

            </div>

        </div>


        <div class="items-modal-footer">

            <span>
                {{ $items->total() }}
                {{ $items->total() === 1 ? 'Item' : 'Items' }}
                total
            </span>

        </div>

    </div>

</div>


<style>

/* =========================================================
SECTION HEADING
========================================================= */

.section-heading {
    margin: 0 0 12px;
}

.section-heading h3 {
    margin: 0;

    color: #17284f;

    font-size: 15px;
    font-weight: 700;
}

.section-heading p {
    margin: 4px 0 0;

    color: #8a94a6;

    font-size: 10px;
}


/* =========================================================
OPPORTUNITY HEADER
========================================================= */

.opportunity-header-card {
    margin-bottom: 24px;
}

.opportunity-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 24px;

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
STATUS
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
INFORMATION GRID
========================================================= */

.information-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 16px;

    margin-bottom: 20px;
}


/* =========================================================
INFORMATION CARD
========================================================= */

.information-card,
.collapsible-card {
    overflow: hidden;
}

.information-toggle {
    width: 100%;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 16px;

    padding: 18px 20px;

    background: transparent;

    border: none;

    cursor: pointer;

    text-align: left;
}

.information-toggle:hover {
    background: #fafbfd;
}

.information-heading {
    display: flex;
    align-items: center;

    gap: 12px;

    min-width: 0;
}

.information-icon {
    width: 38px;
    height: 38px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border-radius: 10px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 12px;
    font-weight: 800;
}

.information-heading h3 {
    margin: 0;

    color: #17284f;

    font-size: 13px;
    font-weight: 700;
}

.information-heading p {
    margin: 4px 0 0;

    color: #8a94a6;

    font-size: 10px;
}


/* =========================================================
TOGGLE
========================================================= */

.toggle-icon {
    width: 26px;
    height: 26px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border-radius: 7px;

    background: #f1f4f8;

    color: #687284;

    font-size: 17px;
    font-weight: 500;

    transition:
        transform .2s ease,
        background .2s ease;
}

.information-toggle.expanded .toggle-icon {
    background: #eaf7f3;

    color: #167d70;

    transform: rotate(45deg);
}


/* =========================================================
INFORMATION CONTENT
========================================================= */

.information-content {
    display: none;

    padding: 0 20px 20px;

    border-top: 1px solid #edf0f4;
}

.information-content.expanded {
    display: block;
}


/* =========================================================
DETAIL ROW
========================================================= */

.detail-row {
    display: flex;

    align-items: flex-start;
    justify-content: space-between;

    gap: 20px;

    padding: 11px 0;

    border-bottom: 1px solid #edf0f4;
}

.information-content .detail-row:first-child {
    padding-top: 16px;
}

.detail-row:last-child {
    border-bottom: none;

    padding-bottom: 0;
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

.collapsible-card {
    margin-bottom: 20px;
}

.description-content {
    padding-top: 16px;

    color: #46536a;

    font-size: 13px;

    line-height: 1.7;
}


/* =========================================================
OPPORTUNITY ITEMS
========================================================= */

.opportunity-items-card {
    margin-bottom: 20px;
}

.items-header-actions {
    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 8px;

    flex: 0 0 auto;
}

.items-count {
    display: inline-flex;

    align-items: center;

    min-height: 28px;

    padding: 0 9px;

    border-radius: 999px;

    background: #f1f4f8;

    color: #687284;

    font-size: 10px;
    font-weight: 600;

    white-space: nowrap;
}


/* =========================================================
SHOW ALL BUTTON
========================================================= */

.btn-show-all {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    min-height: 28px;

    padding: 5px 10px;

    border: 1px solid #d8e8e2;

    border-radius: 6px;

    background: #f5fbf8;

    color: #15966f;

    font-family: inherit;

    font-size: 9px;
    font-weight: 600;

    cursor: pointer;

    transition:
        background .15s ease,
        border-color .15s ease;
}

.btn-show-all:hover {
    background: #eaf7f3;

    border-color: #c6ded5;
}


/* =========================================================
ADD ITEM BUTTON
========================================================= */

.btn-add-item {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    min-height: 28px;

    padding: 5px 10px;

    border: 1px solid #15966f;

    border-radius: 6px;

    background: #15966f;

    color: #ffffff;

    font-family: inherit;

    font-size: 9px;
    font-weight: 600;

    text-decoration: none;

    white-space: nowrap;

    transition:
        background .15s ease,
        border-color .15s ease;
}

.btn-add-item:hover {
    background: #127f5d;

    border-color: #127f5d;

    color: #ffffff;
}


/* =========================================================
TABLE
========================================================= */

.table-wrap {
    width: 100%;

    overflow-x: auto;
}

.table-wrap table {
    width: 100%;

    min-width: 900px;

    border-collapse: collapse;

    font-size: 12px;
}

.table-wrap th,
.table-wrap td {
    text-align: left;

    white-space: nowrap;
}

.table-wrap th {
    color: #667085;

    font-size: 10px;
    font-weight: 700;
}

.table-wrap td {
    color: #526078;

    vertical-align: top;
}

.action-column {
    text-align: right !important;
}


/* =========================================================
PRODUCT
========================================================= */

.product-name {
    color: #17284f;

    font-size: 12px;
    font-weight: 600;
}

.product-code {
    margin-top: 3px;

    color: #8a94a6;

    font-size: 10px;
    font-weight: 500;
}

.notes-cell {
    max-width: 220px;

    color: #46536a;

    line-height: 1.5;

    white-space: normal !important;

    word-break: break-word;
}

.subtotal {
    color: #15966f;

    font-weight: 700;
}

.muted {
    color: #8a94a6;
}


/* =========================================================
TABLE ACTIONS
========================================================= */

.table-actions {
    display: flex;

    align-items: center;
    justify-content: flex-end;

    gap: 6px;

    white-space: nowrap;
}

.table-actions form {
    margin: 0;
}

.action-btn {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    min-width: 48px;

    height: 29px;

    padding: 0 9px;

    border: 1px solid #e2e7ef;

    border-radius: 6px;

    background: #ffffff;

    color: #667085;

    font-family: inherit;

    font-size: 10px;
    font-weight: 600;

    text-decoration: none;

    cursor: pointer;

    transition:
        background .15s ease,
        color .15s ease,
        border-color .15s ease;
}

.action-btn:hover {
    background: #f8fafc;

    border-color: #d4dae3;

    color: #17284f;
}


/* =========================================================
EMPTY
========================================================= */

.items-empty {
    display: flex;

    align-items: center;
    justify-content: center;

    flex-direction: column;

    gap: 5px;

    min-height: 120px;

    text-align: center;

    color: #8a94a6;

    font-size: 11px;
}

.items-empty strong {
    color: #46536a;
}


/* =========================================================
PAGINATION
========================================================= */

.items-pagination {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 20px;

    padding: 16px 20px;

    border-top: 1px solid #edf0f4;
}

.pagination-info {
    color: #8a94a6;

    font-size: 10px;

    white-space: nowrap;
}

.pagination-info strong {
    color: #46536a;
}

.pagination-links nav {
    display: flex;
}

.pagination-links nav > div:first-child {
    display: none;
}

.pagination-links nav > div:last-child {
    display: flex;
}

.pagination-links nav span,
.pagination-links nav a {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    min-width: 28px;
    height: 28px;

    padding: 0 7px;

    border: 1px solid #e6eaf0;

    background: #ffffff;

    color: #687284;

    font-size: 10px;

    text-decoration: none;
}

.pagination-links nav a:hover {
    background: #f5f7fb;

    color: #17284f;
}

.pagination-links nav span[aria-current="page"] {
    background: #15966f;

    border-color: #15966f;

    color: #ffffff;
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
MODAL
========================================================= */

.items-modal {
    position: fixed;

    inset: 0;

    z-index: 9999;

    display: none;
}

.items-modal.open {
    display: flex;

    align-items: center;
    justify-content: center;
}

.items-modal-overlay {
    position: absolute;

    inset: 0;

    background: rgba(15, 23, 42, .42);

    backdrop-filter: blur(2px);
}

.items-modal-dialog {
    position: relative;

    z-index: 2;

    width: min(1180px, calc(100vw - 40px));

    max-height: calc(100vh - 60px);

    display: flex;

    flex-direction: column;

    overflow: hidden;

    background: #ffffff;

    border: 1px solid #e4e8ef;

    border-radius: 12px;

    box-shadow:
        0 20px 60px rgba(23, 40, 79, .18);
}


/* =========================================================
MODAL HEADER
========================================================= */

.items-modal-header {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 20px;

    padding: 20px 22px;

    border-bottom: 1px solid #edf0f4;
}

.items-modal-title {
    color: #17284f;

    font-size: 15px;
    font-weight: 700;
}

.items-modal-subtitle {
    margin-top: 4px;

    color: #8a94a6;

    font-size: 10px;
}

.modal-close {
    width: 30px;
    height: 30px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border: 1px solid #e2e7ef;

    border-radius: 7px;

    background: #ffffff;

    color: #667085;

    font-size: 19px;

    line-height: 1;

    cursor: pointer;

    transition: .15s ease;
}

.modal-close:hover {
    background: #f5f7fb;

    color: #17284f;
}


/* =========================================================
MODAL TOOLBAR
========================================================= */

.items-modal-toolbar {
    display: flex;

    align-items: center;

    gap: 8px;

    padding: 14px 22px;

    background: #fafbfd;

    border-bottom: 1px solid #edf0f4;
}

.items-search {
    position: relative;

    flex: 1;

    min-width: 220px;
}

.search-icon {
    position: absolute;

    left: 11px;
    top: 50%;

    transform: translateY(-50%);

    color: #8a94a6;

    font-size: 15px;

    pointer-events: none;
}

.items-search input,
.items-filter select {
    width: 100%;

    height: 34px;

    padding: 0 11px;

    border: 1px solid #e1e6ed;

    border-radius: 7px;

    background: #ffffff;

    color: #46536a;

    font-family: inherit;

    font-size: 11px;

    outline: none;
}

.items-search input {
    padding-left: 32px;
}

.items-search input:focus,
.items-filter select:focus {
    border-color: #b8d9ce;

    box-shadow:
        0 0 0 3px rgba(21, 150, 111, .07);
}

.items-filter {
    width: 200px;
    flex-shrink: 0;
}

.modal-add-item {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    height: 34px;

    padding: 0 12px;

    border: 1px solid #15966f;

    border-radius: 7px;

    background: #15966f;

    color: #ffffff;

    font-size: 10px;
    font-weight: 600;

    text-decoration: none;

    white-space: nowrap;
}

.modal-add-item:hover {
    background: #127f5d;

    color: #ffffff;
}


/* =========================================================
MODAL TABLE
========================================================= */

.modal-table-wrap {
    flex: 1;

    overflow: auto;
}

.modal-table-wrap table {
    width: 100%;

    min-width: 900px;

    border-collapse: collapse;

    font-size: 12px;
}

.modal-table-wrap th {
    position: sticky;

    top: 0;

    z-index: 2;

    padding: 12px 14px;

    background: #f8fafc;

    border-bottom: 1px solid #e5e9f0;

    color: #667085;

    font-size: 10px;

    font-weight: 700;

    text-align: left;

    white-space: nowrap;
}

.modal-table-wrap td {
    padding: 13px 14px;

    border-bottom: 1px solid #edf0f4;

    color: #526078;

    vertical-align: top;
}

.modal-table-wrap tr:last-child td {
    border-bottom: none;
}

.modal-table-wrap .action-column {
    text-align: right;
}


/* =========================================================
MODAL SEARCH EMPTY
========================================================= */

.modal-search-empty {
    display: none;

    padding: 50px 20px;

    text-align: center;

    color: #8a94a6;

    font-size: 11px;
}

.modal-search-empty.show {
    display: block;
}


/* =========================================================
MODAL FOOTER
========================================================= */

.items-modal-footer {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 20px;

    padding: 13px 22px;

    border-top: 1px solid #edf0f4;

    color: #8a94a6;

    font-size: 10px;
}

.modal-footer-close {
    height: 30px;

    padding: 0 12px;

    border: 1px solid #e2e7ef;

    border-radius: 6px;

    background: #ffffff;

    color: #667085;

    font-family: inherit;

    font-size: 10px;
    font-weight: 600;

    cursor: pointer;
}

.modal-footer-close:hover {
    background: #f5f7fb;

    color: #17284f;
}


/* =========================================================
RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .information-grid {
        grid-template-columns: 1fr;
    }

    .items-modal-dialog {
        width: calc(100vw - 24px);

        max-height: calc(100vh - 24px);
    }

}


@media (max-width: 700px) {

    .opportunity-header {
        align-items: flex-start;

        flex-direction: column;
    }

    .header-statuses {
        width: 100%;
    }

    .items-header-actions {
        flex-wrap: wrap;

        justify-content: flex-end;
    }

    .items-pagination {
        align-items: flex-start;

        flex-direction: column;
    }

    .items-modal-toolbar {
        align-items: stretch;

        flex-direction: column;
    }

    .items-search,
    .items-filter {
        width: 100%;
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

    .items-header-actions {
        width: 100%;

        justify-content: flex-start;
    }

}


@media (max-width: 450px) {

    .information-toggle {
        padding: 16px;
    }

    .information-content {
        padding-left: 16px;
        padding-right: 16px;
    }

    .information-heading p {
        display: none;
    }

    .items-header-actions {
        align-items: stretch;

        flex-direction: column;
    }

    .items-count,
    .btn-show-all,
    .btn-add-item {
        width: 100%;

        justify-content: center;
    }

}

</style>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        /* =====================================================
        INFORMATION TOGGLE

        Jika satu card dibuka:
        -> semua card informasi dibuka.

        Jika satu card ditutup:
        -> semua card informasi ditutup.
        ===================================================== */

        const informationToggles =
            document.querySelectorAll(
                '.information-toggle'
            );


        informationToggles.forEach(
            function (toggle) {

                toggle.addEventListener(
                    'click',
                    function () {

                        const targetId =
                            toggle.dataset.target;

                        const target =
                            document.getElementById(
                                targetId
                            );


                        if (!target) {
                            return;
                        }


                        const isExpanded =
                            target.classList.contains(
                                'expanded'
                            );


                        const allContents =
                            document.querySelectorAll(
                                '.information-content'
                            );


                        const allToggles =
                            document.querySelectorAll(
                                '.information-toggle'
                            );


                        if (isExpanded) {

                            /*
                            CLOSE ALL
                            */

                            allContents.forEach(
                                function (content) {

                                    content.classList.remove(
                                        'expanded'
                                    );

                                }
                            );


                            allToggles.forEach(
                                function (button) {

                                    button.classList.remove(
                                        'expanded'
                                    );

                                }
                            );

                        } else {

                            /*
                            OPEN ALL
                            */

                            allContents.forEach(
                                function (content) {

                                    content.classList.add(
                                        'expanded'
                                    );

                                }
                            );


                            allToggles.forEach(
                                function (button) {

                                    button.classList.add(
                                        'expanded'
                                    );

                                }
                            );

                        }

                    }
                );

            }
        );


        /* =====================================================
        OPPORTUNITY ITEMS MODAL
        ===================================================== */

        const modal =
            document.getElementById(
                'opportunityItemsModal'
            );


        const searchInput =
            document.getElementById(
                'opportunityItemSearch'
            );


        const filterInput =
            document.getElementById(
                'opportunityItemFilter'
            );


        const tableBody =
            document.getElementById(
                'opportunityItemsModalBody'
            );


        const searchEmpty =
            document.getElementById(
                'itemsSearchEmpty'
            );


        /*
        ---------------------------------------------------------
        OPEN MODAL
        ---------------------------------------------------------
        */

        window.openOpportunityItemsModal =
            function () {

                if (!modal) {
                    return;
                }


                modal.classList.add(
                    'open'
                );


                document.body.style.overflow =
                    'hidden';


                setTimeout(
                    function () {

                        if (searchInput) {

                            searchInput.focus();

                        }

                    },
                    100
                );

            };


        /*
        ---------------------------------------------------------
        CLOSE MODAL
        ---------------------------------------------------------
        */

        window.closeOpportunityItemsModal =
            function () {

                if (!modal) {
                    return;
                }


                modal.classList.remove(
                    'open'
                );


                document.body.style.overflow =
                    '';

            };


        /*
        ---------------------------------------------------------
        ESC KEY
        ---------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Escape' &&
                    modal &&
                    modal.classList.contains('open')
                ) {

                    closeOpportunityItemsModal();

                }

            }
        );


        /*
        ---------------------------------------------------------
        SEARCH + FILTER
        ---------------------------------------------------------
        */

        function filterOpportunityItems() {

            if (!tableBody) {
                return;
            }


            const search =
                (
                    searchInput?.value || ''
                )
                .toLowerCase()
                .trim();


            const filter =
                (
                    filterInput?.value || 'all'
                )
                .toLowerCase();


            const rows =
                tableBody.querySelectorAll(
                    'tr'
                );


            let visibleCount = 0;


            rows.forEach(
                function (row) {

                    const rowText =
                        row.textContent
                            .toLowerCase();


                    const product =
                        (
                            row.dataset.product || ''
                        )
                        .toLowerCase();


                    const matchesSearch =
                        !search ||
                        rowText.includes(
                            search
                        );


                    const matchesFilter =
                        filter === 'all' ||
                        product === filter;


                    if (
                        matchesSearch &&
                        matchesFilter
                    ) {

                        row.style.display =
                            '';

                        visibleCount++;

                    } else {

                        row.style.display =
                            'none';

                    }

                }
            );


            if (searchEmpty) {

                if (visibleCount === 0) {

                    searchEmpty.classList.add(
                        'show'
                    );

                } else {

                    searchEmpty.classList.remove(
                        'show'
                    );

                }

            }

        }


        if (searchInput) {

            searchInput.addEventListener(
                'input',
                filterOpportunityItems
            );

        }


        if (filterInput) {

            filterInput.addEventListener(
                'change',
                filterOpportunityItems
            );

        }


        /*
        ---------------------------------------------------------
        CLICK OUTSIDE MODAL
        ---------------------------------------------------------
        */

        if (modal) {

            modal.addEventListener(
                'click',
                function (event) {

                    if (
                        event.target === modal
                    ) {

                        closeOpportunityItemsModal();

                    }

                }
            );

        }

    }
);

</script>

@endsection