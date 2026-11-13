{{--
    CRM Global Delete Confirmation v1
    Include ONCE in resources/views/layouts/app.blade.php, immediately before </body>:
    @include('components.global-delete-confirm')

    Works with ordinary form submissions containing _method=DELETE or method=DELETE.
    Existing module-specific confirmation handlers are respected by default.
    To opt out on any individual form, use data-crm-delete-confirm="off".
    To supply the label, use data-delete-name="Customer Name" on the form.
--}}
<style>
.crm-global-delete-overlay[hidden] { display: none !important; }
.crm-global-delete-overlay {
    position: fixed;
    inset: 0;
    z-index: 100500;
    display: grid;
    place-items: center;
    padding: 20px;
    background: rgba(13, 27, 52, .57);
    backdrop-filter: blur(3px);
}
.crm-global-delete-dialog {
    width: min(100%, 450px);
    max-height: min(90vh, 700px);
    overflow: auto;
    border: 1px solid #e4eaf1;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 24px 68px rgba(14, 27, 53, .24);
    padding: 28px;
    color: #243757;
    text-align: center;
    animation: crm-global-delete-enter .17s ease-out;
}
@keyframes crm-global-delete-enter {
    from { opacity: .65; transform: translateY(9px) scale(.985); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.crm-global-delete-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 18px;
    display: grid;
    place-items: center;
    background: #fff0f1;
    border: 1px solid #ffe1e3;
    color: #cf4857;
    border-radius: 18px;
}
.crm-global-delete-icon svg { width: 29px; height: 29px; stroke: currentColor; }
.crm-global-delete-dialog h2 {
    font-size: 19px;
    font-weight: 700;
    color: #17284f;
    margin: 0 0 10px;
}
.crm-global-delete-dialog p {
    font-size: 13px;
    line-height: 1.7;
    color: #64748b;
    margin: 0;
}
.crm-global-delete-dialog .crm-global-delete-name {
    color: #243757;
    font-weight: 700;
    overflow-wrap: anywhere;
}
.crm-global-delete-note {
    margin: 18px 0 22px;
    padding: 12px 13px;
    border-radius: 9px;
    background: #fff8f7;
    color: #a44e50;
    font-size: 12px;
    line-height: 1.55;
}
.crm-global-delete-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.crm-global-delete-buttons button {
    min-height: 41px;
    padding: 10px 14px;
    font: inherit;
    font-weight: 650;
    font-size: 13px;
    border-radius: 8px;
    cursor: pointer;
    transition: background .16s, border-color .16s;
}
.crm-global-delete-cancel {
    border: 1px solid #dce5ed;
    color: #334155;
    background: #fff;
}
.crm-global-delete-cancel:hover { background: #f8fafc; }
.crm-global-delete-submit {
    border: 1px solid #cc4651;
    background: #d34c57;
    color: #fff;
}
.crm-global-delete-submit:hover { background: #bf3e49; }
.crm-global-delete-buttons button:focus-visible {
    outline: 3px solid rgba(43, 167, 160, .3);
    outline-offset: 3px;
}
body.crm-global-delete-open { overflow: hidden; }
@media (max-width: 430px) {
    .crm-global-delete-dialog { padding: 23px 17px; }
    .crm-global-delete-buttons { grid-template-columns: 1fr; }
}
@media (prefers-reduced-motion: reduce) {
    .crm-global-delete-dialog { animation: none; }
}
</style>

<div class="crm-global-delete-overlay" id="crmGlobalDeleteOverlay" hidden aria-hidden="true">
    <section class="crm-global-delete-dialog" role="alertdialog" aria-modal="true"
        aria-labelledby="crmGlobalDeleteTitle" aria-describedby="crmGlobalDeleteMessage">
        <div class="crm-global-delete-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5"/>
            </svg>
        </div>
        <h2 id="crmGlobalDeleteTitle">Delete this record?</h2>
        <p id="crmGlobalDeleteMessage">Are you sure you want to delete <span class="crm-global-delete-name" id="crmGlobalDeleteName">this record</span>?</p>
        <div class="crm-global-delete-note">This action may be irreversible. Please confirm before continuing.</div>
        <div class="crm-global-delete-buttons">
            <button type="button" class="crm-global-delete-cancel" id="crmGlobalDeleteCancel">Cancel</button>
            <button type="button" class="crm-global-delete-submit" id="crmGlobalDeleteSubmit">Yes, Delete</button>
        </div>
    </section>
</div>
<script>
(function () {
    'use strict';
    // Guard against accidental duplicate includes.
    if (window.__crmGlobalDeleteInitialized) return;
    window.__crmGlobalDeleteInitialized = true;

    const overlay = document.getElementById('crmGlobalDeleteOverlay');
    const cancel = document.getElementById('crmGlobalDeleteCancel');
    const approve = document.getElementById('crmGlobalDeleteSubmit');
    const nameNode = document.getElementById('crmGlobalDeleteName');
    if (!overlay || !cancel || !approve || !nameNode) return;

    let pendingForm = null;
    let originalFocus = null;

    function isDestructiveForm(form) {
        if (!form || form.tagName !== 'FORM') return false;
        const method = (form.getAttribute('method') || 'GET').toUpperCase();
        const spoof = form.querySelector('input[name="_method"]');
        return method === 'DELETE' || (method === 'POST' && spoof && spoof.value.toUpperCase() === 'DELETE');
    }

    function hasLocalConfirmation(form) {
        // Existing designs with their own event handlers are intentionally not changed.
        if (form.matches('.delete-form, .delivery-delete-form, .announcement-delete-form')) return true;
        if (form.dataset.crmDeleteConfirm === 'off') return true;
        if (form.closest('[data-crm-delete-modal-local]')) return true;
        return false;
    }

    function suggestedName(form) {
        const customName = form.dataset.deleteName || form.dataset.crmDeleteName;
        if (customName) return customName;
        const button = form.querySelector('button[type="submit"], input[type="submit"]');
        const aria = button?.getAttribute('aria-label') || button?.getAttribute('title') || '';
        if (/delete\s+\S/i.test(aria)) return aria.replace(/^delete\s+/i, '');
        return 'this record';
    }

    function closeModal(restoreFocus = true) {
        overlay.hidden = true;
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('crm-global-delete-open');
        pendingForm = null;
        if (restoreFocus && originalFocus && typeof originalFocus.focus === 'function') {
            originalFocus.focus();
        }
        originalFocus = null;
    }

    // Capture phase prevents legacy inline onsubmit="return confirm(...)" from
    // opening the native browser confirmation before our styled modal.
    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !isDestructiveForm(form) || hasLocalConfirmation(form)) return;
        if (event.defaultPrevented) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        pendingForm = form;
        originalFocus = event.submitter || document.activeElement;
        nameNode.textContent = suggestedName(form);
        overlay.hidden = false;
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('crm-global-delete-open');
        cancel.focus();
    }, true);

    approve.addEventListener('click', function () {
        const form = pendingForm;
        if (!form) return;
        // Native submit bypasses legacy onsubmit confirm and does NOT alter CSRF
        // or method-spoof fields. This form has already reached the submit event.
        closeModal(false);
        HTMLFormElement.prototype.submit.call(form);
    });

    cancel.addEventListener('click', function () { closeModal(); });
    overlay.addEventListener('click', function (event) {
        if (event.target === overlay) closeModal();
    });
    document.addEventListener('keydown', function (event) {
        if (overlay.hidden) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal();
        } else if (event.key === 'Tab') {
            // Dialog contains only two focusable buttons.
            if (event.shiftKey && document.activeElement === cancel) {
                event.preventDefault();
                approve.focus();
            } else if (!event.shiftKey && document.activeElement === approve) {
                event.preventDefault();
                cancel.focus();
            }
        }
    });
})();
</script>
