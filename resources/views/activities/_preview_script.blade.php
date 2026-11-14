<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('activityEditorForm');
    if (!form) return;
    const find = (id) => document.getElementById(id);
    const value = (id) => find(id)?.value?.trim() || '';
    const optionLabel = (id) => {
        const select = find(id);
        if (!select || !select.value) return '—';
        return select.selectedOptions?.[0]?.textContent?.trim() || '—';
    };
    const setText = (id, text) => { const element = find(id); if (element) element.textContent = text; };
    const update = () => {
        setText('activityPreviewSubject', value('subject') || 'Untitled activity');
        setText('activityPreviewType', value('activity_type') || 'Choose activity type');
        const date = value('activity_day');
        if (/^\d{4}-\d{2}-\d{2}$/.test(date)) {
            const [year, month, day] = date.split('-');
            setText('activityPreviewDate', `${day}/${month}/${year}`);
        } else {
            setText('activityPreviewDate', 'Not selected');
        }
        setText('activityPreviewTime', value('activity_time') || 'Not selected');
        setText('activityPreviewCustomer', optionLabel('customer_id'));
        setText('activityPreviewLead', optionLabel('lead_id'));
        setText('activityPreviewOpportunity', optionLabel('opportunity_id'));
        setText('activityPreviewDescription', value('description') || 'No description added yet.');
    };
    // Customer ↔ Lead selection uses only options already authorized by
    // CrmReferenceDataScope on the server. Never fetch unrestricted references.
    const customerSelect = find('customer_id');
    const leadSelect = find('lead_id');
    const leadHint = find('activityLeadHint');
    const allLeadOptions = leadSelect ? Array.from(leadSelect.options).filter(option => option.value !== '') : [];
    const filterLeads = () => {
        if (!leadSelect || !customerSelect) return;
        const customerId = customerSelect.value;
        let available = 0;
        allLeadOptions.forEach(option => {
            const matches = !customerId || option.dataset.customerId === customerId;
            option.hidden = !matches;
            option.disabled = !matches;
            if (matches) available++;
        });
        const selected = leadSelect.selectedOptions?.[0];
        if (selected?.disabled) leadSelect.value = '';
        if (leadHint) leadHint.textContent = !customerId
            ? 'Choose a lead by contact/company name. Selecting a linked lead will fill its customer.'
            : available + ' related lead(s) available for the selected customer.';
        update();
    };
    customerSelect?.addEventListener('change', filterLeads);
    leadSelect?.addEventListener('change', () => {
        const selected = leadSelect.selectedOptions?.[0];
        if (selected?.value && selected.dataset.customerId && customerSelect) {
            const customerOption = Array.from(customerSelect.options).find(option => option.value === selected.dataset.customerId);
            if (customerOption) customerSelect.value = selected.dataset.customerId;
        }
        filterLeads();
    });
    // Preserve valid edit/old() selections; drop a stale mismatched lead.
    filterLeads();
    form.querySelectorAll('input,select,textarea').forEach(element => {
        element.addEventListener('input', update);
        element.addEventListener('change', update);
    });
    update();
});
</script>
