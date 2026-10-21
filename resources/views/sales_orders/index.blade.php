@extends('layouts.app')

@section('title', 'Sales Orders')

@section('content')

<div class="page-head">
    <div>
        <h1>Sales Orders</h1>
        <p>Manage customer orders created from approved quotations.</p>
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

<div class="card">
    <div class="card-head">
        <div>
            <h3>Sales Order List</h3>
            <p>{{ $orders->total() }} sales orders found</p>
        </div>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('sales_orders.index') }}" class="sales-order-filter-bar">
            <div class="sales-order-search">
                <span class="sales-order-search-icon" aria-hidden="true">⌕</span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search sales orders..."
                    autocomplete="off"
                    aria-label="Search sales orders"
                >
                @if(request('search'))
                    <a
                        href="{{ route('sales_orders.index', request()->except('search', 'page')) }}"
                        class="sales-order-search-clear"
                        title="Clear search"
                        aria-label="Clear search"
                    >×</a>
                @endif
            </div>

            <div class="sales-order-filter-select">
                <select name="status" aria-label="Filter by status">
                    <option value="">All Statuses</option>
                    @foreach(['draft', 'confirmed', 'processing', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn primary sales-order-filter-button">Filter</button>

            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('sales_orders.index') }}" class="btn sales-order-reset-button">Reset</a>
            @endif
        </form>

        @if(request('search') || request('status'))
            <div class="sales-order-filter-summary">
                <span>Showing filtered results</span>
                @if(request('search'))
                    <span class="sales-order-filter-chip">Search: “{{ request('search') }}”</span>
                @endif
                @if(request('status'))
                    <span class="sales-order-filter-chip">Status: {{ ucfirst(request('status')) }}</span>
                @endif
            </div>
        @endif

        <div class="table-wrap">
            <table class="sales-order-table">
                <thead>
                    <tr>
                        <th>Sales Order</th>
                        <th>Quotation</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                    </tr>