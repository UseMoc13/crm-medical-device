<style>
/* INDEX */
/* TABLE CONTAINER */
.table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.empty-state {
        padding: 32px !important;
        color: #6b7280;
        text-align: center !important;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        padding: 7px;
        border: 1px solid #e5e7eb;
        border-radius: 7px;
        background: #fff;
        cursor: pointer;
        text-decoration: none;
    }

    .action-btn svg {
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .action-btn.view {
        color: #2563eb;
    }

    .action-btn.edit {
        color: #d97706;
    }

    .action-btn.delete {
        color: #dc2626;
    }
    
/* TABLE STRUCTURE */
.delivery-table {
    width: 100%;
    min-width: 950px;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
}

/* TABLE HEADER */
.delivery-table thead th {
    padding: 14px 12px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    color: var(--text-muted, #64748b);
    background: var(--surface-secondary, #f8fafc);

    /* Partisi header */
    border-right: 1px solid #e2e8f0;
    border-bottom: 2px solid #cbd5e1;
}

/* TABLE BODY */
.delivery-table tbody td {
    padding: 14px 12px;
    font-size: 13px;
    vertical-align: middle;
    color: var(--text-primary, #1e293b);

    /* Partisi setiap sel */
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;

    overflow-wrap: anywhere;
}

/* HILANGKAN BORDER KANAN PADA KOLOM TERAKHIR */
.delivery-table thead th:last-child,
.delivery-table tbody td:last-child {
    border-right: none;
}

/* HILANGKAN BORDER BAWAH PADA BARIS TERAKHIR */
.delivery-table tbody tr:last-child td {
    border-bottom: none;
}

/* HOVER ROW */
.delivery-table tbody tr:hover {
    background: #f8fafc;
}

/* COLUMN WIDTHS */
.delivery-table .col-number {
    width: 6%;
}

.delivery-table .col-delivery {
    width: 16%;
}

.delivery-table .col-order {
    width: 15%;
}

.delivery-table .col-date {
    width: 12%;
}

.delivery-table .col-address {
    width: 25%;
}

.delivery-table .col-status {
    width: 12%;
}

.delivery-table .col-action {
    width: 14%;
    text-align: center;
}

.table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-top: 18px;
        color: #6b7280;
        font-size: 13px;
    }

    .table-footer {
            align-items: flex-start;
            flex-direction: column;
        }

/* DELIVERY NUMBER */
.delivery-number {
    font-weight: 600;
    white-space: nowrap;
}

/* SALES ORDER NUMBER */
.order-number {
    font-weight: 500;
    white-space: nowrap;
}

/* ADDRESS */
.address-text {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    overflow: hidden;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

/* ACTIONS */
.table-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    white-space: nowrap;
}

/* RESPONSIVE */
@media (max-width: 600px) {
    .delivery-table {
        min-width: 950px;
    }
}

/* STATUS BADGES */
.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.4;
    white-space: nowrap;
    border: 1px solid transparent;
}

/* PENDING */
.badge.warning {
    color: #92400e;
    background: #fef3c7;
    border-color: #fde68a;
}

/* IN TRANSIT */
.badge.info {
    color: #1d4ed8;
    background: #dbeafe;
    border-color: #bfdbfe;
}

/* DELIVERED */
.badge.success {
    color: #166534;
    background: #dcfce7;
    border-color: #bbf7d0;
}

/* CANCELLED */
.badge.danger {
    color: #991b1b;
    background: #fee2e2;
    border-color: #fecaca;
}

/* UNKNOWN STATUS */
.badge.secondary {
    color: #475569;
    background: #f1f5f9;
    border-color: #e2e8f0;
}

/* CREATE */
/* VALIDATION */
    .validation-list {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .validation-list li {
        margin-bottom: 4px;
    }

    /* FORM GRID */
    .delivery-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .delivery-form-grid .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
        margin-bottom: 4px;
    }

    .delivery-form-grid .full-width {
        grid-column: 1 / -1;
    }

    /* SECTION HEADINGS */
    .form-section + .form-section {
        margin-top: 30px;
        padding-top: 26px;
        border-top: 1px solid var(--border, #e2e8f0);
    }

    .section-heading {
        margin-bottom: 20px;
    }

    .section-heading h4 {
        margin: 0 0 6px;
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary, #1e293b);
    }

    .section-heading p {
        margin: 0;
        font-size: 13px;
        line-height: 1.6;
        color: var(--text-muted, #64748b);
    }

    /* LABELS */
    .delivery-form-grid label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary, #334155);
    }

    .required-mark {
        color: #dc2626;
        font-weight: 600;
    }

    .required-note {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: var(--text-muted, #64748b);
        white-space: nowrap;
    }

    /* INPUTS */
    .delivery-form-grid .form-control {
        box-sizing: border-box;
        display: block;
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid var(--border, #cbd5e1);
        border-radius: 7px;
        background: var(--surface, #ffffff);
        color: var(--text-primary, #1e293b);
        font-family: inherit;
        font-size: 13px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .delivery-form-grid textarea.form-control {
        min-height: auto;
        line-height: 1.6;
        resize: vertical;
    }

    .delivery-form-grid .form-control:focus {
        outline: none;
        border-color: var(--primary, #2563eb);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
    }

    .delivery-form-grid .form-control[readonly] {
        background: var(--surface-secondary, #f8fafc);
        color: var(--text-muted, #64748b);
        cursor: default;
    }

    .delivery-form-grid .form-control.is-invalid {
        border-color: #dc2626;
    }

    /* AUTOMATIC DELIVERY NUMBER */
    .auto-number-field {
        position: relative;
    }

    .auto-number-field .form-control {
        padding-right: 65px;
    }

    .auto-number-badge {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        padding: 4px 7px;
        border-radius: 5px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* HELP TEXT AND ERRORS */
    .delivery-form-grid .form-hint {
        font-size: 11px;
        line-height: 1.6;
        color: var(--text-muted, #64748b);
    }

    .delivery-form-grid .field-error {
        color: #dc2626;
        font-size: 12px;
        line-height: 1.5;
    }

    /* FORM ACTIONS */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 30px;
        padding-top: 22px;
        border-top: 1px solid var(--border, #e2e8f0);
    }

    /* DISABLED BUTTON */
    .form-actions button:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .delivery-form-grid {
            grid-template-columns: 1fr;
            gap: 18px;
        }

        .delivery-form-grid .full-width {
            grid-column: auto;
        }

        .page-head {
            flex-wrap: wrap;
            gap: 14px;
        }

        .page-head .actions {
            width: 100%;
        }

        .required-note {
            margin-top: 8px;
        }
    }

    @media (max-width: 480px) {
        .form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .form-actions .btn {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
    }

/* EDIT */
.delivery-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .delivery-form-grid .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .delivery-form-grid .full-width {
        grid-column: 1 / -1;
    }

    .delivery-form-grid label {
        font-size: 13px;
        font-weight: 600;
    }

    .delivery-form-grid input,
    .delivery-form-grid select,
    .delivery-form-grid textarea {
        box-sizing: border-box;
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border-color, #d1d5db);
        border-radius: 6px;
        background: #fff;
        font: inherit;
        font-size: 14px;
    }

    .delivery-form-grid textarea {
        resize: vertical;
    }

    .delivery-form-grid input:focus,
    .delivery-form-grid select:focus,
    .delivery-form-grid textarea:focus {
        outline: none;
        border-color: var(--primary-color, #2563eb);
    }

    .delivery-form-grid input[readonly] {
        background: var(--surface-secondary, #f8fafc);
        color: var(--text-muted, #64748b);
    }

    .delivery-form-grid small {
        font-size: 12px;
        color: var(--text-muted, #64748b);
    }

    .delivery-form-grid .required {
        color: #dc2626;
    }

    .delivery-form-grid .field-error {
        color: #dc2626;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color, #e5e7eb);
    }

    @media (max-width: 768px) {
        .delivery-form-grid {
            grid-template-columns: 1fr;
        }

        .delivery-form-grid .full-width {
            grid-column: auto;
        }
    }

/* SHOW */
/* =====================================================
       DELIVERY DETAIL
    ====================================================== */

    .delivery-information-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
    }

    .delivery-information-grid .information-field {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        min-width: 0;
    }

    .delivery-information-grid .full-width {
        grid-column: 1 / -1;
    }

    .delivery-information-grid .information-label {
        color: var(--text-muted, #64748b);
        font-size: 13px;
    }

    .delivery-information-grid .information-value {
        color: var(--text-primary, #1e293b);
        font-size: 14px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .delivery-number {
        letter-spacing: 0.2px;
    }

    .delivery-text {
        white-space: pre-line;
        line-height: 1.7;
        font-weight: 400 !important;
    }

    /* =====================================================
       DELIVERY STATUS BADGE
    ====================================================== */

    .delivery-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        padding: 6px 11px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-in-transit {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .status-delivered {
        background: #dcfce7;
        color: #166534;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-unknown {
        background: #f1f5f9;
        color: #475569;
    }

    /* =====================================================
       SALES ORDER INFORMATION
    ====================================================== */

    .sales-order-content {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .sales-order-heading {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 16px;
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 8px;
        background: var(--surface-secondary, #f8fafc);
    }

    .sales-order-number {
        color: var(--text-primary, #1e293b);
        font-size: 17px;
        overflow-wrap: anywhere;
    }

    .sales-order-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .sales-order-grid .information-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .sales-order-grid .information-label {
        color: var(--text-muted, #64748b);
        font-size: 13px;
    }

    .sales-order-grid .information-value {
        color: var(--text-primary, #1e293b);
        font-size: 14px;
        overflow-wrap: anywhere;
    }

    @media (max-width: 768px) {
        .delivery-information-grid,
        .sales-order-grid {
            grid-template-columns: 1fr;
        }

        .delivery-information-grid .full-width {
            grid-column: auto;
        }

        .page-head {
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-head .actions {
            flex-wrap: wrap;
        }
    }
/* Shared delivery workflow */
.delivery-workflow { margin-bottom: 18px; }
.delivery-workflow-body { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.delivery-workflow-body form { margin: 0 !important; display: inline-flex; }
.delivery-workflow-body .btn { min-height: 38px; }
.delivery-workflow-note { margin: 9px 0 0; color: #7d8797; font-size: 12px; }
.delivery-table .table-actions form { margin: 0 !important; display: inline-flex; }
.delivery-origin-link { color: #176d97; font-weight: 700; text-decoration: none; }
.delivery-origin-link:hover { text-decoration: underline; }
.delivery-confirm[hidden] { display: none !important; }
.delivery-confirm { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; padding: 18px; z-index: 20000; background: rgba(14,28,54,.58); }
.delivery-confirm-dialog { width: min(100%,440px); box-sizing:border-box; background:#fff; padding: 26px; border-radius: 14px; box-shadow:0 22px 70px rgba(20,32,55,.22); }
.delivery-confirm-dialog h3 { margin: 0 0 12px; font-size:19px; color:#17284f; }
.delivery-confirm-dialog p { color:#667085; font-size:13px; line-height:1.6; margin:0 0 20px; }
.delivery-confirm-actions { display:flex; gap:9px; justify-content:flex-end; flex-wrap:wrap; }
.delivery-warning { color: #9a6a18; font-size:12px; }

.delivery-form-grid input[readonly] { background:#f6f8fb; color:#64748b; cursor:default; }
.delivery-workflow .card-body { padding:20px; }
.delivery-table td { vertical-align:middle; }

/* Unified Delivery listing */
.delivery-table { border-collapse: collapse; border-spacing: 0; width:100%; table-layout:auto; min-width:1040px; }
.delivery-table thead th {background:#f3f4f6;color:#64748b;padding:14px 12px;font-size:11px;letter-spacing:.3px;text-transform:uppercase;border:1px solid #d1d5db;text-align:left;}
.delivery-table tbody td {padding:13px 12px;font-size:12px;color:#17284f;border:1px solid #d1d5db;vertical-align:middle;}
.delivery-table .col-number {width:55px;text-align:center}.delivery-table .col-action {width:160px;text-align:center}
.delivery-table .col-status{width:115px}.delivery-table .col-address{min-width:220px}.delivery-table .col-delivery{min-width:170px}
.delivery-table .table-actions form{margin:0!important;padding:0;display:inline-flex}.delivery-table .table-actions .action-btn{margin:0!important;vertical-align:middle}
.table-wrap{border-color:#d1d5db}.table-actions{gap:7px}.delivery-table tbody tr:hover{background:#f8fafc}
/* Confirm dialog */
.delivery-confirm-dialog{text-align:center;padding:29px;border-radius:16px}.delivery-modal-symbol{display:grid;place-items:center;margin:0 auto 15px;width:54px;height:54px;border-radius:15px;background:#fff1ef;color:#c33e46;font-size:24px;font-weight:800}
.delivery-confirm-dialog h3{font-size:19px;margin:0 0 9px}.delivery-confirm-dialog p{font-size:13px;margin:0 0 22px}.delivery-confirm-actions{justify-content:center}
.delivery-delete-confirm{background:#c94a4a;color:#fff;border-color:#c94a4a}.delivery-delete-confirm:hover{background:#b4383e}.delivery-delete-confirm[hidden]{display:none}
/* Delivery forms and live preview */
.delivery-editor-layout{display:grid;grid-template-columns:minmax(0,1.3fr) minmax(310px,.7fr);gap:22px;align-items:start}
.delivery-editor-layout>.card{min-width:0}.delivery-preview-panel{position:sticky;top:88px;min-width:0;border:1px solid #e3e8ef;border-radius:12px;padding:24px;background:#fafbfd;box-sizing:border-box}
.delivery-preview-eyebrow{font-size:10px;letter-spacing:1px;font-weight:700;color:#8190a3;margin-bottom:20px}.delivery-preview-title{display:flex;align-items:center;gap:12px;padding-bottom:20px;border-bottom:1px solid #e3e8ef}
.delivery-preview-icon{width:42px;height:42px;display:grid;place-items:center;border-radius:10px;background:#e5f5ef;color:#128165;font-size:24px}
.delivery-preview-title h3{margin:0 0 5px;color:#17284f;font-size:16px}.delivery-preview-title small{color:#8792a5;font-size:11px}
.delivery-preview-line{display:flex;gap:15px;justify-content:space-between;align-items:start;padding:12px 0;border-bottom:1px solid #e8edf3;font-size:11px}.delivery-preview-line>span:first-child{color:#8390a3}.delivery-preview-line strong{font-size:12px;color:#17284f;text-align:right;overflow-wrap:anywhere}
.delivery-preview-badge{background:#eaf5f0;color:#15815f;border-radius:6px;padding:5px 9px;font-weight:700;font-size:10px}.delivery-preview-section{padding:17px 0;border-bottom:1px solid #e8edf3}.delivery-preview-section>span{display:block;color:#8390a3;font-size:10px;font-weight:700;margin-bottom:9px}
.delivery-preview-address{font-size:12px;line-height:1.65;color:#34415c;overflow-wrap:anywhere}.delivery-preview-notice{background:#edf4f3;padding:12px;border-radius:8px;font-size:10px;line-height:1.6;color:#57746c;margin-top:18px}
/* Map selection */
.delivery-address-controls{display:flex;gap:9px;flex-wrap:wrap;align-items:center;margin-top:9px}.delivery-address-controls small{font-size:10px;color:#8792a5}
.delivery-map-button{color:#176b87;border-color:#b9d7e3!important;background:#f1f9fc!important}.delivery-map-modal[hidden]{display:none!important}.delivery-map-modal{position:fixed;inset:0;z-index:30000;background:rgba(14,28,54,.62);display:flex;align-items:center;justify-content:center;padding:14px}
.delivery-map-dialog{width:min(820px,100%);max-height:94vh;overflow:auto;background:white;border-radius:15px;padding:22px;box-shadow:0 25px 70px rgba(10,27,50,.24);box-sizing:border-box}
.delivery-map-top{display:flex;justify-content:space-between;gap:20px}.delivery-map-top h3{margin:0;color:#17284f;font-size:18px}.delivery-map-top p{margin:6px 0 16px;color:#7d8797;font-size:12px}.delivery-map-close{border:0;background:#f0f3f7;border-radius:7px;width:32px;height:32px;font-size:22px;cursor:pointer}
.delivery-map-search{display:flex;gap:8px;margin-bottom:12px!important}.delivery-map-search input{min-width:0;flex:1;height:40px;border:1px solid #d9dee8;border-radius:8px;padding:0 12px}
.delivery-map-canvas{width:100%;height:min(390px,46vh);border:1px solid #e1e7ef;border-radius:9px;z-index:1;background:#f1f3f6}.delivery-map-feedback{color:#718096;font-size:11px;margin:9px 0}
.delivery-map-selected{padding:11px;background:#f7f9fb;border:1px solid #e7edf3;border-radius:8px;font-size:12px;color:#34415c;line-height:1.5}
.delivery-map-footer{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:14px}.delivery-map-footer>span{color:#8993a1;font-size:10px}.delivery-map-footer>div{display:flex;gap:8px}.delivery-map-open{overflow:hidden}
@media(max-width:1050px){.delivery-editor-layout{grid-template-columns:1fr}.delivery-preview-panel{position:static}}
@media(max-width:600px){.delivery-map-footer{align-items:stretch;flex-direction:column}.delivery-map-search{flex-direction:column}.delivery-map-canvas{height:340px}.delivery-map-dialog{padding:15px}}



.delivery-filter-bar {
    display: flex; align-items: center; flex-wrap: wrap; gap: 10px;
    margin-bottom: 18px;
}
.delivery-filter-search { flex: 1 1 260px; min-width: 210px; }
.delivery-filter-bar input, .delivery-filter-bar select {
    height: 40px; max-width: 100%; padding: 0 12px; border: 1px solid #d9dee8;
    border-radius: 8px; background: #fff; color: #34415c; font-size: 12px;
}
.delivery-filter-search input { width: 100%; }
.delivery-filter-bar select { min-width: 145px; }
.delivery-filter-bar .btn { height: 40px; white-space: nowrap; }
.delivery-table-wrap { border: 1px solid #e5e7eb; border-radius: 8px; overflow-x: auto; }
.delivery-table { width: 100%; min-width: 1010px; table-layout: auto; border-collapse: collapse; border-spacing: 0; font-size: 12px; }
.delivery-table thead th {
    padding: 13px 14px; border: 1px solid #e5e7eb; background: #f9fafb;
    color: #374151; font-size: 10px; font-weight: 600; text-align: left;
    text-transform: uppercase; letter-spacing: .5px; white-space: nowrap;
}
.delivery-table tbody td {
    padding: 13px 14px; border: 1px solid #e5e7eb; color: #17284f;
    font-size: 12px; text-align: left; vertical-align: middle;
}
.delivery-table th:first-child, .delivery-table td:first-child { width: 55px; text-align: center !important; }
.delivery-table th:last-child, .delivery-table td:last-child { text-align: center; }
.delivery-table tbody tr:hover { background: #f8fafc; }
.delivery-table .table-actions { justify-content: center; align-items: center; }
.delivery-table .table-actions form, .delivery-table .table-actions .action-btn { margin: 0 !important; }
.delivery-table .col-address { min-width: 220px; }
.delivery-table .col-action { min-width: 145px; }
.delivery-table .col-number { width: 55px; }
.table-footer { display:flex; flex-direction: row; justify-content: space-between; align-items: center; gap:14px; flex-wrap:wrap; }
.delivery-pagination nav { display: flex; align-items: center; }
.delivery-map-modal { isolation: isolate; }
.delivery-map-dialog { position: relative; width: min(820px, 100%); max-height: calc(100dvh - 28px); overflow-y: auto; overflow-x: hidden; }
#deliveryMapCanvas.delivery-map-canvas {
    position: relative !important; display: block; width: 100%; height: 360px;
    overflow: hidden !important; isolation: isolate; padding: 0 !important;
    z-index: 0; border-radius: 9px; box-sizing: border-box;
}
#deliveryMapCanvas .leaflet-pane, #deliveryMapCanvas .leaflet-map-pane,
#deliveryMapCanvas .leaflet-layer, #deliveryMapCanvas .leaflet-tile-pane { max-width: none !important; }
#deliveryMapCanvas .leaflet-tile {
    width: 256px !important; height: 256px !important;
    max-width: none !important; max-height: none !important;
    min-width: 0 !important; padding: 0 !important; margin: 0 !important;
    border: 0 !important; border-radius: 0 !important;
    object-fit: fill !important; box-shadow: none !important;
}
#deliveryMapCanvas .leaflet-marker-icon, #deliveryMapCanvas .leaflet-marker-shadow {
    max-width: none !important; max-height: none !important; padding: 0 !important;
    border-radius: 0 !important; object-fit: initial !important;
}
#deliveryMapCanvas .leaflet-control-container { position: static; }
#deliveryMapCanvas .leaflet-control { box-sizing: content-box; }
@media (max-width: 700px) {
    .delivery-filter-bar { flex-direction: column; align-items: stretch; }
    .delivery-filter-search, .delivery-filter-bar select, .delivery-filter-bar .btn { width: 100%; }
    #deliveryMapCanvas.delivery-map-canvas { height: 310px; }
    .delivery-map-dialog { padding: 15px; }
}
.delivery-map-frame { display:block; width:100%; height:360px; border:1px solid #e1e7ef; border-radius:9px; background:#eef2f6; }
@media (max-width:700px) { .delivery-map-frame { height:310px; } }

.delivery-serial-card { margin-bottom: 18px; }
.delivery-serial-form { display: flex; align-items: end; flex-wrap: wrap; gap: 10px; margin: 0 0 18px; }
.delivery-serial-form label { width: 100%; color: #17284f; font-size: 12px; font-weight: 600; }
.delivery-serial-form select { min-height: 40px; flex: 1 1 320px; border: 1px solid #d9dee8; border-radius: 8px; padding: 8px 12px; color: #17284f; }
.delivery-serial-grid { display:grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 16px; }
.delivery-serial-summary { padding: 15px; border:1px solid #e5e7eb; border-radius: 9px; background:#fafbfd; }
.delivery-serial-summary > strong { display:block; margin-bottom: 12px; color:#17284f; font-size: 13px; }
.delivery-serial-summary p { color: #53627a; font-size: 12px; margin: 9px 0; }
.delivery-serial-summary p span { float:right; }
.delivery-serial-assigned { display:flex; align-items:center; justify-content:space-between; gap:12px; border-top:1px solid #e5e7eb; padding: 8px 0; color:#34415c; font-size:12px; }
.delivery-serial-assigned form { margin:0 !important; }
@media(max-width:750px){.delivery-serial-grid{grid-template-columns:1fr}}

.delivery-source-warehouse{display:block;margin-top:4px;color:#7d8797;font-size:11px}
.delivery-dispatch-records{margin-top:18px;padding:14px;border:1px solid #d9e8e4;background:#f6fbf9;border-radius:8px}
.delivery-dispatch-records strong{font-size:12px;color:#17284f}
.delivery-dispatch-records p{font-size:12px;color:#40546a;margin:8px 0 0}
.delivery-serial-form .delivery-warning{flex-basis:100%;font-size:11px;margin:0}
</style>
