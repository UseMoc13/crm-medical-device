<style>
/* Service Tickets module — index, create, edit, show, shared partials.
   Consolidated without changing existing selector declarations. */

/* ========================================================
   INDEX.BLADE.PHP
   ======================================================== */
/* =====================================================
   FILTER BAR
===================================================== */

.ticket-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}


/* =====================================================
   SEARCH
===================================================== */

.ticket-search {
    position: relative;
    flex: 1 1 270px;
    min-width: 220px;
}

.ticket-search input {
    width: 100%;
    height: 40px;
    box-sizing: border-box;
    padding: 0 38px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #17284f;
    font-size: 13px;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.ticket-search input:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}

.ticket-search-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #7d8797;
    font-size: 19px;
    pointer-events: none;
}

.ticket-search-clear {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #7d8797;
    text-decoration: none;
    font-size: 18px;
}

.ticket-search-clear:hover {
    background: #edf1f5;
    color: #17284f;
}


/* =====================================================
   FILTER SELECT
===================================================== */

.ticket-filter-select select {
    width: 100%;
    min-width: 145px;
    height: 40px;
    padding: 0 32px 0 12px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #34415c;
    font-size: 12px;
    cursor: pointer;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.ticket-filter-select select:hover {
    border-color: #b7c0cf;
}

.ticket-filter-select select:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}


/* =====================================================
   FILTER BUTTONS
===================================================== */

.ticket-filter-button,
.ticket-reset-button {
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
}

.ticket-reset-button {
    text-decoration: none;
}


/* =====================================================
   FILTER SUMMARY
===================================================== */

.ticket-filter-summary {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
    margin-bottom: 15px;
    color: #7d8797;
    font-size: 12px;
}

.ticket-filter-summary .filter-chip {
    padding: 5px 9px;
    border-radius: 6px;
    background: #f1f5f7;
    color: #34415c;
    font-size: 11px;
}


/* =====================================================
   TICKET INFORMATION
===================================================== */

.ticket-number-link {
    color: #223a70;
    text-decoration: none;
    transition: color .18s ease;
}

.ticket-number-link:hover {
    color: #2ba7a0;
}

.ticket-number-link strong {
    font-size: 13px;
    letter-spacing: .15px;
}

.ticket-title {
    margin-top: 4px;
    color: #7d8797;
    font-size: 11px;
    line-height: 1.5;
}

.ticket-customer-name {
    color: #34415c;
    font-size: 12px;
    font-weight: 500;
}

.ticket-product-name {
    color: #34415c;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.5;
}

.ticket-technician-name {
    color: #34415c;
    font-size: 12px;
}

.ticket-unassigned {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 6px;
    background: #f2f4f7;
    color: #8791a1;
    font-size: 11px;
}


/* =====================================================
   PRIORITY BADGES
===================================================== */

.ticket-priority-badge,
.ticket-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 9px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.ticket-priority-low {
    background: #f0f1f4;
    color: #737d8d;
}

.ticket-priority-medium {
    background: #fff5d9;
    color: #946200;
}

.ticket-priority-high {
    background: #fff0df;
    color: #b97828;
}

.ticket-priority-urgent {
    background: #fdeaea;
    color: #c03939;
}


/* =====================================================
   STATUS BADGES
===================================================== */

.ticket-status-open {
    background: #edf1fa;
    color: #385b9a;
}

.ticket-status-in-progress {
    background: #fff5d9;
    color: #946200;
}

.ticket-status-on-hold {
    background: #fff0df;
    color: #b97828;
}

.ticket-status-resolved {
    background: #e8f5ef;
    color: #16805f;
}

.ticket-status-closed {
    background: #e7f6f5;
    color: #168b85;
}

.ticket-status-unknown {
    background: #f0f1f4;
    color: #737d8d;
}


/* =====================================================
   TABLE ACTIONS
===================================================== */

.table-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.table-actions .action-btn {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 1px solid #e0e5ed;
    border-radius: 8px;
    background: #fff;
    color: #68758b;
    text-decoration: none;
    font-size: 14px;
    line-height: 1;
    cursor: pointer;
    transition:
        color .18s ease,
        background .18s ease,
        border-color .18s ease;
}

.table-actions .action-btn.view:hover {
    background: #edf4ff;
    border-color: #cbdcf9;
    color: #3569b8;
}

.table-actions .action-btn.edit:hover {
    background: #e8f5ef;
    border-color: #c3e6d6;
    color: #16805f;
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

/* =====================================================
   TABLE
===================================================== */

.table-wrap.ticket-table-wrap {
    width: 100% !important;
    overflow-x: auto !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px;
}

.table-wrap.ticket-table-wrap table.ticket-table {
    width: 100% !important;
    min-width: 1250px;
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    border: 1px solid #cbd5e1 !important;
}

.table-wrap.ticket-table-wrap table.ticket-table thead th {
    border: 1px solid #cbd5e1 !important;
    background-color: #f3f5f9 !important;
}

.table-wrap.ticket-table-wrap table.ticket-table tbody td {
    border: 1px solid #d5dce6 !important;
}

.table-wrap.ticket-table-wrap table.ticket-table tbody tr {
    border-bottom: 1px solid #d5dce6 !important;
}

.table-wrap.ticket-table-wrap table.ticket-table th,
.table-wrap.ticket-table-wrap table.ticket-table td {
    border-right: 1px solid #d5dce6 !important;
    border-bottom: 1px solid #d5dce6 !important;
}

.table-wrap.ticket-table-wrap table.ticket-table tbody tr:hover {
    background-color: #f8fafc !important;
}

.table-wrap.ticket-table-wrap table.ticket-table tr:last-child td {
    border-bottom: 1px solid #d5dce6 !important;
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


/* =====================================================
   TABLE COLUMN WIDTHS
===================================================== */

.ticket-table .col-number {
    width: 55px;
    min-width: 55px;
    max-width: 55px;
    text-align: center !important;
    white-space: nowrap;
}

.ticket-table .col-ticket {
    min-width: 190px;
}

.ticket-table .col-customer {
    min-width: 165px;
}

.ticket-table .col-device {
    min-width: 200px;
}

.ticket-table .col-technician {
    min-width: 150px;
}

.ticket-table .col-opened {
    min-width: 115px;
    white-space: nowrap;
}

.ticket-table .col-priority,
.ticket-table .col-status {
    min-width: 105px;
}

.ticket-table .col-actions {
    min-width: 100px;
    text-align: center;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1200px) {

    .ticket-search {
        flex: 1 1 100%;
    }

    .ticket-filter-select {
        flex: 1 1 150px;
        min-width: 0;
    }

}

@media (max-width: 700px) {

    .ticket-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .ticket-search,
    .ticket-filter-select,
    .ticket-filter-button,
    .ticket-reset-button {
        width: 100%;
        box-sizing: border-box;
    }

    .ticket-filter-select select {
        box-sizing: border-box;
    }

}

/* ========================================================
   CREATE.BLADE.PHP
   ======================================================== */
/* =========================================================
   SERVICE TICKET CREATE
========================================================= */

.ticket-create-card,
.ticket-create-body,
.ticket-form-layout,
.ticket-form-main {
    overflow: visible !important;
}

/* =========================================================
   FORM LAYOUT
========================================================= */

.ticket-form-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.25fr) minmax(280px, .75fr);
    gap: 32px;
    align-items: start;
    width: 100%;
}

.ticket-form-main {
    min-width: 0;
}

/* =========================================================
   SECTION DIVIDER
========================================================= */

.ticket-form-main .section-divider {
    margin: 25px 0 18px;
    padding-top: 20px;
    border-top: 1px solid #edf0f5;
}

.ticket-form-main .section-divider.first-section {
    margin-top: 0;
    padding-top: 0;
    border-top: 0;
}

.ticket-form-main .section-divider h4 {
    margin: 0 0 4px;
    color: #17284f;
    font-size: 13px;
    font-weight: 700;
}

.ticket-form-main .section-divider p {
    margin: 0;
    color: #8a94a6;
    font-size: 11px;
    line-height: 1.5;
}

/* =========================================================
   FORM GROUP
========================================================= */

.ticket-form-main .form-group {
    margin-bottom: 19px;
}

.ticket-form-main .form-group label {
    display: block;
    margin-bottom: 7px;
    color: #17284f;
    font-size: 13px;
    font-weight: 600;
}

.ticket-form-main .required {
    color: #c94a4a;
}

/* =========================================================
   INPUT / SELECT / TEXTAREA
========================================================= */

.ticket-form-main .form-group input,
.ticket-form-main .form-group select,
.ticket-form-main .form-group textarea {
    display: block;
    width: 100%;
    box-sizing: border-box;
    padding: 11px 12px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #17284f;
    font-family: inherit;
    font-size: 13px;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.ticket-form-main .form-group input,
.ticket-form-main .form-group select {
    height: 42px;
}

.ticket-form-main .form-group textarea {
    min-height: 115px;
    resize: vertical;
    line-height: 1.6;
}

.ticket-form-main .form-group input:focus,
.ticket-form-main .form-group select:focus,
.ticket-form-main .form-group textarea:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}

.ticket-form-main .form-group input::placeholder,
.ticket-form-main .form-group textarea::placeholder {
    color: #a0a8b6;
}

/* =========================================================
   HINT / ERROR
========================================================= */

.ticket-form-main .form-hint,
.ticket-form-main .form-error {
    display: block;
    margin-top: 6px;
    font-size: 11px;
    line-height: 1.5;
}

.ticket-form-main .form-hint {
    color: #8a94a6;
}

.ticket-form-main .form-error {
    color: #c94a4a;
}

/* =========================================================
   FORM ACTIONS
========================================================= */

.ticket-form-main .form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 8px;
    padding-top: 16px;
    margin-top: 8px;
    border-top: 1px solid #edf0f5;
}

/* =========================================================
   PREVIEW PANEL
========================================================= */

.ticket-preview-panel {
    position: sticky;
    top: 92px;
    align-self: start;
    min-width: 0;
    height: fit-content;
    padding: 24px;
    box-sizing: border-box;
    border: 1px solid #e6eaf0;
    border-radius: 12px;
    background: #fafbfd;
    z-index: 5;
}

.ticket-preview-panel .preview-label {
    margin-bottom: 20px;
    color: #8a94a6;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.2px;
}

.ticket-preview-panel .preview-ticket-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 52px;
    height: 52px;
    margin-bottom: 16px;
    border-radius: 12px;
    background: #eaf7f3;
    color: #167d70;
    font-size: 14px;
    font-weight: 700;
}

.ticket-preview-panel .preview-ticket-number {
    color: #17284f;
    font-size: 17px;
    font-weight: 700;
}

.ticket-preview-panel .preview-caption {
    margin-top: 5px;
    color: #7d8797;
    font-size: 11px;
}

.ticket-preview-panel .preview-divider {
    height: 1px;
    margin: 21px 0;
    background: #e5e9ef;
}

.ticket-preview-panel .preview-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 17px;
}

.ticket-preview-panel .preview-item span {
    color: #8a94a6;
    font-size: 11px;
}

.ticket-preview-panel .preview-item strong {
    overflow-wrap: anywhere;
    color: #34415c;
    font-size: 12px;
    font-weight: 600;
}

.ticket-preview-panel .preview-item .preview-priority,
.ticket-preview-panel .preview-item .preview-status {
    display: inline-block;
    width: fit-content;
    padding: 5px 9px;
    border-radius: 6px;
    background: #eef1f6;
    color: #596579;
    font-size: 11px;
}

.ticket-preview-panel .preview-item .preview-priority.priority-low {
    background: #eaf7f3;
    color: #167d70;
}

.ticket-preview-panel .preview-item .preview-priority.priority-medium {
    background: #eef3fb;
    color: #41638f;
}

.ticket-preview-panel .preview-item .preview-priority.priority-high {
    background: #fff3df;
    color: #a66b12;
}

.ticket-preview-panel .preview-item .preview-priority.priority-urgent {
    background: #fdebec;
    color: #bd4148;
}

.ticket-preview-panel .preview-item .preview-status.status-open {
    background: #eef3fb;
    color: #41638f;
}

.ticket-preview-panel .preview-item .preview-status.status-in-progress {
    background: #eaf7f3;
    color: #167d70;
}

.ticket-preview-panel .preview-item .preview-status.status-on-hold {
    background: #fff3df;
    color: #a66b12;
}

.ticket-preview-panel .preview-item .preview-status.status-resolved,
.ticket-preview-panel .preview-item .preview-status.status-closed {
    background: #eaf7f3;
    color: #167d70;
}

.ticket-preview-panel .preview-note {
    padding: 13px 14px;
    border-radius: 8px;
    background: #f1f5f7;
}

.ticket-preview-panel .preview-note strong {
    display: block;
    margin-bottom: 5px;
    color: #34415c;
    font-size: 11px;
}

.ticket-preview-panel .preview-note p {
    margin: 0;
    color: #8a94a6;
    font-size: 10px;
    line-height: 1.6;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {
    .ticket-form-layout {
        grid-template-columns: 1fr;
    }

    .ticket-preview-panel {
        position: static;
        order: -1;
    }
}

@media (max-width: 600px) {
    .ticket-form-main .form-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .ticket-form-main .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

/* ========================================================
   EDIT.BLADE.PHP
   ======================================================== */
/* =========================================================
   SERVICE TICKET EDIT
========================================================= */

.ticket-create-card,
.ticket-create-body,
.ticket-form-layout,
.ticket-form-main {
    overflow: visible !important;
}

/* =========================================================
   FORM LAYOUT
========================================================= */

.ticket-form-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.25fr) minmax(280px, .75fr);
    gap: 32px;
    align-items: start;
    width: 100%;
}

.ticket-form-main {
    min-width: 0;
}

/* =========================================================
   SECTION DIVIDER
========================================================= */

.ticket-form-main .section-divider {
    margin: 25px 0 18px;
    padding-top: 20px;
    border-top: 1px solid #edf0f5;
}

.ticket-form-main .section-divider.first-section {
    margin-top: 0;
    padding-top: 0;
    border-top: 0;
}

.ticket-form-main .section-divider h4 {
    margin: 0 0 4px;
    color: #17284f;
    font-size: 13px;
    font-weight: 700;
}

.ticket-form-main .section-divider p {
    margin: 0;
    color: #8a94a6;
    font-size: 11px;
    line-height: 1.5;
}

/* =========================================================
   FORM GROUP
========================================================= */

.ticket-form-main .form-group {
    margin-bottom: 19px;
}

.ticket-form-main .form-group label {
    display: block;
    margin-bottom: 7px;
    color: #17284f;
    font-size: 13px;
    font-weight: 600;
}

.ticket-form-main .required {
    color: #c94a4a;
}

/* =========================================================
   INPUT / SELECT / TEXTAREA
========================================================= */

.ticket-form-main .form-group input,
.ticket-form-main .form-group select,
.ticket-form-main .form-group textarea {
    display: block;
    width: 100%;
    box-sizing: border-box;
    padding: 11px 12px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #17284f;
    font-family: inherit;
    font-size: 13px;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.ticket-form-main .form-group input,
.ticket-form-main .form-group select {
    height: 42px;
}

.ticket-form-main .form-group textarea {
    min-height: 115px;
    resize: vertical;
    line-height: 1.6;
}

.ticket-form-main .form-group input:focus,
.ticket-form-main .form-group select:focus,
.ticket-form-main .form-group textarea:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}

.ticket-form-main .form-group input::placeholder,
.ticket-form-main .form-group textarea::placeholder {
    color: #a0a8b6;
}

.ticket-form-main .form-group input[readonly] {
    background: #f5f7fa;
    color: #788397;
    cursor: not-allowed;
}

/* =========================================================
   HINT / ERROR
========================================================= */

.ticket-form-main .form-hint,
.ticket-form-main .form-error {
    display: block;
    margin-top: 6px;
    font-size: 11px;
    line-height: 1.5;
}

.ticket-form-main .form-hint {
    color: #8a94a6;
}

.ticket-form-main .form-error {
    color: #c94a4a;
}

/* =========================================================
   FORM ACTIONS
========================================================= */

.ticket-form-main .form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 8px;
    padding-top: 16px;
    margin-top: 8px;
    border-top: 1px solid #edf0f5;
}

/* =========================================================
   PREVIEW PANEL
========================================================= */

.ticket-preview-panel {
    position: sticky;
    top: 92px;
    align-self: start;
    min-width: 0;
    height: fit-content;
    padding: 24px;
    box-sizing: border-box;
    border: 1px solid #e6eaf0;
    border-radius: 12px;
    background: #fafbfd;
    z-index: 5;
}

.ticket-preview-panel .preview-label {
    margin-bottom: 20px;
    color: #8a94a6;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.2px;
}

.ticket-preview-panel .preview-ticket-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 52px;
    height: 52px;
    margin-bottom: 16px;
    border-radius: 12px;
    background: #eaf7f3;
    color: #167d70;
    font-size: 14px;
    font-weight: 700;
}

.ticket-preview-panel .preview-ticket-number {
    color: #17284f;
    font-size: 17px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.ticket-preview-panel .preview-caption {
    margin-top: 5px;
    color: #7d8797;
    font-size: 11px;
}

.ticket-preview-panel .preview-divider {
    height: 1px;
    margin: 21px 0;
    background: #e5e9ef;
}

.ticket-preview-panel .preview-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 17px;
}

.ticket-preview-panel .preview-item span {
    color: #8a94a6;
    font-size: 11px;
}

.ticket-preview-panel .preview-item strong {
    overflow-wrap: anywhere;
    color: #34415c;
    font-size: 12px;
    font-weight: 600;
}

.ticket-preview-panel .preview-item .preview-priority,
.ticket-preview-panel .preview-item .preview-status {
    display: inline-block;
    width: fit-content;
    padding: 5px 9px;
    border-radius: 6px;
    background: #eef1f6;
    color: #596579;
    font-size: 11px;
}

.ticket-preview-panel .preview-item .preview-priority.priority-low {
    background: #eaf7f3;
    color: #167d70;
}

.ticket-preview-panel .preview-item .preview-priority.priority-medium {
    background: #eef3fb;
    color: #41638f;
}

.ticket-preview-panel .preview-item .preview-priority.priority-high {
    background: #fff3df;
    color: #a66b12;
}

.ticket-preview-panel .preview-item .preview-priority.priority-urgent {
    background: #fdebec;
    color: #bd4148;
}

.ticket-preview-panel .preview-item .preview-status.status-open {
    background: #eef3fb;
    color: #41638f;
}

.ticket-preview-panel .preview-item .preview-status.status-in-progress {
    background: #eaf7f3;
    color: #167d70;
}

.ticket-preview-panel .preview-item .preview-status.status-on-hold {
    background: #fff3df;
    color: #a66b12;
}

.ticket-preview-panel .preview-item .preview-status.status-resolved,
.ticket-preview-panel .preview-item .preview-status.status-closed {
    background: #eaf7f3;
    color: #167d70;
}

.ticket-preview-panel .preview-note {
    padding: 13px 14px;
    border-radius: 8px;
    background: #f1f5f7;
}

.ticket-preview-panel .preview-note strong {
    display: block;
    margin-bottom: 5px;
    color: #34415c;
    font-size: 11px;
}

.ticket-preview-panel .preview-note p {
    margin: 0;
    color: #8a94a6;
    font-size: 10px;
    line-height: 1.6;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {
    .ticket-form-layout {
        grid-template-columns: 1fr;
    }

    .ticket-preview-panel {
        position: static;
        order: -1;
    }
}

@media (max-width: 600px) {
    .ticket-form-main .form-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .ticket-form-main .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

/* ========================================================
   SHOW.BLADE.PHP
   ======================================================== */
.ticket-overview-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(280px, .85fr);
        gap: 18px;
        align-items: stretch;
        margin-bottom: 18px;
    }

    .ticket-information-card,
    .ticket-summary-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .ticket-information-card .card-body,
    .ticket-summary-card .card-body {
        flex: 1;
        min-width: 0;
    }

    .ticket-device-card,
    .ticket-customer-card,
    .ticket-technician-card,
    .ticket-resolution-card,
    .ticket-parts-card,
    .ticket-progress-card,
    .ticket-registration-card {
        min-width: 0;
        margin-bottom: 18px;
    }

    .ticket-overview-grid .card-head h3,
    .ticket-device-card .card-head h3,
    .ticket-customer-card .card-head h3,
    .ticket-technician-card .card-head h3,
    .ticket-resolution-card .card-head h3,
    .ticket-parts-card .card-head h3,
    .ticket-progress-card .card-head h3,
    .ticket-registration-card .card-head h3 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 15px;
        font-weight: 700;
    }

    .ticket-overview-grid .card-head p,
    .ticket-device-card .card-head p,
    .ticket-customer-card .card-head p,
    .ticket-technician-card .card-head p,
    .ticket-resolution-card .card-head p,
    .ticket-parts-card .card-head p,
    .ticket-progress-card .card-head p,
    .ticket-registration-card .card-head p {
        margin: 0;
        color: #8a94a6;
        font-size: 11px;
        line-height: 1.6;
    }

    .ticket-identity {
        display: flex;
        align-items: center;
        gap: 13px;
        padding-bottom: 20px;
        margin-bottom: 21px;
        border-bottom: 1px solid #edf0f5;
    }

    .ticket-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 50px;
        width: 50px;
        height: 50px;
        border-radius: 11px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 14px;
        font-weight: 700;
    }

    .ticket-identity-content {
        flex: 1;
        min-width: 0;
    }

    .ticket-identity-content h2 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 17px;
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .ticket-identity-content > span {
        color: #7d8797;
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .ticket-status {
        flex-shrink: 0;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 9px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        flex-shrink: 0;
        border-radius: 50%;
        background: currentColor;
    }

    .status-badge.open,
    .status-text.open {
        color: #5264a6;
    }

    .status-badge.open {
        background: #eef2ff;
    }

    .status-badge.progress,
    .status-text.progress {
        color: #167d70;
    }

    .status-badge.progress {
        background: #eaf7f3;
    }

    .status-badge.hold,
    .status-text.hold {
        color: #a66b19;
    }

    .status-badge.hold {
        background: #fff4e5;
    }

    .status-badge.resolved,
    .status-text.resolved,
    .status-badge.closed,
    .status-text.closed {
        color: #167d70;
    }

    .status-badge.resolved,
    .status-badge.closed {
        background: #eaf7f3;
    }

    .status-badge.danger,
    .status-text.danger {
        color: #b34b4b;
    }

    .status-badge.danger {
        background: #fceeee;
    }

    .status-badge.unknown,
    .status-text.unknown {
        color: #596579;
    }

    .status-badge.unknown {
        background: #f1f3f6;
    }

    .status-text,
    .priority-text {
        font-size: 12px;
        font-weight: 700;
    }

    .priority-text.low {
        color: #167d70;
    }

    .priority-text.medium {
        color: #5264a6;
    }

    .priority-text.high {
        color: #c27a28;
    }

    .priority-text.urgent {
        color: #b34b4b;
    }

    .priority-text.unknown {
        color: #596579;
    }

    .ticket-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 24px;
    }

    .info-item,
    .registration-item {
        min-width: 0;
    }

    .info-label {
        display: block;
        margin-bottom: 6px;
        color: #8a94a6;
        font-size: 10px;
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .info-item strong,
    .registration-item strong {
        display: block;
        color: #34415c;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .ticket-code,
    .registration-id {
        font-family: Consolas, Monaco, monospace;
        letter-spacing: .2px;
    }

    .ticket-description {
        padding-top: 18px;
        margin-top: 20px;
        border-top: 1px solid #edf0f5;
    }

    .ticket-description p {
        margin: 0;
        color: #596579;
        font-size: 12px;
        line-height: 1.8;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }

    .ticket-summary-list {
        display: flex;
        flex-direction: column;
    }

    .ticket-summary-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 0;
    }

    .ticket-summary-item + .ticket-summary-item {
        border-top: 1px solid #edf0f5;
    }

    .summary-item-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        border-radius: 9px;
        font-size: 11px;
        font-weight: 700;
    }

    .ticket-summary-icon,
    .parts-summary-icon {
        background: #eaf7f3;
        color: #167d70;
    }

    .customer-summary-icon {
        background: #eef2ff;
        color: #5264a6;
    }

    .priority-summary-icon {
        background: #fff4e5;
        color: #c27a28;
    }

    .status-summary-icon {
        background: #eaf7f3;
        color: #167d70;
    }

    .summary-item-content {
        flex: 1;
        min-width: 0;
    }

    .summary-item-content > span {
        display: block;
        margin-bottom: 4px;
        color: #8a94a6;
        font-size: 10px;
    }

    .summary-item-content > strong {
        display: block;
        margin-bottom: 3px;
        color: #17284f;
        font-size: 12px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .summary-item-content > small {
        display: block;
        color: #9aa3b2;
        font-size: 10px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .ticket-related-layout {
        display: flex;
        align-items: center;
        gap: 15px;
        min-width: 0;
        padding: 5px 0;
    }

    .related-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 46px;
        width: 46px;
        height: 46px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
    }

    .device-related-icon {
        background: #eaf7f3;
        color: #167d70;
    }

    .customer-related-icon {
        background: #eef2ff;
        color: #5264a6;
    }

    .technician-related-icon {
        background: #fff4e5;
        color: #c27a28;
    }

    .related-content {
        flex: 1;
        min-width: 0;
    }

    .related-label {
        display: block;
        margin-bottom: 5px;
        color: #8a94a6;
        font-size: 10px;
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .related-content h4 {
        margin: 0 0 13px;
        color: #17284f;
        font-size: 14px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .related-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 32px;
    }

    .related-meta > div {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 120px;
    }

    .related-meta span {
        color: #8a94a6;
        font-size: 10px;
    }

    .related-meta strong {
        color: #34415c;
        font-size: 11px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .related-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        padding: 6px 9px;
        border-radius: 6px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .related-indicator-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .ticket-empty-state {
        padding: 30px 20px;
        text-align: center;
    }

    .ticket-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        margin: 0 auto 12px;
        border-radius: 10px;
        background: #f1f3f6;
        color: #8a94a6;
        font-size: 14px;
        font-weight: 700;
    }

    .ticket-empty-state h4 {
        margin: 0 0 6px;
        color: #34415c;
        font-size: 13px;
    }

    .ticket-empty-state p {
        max-width: 480px;
        margin: 0 auto;
        color: #9aa3b2;
        font-size: 11px;
        line-height: 1.7;
    }

    .empty-state-actions {
        margin-top: 15px;
    }

    .resolution-content {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 5px 0;
    }

    .resolution-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 40px;
        width: 40px;
        height: 40px;
        border-radius: 9px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 16px;
        font-weight: 700;
    }

    .resolution-details {
        flex: 1;
        min-width: 0;
    }

    .resolution-details p {
        margin: 0;
        color: #596579;
        font-size: 12px;
        line-height: 1.8;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .resolution-meta {
        display: flex;
        flex-direction: column;
        gap: 5px;
        padding-top: 14px;
        margin-top: 14px;
        border-top: 1px solid #edf0f5;
    }

    .resolution-meta span {
        color: #8a94a6;
        font-size: 10px;
    }

    .resolution-meta strong {
        color: #34415c;
        font-size: 11px;
    }

    .parts-count {
        padding: 6px 10px;
        border-radius: 6px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .ticket-parts-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .ticket-parts-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 650px;
    }

    .ticket-parts-table th {
        padding: 12px 13px;
        background: #f8f9fc;
        color: #8a94a6;
        font-size: 10px;
        font-weight: 700;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
    }

    .ticket-parts-table td {
        padding: 14px 13px;
        border-bottom: 1px solid #edf0f5;
        color: #596579;
        font-size: 11px;
        vertical-align: middle;
    }

    .ticket-parts-table tbody tr:last-child td {
        border-bottom: none;
    }

    .part-product {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 180px;
    }

    .part-product-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 11px;
        font-weight: 700;
    }

    .part-product strong {
        display: block;
        margin-bottom: 4px;
        color: #34415c;
        font-size: 11px;
        font-weight: 700;
    }

    .part-product small {
        display: block;
        color: #9aa3b2;
        font-size: 10px;
    }

    .quantity-value {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        padding: 5px 8px;
        border-radius: 5px;
        background: #f1f3f6;
        color: #34415c;
        font-weight: 700;
    }

    .subtotal-value {
        color: #167d70;
        font-weight: 700;
    }

    .part-notes {
        display: block;
        max-width: 180px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .ticket-parts-table tfoot td {
        padding: 14px 13px;
        background: #f8f9fc;
        border-top: 1px solid #edf0f5;
        border-bottom: none;
        color: #34415c;
    }

    .ticket-progress-list {
        display: flex;
        align-items: flex-start;
        gap: 0;
        width: 100%;
    }

    .progress-step {
        position: relative;
        display: flex;
        flex: 1;
        flex-direction: column;
        align-items: center;
        min-width: 0;
        text-align: center;
    }

    .progress-step:not(:last-child)::after {
        position: absolute;
        top: 15px;
        left: calc(50% + 17px);
        width: calc(100% - 34px);
        height: 2px;
        background: #e8ebf1;
        content: '';
    }

    .progress-step.completed:not(:last-child)::after {
        background: #15966f;
    }

    .progress-marker {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        margin-bottom: 10px;
        border: 2px solid #e8ebf1;
        border-radius: 50%;
        background: #fff;
        color: #9aa3b2;
        font-size: 11px;
        font-weight: 700;
    }

    .progress-step.completed .progress-marker {
        border-color: #15966f;
        background: #15966f;
        color: #fff;
    }

    .progress-step.current .progress-marker {
        box-shadow: 0 0 0 4px #eaf7f3;
    }

    .progress-step-content strong {
        display: block;
        color: #34415c;
        font-size: 11px;
        font-weight: 700;
    }

    .progress-step-content small {
        display: block;
        margin-top: 4px;
        color: #9aa3b2;
        font-size: 9px;
    }

    .progress-step.current .progress-step-content strong {
        color: #167d70;
    }

    .ticket-hold-notice,
    .ticket-cancelled-notice {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 13px 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        background: #fff4e5;
        color: #a66b19;
    }

    .ticket-cancelled-notice {
        display: block;
        background: #fceeee;
        color: #b34b4b;
    }

    .hold-notice-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 24px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #f8e5c8;
        font-size: 12px;
        font-weight: 700;
    }

    .ticket-hold-notice strong,
    .ticket-cancelled-notice strong {
        display: block;
        margin-bottom: 4px;
        font-size: 11px;
    }

    .ticket-hold-notice p,
    .ticket-cancelled-notice p {
        margin: 0;
        font-size: 10px;
        line-height: 1.6;
    }

    .ticket-registration-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 24px;
    }

    .registration-id {
        font-size: 11px !important;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    @media (max-width: 950px) {
        .ticket-overview-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 650px) {
        .ticket-identity {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .ticket-identity-content {
            flex-basis: calc(100% - 70px);
        }

        .ticket-status {
            margin-left: 0;
        }

        .ticket-info-grid,
        .ticket-registration-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 17px;
        }

        .ticket-related-layout {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .related-content {
            flex-basis: calc(100% - 65px);
        }

        .related-indicator {
            margin-left: 61px;
        }

        .related-meta {
            gap: 12px 20px;
        }

        .ticket-progress-list {
            align-items: stretch;
            flex-direction: column;
            gap: 17px;
        }

        .progress-step {
            flex-direction: row;
            align-items: center;
            gap: 12px;
            text-align: left;
        }

        .progress-step:not(:last-child)::after {
            top: 32px;
            left: 15px;
            width: 2px;
            height: 17px;
        }

        .progress-marker {
            flex-shrink: 0;
            margin-bottom: 0;
        }

        .progress-step-content {
            min-width: 0;
        }
    }

/* ========================================================
   _DEVICE_CUSTOMER_FILTER.BLADE.PHP
   ======================================================== */
.device-customer-filter-hint{display:block;margin-top:7px;color:#64748b;font-size:11px;line-height:1.5}

/* ========================================================
   _WARRANTY_COVERAGE.BLADE.PHP
   ======================================================== */
.ticket-warranty-panel{margin:14px 0 20px;padding:17px 19px;border:1px solid #e2e8f0;background:#f8fafc;border-radius:9px}
.ticket-warranty-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;color:#17284f;font-size:13px}
.ticket-warranty-pill{padding:5px 10px;border-radius:20px;font-size:11px;font-weight:700;background:#e2e8f0;color:#475569}
.ticket-warranty-pill.covered{background:#dcfce7;color:#166534}.ticket-warranty-pill.expired,.ticket-warranty-pill.void{background:#fee2e2;color:#991b1b}.ticket-warranty-pill.claimed,.ticket-warranty-pill.upcoming{background:#fef3c7;color:#92400e}
.ticket-warranty-panel p{font-size:12px;line-height:1.6;color:#64748b;margin:10px 0 0}.ticket-warranty-link{display:inline-block;margin-top:10px;font-size:12px;font-weight:600;color:#176d97;text-decoration:none}
.ticket-warranty-link[hidden]{display:none!important}

/* ========================================================
   _WARRANTY_DETAIL.BLADE.PHP
   ======================================================== */
.ticket-warranty-detail-card{margin-bottom:18px}.ticket-warranty-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;margin-bottom:18px}
.ticket-warranty-detail-grid>div{display:flex;flex-direction:column;gap:7px}.ticket-warranty-detail-grid small{color:#64748b;font-size:12px}.ticket-warranty-detail-grid strong{font-size:13px;color:#17284f}
@media(max-width:650px){.ticket-warranty-detail-grid{grid-template-columns:1fr}}

/* ========================================================
   _WORKFLOW.BLADE.PHP
   ======================================================== */
/* Workflow only. Scoped to avoid changing existing Service Tickets / Warranty UI. */
.ticket-workflow-card{margin-bottom:18px}
.ticket-workflow-card .card-body{padding:20px}
.ticket-workflow-actions{display:flex;align-items:center;flex-wrap:wrap;gap:10px}
.ticket-workflow-actions .btn{min-height:38px}
.ticket-workflow-current{display:inline-flex;align-items:center;border:1px solid #cbd5e1;border-radius:20px;padding:5px 11px;background:#f1f5f9;color:#334155;font-size:11px;font-weight:700}
.ticket-workflow-hint{display:block;color:#718096;font-size:12px;line-height:1.65;margin:12px 0 0}
.ticket-workflow-validation{background:#fff2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:11px 15px;margin-bottom:15px;font-size:12px}
.ticket-workflow-validation p{margin:4px 0}
.ticket-workflow-modal[hidden],.ticket-workflow-resolution[hidden]{display:none!important}
.ticket-workflow-modal{position:fixed;inset:0;z-index:20000;background:rgba(14,28,54,.58);display:flex;align-items:center;justify-content:center;padding:16px}
.ticket-workflow-dialog{box-sizing:border-box;width:min(100%,470px);max-height:calc(100dvh - 32px);overflow:auto;border-radius:16px;background:white;text-align:center;padding:29px;box-shadow:0 22px 70px rgba(20,32,55,.22)}
.ticket-workflow-symbol{width:54px;height:54px;margin:0 auto 15px;display:grid;place-items:center;border-radius:15px;background:#e5f5ef;color:#16856a;font-size:24px;font-weight:800}
.ticket-workflow-dialog h3{color:#17284f;font-size:19px;margin:0 0 9px}.ticket-workflow-dialog p{font-size:13px;color:#667085;line-height:1.6;margin:0 0 20px}
.ticket-workflow-resolution{text-align:left;margin:10px 0 18px}.ticket-workflow-resolution label{display:block;font-weight:600;font-size:12px;color:#17284f;margin-bottom:8px}
.ticket-workflow-resolution textarea{box-sizing:border-box;width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:8px;resize:vertical;min-height:110px;font:inherit;font-size:12px}
.ticket-workflow-resolution small{font-size:11px;color:#718096}.ticket-workflow-dialog-actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.ticket-workflow-toast{position:fixed;z-index:19000;top:20px;left:50%;transform:translateX(-50%);max-width:min(90vw,540px);padding:14px 18px;border-radius:10px;background:#e7f8ef;color:#106c48;border:1px solid #95d5b4;box-shadow:0 8px 30px #0002;font-size:13px;font-weight:600;text-align:center}
@media(max-width:600px){.ticket-workflow-actions{align-items:stretch;flex-direction:column}.ticket-workflow-actions .btn{text-align:center}}

/* ==============================================================
   SERVICE TICKET SHOW — SERVICE PARTS CRUD (v5)
   All Service Ticket styles remain in this single module partial.
============================================================== */
.ticket-part-header-actions{display:flex;align-items:center;justify-content:flex-end;gap:12px;flex-wrap:wrap}
.ticket-part-action-cell{white-space:nowrap;min-width:132px}
.ticket-part-action{padding:7px 10px;margin:2px;border-radius:7px;border:1px solid #d9e2ee;background:#fff;color:#27618a;font-size:11px;font-weight:600;cursor:pointer}
.ticket-part-action:hover{background:#f1f5f9}
.ticket-part-remove{border-color:#fecaca;color:#b91c1c}
.ticket-part-remove:hover{background:#fef2f2}
.ticket-part-modal[hidden]{display:none!important}
.ticket-part-modal{position:fixed;inset:0;z-index:21000;background:rgba(14,28,54,.65);display:flex;align-items:center;justify-content:center;padding:18px;overflow-y:auto}
.ticket-part-dialog{box-sizing:border-box;width:min(100%,520px);background:#fff;border-radius:14px;box-shadow:0 20px 70px #14203738;padding:25px;text-align:left}
.ticket-part-dialog h3{font-size:18px;color:#17284f;margin:0 0 6px}
.ticket-part-dialog p{font-size:12px;color:#64748b;line-height:1.6;margin:0 0 20px}
.ticket-part-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.ticket-part-field{display:flex;flex-direction:column;gap:7px;min-width:0}
.ticket-part-field.full{grid-column:1/-1}
.ticket-part-field label{color:#334155;font-size:12px;font-weight:600}
.ticket-part-field input,.ticket-part-field select,.ticket-part-field textarea{width:100%;box-sizing:border-box;min-height:40px;padding:9px 11px;background:#fff;border:1px solid #cbd5e1;border-radius:7px;font-size:13px;color:#17284f;font-family:inherit}
.ticket-part-field textarea{min-height:85px;resize:vertical}
.ticket-part-dialog-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:22px}
.ticket-part-dialog-actions .danger{background:#dc2626;color:#fff;border-color:#dc2626}
.ticket-part-dialog .ticket-part-warning{padding:10px 12px;border-radius:7px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;font-size:12px;margin-bottom:14px}
.ticket-part-message{margin-top:12px;color:#64748b;font-size:11px;line-height:1.6}
@media(max-width:600px){.ticket-part-form-grid{grid-template-columns:1fr}.ticket-part-field.full{grid-column:auto}.ticket-part-dialog-actions{flex-wrap:wrap}}

/* Service ticket show v6: warranty, 3-column related cards, spare part guidance, resolution log */
.ticket-warranty-detail-card{margin:0 0 18px}.ticket-warranty-detail-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px;margin-bottom:18px}
.ticket-warranty-detail-grid>div{display:flex;flex-direction:column;gap:7px;min-width:0}.ticket-warranty-detail-grid small{color:#64748b;font-size:12px}.ticket-warranty-detail-grid strong{font-size:13px;color:#17284f;overflow-wrap:anywhere}
.ticket-three-column-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;align-items:stretch;margin-bottom:18px}
.ticket-three-column-grid>.card{min-width:0;margin-bottom:0;display:flex;flex-direction:column}.ticket-three-column-grid>.card>.card-body{flex:1;min-width:0}
.ticket-three-column-grid .ticket-related-layout{display:flex;flex-direction:column;align-items:flex-start;gap:12px}
.ticket-three-column-grid .related-content,.ticket-three-column-grid .related-meta{width:100%;min-width:0;flex-basis:auto}
.ticket-three-column-grid .related-meta{display:grid;grid-template-columns:1fr;gap:12px}
.ticket-three-column-grid .related-meta>div{min-width:0}.ticket-three-column-grid .related-meta strong,.ticket-three-column-grid .related-content h4{overflow-wrap:anywhere;word-break:break-word}
.ticket-three-column-grid .related-indicator{margin:0;align-self:flex-start}
.ticket-quick-actions{display:flex;justify-content:flex-end;margin:0 0 15px}.ticket-parts-guidance{padding:12px 14px;margin:0 0 17px;border:1px solid #dbe7f2;background:#f7fafc;border-radius:8px;font-size:12px;line-height:1.65;color:#51617a}
.ticket-resolution-history-card{margin:0 0 18px}.ticket-resolution-count{font-size:12px;color:#64748b}.ticket-resolution-history-entry{padding:14px 16px;border:1px solid #e2e8f0;border-radius:9px;margin-bottom:12px;background:#fff}
.ticket-resolution-history-meta{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;font-size:12px;color:#17284f}.ticket-resolution-history-meta span{color:#64748b}
.ticket-resolution-history-entry p{white-space:pre-wrap;overflow-wrap:anywhere;font-size:13px;line-height:1.7;margin:10px 0 0;color:#334155}.ticket-resolution-history-empty{color:#64748b;font-size:13px}
@media(max-width:1190px){.ticket-three-column-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.ticket-warranty-detail-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:750px){.ticket-three-column-grid{grid-template-columns:1fr}.ticket-warranty-detail-grid{grid-template-columns:1fr}}


/* Service Parts — quantity and Indonesian Rupiah input */
.ticket-part-field .ticket-price-input-wrap { position:relative; display:flex; align-items:center; min-width:0; }
.ticket-part-field .ticket-price-prefix { position:absolute;left:12px;top:50%;transform:translateY(-50%);font-weight:700;color:#64748b;font-size:13px;pointer-events:none; }
.ticket-part-field #ticketPartPriceDisplay { width:100%;padding-left:37px;box-sizing:border-box;font-variant-numeric:tabular-nums; }
.ticket-part-field #ticketPartQuantity { font-variant-numeric:tabular-nums; }
.ticket-part-field .ticket-price-hint { display:block;margin-top:6px;color:#64748b;font-size:11px;line-height:1.4; }
</style>
