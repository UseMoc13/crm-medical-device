{{-- Shared confirmation dialog for Maintenance Schedule and Record deletions. --}}
<div id="mtDeleteConfirmModal" class="mt-delete-modal" hidden aria-hidden="true">
    <div class="mt-delete-backdrop" data-mt-delete-cancel></div>
    <div class="mt-delete-dialog" role="alertdialog" aria-modal="true" aria-labelledby="mtDeleteTitle" aria-describedby="mtDeleteDescription" tabindex="-1">
        <div class="mt-delete-symbol" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m11 0-1 14H6L5 6M10 11v5m4-5v5"/></svg>
        </div>
        <h3 id="mtDeleteTitle">Delete Maintenance?</h3>
        <p id="mtDeleteDescription">This action cannot be undone.</p>
        <div class="mt-delete-target" id="mtDeleteTarget"></div>
        <div class="mt-delete-actions">
            <button type="button" class="btn mt-delete-cancel" data-mt-delete-cancel>Cancel</button>
            <button type="button" class="btn mt-delete-submit" id="mtDeleteSubmit">Yes, Delete</button>
        </div>
    </div>
</div>
<script>
(() => {
    const modal = document.getElementById('mtDeleteConfirmModal');
    const dialog = modal?.querySelector('.mt-delete-dialog');
    const title = document.getElementById('mtDeleteTitle');
    const description = document.getElementById('mtDeleteDescription');
    const target = document.getElementById('mtDeleteTarget');
    const submit = document.getElementById('mtDeleteSubmit');
    if (!modal || !dialog || !submit) return;

    let pendingForm = null;
    let previousFocus = null;
    let submitting = false;

    const close = () => {
        if (submitting) return;
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        pendingForm = null;
        previousFocus?.focus?.();
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('.mt-confirm-delete')) return;
        if (form.dataset.confirmed === 'true') return;
        event.preventDefault();
        if (submitting) return;
        pendingForm = form;
        previousFocus = document.activeElement;
        const kind = form.dataset.deleteKind === 'record' ? 'record' : 'schedule';
        title.textContent = kind === 'record' ? 'Delete Maintenance Record?' : 'Delete Maintenance Schedule?';
        description.textContent = kind === 'record'
            ? 'This maintenance record will be permanently deleted. This action cannot be undone.'
            : 'This maintenance schedule will be permanently deleted. This action cannot be undone.';
        target.textContent = form.dataset.deleteName || (kind === 'record' ? 'Maintenance Record' : 'Maintenance Schedule');
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        submit.disabled = false;
        submit.textContent = 'Yes, Delete';
        submit.focus();
    });

    modal.querySelectorAll('[data-mt-delete-cancel]').forEach(button => button.addEventListener('click', close));
    document.addEventListener('keydown', (event) => {
        if (modal.hidden) return;
        if (event.key === 'Escape') { event.preventDefault(); close(); }
        if (event.key === 'Tab') {
            const controls = [...modal.querySelectorAll('button:not([disabled])')];
            if (!controls.length) return;
            const first = controls[0], last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    submit.addEventListener('click', () => {
        if (!pendingForm || submitting) return;
        submitting = true;
        submit.disabled = true;
        submit.textContent = 'Deleting...';
        pendingForm.dataset.confirmed = 'true';
        pendingForm.submit();
    });
})();
</script>
