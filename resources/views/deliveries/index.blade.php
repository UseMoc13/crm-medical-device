@extends('layouts.app')

@section('title', 'Deliveries')

@section('content')

{{-- PAGE HEADER --}}
<div class="page-head">
    <div>
        <h1>Deliveries</h1>
        <p>Manage product deliveries and track delivery status.</p>
    </div>

    <div class="actions">
        <a href="{{ route('deliveries.create') }}" class="btn primary">
            + Add Delivery
        </a>
    </div>
</div>

{{-- DELIVERY TABLE --}}
<div class="card">
    <div class="card-head">
        <div>
            <h3>Delivery List</h3>
            <p>Manage and monitor product deliveries.</p>
        </div>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('deliveries.index') }}" class="delivery-filter-bar">
            <div class="delivery-filter-search">
                <input name="search" type="search" value="{{ request('search') }}" placeholder="Search delivery, sales order, address..." aria-label="Search deliveries">
            </div>
            <select name="status" aria-label="Filter delivery status">
                <option value="">All Statuses</option>
                @foreach (['pending' => 'Pending', 'in_transit' => 'In Transit', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="sort" aria-label="Sort deliveries by">
                <option value="created_at" @selected(request('sort', 'created_at') === 'created_at')>Created Date</option>
                <option value="delivery_date" @selected(request('sort') === 'delivery_date')>Delivery Date</option>
                <option value="delivery_number" @selected(request('sort') === 'delivery_number')>Delivery Number</option>
                <option value="status" @selected(request('sort') === 'status')>Status</option>
            </select>
            <select name="direction" aria-label="Sort direction">
                <option value="desc" @selected(request('direction', 'desc') === 'desc')>Descending</option>
                <option value="asc" @selected(request('direction') === 'asc')>Ascending</option>
            </select>
            <button class="btn" type="submit">Filter</button>
            @if (request()->filled('search') || request()->filled('status') || request()->filled('sort') || request()->filled('direction'))
                <a href="{{ route('deliveries.index') }}" class="btn">Reset</a>
            @endif
        </form>

        <div class="table-wrap delivery-table-wrap">
            <table class="delivery-table">
                <thead>
                    <tr>
                        <th class="col-number">No.</th>
                        <th class="col-delivery">Delivery Number</th>
                        <th class="col-order">Sales Order</th>
                        <th class="col-date">Delivery Date</th>
                        <th class="col-address">Delivery Address</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($deliveries as $delivery)

                        @php
                            $status = strtolower(
                                str_replace(
                                    [' ', '-'],
                                    '_',
                                    trim($delivery->status ?? '')
                                )
                            );

                            $statusLabels = [
                                'pending' => 'Pending',
                                'in_transit' => 'In Transit',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ];

                            $statusClasses = [
                                'pending' => 'warning',
                                'in_transit' => 'info',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                            ];

                            $statusLabel = $statusLabels[$status]
                                ?? ucwords(str_replace('_', ' ', $status));

                            $statusClass = $statusClasses[$status]
                                ?? 'secondary';
                        @endphp

                        <tr>
                            {{-- NUMBER --}}
                            <td>
                                {{ $deliveries->firstItem() + $loop->index }}
                            </td>

                            {{-- DELIVERY NUMBER --}}
                            <td>
                                <span class="delivery-number">
                                    {{ $delivery->delivery_number }}
                                </span>
                            </td>

                            {{-- SALES ORDER --}}
                            <td>
                                <a class="delivery-origin-link" href="{{ route('sales-orders.show', $delivery->sales_order_id) }}">
                                    {{ $delivery->salesOrder->sales_order_number ?? '—' }}
                                </a>
                            </td>

                            {{-- DELIVERY DATE --}}
                            <td>
                                {{ $delivery->delivery_date
                                    ? $delivery->delivery_date->format('d M Y')
                                    : '—' }}
                            </td>

                            {{-- DELIVERY ADDRESS --}}
                            <td>
                                <div class="address-text">
                                    {{ $delivery->delivery_address ?: '—' }}
                                </div>
                            </td>

                            {{-- STATUS --}}
                            <td>
                                <span class="badge {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            {{-- ACTIONS --}}
                            <td>
                                <div class="table-actions">
                                    <a
                                        href="{{ route('deliveries.show', $delivery->delivery_id) }}"
                                        class="action-btn view"
                                        title="View"
                                        aria-label="View delivery"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    <a
                                        href="{{ route('deliveries.edit', $delivery->delivery_id) }}"
                                        class="action-btn edit"
                                        title="Edit"
                                        aria-label="Edit delivery"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>
                                    </a>

                                    <form
                                        action="{{ route('deliveries.destroy', $delivery->delivery_id) }}"
                                        method="POST"
                                        class="delivery-delete-form"
                                        data-number="{{ $delivery->delivery_number }}"
                                        data-locked="{{ $status !== 'pending' ? '1' : '0' }}"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-btn delete"
                                            title="Delete"
                                            aria-label="Delete delivery"
                                        >
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M3 6h18"/>
                                                <path d="M8 6V4h8v2"/>
                                                <path d="m19 6-1 14H6L5 6"/>
                                                <path d="M10 11v5M14 11v5"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                No delivery records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div>
                Showing {{ $deliveries->firstItem() ?? 0 }}
                to {{ $deliveries->lastItem() ?? 0 }}
                of {{ $deliveries->total() }} deliveries
            </div>
            <div class="delivery-pagination">{{ $deliveries->links() }}</div>
        </div>
    </div>
</div>

<div class="delivery-confirm delivery-index-modal" id="deliveryDeleteModal" hidden role="dialog" aria-modal="true" aria-labelledby="deliveryDeleteTitle">
  <div class="delivery-confirm-dialog">
    <div class="delivery-modal-symbol" id="deliveryDeleteSymbol">!</div>
    <h3 id="deliveryDeleteTitle">Delete Delivery?</h3>
    <p id="deliveryDeleteMessage">Are you sure?</p>
    <div class="delivery-confirm-actions">
      <button type="button" class="btn" id="deliveryDeleteCancel">Cancel</button>
      <button type="button" class="btn delivery-delete-confirm" id="deliveryDeleteConfirm">Delete Delivery</button>
    </div>
  </div>
</div>
<script>
(() => {
  const modal = document.getElementById('deliveryDeleteModal');
  const title = document.getElementById('deliveryDeleteTitle');
  const message = document.getElementById('deliveryDeleteMessage');
  const confirmButton = document.getElementById('deliveryDeleteConfirm');
  let selectedForm = null;
  const close = () => {modal.hidden = true; selectedForm = null;};
  document.querySelectorAll('.delivery-delete-form').forEach(form => form.addEventListener('submit', e => {
    e.preventDefault(); selectedForm = form; const locked = form.dataset.locked === '1';
    title.textContent = locked ? 'Cannot Delete Delivery' : 'Delete Delivery?';
    message.textContent = locked ? 'Delivery ' + form.dataset.number + ' is no longer Pending. Only Pending deliveries without installations may be deleted.' : 'Delete ' + form.dataset.number + '? This action cannot be undone.';
    confirmButton.hidden = locked;
    document.getElementById('deliveryDeleteCancel').textContent = locked ? 'Close' : 'Cancel';
    modal.hidden = false;
  }));
  confirmButton.addEventListener('click', () => { if(selectedForm) selectedForm.submit(); });
  document.getElementById('deliveryDeleteCancel').addEventListener('click', close);
  modal.addEventListener('click',e => {if(e.target === modal)close();});
  document.addEventListener('keydown',e => {if(e.key === 'Escape' && !modal.hidden)close();});
})();
</script>
{{-- PAGE STYLES --}}
@include('deliveries._style')

@endsection