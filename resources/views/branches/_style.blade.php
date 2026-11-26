<style>
/* A7.9.5.2 — Branches UI refresh. Single module CSS, visually aligned with Roles. */
/* LIST — same card/filter/table vocabulary as resources/views/roles/index.blade.php */
.branch-directory .card-body,
.branch-related-card .card-body { padding:20px; }
.branch-card-head,.branch-related-head,.branch-form-card-head {display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap}
.branch-head-counts {display:flex;align-items:center;gap:14px;flex-wrap:wrap;font-size:11px;font-weight:600;color:#64748b}
.branch-head-counts>span {display:inline-flex;align-items:center;gap:6px}
.branch-count-dot {display:inline-block;width:7px;height:7px;border-radius:50%;background:#a1a9b8}
.branch-count-dot.active {background:#1d9a78}.branch-count-dot.inactive {background:#9ba6b7}
.branch-filter {display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:18px}
.branch-search {position:relative;flex:1 1 270px;min-width:210px}
.branch-search input {width:100%;height:40px;box-sizing:border-box;padding:0 36px;border:1px solid #d9dee8;border-radius:8px;background:#fff;color:#17284f;font-family:inherit;font-size:13px;outline:0;transition:border-color .18s,box-shadow .18s}
.branch-search input:focus,.branch-filter-select select:focus {border-color:#2ba7a0;box-shadow:0 0 0 3px rgba(43,167,160,.08);outline:0}
.branch-search-icon {position:absolute;top:50%;left:13px;transform:translateY(-50%);color:#7d8797;font-size:19px;pointer-events:none}
.branch-search-clear {position:absolute;right:10px;top:50%;transform:translateY(-50%);display:flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;color:#7d8797;font-size:18px;text-decoration:none}
.branch-search-clear:hover {color:#17284f;background:#edf1f5}
.branch-filter-select select {min-width:133px;height:40px;max-width:100%;padding:0 28px 0 12px;border:1px solid #d9dee8;border-radius:8px;background-color:#fff;color:#34415c;font-family:inherit;font-size:13px;cursor:pointer;transition:border-color .18s,box-shadow .18s}
.branch-filter-select select:hover {border-color:#b7c0cf}
.branch-filter-button,.branch-reset-button {min-height:40px;white-space:nowrap;display:inline-flex;align-items:center;justify-content:center}
.branch-filter-summary {display:flex;align-items:center;flex-wrap:wrap;gap:7px;margin-bottom:15px;font-size:12px;color:#7d8797}
.branch-filter-chip {display:inline-flex;padding:5px 9px;border-radius:6px;background:#f1f5f7;color:#34415c;font-size:11px}
.branch-sr-only {position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.table-wrap.branch-table-wrap {display:block;width:100%!important;max-width:100%;border:1px solid #e5eaf1!important;border-radius:10px;background:#fff;overflow-x:auto!important;overflow-y:hidden;box-sizing:border-box}
.table-wrap.branch-table-wrap table.branch-table {width:100%!important;min-width:880px;table-layout:auto;border:0!important;border-collapse:collapse!important;border-spacing:0!important;margin:0;background:#fff}
.table-wrap.branch-table-wrap .branch-table thead th {padding:12px 13px;border:0!important;border-bottom:1px solid #e5eaf1!important;background:#f8fafc!important;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.4px;text-align:left;white-space:nowrap}
.table-wrap.branch-table-wrap .branch-table thead th+th {border-left:1px solid #f0f3f8!important}
.table-wrap.branch-table-wrap .branch-table tbody td {height:auto!important;padding:12px 13px;border:0!important;border-bottom:1px solid #edf1f5!important;color:#34415c;font-size:12px;vertical-align:middle}
.table-wrap.branch-table-wrap .branch-table tbody td+td {border-left:1px solid #f5f6f9!important}
.table-wrap.branch-table-wrap .branch-table tbody tr:last-child td {border-bottom:0!important}
.branch-table tbody tr {transition:background-color .15s}
.branch-table tbody tr:hover {background:#f8fafc}
.branch-table-identity {display:flex;flex-direction:column;align-items:flex-start;gap:4px;min-width:130px}
.branch-name-link {color:#17284f;text-decoration:none;font-size:13px;overflow-wrap:anywhere}
.branch-name-link strong {font-weight:600}
.branch-name-link:hover,.branch-row-link:hover {color:#2ba7a0}
.branch-subtitle {color:#8a94a6;font-size:11px;font-weight:500;letter-spacing:.01em}
.branch-ellipsis {display:block;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#34415c}
.branch-user-badge {display:inline-flex;align-items:center;justify-content:center;width:auto!important;min-height:0!important;max-height:26px;padding:4px 9px;border-radius:6px;background:#eaf7f3;color:#167d70;font-size:11px;font-weight:600;line-height:1.25;white-space:nowrap}
.branch-user-badge.empty {background:#f1f3f6;color:#7d8797}
.branch-status {display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:6px;font-size:11px;font-weight:600;line-height:1.25;white-space:nowrap;background:#f1f3f6;color:#7d8797}
.branch-status:before {content:"";width:6px;height:6px;border-radius:50%;background:currentColor;flex:none}
.branch-status.active {background:#eaf7f3;color:#167d70}
.branch-status.inactive {background:#f1f3f6;color:#7d8797}
.branch-table thead th:last-child,.branch-table tbody td:last-child {min-width:125px;text-align:center;white-space:nowrap}
.branch-actions {display:flex;align-items:center;justify-content:center;gap:6px;white-space:nowrap}
.branch-actions form {display:inline-flex;margin:0}
.branch-actions .action-btn {box-sizing:border-box;width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;flex:none;padding:0;border:1px solid #dfe4eb;border-radius:7px;background:#fff;color:#4d5b70;font-family:inherit;font-size:13px;line-height:1;text-decoration:none;cursor:pointer;transition:background-color .18s,border-color .18s,color .18s}
.branch-actions .action-btn.view:hover {background:#eef7f7;border-color:#b8deda;color:#167d70}
.branch-actions .action-btn.edit:hover {background:#f2f5fa;border-color:#cbd5e4;color:#223a70}
.branch-actions .action-btn.delete:hover {background:#fff0f0;border-color:#e6b7b7;color:#c94a4a}
.branch-actions .action-btn:focus-visible,.branch-row-link:focus-visible,.branch-name-link:focus-visible {outline:2px solid #2ba7a0;outline-offset:2px}
.branch-table tbody td[colspan] {padding:0!important;text-align:center!important}
.branch-empty {padding:40px 18px;text-align:center;color:#718096;font-size:13px}
.branch-empty a {color:#167d70;text-decoration:none;font-weight:600}
.branch-pagination {display:flex;justify-content:center;max-width:100%;margin-top:18px}
/* FORM — matches Roles editor spacing/typography/preview treatment */
.branch-form-card-body {padding:26px!important}
.branch-required-note {color:#7c8798;font-size:11px;white-space:nowrap}
.branch-required-note span,.branch-required {color:#cf4857}
.branch-editor-layout {display:grid;grid-template-columns:minmax(0,1.32fr) minmax(300px,.68fr);gap:28px;align-items:start;width:100%}
.branch-editor-main {min-width:0}
.branch-form-section+.branch-form-section {border-top:1px solid #e2e8f0;margin-top:30px;padding-top:28px}
.branch-section-heading {margin-bottom:20px}
.branch-section-heading h4 {margin:0 0 6px;font-size:15px;font-weight:650;color:#17284f}
.branch-section-heading p {margin:0;font-size:13px;line-height:1.6;color:#64748b}
.branch-identity-fields {display:grid;gap:18px}
.branch-field {display:flex;flex-direction:column;gap:8px;min-width:0}
.branch-field label {color:#334155;font-size:13px;font-weight:600}
.branch-field small {font-size:11px;color:#78869a;line-height:1.5}
.branch-form-card .branch-control {display:block;box-sizing:border-box;width:100%;min-height:42px;padding:10px 12px;border:1px solid #d7e0e9;border-radius:8px;background:#fff;color:#223550;font-family:inherit;font-size:13px;outline:0;transition:border-color .15s,box-shadow .15s}
.branch-form-card textarea.branch-control {min-height:94px;resize:vertical;line-height:1.5}
.branch-form-card .branch-control:focus {border-color:#2ba7a0;box-shadow:0 0 0 3px rgba(43,167,160,.12)}
.branch-form-card .branch-control.is-invalid {border-color:#cf4857}
.branch-error {color:#b83f4d;font-size:11px;font-weight:600}
.branch-form-footer {display:flex;justify-content:flex-end;gap:10px;margin-top:26px;padding-top:24px;border-top:1px solid #e2e8f0}
.branch-editor-preview {position:sticky;top:20px;min-width:0;box-sizing:border-box;border:1px solid #dfe7ee;border-radius:10px;background:#fbfdff;padding:21px}
.branch-eyebrow {color:#71809a;font-size:10px;font-weight:750;letter-spacing:.12em}
.branch-preview-identity {display:flex;align-items:center;gap:12px;margin-top:22px;min-width:0}
.branch-avatar {display:inline-flex;align-items:center;justify-content:center;flex:none;width:54px;height:54px;border-radius:12px;background:#e7f5f2;color:#167d70;font-size:23px;font-weight:750}
.branch-preview-primary {min-width:0}
.branch-preview-primary h3 {margin:0 0 4px;color:#17284f;font-size:15px;line-height:1.4;overflow-wrap:anywhere}
.branch-preview-primary span {font-size:11px;color:#7d8797;overflow-wrap:anywhere}
.branch-preview-separator {height:1px;background:#e2e8f0;margin:20px 0}
.branch-preview-row {display:flex;align-items:baseline;justify-content:space-between;gap:10px;padding:9px 0;font-size:12px}
.branch-preview-row span {color:#64748b}
.branch-preview-row strong {color:#17284f;text-align:right;overflow-wrap:anywhere;font-weight:650}
.branch-preview-heading {color:#17284f;font-size:12px;font-weight:650;margin-bottom:7px}
.branch-preview-specification p {margin:0;color:#64748b;font-size:12px;line-height:1.6;white-space:pre-line;overflow-wrap:anywhere}
.branch-preview-note {background:#eef7f5;border:1px solid #dcefeb;padding:14px;border-radius:9px;margin-top:20px}
.branch-preview-note strong {font-size:11px;color:#167d70}
.branch-preview-note p {margin:6px 0 0;font-size:11px;line-height:1.6;color:#5e7480}
/* DETAILS — Roles detail two-card layout, clean list/cards */
.branch-detail-grid {display:grid;grid-template-columns:minmax(0,1.32fr) minmax(270px,.68fr);gap:20px;align-items:stretch}
.branch-detail-card .card-body {padding:23px}
.branch-detail-identity {display:flex;align-items:center;gap:13px;min-width:0;padding-bottom:22px;border-bottom:1px solid #e7edf3}
.branch-detail-primary {min-width:0;flex:1}
.branch-detail-primary h2 {margin:0 0 5px;font-size:17px;color:#17284f;font-weight:650;overflow-wrap:anywhere}
.branch-detail-primary span {color:#7d8797;font-size:11px}
.branch-details {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 14px;margin-top:22px}
.branch-detail-field {display:flex;flex-direction:column;gap:6px;min-width:0}
.branch-detail-field.wide {grid-column:1/-1}
.branch-detail-field>span {color:#7d8797;font-size:11px;font-weight:600}
.branch-detail-field strong {color:#34415c;font-size:13px;font-weight:600;overflow-wrap:anywhere}
.branch-address {white-space:pre-line;line-height:1.6}
.branch-summary-line {display:flex;justify-content:space-between;align-items:center;gap:10px;border-bottom:1px solid #edf1f5;padding:13px 0;color:#64748b;font-size:12px}
.branch-summary-line strong {font-size:20px;font-weight:650;color:#17284f}
.branch-summary-notice {background:#f4f7fa;border-radius:8px;color:#64748b;padding:14px;font-size:12px;line-height:1.65;margin-top:19px}
.branch-related-card {margin-top:20px}
.branch-user-table {min-width:760px!important}
.branch-user-table tbody td:last-child,.branch-user-table thead th:last-child {min-width:90px}
.branch-row-link {color:#167d70;text-decoration:none;font-size:12px;font-weight:600}
.branch-team-list {display:flex;flex-direction:column}
.branch-team-row {display:flex;align-items:center;gap:12px;padding:13px 2px;border-bottom:1px solid #edf1f5}
.branch-team-row:last-child {border-bottom:0}
.branch-team-icon {display:inline-flex;align-items:center;justify-content:center;flex:none;width:36px;height:36px;border-radius:9px;background:#eaf7f3;color:#167d70;font-size:13px;font-weight:650}
.branch-team-info {min-width:0;display:flex;flex:1;flex-direction:column;gap:3px}
.branch-team-info small {font-size:11px;color:#7d8797}
/* DELETE MODAL — Roles modal dimensions and placement */
.branch-modal {position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:16px}
.branch-modal.open {display:flex}
.branch-modal-overlay {position:absolute;inset:0;background:rgba(15,24,42,.45);backdrop-filter:blur(2px)}
.branch-modal-dialog {position:relative;box-sizing:border-box;width:min(420px,100%);padding:26px;border-radius:14px;background:#fff;box-shadow:0 20px 60px rgba(23,40,79,.20);animation:branchModalIn .18s ease}
@keyframes branchModalIn {from {opacity:0;transform:translateY(8px) scale(.98)} to {opacity:1;transform:translateY(0) scale(1)}}
.branch-modal-icon {display:flex;align-items:center;justify-content:center;width:44px;height:44px;margin-bottom:16px;border-radius:50%;background:#fff0f0;color:#c94a4a;font-size:21px;font-weight:700}
.branch-modal-dialog h3 {margin:0 0 8px;color:#17284f;font-size:18px}
.branch-modal-dialog p {margin:0 0 8px;color:#34415c;font-size:13px;line-height:1.6}
.branch-modal-dialog p strong {color:#17284f}
.branch-modal-warning {color:#8a94a6!important;font-size:12px!important}
.branch-modal-buttons {display:flex;justify-content:flex-end;gap:8px;margin-top:24px}
.branch-danger-button {background:#c94a4a!important;border-color:#c94a4a!important;color:#fff!important}
.branch-danger-button:hover {background:#b83f3f!important;border-color:#b83f3f!important}
@media(max-width:1100px) {
 .branch-search {flex:1 1 100%}
 .branch-filter-select {flex:1 1 140px}
 .branch-filter-select select {width:100%}
 .branch-detail-grid,.branch-editor-layout {grid-template-columns:1fr}
 .branch-editor-preview {position:static}
}
@media(max-width:700px) {
 .branch-filter {flex-direction:column;align-items:stretch}
 .branch-search,.branch-filter-select,.branch-filter-button,.branch-reset-button {width:100%;min-width:0}
 .branch-form-card-body,.branch-detail-card .card-body {padding:17px!important}
 .branch-form-footer {flex-wrap:wrap}
 .branch-form-footer .btn {flex:1 1 120px;text-align:center}
 .branch-details {grid-template-columns:1fr}
 .branch-detail-identity {flex-wrap:wrap}
 .branch-modal-dialog {padding:22px}
 .branch-modal-buttons {flex-direction:column-reverse}
 .branch-modal-buttons .btn {width:100%;justify-content:center}
 .branch-related-card .card-body,.branch-directory .card-body {padding:13px}
 .branch-card-head .branch-head-counts {width:100%}
}
</style>
