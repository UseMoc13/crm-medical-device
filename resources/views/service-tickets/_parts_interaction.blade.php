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
                    <input id="ticketPartQuantity" name="quantity" type="number" min="1" step="1" max="100000" value="1" required>
                </div>
                <div class="ticket-part-field">
                    <label for="ticketPartPrice">Unit Price *</label>
                    <input id="ticketPartPrice" name="unit_price" type="number" min="0" step="0.01" max="999999999.99" value="0" required>
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
    const close = () => { formModal.hidden = true; deleteModal.hidden = true; };
    document.querySelectorAll('.ticket-part-close').forEach(btn => btn.addEventListener('click', close));
    for (const modal of [formModal, deleteModal]) modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
    document.querySelectorAll('.ticket-part-open').forEach(btn => btn.addEventListener('click', () => {
        form.reset(); form.action = addUrl; methodInput.disabled = true;
        document.getElementById('ticketPartFormTitle').textContent = 'Add Service Part';
        formModal.hidden = false;
    }));
    document.querySelectorAll('.ticket-part-edit').forEach(btn => btn.addEventListener('click', () => {
        form.reset(); form.action = btn.dataset.url; methodInput.disabled = false; methodInput.value = 'PATCH';
        document.getElementById('ticketPartFormTitle').textContent = 'Edit Service Part';
        document.getElementById('ticketPartProduct').value = btn.dataset.product;
        document.getElementById('ticketPartQuantity').value = btn.dataset.quantity;
        document.getElementById('ticketPartPrice').value = btn.dataset.price;
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
