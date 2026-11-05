<style>
/* INDEX PAGE */
/* =========================================================
   QUOTATION FILTER BAR
========================================================= */

.quotation-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}


/* =========================================================
   SEARCH
========================================================= */

.quotation-search {
    position: relative;
    flex: 1 1 260px;
    min-width: 220px;
}

.quotation-search input {
    width: 100%;
    height: 40px;

    box-sizing: border-box;

    padding: 0 38px 0 38px;

    border: 1px solid #d9dee8;
    border-radius: 8px;

    background: #fff;

    color: #17284f;
    font-size: 13px;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.quotation-search input:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.quotation-search-icon {
    position: absolute;

    left: 13px;
    top: 50%;

    transform: translateY(-50%);

    color: #7d8797;

    font-size: 19px;

    pointer-events: none;
}

.quotation-search-clear {
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

.quotation-search-clear:hover {
    background: #edf1f5;
    color: #17284f;
}


/* =========================================================
   FILTER SELECT
========================================================= */

.quotation-filter-select {
    position: relative;
}

.quotation-filter-select select {
    min-width: 155px;
    height: 40px;

    padding: 0 34px 0 12px;

    border: 1px solid #d9dee8;
    border-radius: 8px;

    background-color: #fff;

    color: #34415c;

    font-size: 13px;

    cursor: pointer;

    appearance: auto;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.quotation-filter-select select:hover {
    border-color: #b7c0cf;
}

.quotation-filter-select select:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.sort-direction select {
    min-width: 145px;
}


/* =========================================================
   FILTER BUTTONS
========================================================= */

.quotation-filter-button {
    height: 40px;
    white-space: nowrap;
}

.quotation-reset-button {
    height: 40px;

    display: inline-flex;
    align-items: center;

    white-space: nowrap;
}


/* =========================================================
   FILTER SUMMARY
========================================================= */

.quotation-filter-summary {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;

    margin-bottom: 15px;

    font-size: 12px;
    color: #7d8797;
}

.filter-chip {
    padding: 5px 9px;

    border-radius: 6px;

    background: #f1f5f7;

    color: #34415c;

    font-size: 11px;
}


/* =========================================================
   QUOTATION NUMBER
========================================================= */

.quotation-number-link {
    color: inherit;
    text-decoration: none;
}

.quotation-number-link:hover {
    color: #223a70;
}


/* =========================================================
   OPPORTUNITY
========================================================= */

.quotation-opportunity-link {
    color: inherit;
    text-decoration: none;
}

.quotation-opportunity-link:hover {
    color: #223a70;
}


/* =========================================================
   AMOUNT
========================================================= */

.quotation-value {
    color: #17284f;
    white-space: nowrap;
}

.quotation-total {
    color: #15966f;
    white-space: nowrap;
}


/* =========================================================
   STATUS
========================================================= */

.quotation-status-badge {
    display: inline-flex;

    align-items: center;

    padding: 4px 9px;

    border-radius: 6px;

    background: #eef2f6;

    color: #4d5b70;

    font-size: 11px;

    font-weight: 600;

    white-space: nowrap;
}


/* =========================================================
   STATUS VARIANTS
========================================================= */

.quotation-status-draft {
    background: #f1f3f6;
    color: #667085;
}

.quotation-status-sent {
    background: #edf3ff;
    color: #315caa;
}

.quotation-status-approved {
    background: #e8f5f0;
    color: #16805f;
}

.quotation-status-rejected {
    background: #fff0f0;
    color: #a14d4d;
}

.quotation-status-expired {
    background: #fff4e5;
    color: #9a6a18;
}

.quotation-status-cancelled {
    background: #f1f1f1;
    color: #707070;
}

.quotation-status-pending {
    background: #fff6e5;
    color: #a66b12;
}


/* =========================================================
   DELETE MODAL
========================================================= */

.delete-modal {
    position: fixed;

    inset: 0;

    z-index: 9999;

    display: none;

    align-items: center;
    justify-content: center;
}

.delete-modal.open {
    display: flex;
}

.delete-modal-overlay {
    position: absolute;

    inset: 0;

    background: rgba(15, 24, 42, .45);

    backdrop-filter: blur(2px);
}

.delete-modal-dialog {
    position: relative;

    width: min(420px, calc(100% - 32px));

    background: #fff;

    border-radius: 14px;

    padding: 26px;

    box-shadow:
        0 20px 60px rgba(23, 40, 79, .20);

    animation: deleteModalIn .18s ease;
}

@keyframes deleteModalIn {

    from {
        opacity: 0;
        transform: translateY(8px) scale(.98);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

}

.delete-modal-icon {
    width: 44px;
    height: 44px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin-bottom: 16px;

    border-radius: 50%;

    background: #fff0f0;

    color: #c94a4a;

    font-size: 21px;

    font-weight: 700;
}

.delete-modal-content h3 {
    margin: 0 0 8px;

    color: #17284f;

    font-size: 18px;
}

.delete-modal-content p {
    margin: 0 0 6px;

    color: #34415c;

    font-size: 13px;

    line-height: 1.6;
}

.delete-modal-content p strong {
    color: #17284f;
}

.delete-modal-content > span {
    color: #8a94a6;

    font-size: 12px;
}

.delete-modal-actions {
    display: flex;

    justify-content: flex-end;

    gap: 8px;

    margin-top: 24px;
}

.delete-confirm-button {
    background: #c94a4a !important;
    color: #fff !important;

    border-color: #c94a4a !important;
}

.delete-confirm-button:hover {
    background: #b83f3f !important;
    border-color: #b83f3f !important;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1100px) {

    .quotation-search {
        flex: 1 1 100%;
    }

    .quotation-filter-select {
        flex: 1 1 150px;
    }

    .quotation-filter-select select {
        width: 100%;
    }

}


@media (max-width: 600px) {

    .quotation-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .quotation-search,
    .quotation-filter-select,
    .quotation-filter-button,
    .quotation-reset-button {
        width: 100%;
    }

    .quotation-filter-button,
    .quotation-reset-button {
        justify-content: center;
    }

    .delete-modal-dialog {
        padding: 22px;
    }

    .delete-modal-actions {
        flex-direction: column-reverse;
    }

    .delete-modal-actions .btn {
        width: 100%;
        justify-content: center;
    }

}
.table-wrap {
    display: block;
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    box-sizing: border-box;
}

.table-wrap table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    border: none;
}

.table-wrap table thead th {
    padding: 14px 12px;
    background: #f3f4f6;
    color: #64748b;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    text-align: left;
    white-space: nowrap;
    border: 1px solid #d1d5db;
}

.table-wrap table tbody td {
    padding: 13px 12px;
    font-size: 12px;
    color: #17284f;
    vertical-align: middle;
    border: 1px solid #d1d5db;
}

.table-wrap table tbody tr:hover {
    background-color: #f8fafc;
}

.table-wrap table th:nth-child(8),
.table-wrap table td:nth-child(8) {
    text-align: center;
    min-width: 120px;
}

.table-wrap table th:nth-child(9),
.table-wrap table td:nth-child(9) {
    min-width: 170px;
}

.table-wrap table tbody td[colspan] {
    text-align: center;
    padding: 28px 12px;
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

.table-wrap td form {
    display: inline-flex;
    align-items: center;
    margin: 0 !important;
    padding: 0;
}

.table-wrap td .action-btn {
    margin: 0 !important;
    vertical-align: middle;
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

/* Responsive */
@media (max-width: 768px) {
    .table-wrap table {
        min-width: 1250px;
    }

}
    .col-number {
    width: 55px;
    min-width: 55px;
    max-width: 55px;
    text-align: center !important;
    white-space: nowrap;
}

.col-quotation {
    min-width: 150px;
}

.col-opportunity {
    min-width: 170px;
}

.col-date {
    min-width: 140px;
    white-space: nowrap;
}

.col-valid-until {
    min-width: 130px;
    white-space: nowrap;
}

.col-subtotal,
.col-total {
    min-width: 150px;
    text-align: right;
    white-space: nowrap;
}

.col-status {
    min-width: 110px;
}

.col-actions {
    min-width: 130px;
    text-align: center !important;
}

.col-sales-order {
    min-width: 150px;
}

/* CREATE PAGE */
/* =========================================================
   QUOTATION CREATE
========================================================= */

.quotation-create-card,
.quotation-create-body,
.quotation-form-layout,
.quotation-form-main {

    overflow: visible !important;

}


/* =========================================================
   FORM LAYOUT
========================================================= */

.quotation-form-layout {

    display: grid;

    grid-template-columns:
        minmax(0, 1.25fr)
        minmax(320px, .75fr);

    gap: 32px;

    align-items: start;

    width: 100%;

}

.quotation-form-main {

    min-width: 0;

}


/* =========================================================
   SECTION DIVIDER
========================================================= */

.section-divider {

    display: flex;

    align-items: center;

    margin: 25px 0 18px;

    padding-top: 20px;

    border-top: 1px solid #edf0f5;

}

.section-divider.first-section {

    margin-top: 0;

    padding-top: 0;

    border-top: 0;

}

.section-divider h4 {

    margin: 0 0 4px;

    color: #17284f;

    font-size: 13px;

    font-weight: 700;

}

.section-divider p {

    margin: 0;

    color: #8a94a6;

    font-size: 11px;

    line-height: 1.5;

}


/* =========================================================
   FORM GROUP
========================================================= */

.form-group {

    margin-bottom: 19px;

}

.form-group label {

    display: block;

    margin-bottom: 7px;

    color: #17284f;

    font-size: 13px;

    font-weight: 600;

}

.required {

    color: #c94a4a;

}


/* =========================================================
   INPUT / SELECT / TEXTAREA
========================================================= */

.form-group input,
.form-group select,
.form-group textarea {

    width: 100%;

    box-sizing: border-box;

    border: 1px solid #d9dee8;

    border-radius: 8px;

    background: #fff;

    color: #17284f;

    font-family: inherit;

    font-size: 13px;

    transition:
        border-color .18s ease,
        box-shadow .18s ease,
        background .18s ease;

}

.form-group input,
.form-group select {

    height: 42px;

    padding: 0 12px;

}

.form-group textarea {

    min-height: 120px;

    padding: 11px 12px;

    line-height: 1.6;

    resize: vertical;

}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {

    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);

}

.form-group input::placeholder,
.form-group textarea::placeholder {

    color: #a0a8b6;

}


/* =========================================================
   HINT / ERROR
========================================================= */

.form-hint {

    display: block;

    margin-top: 6px;

    color: #8a94a6;

    font-size: 11px;

    line-height: 1.5;

}

.form-error {

    display: block;

    margin-top: 6px;

    color: #c94a4a;

    font-size: 11px;

    line-height: 1.5;

}


/* =========================================================
   TWO COLUMN
========================================================= */

.quotation-two-column {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 16px;

}


/* =========================================================
   INPUT PREFIX
========================================================= */

.input-with-prefix {

    position: relative;

    display: flex;

    width: 100%;

}

.input-with-prefix input {

    padding-left: 44px;

}

.input-prefix {

    position: absolute;

    left: 0;

    top: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    width: 42px;

    height: 42px;

    color: #718096;

    font-size: 12px;

    font-weight: 600;

    pointer-events: none;

}


/* =========================================================
   FINANCIAL OPTION
========================================================= */

.financial-option {

    padding: 16px;

    border: 1px solid #e6eaf0;

    border-radius: 10px;

    background: #fafbfd;

}

.financial-option-header {

    display: flex;

    align-items: center;

}

.checkbox-label {

    display: inline-flex !important;

    align-items: center;

    gap: 9px;

    margin: 0 !important;

    cursor: pointer;

    color: #34415c !important;

    font-size: 12px !important;

    font-weight: 600 !important;

}

.checkbox-label input {

    width: 16px !important;

    height: 16px !important;

    margin: 0;

    accent-color: #15966f;

    cursor: pointer;

}

.financial-option-body {

    margin-top: 15px;

}

.financial-option-body .form-group {

    margin-bottom: 0;

}


/* =========================================================
   ITEMS CONTAINER
========================================================= */

.quotation-items-container {

    width: 100%;

    overflow: visible;

}


/* =========================================================
   ITEM CARD
========================================================= */

.quotation-item-card {

    margin-bottom: 10px;

    padding: 15px;

    border: 1px solid #e6eaf0;

    border-radius: 10px;

    background: #fff;

}

.quotation-item-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    margin-bottom: 12px;

}

.quotation-item-product {

    min-width: 0;

}

.quotation-item-product strong {

    display: block;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #17284f;

    font-size: 12px;

}

.quotation-item-product span {

    display: block;

    margin-top: 3px;

    color: #8a94a6;

    font-size: 10px;

}

.quotation-item-index {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 26px;

    height: 26px;

    padding: 0 7px;

    border-radius: 6px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 10px;

    font-weight: 700;

}

.quotation-item-grid {

    display: grid;

    grid-template-columns:
        .8fr 1fr 1fr;

    gap: 12px;

}

.quotation-item-field {

    display: flex;

    flex-direction: column;

    gap: 5px;

}

.quotation-item-field > span {

    color: #8a94a6;

    font-size: 10px;

}

.quotation-item-field strong {

    color: #34415c;

    font-size: 12px;

}

.quotation-item-price {

    color: #17284f !important;

}


/* =========================================================
   EMPTY ITEMS
========================================================= */

.empty-items {

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    min-height: 150px;

    padding: 25px;

    border: 1px dashed #d9dee8;

    border-radius: 10px;

    background: #fafbfd;

    text-align: center;

}

.empty-items-icon {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 34px;

    height: 34px;

    margin-bottom: 10px;

    border-radius: 8px;

    background: #eaf7f3;

    color: #15966f;

    font-size: 18px;

    font-weight: 700;

}

.empty-items strong {

    margin-bottom: 4px;

    color: #34415c;

    font-size: 12px;

}

.empty-items span {

    color: #8a94a6;

    font-size: 10px;

}


/* =========================================================
   FORM ACTIONS
========================================================= */

.form-actions {

    display: flex;

    justify-content: flex-end;

    align-items: center;

    gap: 8px;

    padding-top: 16px;

    margin-top: 8px;

    border-top: 1px solid #edf0f5;

}


/* =========================================================
   QUOTATION PREVIEW
========================================================= */

.quotation-preview-panel {

    position: -webkit-sticky;

    position: static;

    top: 92px;

    align-self: start;

    height: fit-content;

    padding: 24px;

    border: 1px solid #e6eaf0;

    border-radius: 12px;

    background: #fafbfd;

    box-sizing: border-box;

    min-width: 0;

    z-index: 5;

}


/* =========================================================
   PREVIEW LABEL
========================================================= */

.preview-label {

    margin-bottom: 20px;

    color: #8a94a6;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 1.2px;

}


/* =========================================================
   PREVIEW HEADER
========================================================= */

.preview-quotation-head {

    display: flex;

    align-items: center;

    gap: 13px;

}

.preview-quotation-icon {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 48px;

    height: 48px;

    flex-shrink: 0;

    border-radius: 12px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 17px;

    font-weight: 700;

}

.preview-quotation-main {

    min-width: 0;

}

.preview-quotation-main h3 {

    margin: 0 0 4px;

    color: #17284f;

    font-size: 17px;

    font-weight: 700;

}

.preview-quotation-main span {

    display: block;

    color: #7d8797;

    font-size: 11px;

}


/* =========================================================
   PREVIEW DIVIDER
========================================================= */

.preview-divider {

    height: 1px;

    margin: 21px 0;

    background: #e5e9ef;

}


/* =========================================================
   PREVIEW DETAIL
========================================================= */

.preview-detail {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 20px;

    padding: 9px 0;

}

.preview-detail > span {

    color: #8a94a6;

    font-size: 11px;

}

.preview-detail > strong {

    max-width: 190px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #34415c;

    font-size: 12px;

    font-weight: 600;

    text-align: right;

}


/* =========================================================
   PREVIEW ITEMS
========================================================= */

.preview-section-title {

    margin-bottom: 10px;

    color: #34415c;

    font-size: 11px;

    font-weight: 700;

}

.preview-item {

    padding: 9px 0;

    border-bottom: 1px solid #edf0f5;

}

.preview-item:last-child {

    border-bottom: 0;

}

.preview-item-head {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 12px;

}

.preview-item-name {

    min-width: 0;

    color: #34415c;

    font-size: 10px;

    font-weight: 600;

}

.preview-item-total {

    flex-shrink: 0;

    color: #17284f;

    font-size: 10px;

    font-weight: 700;

}

.preview-item-meta {

    display: flex;

    justify-content: space-between;

    gap: 10px;

    margin-top: 4px;

    color: #8a94a6;

    font-size: 9px;

}

.preview-empty-items {

    padding: 10px 0;

    color: #a0a8b6;

    font-size: 10px;

    text-align: center;

}


/* =========================================================
   PREVIEW FINANCIAL
========================================================= */

.preview-financial-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 8px 0;

}

.preview-financial-row span {

    color: #8a94a6;

    font-size: 11px;

}

.preview-financial-row strong {

    color: #17284f;

    font-size: 12px;

    font-weight: 700;

}


/* =========================================================
   PREVIEW TOTAL
========================================================= */

.preview-total-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

}

.preview-total-row span {

    color: #34415c;

    font-size: 12px;

    font-weight: 700;

}

.preview-total-row strong {

    color: #15966f;

    font-size: 17px;

    font-weight: 800;

}


/* =========================================================
   PREVIEW STATUS
========================================================= */

.preview-status-detail {

    margin-top: 2px;

}

.preview-status {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 4px 8px;

    border-radius: 6px;

    font-size: 10px !important;

    font-weight: 700 !important;

}

.preview-status.draft {

    background: #f1f3f6;

    color: #718096 !important;

}

.preview-status.sent {

    background: #eaf3ff;

    color: #3573b9 !important;

}

.preview-status.accepted {

    background: #eaf7f3;

    color: #167d70 !important;

}

.preview-status.rejected {

    background: #fff0f0;

    color: #c94a4a !important;

}

.preview-status.expired {

    background: #fff6e8;

    color: #b7791f !important;

}


/* =========================================================
   PREVIEW NOTE
========================================================= */

.preview-note {

    padding: 13px 14px;

    border-radius: 8px;

    background: #f1f5f7;

}

.preview-note strong {

    display: block;

    margin-bottom: 5px;

    color: #34415c;

    font-size: 11px;

}

.preview-note p {

    margin: 0;

    color: #8a94a6;

    font-size: 10px;

    line-height: 1.6;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .quotation-form-layout {

        grid-template-columns: 1fr;

    }

    .quotation-preview-panel {

        position: static;

        order: -1;

    }

}


@media (max-width: 600px) {

    .quotation-two-column {

        grid-template-columns: 1fr;

    }

    .quotation-item-grid {

        grid-template-columns: 1fr;

    }

    .form-actions {

        flex-direction: column-reverse;

        align-items: stretch;

    }

    .form-actions .btn {

        width: 100%;

        justify-content: center;

    }

}


@media (max-width: 450px) {

    .quotation-preview-panel {

        padding: 20px;

    }

    .preview-detail {

        flex-direction: column;

        gap: 4px;

    }

    .preview-detail > strong {

        max-width: 100%;

        text-align: left;

    }

}

/* EDIT PAGE */
/* =========================================================
   QUOTATION FORM LAYOUT
========================================================= */

.quotation-form-layout {

    display: grid;

    grid-template-columns:
        minmax(0, 1.25fr)
        minmax(320px, .75fr);

    gap: 32px;

    align-items: start;
}

.quotation-form-main {
    min-width: 0;
}


/* =========================================================
   FORM
========================================================= */

.form-group {
    margin-bottom: 19px;
}

.form-group label,
.financial-input label {

    display: block;

    margin-bottom: 7px;

    color: #17284f;

    font-size: 13px;

    font-weight: 600;
}

.required {
    color: #c94a4a;
}

.form-group input,
.form-group select,
.financial-input input,
.financial-input select {

    width: 100%;

    height: 42px;

    box-sizing: border-box;

    padding: 0 12px;

    border: 1px solid #d9dee8;

    border-radius: 8px;

    background: #fff;

    color: #17284f;

    font-size: 13px;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.form-group input:focus,
.form-group select:focus,
.financial-input input:focus,
.financial-input select:focus,
.form-group textarea:focus {

    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.form-group input:read-only {

    background: #f7f9fb;

    color: #718096;

    cursor: not-allowed;
}

.form-hint {

    display: block;

    margin-top: 6px;

    color: #8a94a6;

    font-size: 11px;

    line-height: 1.5;
}

.form-error {

    display: block;

    margin-top: 6px;

    color: #c94a4a;

    font-size: 11px;
}


/* =========================================================
   TWO COLUMN
========================================================= */

.quotation-two-column {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 16px;
}


/* =========================================================
   SECTION DIVIDER
========================================================= */

.section-divider {

    display: flex;

    align-items: center;

    margin: 25px 0 18px;

    padding-top: 20px;

    border-top: 1px solid #edf0f5;
}

.section-divider h4 {

    margin: 0 0 4px;

    color: #17284f;

    font-size: 13px;
}

.section-divider p {

    margin: 0;

    color: #8a94a6;

    font-size: 11px;
}


/* =========================================================
   FINANCIAL OPTIONS
========================================================= */

.financial-option {

    margin-bottom: 12px;

    border: 1px solid #e3e7ee;

    border-radius: 9px;

    background: #fff;

    overflow: hidden;

    transition:
        border-color .18s ease,
        background .18s ease;
}

.financial-option.active {

    border-color: #b8ddd5;

    background: #fbfefd;
}

.financial-option-head {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    min-height: 48px;

    padding: 0 13px;
}

.financial-option-title {

    display: flex;

    align-items: center;

    gap: 9px;
}

.financial-option-title input {

    width: 16px;

    height: 16px;

    margin: 0;

    accent-color: #2ba7a0;

    cursor: pointer;
}

.financial-option-title label {

    margin: 0;

    color: #34415c;

    font-size: 12px;

    font-weight: 600;

    cursor: pointer;
}

.option-status {

    padding: 4px 8px;

    border-radius: 6px;

    background: #f1f3f6;

    color: #8a94a6;

    font-size: 10px;

    font-weight: 600;
}

.financial-option.active .option-status {

    background: #eaf7f3;

    color: #167d70;
}

.financial-option-body {

    display: none;

    grid-template-columns: 1fr 150px;

    gap: 12px;

    padding: 0 13px 14px;

    border-top: 1px solid #edf0f5;
}

.financial-option.active .financial-option-body {

    display: grid;

    padding-top: 14px;
}

.input-with-prefix {

    display: flex;

    width: 100%;
}

.input-prefix {

    display: flex;

    align-items: center;

    justify-content: center;

    min-width: 42px;

    height: 42px;

    box-sizing: border-box;

    border: 1px solid #d9dee8;

    border-right: 0;

    border-radius: 8px 0 0 8px;

    background: #f7f9fb;

    color: #718096;

    font-size: 12px;

    font-weight: 600;
}

.input-with-prefix input {

    border-radius: 0 8px 8px 0;
}


/* =========================================================
   NOTES
========================================================= */

.quotation-notes {

    margin-top: 22px;
}

.form-group textarea {

    width: 100%;

    min-height: 105px;

    box-sizing: border-box;

    padding: 11px 12px;

    border: 1px solid #d9dee8;

    border-radius: 8px;

    background: #fff;

    color: #17284f;

    font-family: inherit;

    font-size: 13px;

    resize: vertical;
}


/* =========================================================
   ACTIONS
========================================================= */

.form-actions {

    display: flex;

    justify-content: flex-end;

    gap: 8px;

    padding-top: 8px;

    margin-top: 8px;

    border-top: 1px solid #edf0f5;
}


/* =========================================================
   PREVIEW
========================================================= */

.quotation-preview-panel {

    position: sticky;

    top: 24px;

    padding: 24px;

    border: 1px solid #e6eaf0;

    border-radius: 12px;

    background: #fafbfd;
}

.preview-label {

    margin-bottom: 20px;

    color: #8a94a6;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 1.2px;
}

.preview-quotation-head {

    display: flex;

    align-items: center;

    gap: 13px;
}

.preview-document-icon {

    width: 48px;

    height: 48px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 17px;

    font-weight: 700;
}

.preview-quotation-main {

    min-width: 0;
}

.preview-quotation-main h3 {

    margin: 0 0 4px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #17284f;

    font-size: 17px;
}

.preview-quotation-main span {

    display: block;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #7d8797;

    font-size: 11px;
}

.preview-divider {

    height: 1px;

    margin: 21px 0;

    background: #e5e9ef;
}

.preview-detail {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 20px;

    padding: 9px 0;
}

.preview-detail span {

    color: #8a94a6;

    font-size: 11px;
}

.preview-detail strong {

    max-width: 190px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #34415c;

    font-size: 12px;

    text-align: right;
}


/* =========================================================
   STATUS
========================================================= */

.preview-status {

    display: inline-flex;

    padding: 4px 8px;

    border-radius: 6px;

    font-size: 10px !important;
}

.preview-status.draft {

    background: #f1f3f6;

    color: #7d8797 !important;
}

.preview-status.sent {

    background: #edf4fb;

    color: #4776a8 !important;
}

.preview-status.approved {

    background: #eaf7f3;

    color: #167d70 !important;
}

.preview-status.rejected,
.preview-status.cancelled {

    background: #fbeeee;

    color: #b84b4b !important;
}

.preview-status.expired {

    background: #fff5e8;

    color: #b2772e !important;
}


/* =========================================================
   FINANCIAL PREVIEW
========================================================= */

.preview-financial-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 8px 0;

    color: #8a94a6;

    font-size: 11px;
}

.preview-financial-row strong {

    color: #34415c;

    font-size: 11px;
}

.preview-total {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-top: 8px;

    padding-top: 13px;

    border-top: 1px solid #e5e9ef;
}

.preview-total span {

    color: #34415c;

    font-size: 12px;

    font-weight: 600;
}

.preview-total strong {

    color: #17284f;

    font-size: 16px;
}


/* =========================================================
   PREVIEW NOTE
========================================================= */

.preview-note {

    margin-top: 20px;

    padding: 13px 14px;

    border-radius: 8px;

    background: #f1f5f7;
}

.preview-note strong {

    display: block;

    margin-bottom: 5px;

    color: #34415c;

    font-size: 11px;
}

.preview-note p {

    margin: 0;

    color: #8a94a6;

    font-size: 10px;

    line-height: 1.6;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .quotation-form-layout {

        grid-template-columns: 1fr;
    }

    .quotation-preview-panel {

        position: static;

        order: -1;
    }

}

@media (max-width: 600px) {

    .quotation-two-column,
    .financial-option-body {

        grid-template-columns: 1fr;
    }

    .form-actions {

        flex-direction: column-reverse;
    }

    .form-actions .btn {

        width: 100%;

        justify-content: center;
    }

}

/* SHOW PAGE */
/* =========================================================
   INFORMATION
========================================================= */

    .information-layout {

        display: grid;

        grid-template-columns:
            minmax(0, 1.2fr) minmax(0, 1fr);

        gap: 18px;

        margin-bottom: 18px;

        align-items: stretch;

    }


    .information-card {

        display: flex;

        flex-direction: column;

        min-width: 0;

        overflow: hidden;

    }


    .information-card .card-head {

        min-height: 68px;

        box-sizing: border-box;

    }


    .information-card .card-body {

        flex: 1;

        min-width: 0;

    }


    /* =========================================================
   QUOTATION INFORMATION
========================================================= */

    .quotation-information-grid {

        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        column-gap: 34px;

        row-gap: 22px;

    }


    .information-field {

        display: flex;

        flex-direction: column;

        align-items: flex-start;

        justify-content: center;

        min-width: 0;

        min-height: 46px;

    }


    .information-label {

        margin-bottom: 6px;

        color: #8a94a6;

        font-size: 9px;

        font-weight: 500;

        text-transform: uppercase;

        letter-spacing: .03em;

    }


    .information-value {

        display: block;

        max-width: 100%;

        overflow: hidden;

        color: #34415c;

        font-size: 12px;

        font-weight: 600;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .quotation-number {

        color: #15966f !important;

        font-size: 13px !important;

        font-weight: 700 !important;

    }


    /* =========================================================
   STATUS
========================================================= */

    .quotation-status-badge {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        width: fit-content;

        min-height: 23px;

        padding: 4px 9px;

        box-sizing: border-box;

        border-radius: 6px;

        background: #eef2f6;

        color: #4d5b70;

        font-size: 10px;

        font-weight: 600;

        line-height: 1;

        white-space: nowrap;

    }


    .quotation-status-draft {

        background: #f1f3f6;

        color: #667085;

    }


    .quotation-status-sent {

        background: #edf3ff;

        color: #315caa;

    }


    .quotation-status-approved {

        background: #e8f5f0;

        color: #16805f;

    }


    .quotation-status-rejected {

        background: #fff0f0;

        color: #a14d4d;

    }


    .quotation-status-expired {

        background: #fff4e5;

        color: #9a6a18;

    }


    .quotation-status-cancelled {

        background: #f1f1f1;

        color: #707070;

    }


    /* =========================================================
   OPPORTUNITY
========================================================= */

    .opportunity-content {

        width: 100%;

        min-width: 0;

    }


    .opportunity-link {

        display: flex;

        align-items: center;

        justify-content: space-between;

        width: 100%;

        min-width: 0;

        gap: 12px;

        padding: 10px 12px;

        box-sizing: border-box;

        border: 1px solid #e7edf3;

        border-radius: 8px;

        background: #fafbfd;

        text-decoration: none;

    }


    .opportunity-link-content {

        display: flex;

        flex-direction: column;

        min-width: 0;

        gap: 3px;

    }


    .opportunity-link-label {

        color: #8a94a6;

        font-size: 8px;

        text-transform: uppercase;

    }


    .opportunity-code {

        overflow: hidden;

        color: #17284f;

        font-size: 12px;

        font-weight: 700;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .opportunity-arrow {

        display: flex;

        align-items: center;

        justify-content: center;

        flex: 0 0 auto;

        width: 25px;

        height: 25px;

        border-radius: 6px;

        background: #e9f7f3;

        color: #15966f;

    }


    .opportunity-name {

        margin-top: 11px;

        overflow: hidden;

        color: #34415c;

        font-size: 12px;

        font-weight: 600;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .opportunity-meta {

        display: grid;

        grid-template-columns:
            repeat(3, minmax(0, 1fr));

        margin-top: 17px;

        padding-top: 15px;

        border-top: 1px solid #edf0f5;

    }


    .opportunity-meta-item {

        display: flex;

        flex-direction: column;

        min-width: 0;

        gap: 5px;

        padding: 0 14px;

    }


    .opportunity-meta-item:first-child {

        padding-left: 0;

    }


    .opportunity-meta-item:last-child {

        padding-right: 0;

    }


    .opportunity-meta-item:not(:last-child) {

        border-right: 1px solid #edf0f5;

    }


    .opportunity-meta-item span {

        color: #8a94a6;

        font-size: 8px;

        text-transform: uppercase;

    }


    .opportunity-meta-item strong {

        overflow: hidden;

        color: #34415c;

        font-size: 10px;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    /* =========================================================
   MAIN GRID
========================================================= */

    .main-detail-grid {

        display: grid;

        grid-template-columns:
            minmax(0, 1.65fr) minmax(300px, .85fr);

        gap: 18px;

        align-items: stretch;

        margin-bottom: 18px;

    }


    .items-card,
    .financial-card {

        min-width: 0;

    }


    /*
|--------------------------------------------------------------------------
| Important
|--------------------------------------------------------------------------
|
| Kedua card dibuat stretch.
| Jadi ketika Quotation Items hanya memiliki 1 item,
| card tetap mengikuti tinggi Financial Summary.
|
*/

    .items-card {

        display: flex;

        flex-direction: column;

    }


    .items-card .card-body {

        flex: 1;

        display: flex;

        flex-direction: column;

    }


    .financial-card {

        position: static;

    }


    /* =========================================================
   ITEMS HEADER
========================================================= */

    .items-header-actions {

        display: flex;

        align-items: center;

        gap: 8px;

        flex: 0 0 auto;

    }


    .items-count {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        min-width: 52px;

        padding: 5px 9px;

        border-radius: 6px;

        background: #f1f5f9;

        color: #64748b;

        font-size: 9px;

        font-weight: 600;

        white-space: nowrap;

    }


    .btn-show-all {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        min-height: 28px;

        padding: 5px 10px;

        border: 1px solid #d8e8e2;

        border-radius: 6px;

        background: #f5fbf8;

        color: #15966f;

        font-family: inherit;

        font-size: 9px;

        font-weight: 600;

        cursor: pointer;

        transition:
            background .15s ease,
            border-color .15s ease;

    }


    .btn-show-all:hover {

        border-color: #b9d9cd;

        background: #e9f7f3;

    }


    /* =========================================================
   ITEM LIST
========================================================= */

    .items-list {

        width: 100%;

    }


    .quotation-item {

        display: grid;

        grid-template-columns:
            minmax(190px, 1.5fr) minmax(65px, .55fr) minmax(125px, 1fr) minmax(105px, .8fr) minmax(130px, 1fr);

        gap: 16px;

        align-items: center;

        padding: 17px 20px;

        border-bottom: 1px solid #edf0f5;

        box-sizing: border-box;

    }


    .quotation-item:last-child {

        border-bottom: 0;

    }


    .item-product {

        display: flex;

        align-items: center;

        min-width: 0;

        gap: 11px;

    }


    .product-icon {

        display: flex;

        align-items: center;

        justify-content: center;

        flex: 0 0 auto;

        width: 34px;

        height: 34px;

        border-radius: 8px;

        background: #edf3ff;

        color: #315caa;

        font-size: 11px;

        font-weight: 700;

    }


    .item-product-info {

        display: flex;

        flex-direction: column;

        min-width: 0;

        gap: 3px;

    }


    .item-product-info strong {

        overflow: hidden;

        color: #34415c;

        font-size: 11px;

        font-weight: 600;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .item-product-info span {

        overflow: hidden;

        color: #8a94a6;

        font-size: 9px;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .item-detail,
    .item-subtotal {

        display: flex;

        flex-direction: column;

        min-width: 0;

        gap: 4px;

    }


    .item-detail span,
    .item-subtotal span {

        color: #8a94a6;

        font-size: 8px;

        text-transform: uppercase;

    }


    .item-detail strong,
    .item-subtotal strong {

        overflow: hidden;

        color: #34415c;

        font-size: 10px;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .item-subtotal {

        text-align: right;

    }


    .item-subtotal strong {

        color: #17284f;

        font-size: 11px;

    }


    .item-discount {

        color: #b45353 !important;

    }


    .item-no-discount {

        color: #a0a8b5 !important;

    }


    /* =========================================================
   ITEMS FOOTER
========================================================= */

    .items-footer {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        margin-top: auto;

        padding: 13px 20px;

        border-top: 1px solid #edf0f5;

        background: #fafbfd;

    }


    .items-pagination-info {

        color: #8a94a6;

        font-size: 9px;

    }


    .items-pagination-info strong {

        color: #5d6879;

    }


    /* =========================================================
   PAGINATION
========================================================= */

    .quotation-pagination {

        display: flex;

        align-items: center;

    }


    .quotation-pagination nav {

        display: flex;

        align-items: center;

    }


    .quotation-pagination nav>div:first-child {

        display: none;

    }


    .quotation-pagination nav>div:last-child {

        display: flex;

        align-items: center;

    }


    .quotation-pagination nav a,
    .quotation-pagination nav span {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        min-width: 27px;

        height: 27px;

        margin-left: 4px;

        padding: 0 7px;

        border: 1px solid #e4e9ef;

        border-radius: 6px;

        background: #fff;

        color: #64748b;

        font-size: 9px;

        text-decoration: none;

    }


    .quotation-pagination nav a:hover {

        border-color: #cfe4dc;

        background: #f5fbf8;

        color: #15966f;

    }


    .quotation-pagination nav span[aria-current="page"] {

        border-color: #15966f;

        background: #15966f;

        color: #fff;

    }


    /* =========================================================
   EMPTY
========================================================= */

    .items-empty {

        display: flex;

        flex: 1;

        flex-direction: column;

        align-items: center;

        justify-content: center;

        min-height: 180px;

        padding: 30px;

        text-align: center;

    }


    .empty-icon {

        display: flex;

        align-items: center;

        justify-content: center;

        width: 36px;

        height: 36px;

        margin-bottom: 10px;

        border-radius: 8px;

        background: #f1f5f9;

        color: #94a3b8;

        font-size: 18px;

    }


    .items-empty strong {

        color: #34415c;

        font-size: 12px;

    }


    .items-empty span {

        margin-top: 5px;

        color: #8a94a6;

        font-size: 10px;

    }


    /* =========================================================
   FINANCIAL
========================================================= */

    .financial-list {

        width: 100%;

    }


    .financial-row {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        padding: 12px 0;

        border-bottom: 1px solid #edf0f5;

    }


    .financial-row>div:first-child {

        display: flex;

        flex-direction: column;

        min-width: 0;

        gap: 3px;

    }


    .financial-row span {

        color: #34415c;

        font-size: 10px;

        font-weight: 600;

    }


    .financial-row small {

        color: #8a94a6;

        font-size: 8px;

    }


    .financial-row>strong {

        flex: 0 0 auto;

        color: #34415c;

        font-size: 10px;

        white-space: nowrap;

    }


    .financial-amount {

        display: flex;

        align-items: center;

        justify-content: flex-end;

        flex-wrap: wrap;

        gap: 7px;

    }


    .discount-amount strong {

        color: #b45353;

    }


    .tax-amount strong {

        color: #16805f;

    }


    .type-badge {

        display: inline-flex;

        padding: 3px 6px;

        border-radius: 5px;

        font-size: 8px;

        font-weight: 700;

    }


    .discount-badge {

        background: #fff0f0;

        color: #b45353;

    }


    .tax-badge {

        background: #e8f5f0;

        color: #16805f;

    }


    .after-discount-row {

        background: #fafbfd;

    }


    .grand-total {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        margin-top: 17px;

        padding: 15px;

        border-radius: 9px;

        background: #f4f8f7;

    }


    .grand-total>div {

        display: flex;

        flex-direction: column;

        gap: 4px;

    }


    .grand-total span {

        color: #34415c;

        font-size: 10px;

        font-weight: 600;

    }


    .grand-total small {

        color: #8a94a6;

        font-size: 8px;

    }


    .grand-total strong {

        color: #15966f;

        font-size: 16px;

        white-space: nowrap;

    }


    .financial-status {

        display: flex;

        align-items: center;

        gap: 7px;

        margin-top: 13px;

        color: #8a94a6;

        font-size: 8px;

    }


    .status-dot {

        width: 6px;

        height: 6px;

        flex: 0 0 auto;

        border-radius: 50%;

        background: #1db887;

    }


    /* =========================================================
   NOTES
========================================================= */

    .notes-card {

        margin-bottom: 12px;

    }


    .notes-content {

        padding: 12px 14px;

        border: 1px solid #edf0f5;

        border-radius: 8px;

        background: #fafbfd;

        color: #4a5568;

        font-size: 11px;

        line-height: 1.7;

        word-break: break-word;

    }


    .notes-empty {

        color: #8a94a6;

        font-size: 10px;

    }


    /* =========================================================
   METADATA
========================================================= */

    .quotation-metadata {

        display: flex;

        align-items: center;

        justify-content: space-between;

        flex-wrap: wrap;

        gap: 8px 20px;

        padding: 2px 2px 20px;

        color: #9aa3b1;

        font-size: 8px;

    }


    /* =========================================================
   MODAL
========================================================= */

    .quotation-items-modal {

        position: fixed;

        inset: 0;

        z-index: 9999;

        display: none;

        align-items: center;

        justify-content: center;

        padding: 25px;

        box-sizing: border-box;

    }


    .quotation-items-modal.is-open {

        display: flex;

    }


    .quotation-items-modal-overlay {

        position: absolute;

        inset: 0;

        background: rgba(23, 40, 79, .42);

        backdrop-filter: blur(2px);

    }


    .quotation-items-dialog {

        position: relative;

        z-index: 1;

        display: flex;

        flex-direction: column;

        width: min(1100px, 100%);

        max-height: min(760px, 90vh);

        overflow: hidden;

        border: 1px solid #e5eaf0;

        border-radius: 12px;

        background: #fff;

        box-shadow: 0 20px 60px rgba(23, 40, 79, .18);

    }


    .quotation-items-modal-header {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        padding: 18px 22px;

        border-bottom: 1px solid #edf0f5;

    }


    .quotation-items-modal-header h3 {

        margin: 0;

        color: #17284f;

        font-size: 15px;

        font-weight: 700;

    }


    .quotation-items-modal-header p {

        margin: 4px 0 0;

        color: #8a94a6;

        font-size: 9px;

    }


    .modal-close-button {

        display: flex;

        align-items: center;

        justify-content: center;

        width: 30px;

        height: 30px;

        flex: 0 0 auto;

        border: 0;

        border-radius: 7px;

        background: #f4f6f8;

        color: #64748b;

        font-size: 20px;

        line-height: 1;

        cursor: pointer;

    }


    .modal-close-button:hover {

        background: #edf0f5;

        color: #17284f;

    }


    /* =========================================================
   MODAL TOOLBAR
========================================================= */

    .quotation-items-toolbar {

        display: flex;

        align-items: center;

        gap: 10px;

        padding: 13px 22px;

        border-bottom: 1px solid #edf0f5;

        background: #fafbfd;

    }


    .quotation-items-search {

        position: relative;

        flex: 1;

        min-width: 0;

    }


    .quotation-items-search-icon {

        position: absolute;

        top: 50%;

        left: 11px;

        transform: translateY(-50%);

        color: #7d8898;

        font-size: 18px;

        line-height: 1;

        pointer-events: none;

    }


    .quotation-items-search input {

        width: 100%;

        height: 34px;

        padding: 0 12px 0 34px;

        border: 1px solid #dfe5ec;

        border-radius: 7px;

        outline: none;

        background: #fff;

        color: #34415c;

        font-family: inherit;

        font-size: 10px;

        box-sizing: border-box;

    }


    .quotation-items-search input:focus {

        border-color: #9fcdbf;

        box-shadow: 0 0 0 3px rgba(21, 150, 111, .08);

    }


    .quotation-items-sort {

        width: 180px;

        height: 34px;

        padding: 0 10px;

        border: 1px solid #dfe5ec;

        border-radius: 7px;

        outline: none;

        background: #fff;

        color: #34415c;

        font-family: inherit;

        font-size: 10px;

    }


    /* =========================================================
   MODAL BODY
========================================================= */

    .quotation-items-modal-body {

        position: relative;

        min-height: 300px;

        overflow: auto;

    }


    .quotation-items-table-wrapper {

        width: 100%;

        overflow-x: auto;

    }


    .quotation-items-table {

        width: 100%;

        min-width: 760px;

        border-collapse: collapse;

    }


    .quotation-items-table th {

        position: sticky;

        top: 0;

        z-index: 2;

        padding: 11px 16px;

        border-bottom: 1px solid #e7ebf0;

        background: #f8fafc;

        color: #7d8898;

        font-size: 8px;

        font-weight: 600;

        text-align: left;

        text-transform: uppercase;

        letter-spacing: .03em;

    }


    .quotation-items-table td {

        padding: 12px 16px;

        border-bottom: 1px solid #edf0f5;

        color: #34415c;

        font-size: 10px;

        vertical-align: middle;

    }


    .quotation-items-table tbody tr:hover {

        background: #fafcfb;

    }


    .modal-product {

        display: flex;

        align-items: center;

        min-width: 0;

        gap: 10px;

    }


    .modal-product-icon {

        display: flex;

        align-items: center;

        justify-content: center;

        width: 30px;

        height: 30px;

        flex: 0 0 auto;

        border-radius: 7px;

        background: #edf3ff;

        color: #315caa;

        font-size: 9px;

        font-weight: 700;

    }


    .modal-product-info {

        display: flex;

        flex-direction: column;

        min-width: 0;

        gap: 3px;

    }


    .modal-product-info strong {

        overflow: hidden;

        color: #34415c;

        font-size: 10px;

        font-weight: 600;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .modal-product-info span {

        color: #8a94a6;

        font-size: 8px;

    }


    .modal-number {

        white-space: nowrap;

    }


    .modal-discount {

        color: #b45353 !important;

    }


    .modal-subtotal {

        color: #17284f !important;

        font-weight: 700;

        white-space: nowrap;

    }


    /* =========================================================
   MODAL LOADING / EMPTY
========================================================= */

    .quotation-items-loading,
    .quotation-items-modal-empty {

        display: flex;

        align-items: center;

        justify-content: center;

        min-height: 300px;

        color: #8a94a6;

        font-size: 10px;

    }


    .quotation-items-loading {

        display: none;

    }


    /* =========================================================
   MODAL FOOTER
========================================================= */

    .quotation-items-modal-footer {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        min-height: 54px;

        padding: 10px 22px;

        border-top: 1px solid #edf0f5;

        background: #fafbfd;

        box-sizing: border-box;

    }


    .quotation-items-modal-info {

        color: #8a94a6;

        font-size: 9px;

    }


    .quotation-items-modal-info strong {

        color: #5d6879;

    }


    .quotation-items-modal-pagination {

        display: flex;

        align-items: center;

        gap: 4px;

    }


    .modal-page-button {

        display: flex;

        align-items: center;

        justify-content: center;

        min-width: 27px;

        height: 27px;

        padding: 0 7px;

        border: 1px solid #e4e9ef;

        border-radius: 6px;

        background: #fff;

        color: #64748b;

        font-family: inherit;

        font-size: 9px;

        cursor: pointer;

    }


    .modal-page-button:hover {

        border-color: #cfe4dc;

        background: #f5fbf8;

        color: #15966f;

    }


    .modal-page-button.active {

        border-color: #15966f;

        background: #15966f;

        color: #fff;

    }


    .modal-page-button:disabled {

        opacity: .45;

        cursor: default;

    }


    /* =========================================================
   RESPONSIVE
========================================================= */

    @media (max-width: 1150px) {

        .main-detail-grid {

            grid-template-columns:
                minmax(0, 1.45fr) minmax(280px, .85fr);

        }


        .quotation-item {

            grid-template-columns:
                minmax(170px, 1.4fr) minmax(60px, .5fr) minmax(110px, .9fr) minmax(95px, .75fr) minmax(115px, .9fr);

            gap: 12px;

        }

    }


    @media (max-width: 950px) {

        .information-layout {

            grid-template-columns: 1fr;

        }


        .main-detail-grid {

            grid-template-columns: 1fr;

        }

    }


    @media (max-width: 750px) {

        .quotation-information-grid {

            grid-template-columns: 1fr 1fr;

            column-gap: 24px;

        }


        .quotation-item {

            grid-template-columns:
                minmax(0, 1.5fr) minmax(70px, .6fr) minmax(100px, .9fr);

        }


        .item-subtotal {

            grid-column: 3;

            text-align: left;

        }


        .items-footer {

            align-items: flex-start;

            flex-direction: column;

        }


        .quotation-pagination {

            width: 100%;

        }


        .quotation-items-toolbar {

            align-items: stretch;

            flex-direction: column;

        }


        .quotation-items-sort {

            width: 100%;

        }


        .quotation-items-modal {

            padding: 10px;

        }


        .quotation-items-dialog {

            max-height: 95vh;

        }

    }


    @media (max-width: 600px) {

        .quotation-information-grid {

            grid-template-columns: 1fr;

        }


        .opportunity-meta {

            grid-template-columns: 1fr;

            gap: 12px;

        }


        .opportunity-meta-item {

            padding: 0 !important;

        }


        .opportunity-meta-item:not(:last-child) {

            padding-bottom: 12px;

            border-right: 0;

            border-bottom: 1px solid #edf0f5;

        }


        .quotation-item {

            grid-template-columns: 1fr 1fr;

            gap: 15px;

            padding: 16px;

        }


        .item-product {

            grid-column: 1 / -1;

        }


        .item-subtotal {

            grid-column: auto;

        }


        .items-header-actions {

            flex-wrap: wrap;

        }


        .quotation-items-modal-footer {

            align-items: flex-start;

            flex-direction: column;

        }

    }


    @media (max-width: 480px) {

        .quotation-items-modal-header {

            padding: 15px;

        }


        .quotation-items-toolbar {

            padding: 11px 15px;

        }


        .quotation-items-modal-footer {

            padding: 10px 15px;

        }

    }

.quotation-status-actions { margin-bottom: 20px; }
.quotation-status-actions-body { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.quotation-status-actions-body form { margin: 0; }

/* Quotation financial controls: keep prefixes separate from editable values. */
.financial-option .input-with-prefix { display: flex; align-items: stretch; width: 100%; position: relative; min-width: 0; }
.financial-option .input-with-prefix .input-prefix { position: static; flex: 0 0 44px; min-width: 44px; width: 44px; height: 42px; border: 1px solid #d9dee8; border-right: 0; border-radius: 8px 0 0 8px; background: #f7f9fb; }
.financial-option .input-with-prefix input { flex: 1 1 auto; width: 0; min-width: 0; height: 42px; padding: 9px 12px !important; border-radius: 0 8px 8px 0; box-sizing: border-box; }
.financial-option .financial-option-header, .financial-option .financial-option-head { min-height: 54px; padding: 0 14px; cursor: pointer; }
.financial-option .financial-option-header .checkbox-label { display: flex !important; align-items: center; width: 100%; min-height: 54px; cursor: pointer; }
.financial-option .financial-option-head .financial-option-title { flex: 1; min-height: 54px; }
.financial-option .financial-option-head .financial-option-title label { flex: 1; min-height: 54px; display: flex; align-items: center; cursor: pointer; }
.financial-option .financial-option-header input[type="checkbox"], .financial-option .financial-option-head input[type="checkbox"] { width: 18px !important; height: 18px !important; flex-shrink: 0; }
.quotation-decision-modal[hidden] { display: none !important; }
.quotation-decision-modal { position: fixed; inset: 0; z-index: 2200; display: flex; justify-content: center; align-items: center; padding: 18px; background: rgba(17, 34, 65, .57); }
.quotation-decision-dialog { width: min(100%, 440px); padding: 26px; border-radius: 13px; background: white; box-shadow: 0 18px 60px rgba(0,0,0,.22); }
.quotation-decision-dialog h3 { margin: 0 0 10px; color: #17284f; }
.quotation-decision-dialog p { margin: 0 0 20px; color: #5c6a81; line-height: 1.6; }
.quotation-decision-actions { display: flex; gap: 9px; justify-content: flex-end; flex-wrap: wrap; }
.quotation-decision-actions .danger { color: #fff; background: #c63d49; border-color: #c63d49; }


.quotation-sales-modal[hidden] { display: none !important; }
.quotation-sales-modal { position: fixed; inset: 0; z-index: 30000; background: rgba(12,27,56,.58); display:flex; align-items:center; justify-content:center; padding:18px; }
.quotation-sales-dialog { max-width: 450px; width:100%; border-radius:16px; padding:28px; background:#fff; box-shadow: 0 25px 75px rgba(8,28,59,.22); text-align:center; }
.quotation-sales-icon { margin:0 auto 15px; display:grid; place-items:center; width:55px; height:55px; border-radius:14px; color:#168568; background:#e8f8f0; font-size:17px; font-weight:800; }
.quotation-sales-dialog h3 { margin:0 0 9px; color:#17284f; font-size:19px; }
.quotation-sales-dialog p { margin:0 0 24px; line-height:1.65; font-size:13px; color:#6b7890; }
.quotation-sales-dialog strong { color:#17284f; }
.quotation-sales-buttons { display:flex; justify-content:center; gap:9px; flex-wrap:wrap; }
.quotation-sales-buttons .btn { min-height:40px; }
</style>
