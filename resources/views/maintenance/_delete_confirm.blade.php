{{-- Shared delete confirmation for Maintenance Schedule and Maintenance Records. --}}
<div id="mtDeleteModal" class="mt-workflow-modal" hidden>
    <div class="mt-workflow-dialog mt-delete-dialog" role="dialog" aria-modal="true"
         aria-labelledby="mtDeleteTitle" aria-describedby="mtDeleteMessage" tabindex="-1">
        <div class="mt-delete-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m5 5v5m4-5v5"/>
            </svg>
        </div>
        <h3 id="mtDeleteTitle">Delete Maintenance?</h3>
        <p id="mtDeleteMessage">This action cannot be undone.</p>
        <form id="mtDeleteForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="mt-workflow-modal-actions">
                <button type="button" class="btn" id="mtDeleteCancel">Cancel</button>
                <button type="submit" class="btn danger mt-delete-submit" id="mtDeleteConfirm">Yes, Delete</button>
            </div>
        </form>
    </div>
</div>
<script>
(() => {
    const modal = document.getElementById('mtDeleteModal');
    const form = document.getElementById('mtDeleteForm');
    const title = document.getElementById('mtDeleteTitle');
    const message = document.getElementById('mtDeleteMessage');
    const dialog = modal?.querySelector('[role="dialog"]');
    const cancel = document.getElementById('mtDeleteCancel');
    if (!modal || !form) return;
    let priorFocus = null;
    const close = () => {
        modal.hidden = true;
        form.setAttribute('action', '');
        priorFocus?.focus?.();
    };
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-mt-delete-action]');
        if (!trigger) return;
        e.preventDefault();
        priorFocus = trigger;
        form.action = trigger.dataset.mtDeleteAction;
        title.textContent = trigger.dataset.mtDeleteTitle || 'Confirm Delete';
        message.textContent = trigger.dataset.mtDeleteMessage || 'This action cannot be undone.';
        modal.hidden = false;
        cancel?.focus();
    });
    cancel?.addEventListener('click', close);
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => {
        if (modal.hidden) return;
        if (e.key === 'Escape') { e.preventDefault(); close(); }
        if (e.key === 'Tab') {
            const elements = [...modal.querySelectorAll('button:not([disabled])')];
            if (!elements.length) return;
            const first = elements[0], last = elements[elements.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });
})();
</script>
