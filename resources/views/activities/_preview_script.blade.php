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
    form.querySelectorAll('input,select,textarea').forEach(element => {
        element.addEventListener('input', update);
        element.addEventListener('change', update);
    });
    update();
});
</script>
