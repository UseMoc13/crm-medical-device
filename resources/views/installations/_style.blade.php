@if($page === 'index')
<style>

    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .page-head h1 {
        margin: 0 0 6px;
        font-size: 26px;
    }

    .page-head p {
        margin: 0;
        color: #6b7280;
    }

    .actions,
    .filter-form,
    .table-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card {
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
    }

    .card-head {
        padding: 18px 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-head h2 {
        margin: 0;
        font-size: 18px;
    }

    .card-body {
        padding: 20px;
    }

    .btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #374151;
        text-decoration: none;
        cursor: pointer;
        font-size: 14px;
    }

    .btn.primary {
        background: #15966f;
        border-color: #15966f;
        color: #fff;
    }

    .filter-form {
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .filter-field input,
    .filter-field select {
        min-height: 40px;
        padding: 9px 12px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #374151;
    }

    .filter-field input {
        width: 280px;
        max-width: 100%;
    }

    .table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .installation-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .installation-table th,
    .installation-table td {
        padding: 13px 14px;
        border: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: middle;
    }

    .installation-table th {
        background: #f9fafb;
        color: #374151;
        font-weight: 600;
        white-space: nowrap;
    }

    .installation-table tbody tr:hover {
        background: #f9fafb;
    }

    .col-number {
        width: 55px;
        text-align: center !important;
    }

    .col-date {
        min-width: 135px;
    }

    .col-location {
        min-width: 180px;
    }

    .col-status {
        min-width: 125px;
    }

    .col-actions {
        width: 130px;
        text-align: center !important;
    }

    .badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge.info {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge.success {
        background: #dcfce7;
        color: #166534;
    }

    .badge.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge.secondary {
        background: #e5e7eb;
        color: #374151;
    }

    .table-actions {
        justify-content: center;
    }

    .table-actions form {
        display: inline-flex;
        margin: 0;
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

    .empty-state {
        padding: 32px !important;
        color: #6b7280;
        text-align: center !important;
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

    .alert {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 8px;
    }

    .alert.success {
        background: #dcfce7;
        color: #166534;
    }

    .alert.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    @media (max-width: 768px) {
        .page-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .filter-form {
            align-items: stretch;
            flex-direction: column;
        }

        .filter-field input,
        .filter-field select {
            width: 100%;
        }

        .table-footer {
            align-items: flex-start;
            flex-direction: column;
        }
    }

</style>
@endif
@if($page === 'create')
<style>

    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .page-head h1 {
        margin: 0 0 6px;
        font-size: 26px;
    }

    .page-head p {
        margin: 0;
        color: #6b7280;
    }

    .card {
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
    }

    .card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 18px 22px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-head h2 {
        margin: 0;
        font-size: 18px;
    }

    .required-note {
        color: #6b7280;
        font-size: 12px;
    }

    .card-body {
        padding: 22px;
    }

    .form-section + .form-section {
        margin-top: 28px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }

    .form-section h3 {
        margin: 0 0 20px;
        font-size: 16px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }

    .required {
        color: #dc2626;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        box-sizing: border-box;
        width: 100%;
        padding: 11px 12px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #111827;
        font: inherit;
    }

    .form-group textarea {
        resize: vertical;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgb(37 99 235 / 12%);
    }

    .field-error {
        color: #dc2626;
        font-size: 12px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 10px 16px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #374151;
        text-decoration: none;
        cursor: pointer;
        font-size: 14px;
    }

    .btn.primary {
        background: #15966f;
        border-color: #15966f;
        color: #fff;
    }

    .btn:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    .alert {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 8px;
    }

    .alert.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .alert.warning {
        background: #fef3c7;
        color: #92400e;
    }

    @media (max-width: 700px) {
        .page-head,
        .card-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full-width {
            grid-column: auto;
        }

        .card-body {
            padding: 16px;
        }
    }

</style>
@endif
@if($page === 'edit')
<style>

    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .page-head h1 {
        margin: 0 0 6px;
        font-size: 26px;
    }

    .page-head p {
        margin: 0;
        color: #6b7280;
    }

    .card {
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
    }

    .card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 18px 22px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-head h2 {
        margin: 0;
        font-size: 18px;
    }

    .required-note {
        color: #6b7280;
        font-size: 12px;
    }

    .card-body {
        padding: 22px;
    }

    .form-section + .form-section {
        margin-top: 28px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }

    .form-section h3 {
        margin: 0 0 20px;
        font-size: 16px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }

    .required {
        color: #dc2626;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        box-sizing: border-box;
        width: 100%;
        padding: 11px 12px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #111827;
        font: inherit;
    }

    .form-group textarea {
        resize: vertical;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgb(37 99 235 / 12%);
    }

    .field-error {
        color: #dc2626;
        font-size: 12px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 10px 16px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #374151;
        text-decoration: none;
        cursor: pointer;
        font-size: 14px;
    }

    .btn.primary {
        background: #15966f;
        border-color: #15966f;
        color: #fff;
    }

    .alert {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 8px;
    }

    .alert.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    @media (max-width: 700px) {
        .page-head,
        .card-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full-width {
            grid-column: auto;
        }

        .card-body {
            padding: 16px;
        }
    }

</style>
@endif
@if($page === 'show')
<style>

    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .page-head h1 {
        margin: 0 0 6px;
        font-size: 26px;
    }

    .page-head p {
        margin: 0;
        color: #6b7280;
    }

    .actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card {
        overflow: hidden;
        margin-bottom: 22px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
    }

    .card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 18px 22px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-head h2 {
        margin: 0;
        font-size: 18px;
    }

    .card-body {
        padding: 22px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .detail-item.full-width {
        grid-column: 1 / -1;
    }

    .detail-label {
        color: #6b7280;
        font-size: 13px;
    }

    .detail-value {
        color: #111827;
        font-size: 14px;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }

    .badge {
        display: inline-block;
        width: fit-content;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge.info {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge.success {
        background: #dcfce7;
        color: #166534;
    }

    .badge.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge.secondary {
        background: #e5e7eb;
        color: #374151;
    }

    .checklist-count {
        color: #6b7280;
        font-size: 13px;
    }

    .table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .checklist-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .checklist-table th,
    .checklist-table td {
        padding: 13px 14px;
        border: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: middle;
    }

    .checklist-table th {
        background: #f9fafb;
        color: #374151;
        font-weight: 600;
        white-space: nowrap;
    }

    .col-number {
        width: 60px;
        text-align: center !important;
    }

    .empty-state {
        padding: 30px;
        text-align: center;
        color: #6b7280;
    }

    .empty-state h3 {
        margin: 0 0 8px;
        color: #374151;
    }

    .empty-state p {
        margin: 0;
    }

    .btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 10px 16px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #374151;
        text-decoration: none;
        cursor: pointer;
        font-size: 14px;
    }

    .btn.primary {
        background: #15966f;
        border-color: #15966f;
        color: #fff;
    }

    .alert {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 8px;
    }

    .alert.success {
        background: #dcfce7;
        color: #166534;
    }

    @media (max-width: 700px) {
        .page-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .actions {
            flex-wrap: wrap;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }

        .detail-item.full-width {
            grid-column: auto;
        }

        .card-body {
            padding: 16px;
        }
    }

    .detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    border-top: 1px solid #e5e7eb;
    border-left: 1px solid #e5e7eb;
}

.detail-item {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
    min-width: 0;
    padding: 18px 20px;
    border-right: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    box-sizing: border-box;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-label {
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
}

.detail-value {
    color: #111827;
    font-size: 14px;
    line-height: 1.6;
    overflow-wrap: anywhere;
    white-space: pre-line;
}

@media (max-width: 700px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }

    .detail-item.full-width {
        grid-column: auto;
    }

    .detail-item {
        padding: 15px 16px;
    }
}

</style>
@endif
@if(($page ?? '') === 'show')
<style>
.installation-progress-card { margin-bottom: 20px; }
.installation-progress-heading { display:flex; justify-content:space-between; gap:12px; margin-bottom:10px; color:#0f2744; }
.installation-progress-track { width:100%;height:10px;background:#e5edf1;border-radius:20px;overflow:hidden; }
.installation-progress-track > div { height:100%;background:#15966f;border-radius:20px;transition:width .2s; }
.installation-status-actions { display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:18px; }
.installation-checklist-action { display:flex;gap:6px;align-items:center;margin-top:8px; }
.installation-modal-backdrop[hidden] { display:none!important; }
.installation-modal-backdrop { position:fixed;inset:0;z-index:1200;background:rgba(6,24,44,.6);display:flex;align-items:center;justify-content:center;padding:20px; }
.installation-modal { background:#fff;border-radius:14px;padding:25px;width:min(100%,440px);box-shadow:0 20px 60px rgba(0,0,0,.25); }
.installation-modal h3 { margin:0 0 8px;color:#0f2744; }
.installation-modal p { color:#526477; }
.installation-modal-actions { display:flex;justify-content:flex-end;gap:10px;margin-top:20px; }
.installation-toast { position:fixed;z-index:1300;top:20px;left:50%;transform:translateX(-50%);min-width:260px;max-width:min(90vw,600px);padding:14px 20px;border-radius:10px;box-shadow:0 8px 30px #0002;font-weight:600;animation:installation-toast-fade .5s ease 4s forwards; }
.installation-toast.success { background:#e7f8ef;color:#106c48;border:1px solid #95d5b4; }
.installation-toast.danger { background:#fff0f0;color:#9f2323;border:1px solid #f0b9b9; }
@keyframes installation-toast-fade { to { opacity:0;visibility:hidden; } }
</style>
@endif
