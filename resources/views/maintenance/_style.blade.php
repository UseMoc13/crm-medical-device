<style>
/* =====================================================
   MAINTENANCE MODULE (prefix: mt-)
===================================================== */

/* ---------- Summary cards ---------- */
.mt-summary { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:14px; margin-bottom:18px; }
.mt-summary-card { display:block; padding:16px 18px; border:1px solid #e6eaf0; border-radius:12px; background:#fff; text-decoration:none; transition:border-color .18s ease, box-shadow .18s ease; }
.mt-summary-card:hover { border-color:#2ba7a0; box-shadow:0 2px 10px rgba(23,40,79,.06); }
.mt-summary-card span { display:block; color:#8a94a6; font-size:11px; }
.mt-summary-card strong { display:block; margin-top:6px; color:#17284f; font-size:22px; font-weight:700; }
.mt-summary-card.danger strong { color:#c03939; }

/* ---------- Filter bar ---------- */
.mt-filter-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:18px; }
.mt-search { position:relative; flex:1 1 270px; min-width:220px; }
.mt-search input { width:100%; height:40px; box-sizing:border-box; padding:0 38px; border:1px solid #d9dee8; border-radius:8px; background:#fff; color:#17284f; font-size:13px; }
.mt-search input:focus, .mt-select select:focus { outline:none; border-color:#2ba7a0; box-shadow:0 0 0 3px rgba(43,167,160,.08); }
.mt-search-icon { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#7d8797; font-size:19px; pointer-events:none; }
.mt-search-clear { position:absolute; right:10px; top:50%; transform:translateY(-50%); width:22px; height:22px; display:flex; align-items:center; justify-content:center; border-radius:50%; color:#7d8797; text-decoration:none; font-size:18px; }
.mt-search-clear:hover { background:#edf1f5; color:#17284f; }
.mt-select select { width:100%; min-width:140px; height:40px; padding:0 32px 0 12px; border:1px solid #d9dee8; border-radius:8px; background:#fff; color:#34415c; font-size:12px; cursor:pointer; }
.mt-filter-btn { height:40px; display:inline-flex; align-items:center; justify-content:center; white-space:nowrap; text-decoration:none; }
.mt-filter-summary { display:flex; align-items:center; gap:7px; flex-wrap:wrap; margin-bottom:15px; color:#7d8797; font-size:12px; }
.mt-chip { padding:5px 9px; border-radius:6px; background:#f1f5f7; color:#34415c; font-size:11px; }

/* ---------- Table ---------- */
.mt-table-wrap { width:100%; overflow-x:auto; border:1px solid #cbd5e1; border-radius:8px; }
.mt-table { width:100%; min-width:1200px; border-collapse:collapse; }
.mt-table thead th { padding:12px 13px; background:#f3f5f9; border:1px solid #cbd5e1; color:#596579; font-size:11px; font-weight:700; text-align:left; white-space:nowrap; }
.mt-table tbody td { padding:13px; border:1px solid #d5dce6; color:#34415c; font-size:12px; vertical-align:middle; }
.mt-table tbody tr:hover { background:#f8fafc; }
.mt-col-no { width:55px; text-align:center !important; }
.mt-link { color:#223a70; text-decoration:none; font-weight:700; font-size:13px; }
.mt-link:hover { color:#2ba7a0; }
.mt-sub { margin-top:4px; color:#7d8797; font-size:11px; line-height:1.5; }
.mt-unassigned { display:inline-flex; padding:4px 8px; border-radius:6px; background:#f2f4f7; color:#8791a1; font-size:11px; }
.mt-table-footer { display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; margin-top:18px; color:#6b7280; font-size:13px; }

/* ---------- Badges ---------- */
.mt-badge { display:inline-flex; align-items:center; padding:5px 9px; border-radius:6px; font-size:11px; font-weight:600; white-space:nowrap; background:#f0f1f4; color:#737d8d; }
.mt-status-scheduled { background:#edf1fa; color:#385b9a; }
.mt-status-in-progress { background:#fff5d9; color:#946200; }
.mt-status-completed { background:#e8f5ef; color:#16805f; }
.mt-status-cancelled { background:#f0f1f4; color:#737d8d; }
.mt-status-overdue { background:#fdeaea; color:#c03939; }
.mt-result-passed { background:#e8f5ef; color:#16805f; }
.mt-result-passed-with-notes { background:#edf1fa; color:#385b9a; }
.mt-result-requires-follow-up { background:#fff0df; color:#b97828; }
.mt-result-failed { background:#fdeaea; color:#c03939; }
.mt-type-badge { background:#eaf7f3; color:#167d70; }
.mt-overdue-tag { display:inline-block; margin-top:5px; padding:3px 7px; border-radius:5px; background:#fdeaea; color:#c03939; font-size:10px; font-weight:700; }

/* ---------- Row actions ---------- */
.mt-actions { display:flex; align-items:center; gap:6px; }
.mt-actions form { margin:0; }
.mt-icon-btn { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; padding:7px; box-sizing:border-box; border:1px solid #e0e5ed; border-radius:8px; background:#fff; cursor:pointer; text-decoration:none; }
.mt-icon-btn svg { width:17px; height:17px; fill:none; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
.mt-icon-btn.view { color:#2563eb; }
.mt-icon-btn.edit { color:#d97706; }
.mt-icon-btn.delete { color:#dc2626; }
.mt-icon-btn:hover { background:#f8fafc; }

/* ---------- Form layout (create / edit) ---------- */
.mt-card, .mt-card .card-body, .mt-form-layout, .mt-form-main { overflow:visible !important; }
.mt-form-layout { display:grid; grid-template-columns:minmax(0,1.25fr) minmax(280px,.75fr); gap:32px; align-items:start; width:100%; }
.mt-form-main { min-width:0; }
.mt-section { margin:25px 0 18px; padding-top:20px; border-top:1px solid #edf0f5; }
.mt-section.first { margin-top:0; padding-top:0; border-top:0; }
.mt-section h4 { margin:0 0 4px; color:#17284f; font-size:13px; font-weight:700; }
.mt-section p { margin:0; color:#8a94a6; font-size:11px; line-height:1.5; }
.mt-field { margin-bottom:19px; }
.mt-field label { display:block; margin-bottom:7px; color:#17284f; font-size:13px; font-weight:600; }
.mt-field .required { color:#c94a4a; }
.mt-field input, .mt-field select, .mt-field textarea { display:block; width:100%; box-sizing:border-box; padding:11px 12px; border:1px solid #d9dee8; border-radius:8px; background:#fff; color:#17284f; font-family:inherit; font-size:13px; }
.mt-field input, .mt-field select { height:42px; }
.mt-field textarea { min-height:100px; resize:vertical; line-height:1.6; }
.mt-field input:focus, .mt-field select:focus, .mt-field textarea:focus { outline:none; border-color:#2ba7a0; box-shadow:0 0 0 3px rgba(43,167,160,.08); }
.mt-hint, .mt-error { display:block; margin-top:6px; font-size:11px; line-height:1.5; }
.mt-hint { color:#8a94a6; }
.mt-error { color:#c94a4a; }
.mt-form-actions { display:flex; justify-content:flex-end; align-items:center; gap:8px; padding-top:16px; margin-top:8px; border-top:1px solid #edf0f5; }
.mt-check { display:flex; align-items:center; gap:9px; margin-bottom:19px; color:#34415c; font-size:12px; }
.mt-check input { width:16px; height:16px; margin:0; }

/* ---------- Preview panel ---------- */
.mt-preview { position:sticky; top:92px; padding:24px; box-sizing:border-box; border:1px solid #e6eaf0; border-radius:12px; background:#fafbfd; }
.mt-preview-label { margin-bottom:20px; color:#8a94a6; font-size:10px; font-weight:700; letter-spacing:1.2px; }
.mt-preview-icon { display:flex; align-items:center; justify-content:center; width:52px; height:52px; margin-bottom:16px; border-radius:12px; background:#eaf7f3; color:#167d70; font-size:14px; font-weight:700; }
.mt-preview-title { color:#17284f; font-size:17px; font-weight:700; }
.mt-preview-caption { margin-top:5px; color:#7d8797; font-size:11px; }
.mt-preview-divider { height:1px; margin:21px 0; background:#e5e9ef; }
.mt-preview-item { display:flex; flex-direction:column; gap:5px; margin-bottom:17px; }
.mt-preview-item span { color:#8a94a6; font-size:11px; }
.mt-preview-item strong { overflow-wrap:anywhere; color:#34415c; font-size:12px; font-weight:600; }
.mt-preview-note { padding:13px 14px; border-radius:8px; background:#f1f5f7; }
.mt-preview-note strong { display:block; margin-bottom:5px; color:#34415c; font-size:11px; }
.mt-preview-note p { margin:0; color:#8a94a6; font-size:10px; line-height:1.6; }

/* ---------- Show page ---------- */
.mt-grid { display:grid; grid-template-columns:minmax(0,1.45fr) minmax(280px,.85fr); gap:18px; margin-bottom:18px; }
.mt-block { min-width:0; margin-bottom:18px; }
.mt-grid .mt-block { margin-bottom:0; }
.mt-block .card-head h3 { margin:0 0 5px; color:#17284f; font-size:15px; font-weight:700; }
.mt-block .card-head p { margin:0; color:#8a94a6; font-size:11px; line-height:1.6; }
.mt-identity { display:flex; align-items:center; gap:13px; padding-bottom:20px; margin-bottom:21px; border-bottom:1px solid #edf0f5; }
.mt-identity-icon { display:flex; align-items:center; justify-content:center; flex:0 0 50px; width:50px; height:50px; border-radius:11px; background:#eaf7f3; color:#167d70; font-size:14px; font-weight:700; }
.mt-identity-body { flex:1; min-width:0; }
.mt-identity-body h2 { margin:0 0 5px; color:#17284f; font-size:17px; font-weight:700; overflow-wrap:anywhere; }
.mt-identity-body span { color:#7d8797; font-size:11px; }
.mt-info-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:20px 24px; }
.mt-info-item { min-width:0; }
.mt-info-label { display:block; margin-bottom:6px; color:#8a94a6; font-size:10px; letter-spacing:.35px; text-transform:uppercase; }
.mt-info-item strong { display:block; color:#34415c; font-size:12px; font-weight:600; line-height:1.6; overflow-wrap:anywhere; }
.mt-mono { font-family:Consolas, Monaco, monospace; font-size:11px !important; }
.mt-notes { padding-top:18px; margin-top:20px; border-top:1px solid #edf0f5; }
.mt-notes p { margin:0; color:#596579; font-size:12px; line-height:1.8; white-space:pre-line; overflow-wrap:anywhere; }
.mt-stat-list { display:flex; flex-direction:column; }
.mt-stat { padding:13px 0; }
.mt-stat + .mt-stat { border-top:1px solid #edf0f5; }
.mt-stat span { display:block; margin-bottom:4px; color:#8a94a6; font-size:10px; }
.mt-stat strong { display:block; color:#17284f; font-size:13px; font-weight:700; }
.mt-stat small { display:block; margin-top:3px; color:#9aa3b2; font-size:10px; }
.mt-empty { padding:30px 20px; text-align:center; }
.mt-empty h4 { margin:0 0 6px; color:#34415c; font-size:13px; }
.mt-empty p { max-width:480px; margin:0 auto; color:#9aa3b2; font-size:11px; line-height:1.7; }

/* ---------- Records timeline ---------- */
.mt-record { padding:18px 0; }
.mt-record + .mt-record { border-top:1px solid #edf0f5; }
.mt-record-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:14px; }
.mt-record-head strong { display:block; color:#17284f; font-size:13px; }
.mt-record-head small { display:block; margin-top:4px; color:#8a94a6; font-size:11px; }
.mt-record-side { display:flex; align-items:center; gap:8px; }
.mt-record-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
.mt-record-grid p { margin:0; color:#596579; font-size:12px; line-height:1.7; white-space:pre-line; overflow-wrap:anywhere; }
.mt-record-notes { margin-top:14px; padding:11px 13px; border-radius:8px; background:#f1f5f7; color:#596579; font-size:12px; line-height:1.7; white-space:pre-line; }

.mt-record-form { padding:20px; border:1px dashed #cfd6e2; border-radius:12px; background:#fafbfd; }
.mt-record-form .mt-two { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:0 18px; }
.mt-toggle { margin-bottom:18px; }
.mt-toggle > summary { display:inline-flex; list-style:none; cursor:pointer; }
.mt-toggle > summary::-webkit-details-marker { display:none; }
.mt-toggle[open] > summary { margin-bottom:16px; }

/* ---------- Responsive ---------- */
@media (max-width:1200px) {
    .mt-summary { grid-template-columns:repeat(3,minmax(0,1fr)); }
    .mt-search { flex:1 1 100%; }
    .mt-select { flex:1 1 150px; min-width:0; }
}
@media (max-width:950px) {
    .mt-grid { grid-template-columns:minmax(0,1fr); }
    .mt-form-layout { grid-template-columns:1fr; }
    .mt-preview { position:static; order:-1; }
    .mt-record-grid { grid-template-columns:1fr; }
}
@media (max-width:700px) {
    .mt-summary { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .mt-filter-bar { flex-direction:column; align-items:stretch; }
    .mt-info-grid, .mt-record-form .mt-two { grid-template-columns:1fr; }
    .mt-form-actions { flex-direction:column-reverse; align-items:stretch; }
    .mt-form-actions .btn { width:100%; justify-content:center; }
}

/* ---------- Records card header (Show All / + Add) ---------- */
.mt-records-card .card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
.mt-head-actions { display:flex; align-items:center; justify-content:flex-end; gap:8px; flex:0 0 auto; }
.mt-count { display:inline-flex; align-items:center; justify-content:center; min-height:32px; padding:0 11px; border-radius:999px; background:#f1f4f8; color:#687284; font-size:11px; font-weight:600; white-space:nowrap; }
.mt-btn-show-all,
.mt-btn-add { display:inline-flex; align-items:center; justify-content:center; box-sizing:border-box; min-height:32px; padding:0 14px; border-radius:7px; font-family:inherit; font-size:12px; font-weight:600; line-height:1; text-align:center; white-space:nowrap; text-decoration:none; cursor:pointer; transition:background .15s ease, border-color .15s ease; }
.mt-btn-show-all { border:1px solid #d8e8e2; background:#f5fbf8; color:#15966f; }
.mt-btn-show-all:hover { background:#eaf7f3; border-color:#c6ded5; }
.mt-btn-add { border:1px solid #15966f; background:#15966f; color:#fff; }
.mt-btn-add:hover { background:#127f5d; border-color:#127f5d; color:#fff; }

.mt-icon-btn.add { color:#15966f; }
.mt-icon-btn.add:hover { background:#eaf7f3; border-color:#c6ded5; }

/* ---------- Records table (card + modal) ---------- */
.mt-rec-wrap { width:100%; overflow-x:auto; }
.mt-rec-table { width:100%; min-width:900px; border-collapse:collapse; }
.mt-rec-table th { padding:12px 14px; background:#f8f9fc; border-bottom:1px solid #edf0f5; color:#8a94a6; font-size:10px; font-weight:700; text-align:left; text-transform:uppercase; letter-spacing:.3px; white-space:nowrap; }
.mt-rec-table td { padding:14px; border-bottom:1px solid #edf0f5; color:#46536a; font-size:12px; line-height:1.6; vertical-align:middle; }
.mt-rec-table tbody tr:last-child td { border-bottom:0; }
.mt-rec-table tbody tr:hover { background:#fafbfd; }
.mt-rec-table .mt-clip { display:block; max-width:220px; overflow-wrap:anywhere; }
.mt-rec-table .mt-col-actions { width:110px; text-align:center; }
.mt-rec-table th.mt-col-actions { text-align:center; }
.mt-rec-table .mt-actions { justify-content:center; }
.mt-rec-empty { padding:36px 20px; text-align:center; }
.mt-rec-empty strong { display:block; margin-bottom:6px; color:#34415c; font-size:13px; }
.mt-rec-empty span { color:#9aa3b2; font-size:11px; line-height:1.7; }
.mt-rec-pagination { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; padding:16px 14px 4px; color:#7d8797; font-size:12px; }

/* ---------- Modal ---------- */
.mt-modal { position:fixed; inset:0; z-index:9999; display:none; }
.mt-modal.open { display:flex; align-items:center; justify-content:center; }
.mt-modal-overlay { position:absolute; inset:0; background:rgba(15,23,42,.42); backdrop-filter:blur(2px); }
.mt-modal-dialog { position:relative; z-index:2; width:min(1180px, calc(100vw - 40px)); max-height:calc(100vh - 60px); display:flex; flex-direction:column; overflow:hidden; background:#fff; border:1px solid #e4e8ef; border-radius:12px; box-shadow:0 20px 60px rgba(23,40,79,.18); }
.mt-modal-header { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:20px 24px; border-bottom:1px solid #edf0f5; }
.mt-modal-title { color:#17284f; font-size:16px; font-weight:700; }
.mt-modal-subtitle { margin-top:4px; color:#8a94a6; font-size:11px; }
.mt-modal-close { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border:1px solid #e0e5ed; border-radius:8px; background:#fff; color:#68758b; font-size:20px; line-height:1; cursor:pointer; }
.mt-modal-close:hover { background:#f5f7fa; color:#17284f; }
.mt-modal-toolbar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding:16px 24px; border-bottom:1px solid #edf0f5; }
.mt-modal-search { position:relative; flex:1 1 260px; min-width:200px; }
.mt-modal-search input { width:100%; height:40px; box-sizing:border-box; padding:0 14px 0 38px; border:1px solid #d9dee8; border-radius:8px; background:#fff; color:#17284f; font-size:13px; }
.mt-modal-search input:focus, .mt-modal-filter select:focus { outline:none; border-color:#2ba7a0; box-shadow:0 0 0 3px rgba(43,167,160,.08); }
.mt-modal-search .mt-search-icon { left:13px; }
.mt-modal-filter select { height:40px; min-width:170px; padding:0 32px 0 12px; border:1px solid #d9dee8; border-radius:8px; background:#fff; color:#34415c; font-size:12px; cursor:pointer; }
.mt-modal-body { flex:1; min-height:0; overflow:auto; }
.mt-modal-empty { display:none; padding:36px 20px; text-align:center; color:#9aa3b2; font-size:12px; }
.mt-modal-empty.show { display:block; }
.mt-modal-footer { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 24px; border-top:1px solid #edf0f5; color:#7d8797; font-size:12px; }

@media (max-width:700px) {
    .mt-modal-dialog { width:100vw; max-height:100vh; border-radius:0; }
    .mt-modal-toolbar { flex-direction:column; align-items:stretch; }
    .mt-head-actions { flex-wrap:wrap; justify-content:flex-start; }
}

/* Maintenance status workflow — Show page (shared module stylesheet) */
.mt-workflow-card { margin-bottom:18px; }
.mt-workflow-card .card-head { gap:12px; align-items:flex-start; }
.mt-workflow-card .card-head p { font-size:12px; line-height:1.6; color:#718096; margin:5px 0 0; }
.mt-workflow-state { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.mt-workflow-steps { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
.mt-workflow-step { display:inline-flex; align-items:center; gap:6px; background:#f3f5f9; color:#64748b; border:1px solid #e2e8f0; border-radius:999px; padding:7px 12px; font-size:12px; font-weight:600; }
.mt-workflow-step.is-current { border-color:#89d4bb; background:#e5f7f0; color:#116b52; }
.mt-workflow-arrow { font-size:14px; color:#94a3b8; }
.mt-workflow-actions { display:flex; align-items:center; flex-wrap:wrap; gap:9px; }
.mt-workflow-hint { color:#64748b; font-size:12px; line-height:1.6; margin:12px 0 0; }
.mt-workflow-error { padding:12px 14px; background:#fff1f2; color:#9f1239; border:1px solid #fecdd3; border-radius:8px; margin-bottom:14px; font-size:12px; }
.mt-workflow-modal[hidden] { display:none!important; }
.mt-workflow-modal { position:fixed; inset:0; z-index:20000; display:flex; align-items:center; justify-content:center; padding:18px; background:rgba(14,28,54,.60); }
.mt-workflow-dialog { box-sizing:border-box; width:min(100%,440px); background:#fff; box-shadow:0 24px 70px rgba(18,32,62,.2); border-radius:14px; padding:27px; text-align:center; }
.mt-workflow-dialog h3 { font-size:18px; margin:0 0 9px; color:#17284f; }
.mt-workflow-dialog p { font-size:13px; line-height:1.65; color:#64748b; margin:0 0 22px; }
.mt-workflow-modal-actions { display:flex; gap:9px; justify-content:center; flex-wrap:wrap; }
.mt-workflow-danger { background:#c94a4a!important; color:#fff!important; border-color:#c94a4a!important; }
@media(max-width:700px) { .mt-workflow-card .card-head { flex-direction:column; } .mt-workflow-actions .btn { flex:1 1 auto; text-align:center; } }


/* ---------- Maintenance confirmation / protected actions ---------- */
.mt-confirm-open { overflow:hidden; }
.mt-confirm-modal { position:fixed; inset:0; z-index:12000; display:none; align-items:center; justify-content:center; padding:18px; }
.mt-confirm-modal.open { display:flex; }
.mt-confirm-backdrop { position:absolute; inset:0; background:rgba(15,23,42,.5); backdrop-filter:blur(2px); }
.mt-confirm-dialog { position:relative; z-index:1; width:min(430px,100%); padding:28px 26px 22px; border-radius:14px; border:1px solid #e4e8ef; background:#fff; box-shadow:0 22px 65px rgba(23,40,79,.22); text-align:center; outline:none; }
.mt-confirm-x { position:absolute; right:14px; top:10px; padding:4px 7px; border:0; background:none; color:#8b95a5; cursor:pointer; font-size:23px; line-height:1; }
.mt-confirm-x:hover { color:#17284f; }
.mt-confirm-icon { display:grid; place-items:center; width:58px; height:58px; margin:0 auto 15px; border-radius:50%; background:#fff0f0; color:#cb4149; font-size:30px; font-weight:700; }
.mt-confirm-icon-warning { background:#fff4e5; color:#c27a24; }
.mt-confirm-dialog h3 { margin:0 0 9px; color:#17284f; font-size:18px; line-height:1.45; }
.mt-confirm-dialog p { margin:0; color:#67758a; font-size:13px; line-height:1.7; white-space:normal; }
.mt-confirm-device { margin:16px 0 0; padding:11px 12px; border:1px solid #e5eaf2; border-radius:9px; background:#f8fafc; color:#334155; font-size:12px; overflow-wrap:anywhere; }
.mt-confirm-buttons { display:flex; justify-content:center; gap:9px; margin-top:22px; }
.mt-confirm-buttons .btn { min-width:110px; padding:10px 15px; font-weight:600; }
.mt-confirm-buttons .btn.danger { background:#d9534f; border-color:#d9534f; color:#fff; }
.mt-confirm-buttons .btn.danger:hover { background:#c7403b; }
.mt-confirm-buttons .btn[hidden] { display:none; }
@media (max-width:480px) { .mt-confirm-dialog { padding:25px 18px 18px; } .mt-confirm-buttons { flex-wrap:wrap; } }

.mt-followup-notice { padding:16px 18px; margin-bottom:18px; border:1px solid #b8dfd6; border-left:4px solid #15966f; border-radius:10px; background:#f1faf7; color:#34415c; }
.mt-followup-notice strong, .mt-followup-action strong { color:#17284f; font-size:14px; }
.mt-followup-notice p, .mt-followup-action p { margin:7px 0 0; font-size:12px; line-height:1.7; color:#596579; }
.mt-followup-notice a { color:#147c62; font-weight:700; text-decoration:underline; }
.mt-followup-action { display:flex; justify-content:space-between; align-items:center; gap:16px; }
.mt-followup-action .btn { flex-shrink:0; white-space:nowrap; }
@media(max-width:700px){.mt-followup-action{flex-direction:column;align-items:stretch}}

/* NEXT PREVENTIVE (v19) */
.mt-recurrence-links { display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; }
.mt-recurrence-links .btn { white-space:nowrap; }
@media (max-width:720px) { .mt-recurrence-links { justify-content:flex-start; } }

/* =====================================================
   MAINTENANCE v20 — DUE DATE MONITORING (read-only)
   Scoped to Maintenance; other module styles unchanged.
===================================================== */
.mt-due-overview { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin:0 0 18px; }
.mt-due-card { display:flex; flex-direction:column; gap:5px; min-width:0; padding:15px 18px; background:#fff; border:1px solid #e0e7ef; border-radius:12px; text-decoration:none; transition:box-shadow .15s,border-color .15s; }
.mt-due-card:hover,.mt-due-card.is-active { border-color:#2ba7a0; box-shadow:0 4px 14px rgba(23,40,79,.07); }
.mt-due-title { font-size:12px; font-weight:650; color:#596579; }
.mt-due-card strong { font-size:23px; line-height:1.3; color:#17284f; }
.mt-due-card small { font-size:11px; line-height:1.5; color:#8a94a6; }
.mt-due-card.soon strong { color:#946200; }
.mt-due-card.overdue strong { color:#c03939; }
.mt-due-tag { display:inline-flex; align-items:center; margin-top:5px; padding:3px 7px; border-radius:5px; font-size:10px; font-weight:700; }
.mt-due-tag.today { background:#e9f1fb; color:#285b96; }
.mt-due-tag.soon { background:#fff5dc; color:#906300; }
@media (max-width:760px) { .mt-due-overview { grid-template-columns:1fr; gap:9px; } }

</style>
