@extends('layouts.app')

@section('title', 'Delivery Details')

@section('content')

@php
    $deliveryStatuses = [
        'pending' => 'Pending',
        'in_transit' => 'In Transit',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    $statusLabel = $deliveryStatuses[
        strtolower($delivery->status ?? '')
    ] ?? ucfirst($delivery->status ?? 'Unknown');

    $statusClass = \Illuminate\Support\Str::slug(
        strtolower($delivery->status ?? 'unknown')
    );
@endphp

{{-- PAGE HEADER --}}
<div class="page-head">
    <div>
        <h1>{{ $delivery->delivery_number }}</h1>
        <p>
            View delivery details and related sales order information.
        </p>
    </div>

    <div class="actions">
        <a
            href="{{ route('deliveries.edit', $delivery->delivery_id) }}"
            class="btn primary"
        >
            ✎ Edit Delivery
        </a>

        <a
            href="{{ route('deliveries.index') }}"
            class="btn"
        >
            ← Back
        </a>
    </div>
</div>

<div class="card delivery-workflow">
    <div class="card-head"><div><h3>Delivery Workflow</h3><p>Record shipping progress after completing the physical action.</p></div></div>
    <div class="card-body">
        <div class="delivery-workflow-body">
            @if($delivery->status === 'pending')
                <button type="button" class="btn primary" data-delivery-action="in_transit" data-delivery-title="Mark as In Transit?" data-delivery-text="Dispatch the selected units and deduct inventory from their selected warehouses? This action records Stock Movements and assigns serial numbers.">Mark as In Transit</button>
                <button type="button" class="btn" data-delivery-action="cancelled" data-delivery-title="Cancel Delivery?" data-delivery-text="Cancel this pending delivery?">Cancel Delivery</button>
            @elseif($delivery->status === 'in_transit')
                <button type="button" class="btn primary" data-delivery-action="delivered" data-delivery-title="Confirm Delivery?" data-delivery-text="Confirm that the customer has received this shipment. Stock and serial number updates were recorded at dispatch.">Confirm Delivered</button>
            @else
                <span class="delivery-warning">This delivery has reached a final status.</span>
            @endif
        </div>
        <p class="delivery-workflow-note">Dispatch deducts stock and marks allocated serials Assigned. Confirm Delivered only after customer receipt. Installation integration follows in the next phase.</p>
    </div>
</div>

<div class="delivery-confirm" id="deliveryConfirm" hidden role="dialog" aria-modal="true" aria-labelledby="deliveryConfirmTitle">
    <div class="delivery-confirm-dialog">
        <div class="delivery-modal-symbol">?</div>
        <h3 id="deliveryConfirmTitle">Confirm status</h3>
        <p id="deliveryConfirmText"></p>
        <form method="POST" action="{{ route('deliveries.change-status', $delivery->delivery_id) }}" id="deliveryStatusForm">
            @csrf
            {{-- HTTP method MUST stay PATCH; delivery statuses belong only in the status field. --}}
            <input type="hidden" name="_method" id="deliveryHttpMethod" value="PATCH">
            <input type="hidden" name="status" id="deliveryNextStatus">
            <div class="delivery-confirm-actions">
                <button type="button" class="btn" id="deliveryCancel">Cancel</button>
                <button type="submit" class="btn primary">Confirm</button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('deliveryConfirm');
    const title = document.getElementById('deliveryConfirmTitle');
    const message = document.getElementById('deliveryConfirmText');
    const next = document.getElementById('deliveryNextStatus');
    const form = document.getElementById('deliveryStatusForm');
    const httpMethod = document.getElementById('deliveryHttpMethod');
    const allowed = new Set(['in_transit', 'delivered', 'cancelled']);
    const close = () => { modal.hidden = true; };
    document.querySelectorAll('[data-delivery-action]').forEach(button => {
        button.addEventListener('click', () => {
            title.textContent = button.dataset.deliveryTitle;
            message.textContent = button.dataset.deliveryText;
            const status = button.dataset.deliveryAction;
            if (!allowed.has(status)) return;
            next.value = status;
            httpMethod.value = 'PATCH';
            modal.hidden = false;
        });
    });
    form.addEventListener('submit', (event) => {
        // Prevent accidental mutation of Laravel's HTTP method spoofing value.
        httpMethod.value = 'PATCH';
        if (!allowed.has(next.value)) {
            event.preventDefault();
            return;
        }
    });
    document.getElementById('deliveryCancel').addEventListener('click', close);
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if(e.key === 'Escape') close(); });
})();
</script>

<div class="card delivery-serial-card">
    <div class="card-head"><div><h3>Delivery Items & Serial Numbers</h3><p>Allocate devices included in the Sales Order before dispatch. Select each unit and source warehouse. Stock is deducted only when dispatch is confirmed.</p></div></div>
    <div class="card-body">
        @if(!$trackingAvailable)
            <p class="delivery-warning">Delivery Item warehouse tracking is unavailable. Run both Delivery Items migrations first.</p>
        @else
            @if($delivery->status === 'pending')
                <form action="{{ route('deliveries.items.store', $delivery) }}" method="POST" class="delivery-serial-form">
                    @csrf
                    <label for="serial_number_id">Available serial number</label>
                    <select name="serial_number_id" id="serial_number_id" required>
                        <option value="">Select a unit from the Sales Order...</option>
                        @foreach($availableSerials as $serial)
                            <option value="{{ $serial->serial_number_id }}">{{ $serial->serial_number }} — {{ $serial->product->product_name ?? $serial->product_id }}</option>
                        @endforeach
                    </select>
                    <label for="warehouse_id">Source warehouse</label>
                    <select name="warehouse_id" id="warehouse_id" required>
                        <option value="">Select warehouse with stock...</option>
                        @foreach($warehouseStocks as $inventory)
                            <option value="{{ $inventory->warehouse_id }}" data-product-id="{{ $inventory->product_id }}">
                                {{ $inventory->warehouse->warehouse_name ?? $inventory->warehouse_id }} — {{ $inventory->quantity }} available
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn primary" @disabled($availableSerials->isEmpty() || $warehouseStocks->isEmpty())>Add Unit</button>
                    <p class="delivery-warning">Select a warehouse that physically holds the chosen serial number. Current database does not track each serial's warehouse location.</p>
                </form>
                @error('serial_number_id') <p class="delivery-warning">{{ $message }}</p> @enderror
                @error('warehouse_id') <p class="delivery-warning">{{ $message }}</p> @enderror
                @error('status') <p class="delivery-warning">{{ $message }}</p> @enderror
            @endif
            <div class="delivery-serial-grid">
                <div class="delivery-serial-summary">
                    <strong>Sales Order Items</strong>
                    @forelse($orderProducts as $product)
                        <p>{{ $product['name'] }} <span>Ordered: {{ $product['quantity'] }}</span></p>
                    @empty
                        <p>No products found.</p>
                    @endforelse
                </div>
                <div class="delivery-serial-summary">
                    <strong>Allocated Units ({{ $delivery->items->count() }})</strong>
                    @forelse($delivery->items as $item)
                        <div class="delivery-serial-assigned">
                            <span>{{ $item->serialNumber->serial_number ?? 'Unknown Serial' }} — {{ $item->serialNumber->product->product_name ?? '-' }}<small class="delivery-source-warehouse">{{ $item->warehouse->warehouse_name ?? 'Warehouse not assigned' }}</small></span>
                            @if($delivery->status === 'pending')
                                <form action="{{ route('deliveries.items.destroy', [$delivery, $item]) }}" method="POST" onsubmit="return confirm('Remove this serial number from Delivery?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn">Remove</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p>No serial numbers allocated yet.</p>
                    @endforelse
                </div>
            </div>
            @if($dispatchMovements->isNotEmpty())
                <div class="delivery-dispatch-records">
                    <strong>Recorded Stock Out</strong>
                    @foreach($dispatchMovements as $movement)
                        <p>{{ $movement->product_name }} — {{ $movement->warehouse_name }}: {{ $movement->quantity }} unit(s)</p>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>

<script>
(() => {
  const serial = document.getElementById('serial_number_id');
  const warehouse = document.getElementById('warehouse_id');
  if (!serial || !warehouse) return;
  const products = @json($serialProductMap);
  function filterWarehouses() {
    const productId = products[serial.value];
    [...warehouse.options].forEach(option => {
      if (!option.value) return;
      option.hidden = Boolean(productId && option.dataset.productId !== productId);
      option.disabled = Boolean(productId && option.dataset.productId !== productId);
    });
    if (warehouse.selectedOptions[0]?.disabled) warehouse.value = '';
  }
  serial.addEventListener('change', filterWarehouses);
  filterWarehouses();
})();
</script>

{{-- DELIVERY INFORMATION --}}
<div class="information-layout">

    <div class="card information-card delivery-information-card">

        <div class="card-head">
            <div>
                <h3>Delivery Information</h3>
                <p>Basic delivery and shipment information.</p>
            </div>

            <span class="delivery-status-badge status-{{ $statusClass }}">
                {{ $statusLabel }}
            </span>
        </div>

        <div class="card-body">

            <div class="delivery-information-grid">

                <div class="information-field">
                    <span class="information-label">
                        Delivery Number
                    </span>

                    <strong class="information-value delivery-number">
                        {{ $delivery->delivery_number }}
                    </strong>
                </div>

                <div class="information-field">
                    <span class="information-label">
                        Delivery Date
                    </span>

                    <strong class="information-value">
                        {{ $delivery->delivery_date
                            ? $delivery->delivery_date->format('d M Y')
                            : '-' }}
                    </strong>
                </div>

                <div class="information-field">
                    <span class="information-label">
                        Delivery Status
                    </span>

                    <span class="delivery-status-badge status-{{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="information-field">
                    <span class="information-label">
                        Created At
                    </span>

                    <strong class="information-value">
                        {{ $delivery->created_at
                            ? $delivery->created_at->format('d M Y, H:i')
                            : '-' }}
                    </strong>
                </div>

                <div class="information-field full-width">
                    <span class="information-label">
                        Delivery Address
                    </span>

                    <strong class="information-value delivery-text">
                        {{ $delivery->delivery_address ?: '-' }}
                    </strong>
                </div>

                <div class="information-field full-width">
                    <span class="information-label">
                        Notes
                    </span>

                    <div class="information-value delivery-text">
                        {{ $delivery->notes ?: '-' }}
                    </div>
                </div>

                <div class="information-field">
                    <span class="information-label">
                        Last Updated
                    </span>

                    <strong class="information-value">
                        {{ $delivery->updated_at
                            ? $delivery->updated_at->format('d M Y, H:i')
                            : '-' }}
                    </strong>
                </div>

            </div>

        </div>
    </div>

    {{-- SALES ORDER INFORMATION --}}
    <div class="card information-card sales-order-information-card">

        <div class="card-head">
            <div>
                <h3>Sales Order Information</h3>
                <p>Sales order associated with this delivery.</p>
            </div>
        </div>

        <div class="card-body">

            @if($delivery->salesOrder)

                <div class="sales-order-content">

                    <div class="sales-order-heading">
                        <span class="information-label">
                            Sales Order Number
                        </span>

                        <a class="delivery-origin-link sales-order-number" href="{{ route('sales-orders.show', $delivery->sales_order_id) }}">
                            {{ $delivery->salesOrder->sales_order_number ?? '-' }}
                        </a>
                    </div>

                    <div class="sales-order-grid">

                        <div class="information-field">
                            <span class="information-label">
                                Order Date
                            </span>

                            <strong class="information-value">
                                {{ $delivery->salesOrder->order_date
                                    ? \Illuminate\Support\Carbon::parse(
                                        $delivery->salesOrder->order_date
                                    )->format('d M Y')
                                    : '-' }}
                            </strong>
                        </div>

                        <div class="information-field">
                            <span class="information-label">
                                Sales Order Status
                            </span>

                            <strong class="information-value">
                                {{ $delivery->salesOrder->status ?? '-' }}
                            </strong>
                        </div>

                    </div>

                </div>

            @else

                <div class="empty-small">
                    No sales order information available.
                </div>

            @endif

        </div>
    </div>

</div>


<div class="card information-card">
    <div class="card-head"><div><h3>Installation Follow-up</h3><p>Schedule device setup after the customer receives the shipment.</p></div></div>
    <div class="card-body">
        @if($delivery->status === 'delivered')
            @php($existingInstallation = \App\Models\Installation::where('delivery_id', $delivery->delivery_id)->first())
            @if($existingInstallation)
                <a class="btn primary" href="{{ route('installations.show', $existingInstallation) }}">View Installation</a>
            @else
                <a class="btn primary" href="{{ route('installations.create', ['delivery_id' => $delivery->delivery_id]) }}">+ Schedule Installation</a>
            @endif
        @else
            <p>Confirm the Delivery as Delivered before scheduling Installation.</p>
        @endif
    </div>
</div>

@include('deliveries._style')

@endsection