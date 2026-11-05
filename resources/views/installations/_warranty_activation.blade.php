@php
    $activationAvailable = $installation->status === 'Completed';
    $warrantyItems = $deliveryItems ?? collect();
    $registeredWarranties = \Illuminate\Support\Facades\DB::table('warranties')
        ->whereIn('serial_number_id', $warrantyItems->pluck('serial_number_id')->filter())
        ->get()->keyBy('serial_number_id');
    $unregisteredCount = $warrantyItems->filter(fn ($item) => $item->serial_number_id && !$registeredWarranties->has($item->serial_number_id))->count();
@endphp
<div class="card installation-warranty-card">
    <div class="card-head">
        <div><h3>Warranty Activation</h3><p>Register manufacturer warranty coverage for installed devices.</p></div>
        <span class="installation-badge {{ $unregisteredCount === 0 && $warrantyItems->isNotEmpty() ? 'success' : 'warning' }}">
            {{ $unregisteredCount === 0 && $warrantyItems->isNotEmpty() ? 'Registered' : 'Pending Activation' }}
        </span>
    </div>
    <div class="card-body">
        <p class="installation-muted">The warranty starts on the activation date. The coverage duration comes from each product's Warranty Period (months). Existing coverage is preserved.</p>
        @if($warrantyItems->isNotEmpty())
            <div class="table-wrap installation-table-wrap">
                <table class="installation-table">
                    <thead><tr><th>No.</th><th>Serial Number</th><th>Product</th><th>Warranty</th></tr></thead>
                    <tbody>
                    @foreach($warrantyItems as $item)
                        @php($registered = $registeredWarranties->get($item->serial_number_id))
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->serialNumber?->serial_number ?? '—' }}</td>
                            <td>{{ $item->serialNumber?->product?->product_name ?? '—' }}</td>
                            <td>
                                @if($registered)
                                    <a class="installation-link" href="{{ route('warranties.show', $registered->warranty_id) }}">{{ $registered->warranty_number }}</a>
                                @else
                                    <span class="installation-badge warning">Not Registered</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @if($activationAvailable && $unregisteredCount > 0)
            <button type="button" class="btn primary installation-warranty-trigger" id="openWarrantyActivation">Activate Warranties</button>
        @elseif(!$activationAvailable)
            <p class="installation-muted">Complete this Installation before activating warranty coverage.</p>
        @else
            <p class="installation-muted">No new warranty records are required for these devices.</p>
        @endif
    </div>
</div>
@if($activationAvailable && $unregisteredCount > 0)
<div class="installation-confirm" id="installationWarrantyModal" hidden>
    <div class="installation-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="warrantyModalTitle">
        <div class="installation-confirm-symbol">✓</div>
        <h3 id="warrantyModalTitle">Activate Device Warranties?</h3>
        <p>This registers new manufacturer warranties for installed units. Existing records will not be overwritten.</p>
        <form method="POST" action="{{ route('installations.warranties.activate', $installation) }}">
            @csrf
            <div class="installation-confirm-actions">
                <button type="button" class="btn" id="cancelWarrantyActivation">Cancel</button>
                <button type="submit" class="btn primary" id="confirmWarrantyActivation">Activate Warranties</button>
            </div>
        </form>
    </div>
</div>
<script>
(() => {
    const modal = document.getElementById('installationWarrantyModal');
    const open = document.getElementById('openWarrantyActivation');
    const cancel = document.getElementById('cancelWarrantyActivation');
    if (!modal || !open || !cancel) return;
    open.addEventListener('click', () => { modal.hidden = false; });
    cancel.addEventListener('click', () => { modal.hidden = true; });
    modal.addEventListener('click', event => { if (event.target === modal) modal.hidden = true; });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') modal.hidden = true; });
    modal.querySelector('form').addEventListener('submit', () => {
        modal.querySelector('#confirmWarrantyActivation').disabled = true;
    });
})();
</script>
@endif
