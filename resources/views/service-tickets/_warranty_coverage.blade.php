{{-- Warranty information is advisory. Out-of-warranty service is still permitted. --}}
<div class="ticket-warranty-panel" id="ticketWarrantyPanel" role="status" aria-live="polite">
    <div class="ticket-warranty-head"><strong>Warranty Coverage</strong><span class="ticket-warranty-pill" id="ticketWarrantyStatus">Select a device</span></div>
    <p id="ticketWarrantyDetail">Select a customer and serial number to check warranty coverage.</p>
    <a id="ticketWarrantyLink" href="#" class="ticket-warranty-link" hidden>View Warranty</a>
</div>

<script>
(() => {
    const coverage = @json($warrantyCoverageBySerial ?? []);
    const customer = document.getElementById('customer_id');
    const serial = document.getElementById('serial_number_id');
    const badge = document.getElementById('ticketWarrantyStatus');
    const detail = document.getElementById('ticketWarrantyDetail');
    const link = document.getElementById('ticketWarrantyLink');
    if (!customer || !serial || !badge || !detail || !link) return;
    function update() {
        const selected = serial.value;
        const data = selected ? coverage[selected] : null;
        badge.className = 'ticket-warranty-pill';
        link.hidden = true;
        link.removeAttribute('href');
        if (!selected) {badge.textContent='Select a device'; detail.textContent='Select a device to check warranty coverage.';return;}
        if (!data) {badge.textContent='No Warranty';detail.textContent='No warranty is registered for this serial number. Service can still be created.';return;}
        badge.textContent = data.label;
        badge.classList.add(data.status);
        const mismatch = customer.value && data.customer_id && customer.value !== data.customer_id;
        detail.textContent = (data.warranty_number || 'Warranty') + ' · ' + (data.start_date || '—') + ' to ' + (data.end_date || '—') + (mismatch ? ' · WARNING: Warranty customer differs from selected customer.' : '') + '. The ticket may still be registered for out-of-warranty service.';
        if (data.warranty_id) {link.href = @json(url('/warranties')) + '/' + encodeURIComponent(data.warranty_id);link.hidden = false;}
    }
    customer.addEventListener('change', update);
    serial.addEventListener('change', update);
    update();
})();
</script>
