{{-- Warranty information is advisory. Out-of-warranty service is still permitted. --}}
<div class="ticket-warranty-panel" id="ticketWarrantyPanel" role="status" aria-live="polite">
    <div class="ticket-warranty-head"><strong>Warranty Coverage</strong><span class="ticket-warranty-pill" id="ticketWarrantyStatus">Select a device</span></div>
    <p id="ticketWarrantyDetail">Select a customer and serial number to check warranty coverage.</p>
    <a id="ticketWarrantyLink" href="#" class="ticket-warranty-link" hidden>View Warranty</a>
</div>
<style>
.ticket-warranty-panel{margin:14px 0 20px;padding:17px 19px;border:1px solid #e2e8f0;background:#f8fafc;border-radius:9px}
.ticket-warranty-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;color:#17284f;font-size:13px}
.ticket-warranty-pill{padding:5px 10px;border-radius:20px;font-size:11px;font-weight:700;background:#e2e8f0;color:#475569}
.ticket-warranty-pill.covered{background:#dcfce7;color:#166534}.ticket-warranty-pill.expired,.ticket-warranty-pill.void{background:#fee2e2;color:#991b1b}.ticket-warranty-pill.claimed,.ticket-warranty-pill.upcoming{background:#fef3c7;color:#92400e}
.ticket-warranty-panel p{font-size:12px;line-height:1.6;color:#64748b;margin:10px 0 0}.ticket-warranty-link{display:inline-block;margin-top:10px;font-size:12px;font-weight:600;color:#176d97;text-decoration:none}
.ticket-warranty-link[hidden]{display:none!important}
</style>
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
