@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Sales Orders</h1>
            <p class="text-muted">
                Daftar pesanan pelanggan.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" class="row g-2 align-items-center mb-3">
        <div class="col">
            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Cari nomor SO, quotation, atau status"
                value="{{ request('search') }}"
            >
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Semua status</option>

                @foreach ([
                    'draft',
                    'confirmed',
                    'processing',
                    'completed',
                    'cancelled',
                ] as $status)
                    <option
                        value="{{ $status }}"
                        @selected(request('status') === $status)
                    >
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn primary">
                Cari
            </button>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nomor Sales Order</th>
                        <th>Quotation</th>
                        <th>Customer</th>
                        <th>Tanggal Order</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                {{ $order->sales_order_number }}
                            </td>

                            <td>
                                {{ $order->quotation->quotation_number ?? '-' }}
                            </td>

                            <td>
                                {{ $order->quotation->opportunity->customer->customer_name ?? '-' }}
                            </td>

                            <td>
                                {{ $order->order_date?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td>
                                Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                            </td>

                            <td>
                                <span class="badge bg-secondary">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                Belum ada Sales Order.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $orders->links() }}
    </div>

</div>
@endsection
