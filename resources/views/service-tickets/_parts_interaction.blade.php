{{-- Service part editor: scoped to Service Ticket Details; styles in _style.blade.php --}}
@if($serviceTicket->status === 'in_progress')
<div class="ticket-part-modal" id="ticketPartFormModal" hidden>
    <div class="ticket-part-dialog" role="dialog" aria-modal="true" aria-labelledby="ticketPartFormTitle">
        <h3 id="ticketPartFormTitle">Add Service Part</h3>
        <p>Record the replacement product used during service. This record does not deduct inventory.</p>
        <form method="POST" id="ticketPartForm" action="{{ route('service-tickets.parts.store', $serviceTicket) }}">
            @csrf
            <input type="hidden" id="ticketPartMethod" name="_method" value="POST" disabled>
            <div class="ticket-part-form-grid">
                <div class="ticket-part-field full">
                    <label for="ticketPartProduct">Product <span aria-hidden="true">*</span></label>
                    <select id="ticketPartProduct" name="product_id" required>
                        <option value="">Select replacement part</option>
                        @foreach($partProducts as $product)
                            <option value="{{ $product->product_id }}">{{ $product->product_name }} ({{ $product->product_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="ticket-part-field">
                    <label for="ticketPartQuantity">Quantity *</label>
                    <input id="ticketPartQuantity" name="quantity" type="text" inputmode="numeric" pattern="[0-9]+" maxlength="6" value="1" autocomplete="off" required>
                </div>
                <div class="ticket-part-field">
                    <label for="ticketPartPrice">Unit Price *</label>
                    <div class="ticket-price-input-wrap"><span class="ticket-price-prefix"> Rp</span><input id="ticketPartPriceDisplay" type="text" inputmode="numeric" autocomplete="off" value="0" aria-label="Unit price in rupiah" required><input id="ticketPartPrice" name="unit_price" type="hidden" value="0"></div>
                </div>
                <div class="ticket-part-field full">
                    <label for="ticketPartNotes">Notes</label>
                    <textarea id="ticketPartNotes" name="notes" maxlength="2000" placeholder="Reason for replacement or repair notes..."></textarea>
                </div>
            </div>
            <div class="ticket-part-message">The service ticket must remain In Progress to add or edit parts.</div>
            <div class="ticket-part-dialog-actions">
                <button type="button" class="btn ticket-part-close">Cancel</button>
                <button type="submit" class="btn primary">Save Service Part</button>
            </div>
        </form>
    </div>
</div>
<div class="ticket-part-modal" id="ticketPartDeleteModal" hidden>
    <div class="ticket-part-dialog" role="dialog" aria-modal="true" aria-labelledby="ticketPartDeleteTitle">
        <h3 id="ticketPartDeleteTitle">Remove Service Part?</h3>
        <p id="ticketPartDeleteMessage">Are you sure you want to remove this service part record?</p>
        <form method="POST" id="ticketPartDeleteForm">
            @csrf
            @method('DELETE')
            <div class="ticket-part-dialog-actions">
                <button type="button" class="btn ticket-part-close">Cancel</button>
                <button type="submit" class="btn danger">Remove Part</button>
            </div>
        </form>
    </div>
</div>
<script>
(() => {
    const formModal = document.getElementById('ticketPartFormModal');
    const deleteModal = document.getElementById('ticketPartDeleteModal');
    if (!formModal || !deleteModal) return;
    const form = document.getElementById('ticketPartForm');
    const addUrl = @json(route('service-tickets.parts.store', $serviceTicket));
    const methodInput = document.getElementById('ticketPartMethod');
    const quantityInput = document.getElementById('ticketPartQuantity');
    const priceDisplay = document.getElementById('ticketPartPriceDisplay');
    const priceRaw = document.getElementById('ticketPartPrice');
    const asDigits = value => String(value ?? '').replace(/\D/g, '').replace(/^0+(?=\d)/, '');
    const formatIDR = value => (Number(value || 0)).toLocaleString('id-ID', {maximumFractionDigits: 0});
    const setPrice = value => {
        const digits = asDigits(value).slice(0, 9);
        priceRaw.value = digits === '' ? '' : digits;
        priceDisplay.value = digits === '' ? '' : formatIDR(digits);
    };
    const selectAll = input => { input.select(); };
    [quantityInput, priceDisplay].forEach(input => {
        input.addEventListener('focus', () => selectAll(input));
        input.addEventListener('click', () => selectAll(input));
    });
    quantityInput.addEventListener('input', () => { quantityInput.value = asDigits(quantityInput.value).slice(0, 6); });
    priceDisplay.addEventListener('input', () => setPrice(priceDisplay.value));
    form.addEventListener('submit', event => {
        if (!/^[1-9]\d*$/.test(quantityInput.value) || Number(quantityInput.value) > 100000) {
            event.preventDefault(); quantityInput.setCustomValidity('Quantity must be between 1 and 100000.');
            quantityInput.reportValidity(); return;
        }
        quantityInput.setCustomValidity('');
        if (priceRaw.value === '' || Number(priceRaw.value) > 999999999) {
            event.preventDefault(); priceDisplay.setCustomValidity('Enter a valid unit price (0–999,999,999).');
            priceDisplay.reportValidity(); return;
        }
        priceDisplay.setCustomValidity('');
    });
    quantityInput.addEventListener('input', () => quantityInput.setCustomValidity(''));
    priceDisplay.addEventListener('input', () => priceDisplay.setCustomValidity(''));
    setPrice(0);
    const close = () => { formModal.hidden = true; deleteModal.hidden = true; };
    document.querySelectorAll('.ticket-part-close').forEach(btn => btn.addEventListener('click', close));
    for (const modal of [formModal, deleteModal]) modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
    document.querySelectorAll('.ticket-part-open').forEach(btn => btn.addEventListener('click', () => {
        form.reset(); form.action = addUrl; methodInput.disabled = true; setPrice(0);
        document.getElementById('ticketPartFormTitle').textContent = 'Add Service Part';
        formModal.hidden = false;
    }));
    document.querySelectorAll('.ticket-part-edit').forEach(btn => btn.addEventListener('click', () => {
        form.reset(); form.action = btn.dataset.url; methodInput.disabled = false; methodInput.value = 'PATCH';
        document.getElementById('ticketPartFormTitle').textContent = 'Edit Service Part';
        document.getElementById('ticketPartProduct').value = btn.dataset.product;
        document.getElementById('ticketPartQuantity').value = btn.dataset.quantity;
        setPrice(Math.round(Number(btn.dataset.price || '0')));
        document.getElementById('ticketPartNotes').value = btn.dataset.notes || '';
        formModal.hidden = false;
    }));
    document.querySelectorAll('.ticket-part-remove').forEach(btn => btn.addEventListener('click', () => {
        document.getElementById('ticketPartDeleteForm').action = btn.dataset.url;
        document.getElementById('ticketPartDeleteMessage').textContent = 'Remove "' + btn.dataset.name + '" from this service ticket?';
        deleteModal.hidden = false;
    }));
})();
</script>
@endif
