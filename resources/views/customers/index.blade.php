@extends('layouts.app')

@section('title', 'Customers')

@section('content')

<div class="page-head">

    <div>
        <h1>Customers</h1>

        <p>
            Manage customer information and relationships.
        </p>
    </div>

    <div class="actions">

        <a href="{{ route('customers.create') }}" class="btn primary">
            + New Customer
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
            <h3>Customer List</h3>

            <p>
                {{ $customers->total() }} customers found
            </p>
        </div>

    </div>


    <div class="card-body">

        <form
            method="GET"
            action="{{ route('customers.index') }}"
            class="filter-bar">

            <div class="search-box">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search customer...">

            </div>


            <select name="customer_type">

                <option value="">
                    All Types
                </option>

                <option
                    value="Hospital"
                    {{ request('customer_type') === 'Hospital' ? 'selected' : '' }}>
                    Hospital
                </option>

                <option
                    value="Clinic"
                    {{ request('customer_type') === 'Clinic' ? 'selected' : '' }}>
                    Clinic
                </option>

                <option
                    value="Laboratory"
                    {{ request('customer_type') === 'Laboratory' ? 'selected' : '' }}>
                    Laboratory
                </option>

                <option
                    value="Distributor"
                    {{ request('customer_type') === 'Distributor' ? 'selected' : '' }}>
                    Distributor
                </option>

                <option
                    value="Other"
                    {{ request('customer_type') === 'Other' ? 'selected' : '' }}>
                    Other
                </option>

            </select>


            <select name="status">

                <option value="">
                    All Status
                </option>

                <option
                    value="active"
                    {{ request('status') === 'active' ? 'selected' : '' }}>
                    Active
                </option>

                <option
                    value="inactive"
                    {{ request('status') === 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>

            </select>


            <button class="btn" type="submit">
                Filter
            </button>


            @if(request()->hasAny(['search', 'status', 'customer_type']))

            <a
                href="{{ route('customers.index') }}"
                class="btn">
                Reset
            </a>

            @endif

        </form>


        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>Code</th>

                        <th>Customer</th>

                        <th>Type</th>

                        <th>Phone</th>

                        <th>Location</th>

                        <th>Status</th>

                        <th>Created</th>

                        <th></th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($customers as $customer)

                    <tr>

                        <td>

                            <strong>
                                {{ $customer->customer_code }}
                            </strong>

                        </td>


                        <td>

                            <a
                                href="{{ route('customers.show', $customer) }}">
                                <strong>
                                    {{ $customer->customer_name }}
                                </strong>
                            </a>

                            @if($customer->email)

                            <div class="muted">
                                {{ $customer->email }}
                            </div>

                            @endif

                        </td>


                        <td>
                            {{ $customer->customer_type ?? '-' }}
                        </td>


                        <td>
                            {{ $customer->phone ?? '-' }}
                        </td>


                        <td>

                            @if($customer->city || $customer->province)

                            {{ $customer->city }}

                            @if($customer->province)
                            , {{ $customer->province }}
                            @endif

                            @else

                            -

                            @endif

                        </td>


                        <td>

                            <span class="status-badge">

                                {{ ucfirst($customer->status) }}

                            </span>

                        </td>


                        <td>

                            {{ $customer->created_at?->format('d M Y') }}

                        </td>


                        <td>

                            <div class="table-actions">

                                <a
                                    href="{{ route('customers.show', $customer) }}"
                                    class="action-btn view"
                                    title="View Customer"
                                    aria-label="View Customer">
                                    👁
                                </a>


                                <a
                                    href="{{ route('customers.edit', $customer) }}"
                                    class="action-btn edit"
                                    title="Edit Customer"
                                    aria-label="Edit Customer">
                                    ✎
                                </a>


                                <form
                                    action="{{ route('customers.destroy', $customer) }}"
                                    method="POST"
                                    class="delete-form"
                                    onsubmit="return confirmDeleteCustomer('{{ addslashes($customer->customer_name) }}')">

                                    @csrf

                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="action-btn delete"
                                        title="Delete Customer"
                                        aria-label="Delete Customer">
                                        🗑
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="8">

                            <div class="empty">

                                No customers found.

                            </div>

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        @if($customers->hasPages())

        <div class="pagination">

            {{ $customers->links() }}

        </div>

        @endif

    </div>

</div>

@endsection

<script>

function confirmDeleteCustomer(customerName)
{
    return confirm(
        'Delete Customer\n\n' +
        'Are you sure you want to delete "' +
        customerName +
        '"?\n\n' +
        'This action cannot be undone.'
    );
}

</script>