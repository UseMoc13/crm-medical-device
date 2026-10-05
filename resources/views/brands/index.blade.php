@extends('layouts.app')

@section('title', 'Brands')

@section('content')

<div class="page-head">

    <div>

        <h1>Brands</h1>

        <p>
            Manage medical device brands used to organize product information.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('brands.create') }}"
            class="btn primary">
            + New Brand
        </a>

    </div>

</div>


{{-- =========================================================
     ALERT
========================================================= --}}

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


@if($errors->any())

<div class="alert error">

    <strong>Please fix the following errors:</strong>

    <ul>

        @foreach($errors->all() as $error)

        <li>
            {{ $error }}
        </li>

        @endforeach

    </ul>

</div>

@endif


{{-- =========================================================
     BRAND LIST
========================================================= --}}

<div class="card">

    <div class="card-head">

        <div>

            <h3>Brand List</h3>

            <p>
                {{ $brands->total() }} brands found
            </p>

        </div>

    </div>


    <div class="card-body">


        {{-- =====================================================
             SEARCH
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('brands.index') }}"
            class="brand-filter-bar">

            <div class="brand-search">

                <span class="product-search-icon">
                    ⌕
                </span>


                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search brands..."
                    autocomplete="off">


                @if(request('search'))

                <a
                    href="{{ route(
                            'brands.index',
                            request()->except(
                                'search',
                                'page'
                            )
                        ) }}"
                    class="product-search-clear"
                    title="Clear search">
                    ×
                </a>

                @endif

            </div>


            <button
                type="submit"
                class="btn brand-filter-button">
                Search
            </button>


            @if(request('search'))

            <a
                href="{{ route('brands.index') }}"
                class="btn brand-reset-button">
                Reset
            </a>

            @endif

        </form>


        {{-- =====================================================
             ACTIVE FILTER
        ====================================================== --}}

        @if(request('search'))

        <div class="brand-filter-summary">

            <span>
                Showing filtered results
            </span>


            <span class="filter-chip">

                Search:
                "{{ request('search') }}"

            </span>

        </div>

        @endif


        {{-- =====================================================
             BRAND TABLE
        ====================================================== --}}

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            Brand
                        </th>

                        <th>
                            Description
                        </th>

                        <th>
                            Products
                        </th>

                        <th>
                            Created
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($brands as $brand)

                    <tr>


                        {{-- Brand --}}

                        <td>

                            <a
                                href="#"
                                class="brand-name-link">

                                <strong>
                                    {{ $brand->brand_name }}
                                </strong>

                            </a>


                            <div class="muted">

                                ID:
                                {{ $brand->brand_id }}

                            </div>

                        </td>


                        {{-- Description --}}

                        <td>

                            @if($brand->description)

                            <div class="brand-description">

                                {{ $brand->description }}

                            </div>

                            @else

                            <span class="muted">
                                -
                            </span>

                            @endif

                        </td>


                        {{-- Products --}}

                        <td>

                            <span class="brand-product-badge">

                                {{ $brand->products()->count() }}

                                product{{ $brand->products()->count() != 1 ? 's' : '' }}

                            </span>

                        </td>


                        {{-- Created --}}

                        <td>

                            @if($brand->created_at)

                            <div>

                                {{ $brand->created_at->format('d M Y') }}

                            </div>


                            <div class="muted">

                                {{ $brand->created_at->format('H:i') }}

                            </div>

                            @else

                            <span class="muted">
                                -
                            </span>

                            @endif

                        </td>


                        {{-- Actions --}}

                        <td>

                            <div class="table-actions">

                                <a
                                    href="{{ route('brands.show', $brand) }}"
                                    class="table-action"
                                    title="View Brand">
                                    View
                                </a>

                                <a
                                    href="{{ route('brands.edit', $brand) }}"
                                    class="table-action"
                                    title="Edit Brand">
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('brands.destroy', $brand) }}"
                                    class="delete-form">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="table-action danger"
                                        title="Delete Brand">
                                        Delete
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>


                    @empty

                    <tr>

                        <td colspan="5">

                            <div class="empty">

                                No brands found.

                            </div>

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =====================================================
             PAGINATION
        ====================================================== --}}

        @if($brands->hasPages())

        <div class="pagination">

            {{ $brands->links() }}

        </div>

        @endif

    </div>

</div>


<style>
    /* =========================================================
   BRAND FILTER BAR
========================================================= */

    .brand-filter-bar {

        display: flex;

        align-items: center;

        gap: 10px;

        flex-wrap: wrap;

        margin-bottom: 18px;

    }


    /* =========================================================
   SEARCH
========================================================= */

    .brand-search {

        position: relative;

        flex: 1 1 260px;

        min-width: 220px;

    }

    .brand-search input {

        width: 100%;

        height: 40px;

        box-sizing: border-box;

        padding: 0 38px 0 38px;

        border: 1px solid #d9dee8;

        border-radius: 8px;

        background: #fff;

        color: #17284f;

        font-size: 13px;

        transition:
            border-color .18s ease,
            box-shadow .18s ease;

    }

    .brand-search input:focus {

        outline: none;

        border-color: #2ba7a0;

        box-shadow:
            0 0 0 3px rgba(43, 167, 160, .08);

    }

    .product-search-icon {

        position: absolute;

        left: 13px;

        top: 50%;

        transform: translateY(-50%);

        color: #7d8797;

        font-size: 19px;

        pointer-events: none;

    }

    .product-search-clear {

        position: absolute;

        right: 10px;

        top: 50%;

        transform: translateY(-50%);

        width: 22px;

        height: 22px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 50%;

        color: #7d8797;

        text-decoration: none;

        font-size: 18px;

    }

    .product-search-clear:hover {

        background: #edf1f5;

        color: #17284f;

    }


    /* =========================================================
   FILTER BUTTONS
========================================================= */

    .brand-filter-button {

        height: 40px;

        white-space: nowrap;

    }

    .brand-reset-button {

        height: 40px;

        display: inline-flex;

        align-items: center;

        white-space: nowrap;

    }


    /* =========================================================
   FILTER SUMMARY
========================================================= */

    .brand-filter-summary {

        display: flex;

        align-items: center;

        gap: 7px;

        flex-wrap: wrap;

        margin-bottom: 15px;

        font-size: 12px;

        color: #7d8797;

    }

    .filter-chip {

        padding: 5px 9px;

        border-radius: 6px;

        background: #f1f5f7;

        color: #34415c;

        font-size: 11px;

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

        min-width: 950px;

        border-collapse: collapse;

    }

    .table-wrap th {

        padding: 13px 14px;

        border-bottom: 1px solid #e7ebf1;

        color: #718096;

        font-size: 11px;

        font-weight: 700;

        text-align: left;

        white-space: nowrap;

    }

    .table-wrap td {

        padding: 15px 14px;

        border-bottom: 1px solid #edf0f4;

        color: #34415c;

        font-size: 13px;

        vertical-align: middle;

    }

    .table-wrap tbody tr:hover {

        background: #fafbfd;

    }


    /* =========================================================
   BRAND NAME
========================================================= */

    .brand-name-link {

        color: inherit;

        text-decoration: none;

    }

    .brand-name-link:hover {

        color: #223a70;

    }

    .brand-name-link strong {

        color: #17284f;

        font-size: 13px;

    }


    /* =========================================================
   DESCRIPTION
========================================================= */

    .brand-description {

        max-width: 330px;

        color: #4d5b70;

        font-size: 13px;

        line-height: 1.5;

    }


    /* =========================================================
   MUTED
========================================================= */

    .muted {

        color: #8a94a6;

        font-size: 12px;

    }


    /* =========================================================
   PRODUCT COUNT
========================================================= */

    .brand-product-badge {

        display: inline-flex;

        align-items: center;

        padding: 4px 9px;

        border-radius: 6px;

        background: #f1f5f7;

        color: #34415c;

        font-size: 11px;

        font-weight: 600;

        white-space: nowrap;

    }


    /* =========================================================
   ACTIONS
========================================================= */

    .table-actions {

        display: flex;

        align-items: center;

        gap: 6px;

    }

    .action-btn {

        width: 30px;

        height: 30px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        padding: 0;

        border: 1px solid #dfe4eb;

        border-radius: 7px;

        background: #fff;

        color: #4d5b70;

        font-size: 13px;

        line-height: 1;

        text-decoration: none;

        cursor: pointer;

        transition:
            background .18s ease,
            border-color .18s ease,
            color .18s ease;

    }

    .action-btn:hover {

        background: #f4f7f9;

        border-color: #cbd3df;

        color: #17284f;

    }

    .action-btn.delete:hover {

        background: #fff0f0;

        border-color: #e6b7b7;

        color: #c94a4a;

    }


    /* =========================================================
   EMPTY
========================================================= */

    .empty {

        padding: 55px 20px;

        text-align: center;

        color: #718096;

        font-size: 13px;

    }


    /* =========================================================
   RESPONSIVE
========================================================= */

    @media (max-width: 700px) {

        .brand-filter-bar {

            flex-direction: column;

            align-items: stretch;

        }

        .brand-search,
        .brand-filter-button,
        .brand-reset-button {

            width: 100%;

        }

        .brand-filter-button,
        .brand-reset-button {

            justify-content: center;

        }

    }
</style>

@endsection