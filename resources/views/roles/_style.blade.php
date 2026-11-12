<style>
/* Roles module styling — aligned with Product CRUD visual language. */
.role-form-shell,
.role-form-card,
.role-form-card-body,
.role-editor-grid,
.role-editor-main,
.role-editor-aside {
    overflow: visible !important;
    min-width: 0;
}

.role-form-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.role-required-note {
    font-size: 11px;
    color: #7c8798;
    white-space: nowrap;
}

.role-required-note span,
.role-required {
    color: #cf4857;
}

.role-form-card-body {
    padding: 26px !important;
}

.role-editor-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.32fr) minmax(300px, .68fr);
    gap: 28px;
    align-items: start;
    width: 100%;
}

.role-editor-main {
    min-width: 0;
}

.role-editor-aside {
    position: relative;
    align-self: start;
}

.role-form-section + .role-form-section {
    border-top: 1px solid #e2e8f0;
    margin-top: 30px;
    padding-top: 28px;
}

.role-section-heading {
    margin-bottom: 20px;
}

.role-section-heading h4 {
    margin: 0 0 6px;
    font-size: 15px;
    font-weight: 650;
    color: #17284f;
}

.role-section-heading p {
    margin: 0;
    font-size: 13px;
    line-height: 1.6;
    color: #64748b;
}

.role-identity-fields {
    display: grid;
    gap: 18px;
}

.role-field {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.role-field label {
    font-size: 13px;
    font-weight: 600;
    color: #334155;
}

.role-field small {
    font-size: 11px;
    color: #78869a;
    line-height: 1.5;
}

.role-form-shell .role-control {
    display: block;
    width: 100%;
    min-height: 42px;
    box-sizing: border-box;
    border: 1px solid #d7e0e9;
    border-radius: 8px;
    background: #fff;
    padding: 10px 12px;
    font-size: 13px;
    font-family: inherit;
    color: #223550;
    outline: 0;
    transition: border-color .15s, box-shadow .15s;
}

.role-form-shell textarea.role-control {
    min-height: 94px;
    resize: vertical;
}

.role-form-shell .role-control:focus {
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .12);
}

.role-editor-modes {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 18px;
}

.role-editor-mode {
    position: relative;
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    border: 1px solid #dfe7ee;
    border-radius: 9px;
    background: #fff;
    cursor: pointer;
    transition: border .15s, background .15s;
}

.role-editor-mode:has(input:checked) {
    background: #f1faf8;
    border-color: #28a49d;
}

.role-editor-mode > input {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
    overflow: hidden;
}

.role-mode-check {
    display: block;
    flex: 0 0 17px;
    width: 17px;
    height: 17px;
    border: 1.5px solid #b5c3d3;
    background: #fff;
    border-radius: 50%;
    position: relative;
}

.role-editor-mode:has(input:checked) .role-mode-check {
    border: 5px solid #159e92;
}

.role-editor-mode:has(input:focus-visible) {
    outline: 2px solid #159e92;
    outline-offset: 3px;
}

.role-mode-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.role-mode-content strong {
    font-size: 13px;
    color: #17284f;
    line-height: 1.4;
}

.role-mode-content small {
    font-size: 11px;
    line-height: 1.5;
    color: #64748b;
}

.role-template-box,
.role-custom-area {
    border: 1px solid #e1e9ef;
    border-radius: 10px;
    background: #fbfdff;
    padding: 18px;
}

.role-custom-area {
    margin-top: 14px;
}

.role-help {
    font-size: 12px;
    color: #6b7d91;
    line-height: 1.6;
    margin: 9px 0 0;
}

.role-copy-template {
    margin-top: 12px;
}

.role-matrix-intro {
    display: flex;
    align-items: start;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 16px;
}

.role-matrix-intro strong {
    color: #17284f;
    font-size: 13px;
}

.role-matrix-intro .role-help {
    margin-top: 5px;
}

.role-matrix-toolbar {
    display: flex;
    gap: 7px;
    flex-wrap: wrap;
}

.role-matrix-toolbar .btn {
    font-size: 11px;
    padding: 7px 10px;
    white-space: nowrap;
}

.role-module {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 14px;
    overflow: hidden;
    background: #fff;
}

.role-module-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 14px;
    background: #f8fafc;
    border-bottom: 1px solid #e9eef3;
}

.role-module-toggle {
    border: 0;
    padding: 2px 0;
    display: flex;
    align-items: center;
    gap: 9px;
    flex: 1;
    min-width: 0;
    background: transparent;
    text-align: left;
    cursor: pointer;
    color: #17284f;
    font-size: 13px;
    font-weight: 650;
}

.role-chevron {
    flex: 0 0 15px;
    transition: transform .16s;
    display: inline-block;
    font-size: 18px;
    color: #688297;
}

.role-module-toggle[aria-expanded="false"] .role-chevron {
    transform: rotate(-90deg);
}

.role-module-title {
    overflow-wrap: anywhere;
}

.role-module-count {
    white-space: nowrap;
    color: #8290a2;
    background: #edf2f6;
    border-radius: 999px;
    padding: 3px 7px;
    font-size: 10px;
    font-weight: 600;
}

.role-module-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.role-inline-button {
    border: none;
    background: transparent;
    font-size: 11px;
    font-weight: 600;
    color: #087e75;
    cursor: pointer;
    white-space: nowrap;
    padding: 4px 0;
}

.role-inline-button:hover {
    text-decoration: underline;
}

.role-module-toggle:focus-visible,
.role-inline-button:focus-visible {
    outline: 2px solid #20a79b;
    outline-offset: 2px;
}

.role-module-body[hidden],
.role-template-box[hidden],
.role-custom-area[hidden] {
    display: none !important;
}

.role-matrix-scroll {
    overflow-x: auto;
    max-width: 100%;
}

.role-form-shell table.role-matrix {
    width: 100%;
    min-width: 560px;
    table-layout: fixed;
    border-collapse: collapse;
    margin: 0;
}

.role-form-shell .role-matrix th,
.role-form-shell .role-matrix td {
    box-sizing: border-box;
    padding: 11px 5px;
    text-align: center;
    border: 0;
    border-bottom: 1px solid #eef2f6;
    background: transparent;
    vertical-align: middle;
    font-size: 12px;
    line-height: 1.3;
}

.role-form-shell .role-matrix thead th {
    background: #fbfcfe;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: #64748b;
}

.role-form-shell .role-matrix th:first-child {
    width: 40%;
    text-align: left;
    padding-left: 15px;
}

.role-form-shell .role-matrix th:not(:first-child) {
    width: 12%;
}

.role-form-shell .role-matrix tbody tr:last-child th,
.role-form-shell .role-matrix tbody tr:last-child td {
    border-bottom: 0;
}

.role-form-shell .role-matrix tbody tr:hover {
    background: #f9fcfc;
}

.role-form-shell .role-matrix .role-all-cell,
.role-form-shell .role-matrix .role-all-heading {
    background: #f4f9f8;
    border-left: 1px solid #e4efed;
}

.role-page-cell {
    padding: 14px 15px !important;
    text-align: left !important;
    vertical-align: top !important;
}

.role-page-title {
    color: #243757;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.5;
}

.role-page-caption {
    margin-top: 4px;
    color: #7c8ba0;
    font-size: 10.5px;
    line-height: 1.5;
}

.role-scope-field {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
    padding: 8px 10px;
    border: 1px solid #e6edf3;
    border-radius: 8px;
    background: #f8fafc;
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
}

.role-scope-field.is-disabled {
    opacity: .72;
}

.role-scope-field span {
    flex: 0 0 auto;
}

.role-scope-select {
    display: block;
    box-sizing: border-box;
    min-width: 0;
    width: min(100%, 190px);
    min-height: 34px;
    padding: 6px 10px;
    border: 1px solid #cfd8e3;
    border-radius: 7px;
    background: #fff;
    color: #334155;
    font-size: 11px;
    font-family: inherit;
}

.role-scope-select:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .12);
}

.role-scope-select:disabled {
    background: #f1f5f9;
    color: #94a3b8;
    cursor: not-allowed;
}

/* Isolated checkbox control: prevents global form CSS from breaking alignment. */
.role-form-shell .role-checkbox-cell {
    width: 27px;
    height: 27px;
    margin: 0 auto;
    padding: 0 !important;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-sizing: border-box;
}

.role-form-shell .role-checkbox-cell input[type="checkbox"] {
    position: absolute !important;
    inset: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 27px !important;
    height: 27px !important;
    min-height: 0 !important;
    max-width: none !important;
    opacity: 0 !important;
    z-index: 2;
    cursor: pointer;
    appearance: none !important;
    border: 0 !important;
    box-shadow: none !important;
}

.role-form-shell .role-check-visual {
    width: 17px;
    height: 17px;
    border: 1.5px solid #c1cedb;
    border-radius: 5px;
    background: #fff;
    display: grid;
    place-items: center;
    pointer-events: none;
    box-sizing: border-box;
    transition: .12s;
}

.role-form-shell .role-checkbox-cell input:checked + .role-check-visual {
    background: #169b90;
    border-color: #169b90;
}

.role-form-shell .role-checkbox-cell input:checked + .role-check-visual::after {
    content: '';
    display: block;
    width: 8px;
    height: 5px;
    border-bottom: 2px solid #fff;
    border-left: 2px solid #fff;
    transform: translateY(-1px) rotate(-45deg);
}

.role-form-shell .role-checkbox-cell input:indeterminate + .role-check-visual {
    background: #e2f3f1;
    border-color: #159e92;
}

.role-form-shell .role-checkbox-cell input:indeterminate + .role-check-visual::after {
    content: '';
    width: 8px;
    border-top: 2px solid #159e92;
}

.role-form-shell .role-checkbox-cell input:focus-visible + .role-check-visual {
    outline: 2px solid #159e92;
    outline-offset: 3px;
}

.role-scope-note {
    margin-top: 14px;
}

/* Live preview panel */
.role-preview-panel {
    position: sticky;
    top: 24px;
    align-self: start;
    min-width: 0;
    max-height: calc(100vh - 48px);
    overflow: auto;
    padding: 22px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
    box-sizing: border-box;
    z-index: 2;
}

.role-preview-panel::-webkit-scrollbar,
.role-preview-modules::-webkit-scrollbar {
    width: 8px;
}

.role-preview-panel::-webkit-scrollbar-thumb,
.role-preview-modules::-webkit-scrollbar-thumb {
    background: #d1dae5;
    border-radius: 999px;
}

.role-preview-label {
    margin-bottom: 20px;
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.2px;
}

.role-preview-identity {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
}

.role-preview-avatar {
    display: flex;
    flex: 0 0 48px;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: #dbeafe;
    color: #1d4ed8;
    font-size: 17px;
    font-weight: 700;
}

.role-preview-primary {
    min-width: 0;
    flex: 1;
}

.role-preview-primary h3 {
    overflow: hidden;
    margin: 0 0 4px;
    color: #1e293b;
    font-size: 16px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.role-preview-primary > span {
    display: block;
    overflow: hidden;
    color: #64748b;
    font-size: 11px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.role-preview-divider {
    height: 1px;
    margin: 20px 0;
    background: #e2e8f0;
}

.role-preview-detail {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    padding: 9px 0;
}

.role-preview-detail[hidden] {
    display: none !important;
}

.role-preview-detail > span {
    flex-shrink: 0;
    color: #64748b;
    font-size: 11px;
}

.role-preview-detail > strong {
    max-width: 65%;
    overflow-wrap: anywhere;
    color: #334155;
    font-size: 12px;
    font-weight: 600;
    text-align: right;
}

.role-preview-section-title {
    margin-bottom: 8px;
    color: #334155;
    font-size: 11px;
    font-weight: 700;
}

.role-preview-specification > p {
    margin: 0;
    color: #64748b;
    font-size: 11px;
    line-height: 1.6;
    white-space: pre-line;
    overflow-wrap: anywhere;
}

.role-preview-modules {
    display: grid;
    gap: 0;
    max-height: 320px;
    overflow-y: auto;
    scrollbar-width: thin;
}

.role-preview-module {
    padding: 10px 0;
    border-bottom: 1px solid #e2e8f0;
}

.role-preview-module:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}

.role-preview-module-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
}

.role-preview-module-head strong {
    font-size: 11px;
    color: #334155;
    font-weight: 700;
}

.role-preview-module-head span {
    flex-shrink: 0;
    color: #64748b;
    font-size: 10px;
    white-space: nowrap;
}

.role-preview-module p,
.role-preview-empty {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 11px;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.role-preview-notice {
    margin-top: 20px;
    padding: 13px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    background: #fff;
}

.role-preview-notice strong {
    display: block;
    margin-bottom: 5px;
    color: #334155;
    font-size: 11px;
}

.role-preview-notice p {
    margin: 0;
    color: #64748b;
    font-size: 10px;
    line-height: 1.6;
}

.role-form-footer {
    border-top: 1px solid #e6ecf2;
    padding: 18px 26px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* Read-only Role Details */
.role-detail-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(280px, .8fr);
    gap: 20px;
    align-items: start;
}

.role-detail-card .card-body {
    padding: 22px;
}

.role-detail-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 23px;
}

.role-detail-field {
    display: grid;
    gap: 6px;
    min-width: 0;
}

.role-detail-field.wide {
    grid-column: 1 / -1;
}

.role-detail-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .04em;
    font-weight: 650;
    color: #748196;
}

.role-detail-value {
    font-size: 14px;
    color: #17284f;
    overflow-wrap: anywhere;
}

.role-detail-value.muted {
    color: #7c8ba0;
    font-weight: 400;
}

.role-detail-summary {
    padding: 18px;
    background: #f8fafc;
    border: 1px solid #e5ebf1;
    border-radius: 9px;
    display: flex;
    align-items: center;
    gap: 13px;
}

.role-detail-icon {
    height: 45px;
    width: 45px;
    flex: 0 0 45px;
    background: #17284f;
    border-radius: 10px;
    color: white;
    display: grid;
    place-items: center;
    font-size: 19px;
    font-weight: 700;
}

.role-detail-summary strong {
    color: #17284f;
    font-size: 15px;
    overflow-wrap: anywhere;
}

.role-detail-summary small {
    color: #69798d;
    display: block;
    margin-top: 4px;
    font-size: 11px;
}

.role-detail-users {
    display: grid;
    gap: 0;
}

.role-detail-user {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 12px 0;
    border-bottom: 1px solid #e9eef3;
}

.role-detail-user:last-child {
    border-bottom: 0;
}

.role-detail-user-icon {
    flex: 0 0 36px;
    width: 36px;
    height: 36px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: #eaf0fb;
    color: #213d72;
    font-size: 13px;
    font-weight: 700;
}

.role-detail-user-info {
    min-width: 0;
    flex: 1;
    display: grid;
    gap: 3px;
}

.role-detail-user-info strong {
    font-size: 12px;
    color: #243757;
    overflow-wrap: anywhere;
}

.role-detail-user-info small {
    font-size: 11px;
    color: #77859b;
    overflow-wrap: anywhere;
}

.role-detail-status {
    padding: 4px 8px;
    font-size: 10px;
    border-radius: 6px;
    background: #edf2f6;
    color: #64748b;
    white-space: nowrap;
}

.role-detail-status.active {
    color: #138878;
    background: #e6f7f1;
}

.role-detail-empty {
    text-align: center;
    color: #79879b;
    padding: 24px 10px;
    font-size: 13px;
}

.role-detail-counter {
    font-size: 11px;
    padding: 5px 9px;
    border-radius: 6px;
    background: #eef8f5;
    color: #0b8577;
    font-weight: 650;
}

.role-detail-permission-grid {
    display: grid;
    gap: 13px;
}

.role-detail-module {
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    overflow: hidden;
}

.role-detail-module-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 9px;
    padding: 12px 15px;
    background: #f8fafc;
    border-bottom: 1px solid #e7edf2;
    color: #17284f;
    font-size: 13px;
    font-weight: 650;
}

.role-detail-module-count {
    color: #687d91;
    font-size: 11px;
    font-weight: 500;
}

.role-detail-matrix-wrap {
    overflow: auto;
}

.role-detail-matrix {
    width: 100%;
    min-width: 490px;
    border-collapse: collapse;
    table-layout: fixed;
}

.role-detail-matrix th,
.role-detail-matrix td {
    text-align: center;
    padding: 11px 7px;
    border-bottom: 1px solid #edf1f5;
    font-size: 12px;
}

.role-detail-matrix thead th {
    background: #fdfefe;
    color: #65758a;
    text-transform: uppercase;
    letter-spacing: .04em;
    font-size: 10px;
}

.role-detail-matrix th:first-child {
    text-align: left;
    padding-left: 15px;
    width: 36%;
}

.role-detail-matrix tbody tr:last-child th,
.role-detail-matrix tbody tr:last-child td {
    border-bottom: 0;
}

.role-detail-matrix tbody th {
    font-weight: 600;
    color: #334155;
}

.role-detail-allowed {
    color: #109483;
    font-weight: 700;
}

.role-detail-denied {
    color: #c8d2dc;
}

/* Workflow section */
.role-workflow-section {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #e2e8f0;
}

.role-workflow-group {
    margin: 12px 0 18px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
}

.role-workflow-group-title {
    background: #f8fafc;
    padding: 10px 14px;
    font-size: 12px;
    font-weight: 700;
    color: #334155;
}

.role-workflow-row {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 52px;
    padding: 11px 14px;
    border-top: 1px solid #e2e8f0;
    cursor: pointer;
}

.role-workflow-row input[type="checkbox"] {
    position: static !important;
    opacity: 1 !important;
    appearance: auto !important;
    flex: 0 0 16px;
    width: 16px !important;
    height: 16px !important;
    margin: 0;
    accent-color: #0f9a7a;
}

.role-workflow-row input:disabled {
    cursor: not-allowed;
    opacity: .45 !important;
}

.role-workflow-text {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}

.role-workflow-text strong {
    font-size: 12px;
    font-weight: 600;
    color: #263346;
}

.role-workflow-text small {
    font-size: 11px;
    color: #64748b;
}

@media (max-width: 1280px) {
    .role-editor-grid {
        grid-template-columns: minmax(0, 1fr);
    }

    .role-preview-panel {
        position: static;
        max-height: none;
    }

    .role-preview-modules {
        max-height: 300px;
    }
}

@media (max-width: 900px) {
    .role-detail-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 650px) {
    .role-form-card-body {
        padding: 16px !important;
    }

    .role-editor-modes,
    .role-form-card-head,
    .role-detail-info-grid,
    .role-matrix-intro,
    .role-module-head,
    .role-form-footer {
        grid-template-columns: 1fr;
        flex-direction: column;
    }

    .role-form-card-head,
    .role-module-head,
    .role-form-footer {
        align-items: stretch;
    }

    .role-detail-field.wide {
        grid-column: auto;
    }

    .role-form-footer .btn {
        flex: 1;
        text-align: center;
        justify-content: center;
    }

    .role-matrix-toolbar {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .role-preview-panel {
        padding: 18px;
    }

    .role-scope-field {
        align-items: stretch;
    }

    .role-scope-select {
        width: 100%;
    }
}
</style>
