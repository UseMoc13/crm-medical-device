@if(($page ?? '') === 'index')
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

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .summary-card {
        padding: 20px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
    }

    .summary-label {
        display: block;
        margin-bottom: 10px;
        color: #6b7280;
        font-size: 13px;
    }

    .summary-card strong {
        font-size: 26px;
        color: #111827;
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

    .filter-field select {
        max-width: 100%;
    }

    .table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .warranty-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .warranty-table th,
    .warranty-table td {
        padding: 13px 14px;
        border: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: middle;
    }

    .warranty-table th {
        background: #f9fafb;
        color: #374151;
        font-weight: 600;
        white-space: nowrap;
    }

    .warranty-table tbody tr:hover {
        background: #f9fafb;
    }

    .col-number {
        width: 55px;
        text-align: center !important;
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

    .badge.success {
        background: #dcfce7;
        color: #166534;
    }

    .badge.warning {
        background: #fef3c7;
        color: #92400e;
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

    @media (max-width: 900px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
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

@if(($page ?? '') === 'create')
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
        padding: 24px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        color: #374151;
        font-size: 14px;
        font-weight: 600;
    }

    .required {
        color: #dc2626;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 11px 12px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #374151;
        font-size: 14px;
        font-family: inherit;
    }

    .form-group input,
    .form-group select {
        min-height: 42px;
    }

    .form-group textarea {
        resize: vertical;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #15966f;
        box-shadow: 0 0 0 3px rgb(21 150 111 / 10%);
    }

    .full-width {
        grid-column: 1 / -1;
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

    .alert {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 8px;
    }

    .alert.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .error-text {
        color: #dc2626;
        font-size: 12px;
    }

    @media (max-width: 768px) {
        .page-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .full-width {
            grid-column: auto;
        }

        .card-body {
            padding: 16px;
        }
    }
    /* AUTOMATIC WARRANTY NUMBER */
    .auto-number-field {
        position: relative;
    }

    .auto-number-field .form-control {
        padding-right: 65px;
    }

    .auto-number-field .form-control[readonly] {
        background: var(--surface-secondary, #f8fafc);
        color: var(--text-muted, #64748b);
        cursor: default;
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

    .form-hint {
        font-size: 11px;
        line-height: 1.6;
        color: var(--text-muted, #64748b);
    }

</style>
@endif

@if(($page ?? '') === 'edit')
<style>
    .page-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px;
    }

    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .page-head h1 {
        margin: 0 0 8px;
        font-size: 26px;
        font-weight: 700;
        color: #1f2937;
    }

    .page-subtitle {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .page-actions {
        display: flex;
        gap: 10px;
    }

    .card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .card-header {
        padding: 22px 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-header h2 {
        margin: 0 0 6px;
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
    }

    .card-header p {
        margin: 0;
        color: #6b7280;
        font-size: 13px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
        padding: 24px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .form-group-full {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }

    .required {
        color: #dc2626;
    }

    .form-control {
        width: 100%;
        box-sizing: border-box;
        padding: 11px 12px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #111827;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    .is-invalid {
        border-color: #dc2626;
    }

    .error-message {
        color: #dc2626;
        font-size: 12px;
    }

    .form-hint {
        color: #6b7280;
        font-size: 12px;
    }

    .alert {
        padding: 16px 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        font-size: 14px;
    }

    .alert-danger {
        color: #991b1b;
        background: #fef2f2;
        border: 1px solid #fecaca;
    }

    .alert ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 20px 24px;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border: 1px solid transparent;
        border-radius: 7px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-primary {
        color: #fff;
        background: #2563eb;
    }

    .btn-primary:hover {
        background: #1d4ed8;
    }

    .btn-secondary {
        color: #374151;
        background: #fff;
        border-color: #d1d5db;
    }

    .btn-secondary:hover {
        background: #f3f4f6;
    }

    @media (max-width: 768px) {
        .page-container {
            padding: 16px;
        }

        .page-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .form-grid {
            grid-template-columns: 1fr;
            padding: 18px;
        }

        .form-group-full {
            grid-column: auto;
        }
    }
</style>
@endif

@if(($page ?? '') === 'show')
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

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
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

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 7px;
        padding: 13px 0;
        border-bottom: 1px solid #f3f4f6;
        overflow-wrap: anywhere;
    }

    .detail-item:first-child {
        padding-top: 0;
    }

    .detail-item:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    .detail-label {
        color: #6b7280;
        font-size: 13px;
    }

    .detail-item strong,
    .detail-item > span:not(.detail-label) {
        color: #374151;
        font-size: 14px;
    }

    .badge {
        display: inline-block;
        width: fit-content;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge.success {
        background: #dcfce7;
        color: #166534;
    }

    .badge.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge.secondary {
        background: #e5e7eb;
        color: #374151;
    }

    .terms-content {
        color: #374151;
        font-size: 14px;
        line-height: 1.7;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .empty-state {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .bottom-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        margin-top: 24px;
    }

    .bottom-actions form {
        margin: 0;
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

    .btn.danger {
        background: #dc2626;
        border-color: #dc2626;
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

    @media (max-width: 768px) {
        .page-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }

        .bottom-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .bottom-actions .btn {
            width: 100%;
            box-sizing: border-box;
        }
    }
</style>
@endif
