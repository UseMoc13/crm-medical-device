<aside class="activity-preview-panel" aria-label="Activity live preview">
    <div class="activity-preview-top"><span class="activity-preview-icon">◷</span><div><h3>Activity Preview</h3><p>Updates automatically as you complete the form.</p></div></div>
    <div class="activity-preview-body" aria-live="polite" aria-atomic="false">
        <div class="activity-preview-overline">ACTIVITY SUMMARY</div>
        <h4 id="activityPreviewSubject">Untitled activity</h4>
        <span id="activityPreviewType" class="activity-type">Choose activity type</span>
        <div class="activity-preview-separator"></div>
        <div class="activity-preview-row"><span>Scheduled Date</span><strong id="activityPreviewDate">Not selected</strong></div>
        <div class="activity-preview-row"><span>Time</span><strong id="activityPreviewTime">Not selected</strong></div>
        <div class="activity-preview-row"><span>Owner</span><strong>{{ auth()->user()?->name ?? 'Current user' }}</strong></div>
        <div class="activity-preview-separator"></div>
        <div class="activity-preview-overline">RELATED CRM RECORDS</div>
        <div class="activity-preview-row"><span>Customer</span><strong id="activityPreviewCustomer">—</strong></div>
        <div class="activity-preview-row"><span>Lead</span><strong id="activityPreviewLead">—</strong></div>
        <div class="activity-preview-row"><span>Opportunity</span><strong id="activityPreviewOpportunity">—</strong></div>
        <div class="activity-preview-separator"></div>
        <div class="activity-preview-overline">DESCRIPTION</div>
        <p id="activityPreviewDescription" class="activity-preview-description">No description added yet.</p>
    </div>
    <div class="activity-preview-footer">Preview only · Nothing is saved until you click Save.</div>
</aside>
