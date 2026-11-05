@if($ticketWarrantyCoverage)
<div class="card ticket-warranty-detail-card">
    <div class="card-head"><div><h3>Warranty Coverage</h3><p>Warranty information for this service device at the time this page is viewed.</p></div></div>
    <div class="card-body">
        <div class="ticket-warranty-detail-grid">
            <div><small>Warranty Number</small><strong>{{ $ticketWarrantyCoverage['warranty_number'] }}</strong></div>
            <div><small>Coverage Status</small><strong>{{ $ticketWarrantyCoverage['label'] }}</strong></div>
            <div><small>Start Date</small><strong>{{ $ticketWarrantyCoverage['start_date'] }}</strong></div>
            <div><small>End Date</small><strong>{{ $ticketWarrantyCoverage['end_date'] }}</strong></div>
        </div>
        <a class="btn" href="{{ route('warranties.show', $ticketWarrantyCoverage['warranty_id']) }}">View Warranty</a>
    </div>
</div>
@else
<div class="card ticket-warranty-detail-card">
    <div class="card-head"><div><h3>Warranty Coverage</h3><p>No warranty has been registered for this device.</p></div></div>
    <div class="card-body"><p>Service can still be performed outside warranty coverage.</p></div>
</div>
@endif
<style>
.ticket-warranty-detail-card{margin-bottom:18px}.ticket-warranty-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;margin-bottom:18px}
.ticket-warranty-detail-grid>div{display:flex;flex-direction:column;gap:7px}.ticket-warranty-detail-grid small{color:#64748b;font-size:12px}.ticket-warranty-detail-grid strong{font-size:13px;color:#17284f}
@media(max-width:650px){.ticket-warranty-detail-grid{grid-template-columns:1fr}}
</style>
