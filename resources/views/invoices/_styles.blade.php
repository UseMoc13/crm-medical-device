<style>
/* =====================================================
   INVOICES (prefix: inv-)
===================================================== */

/* ---------- Summary ---------- */
.inv-summary { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin-bottom:18px; }
.inv-summary-card { display:block; padding:16px 18px; border:1px solid #e6eaf0; border-radius:12px; background:#fff; text-decoration:none; transition:border-color .18s ease, box-shadow .18s ease; }
a.inv-summary-card:hover { border-color:#2ba7a0; box-shadow:0 2px 10px rgba(23,40,79,.06); }
.inv-summary-card span { display:block; color:#8a94a6; font-size:11px; }
.inv-summary-card strong { display:block; margin-top:6px; color:#17284f; font-size:20px; font-weight:700; }
.inv-summary-card small { display:block; margin-top:4px; color:#9aa3b2; font-size:10px; }
.inv-summary-card.danger strong { color:#c03939; }
.inv-summary-card.success strong { color:#16805f; }

/* ---------- Filter ---------- */
.inv-filter-bar { display:flex; align-items:flex-end; gap:10px; flex-wrap:wrap; margin-bottom:18px; }
.inv-search { position:relative; flex:1 1 250px; min-width:220px; }
.inv-search input { width:100%; height:40px; box-sizing:border-box; padding:0 38px; border:1px solid #d9dee8; border-radius:8px; background:#fff; color:#17284f; font-size:13px; }
.inv-search-icon { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#7d8797; font-size:19px; pointer-events:none; }
.inv-search-clear { position:absolute; right:10px; top:50%; transform:translateY(-50%); width:22px; height:22px; display:flex; align-items:center; justify-content:center; border-radius:50%; color:#7d8797; text-decoration:none; font-size:18px; }
.inv-search-clear:hover { background:#edf1f5; color:#17284f; }
.inv-ffield label { display:block; margin-bottom:5px; color:#8a94a6; font-size:10px; letter-spacing:.3px; text-transform:uppercase; }
.inv-ffield select, .inv-ffield input[type=date] { height:40px; min-width:135px; box-sizing:border-box; padding:0 12px; border:1px solid #d9dee8; border-radius:8px; background:#fff; color:#34415c; font-family:inherit; font-size:12px; }
.inv-search input:focus, .inv-ffield select:focus, .inv-ffield input:focus { outline:none; border-color:#2ba7a0; box-shadow:0 0 0 3px rgba(43,167,160,.08); }
.inv-btn { height:40px; display:inline-flex; align-items:center; justify-content:center; white-space:nowrap; text-decoration:none; }
.inv-filter-summary { display:flex; align-items:center; gap:7px; flex-wrap:wrap; margin-bottom:15px; color:#7d8797; font-size:12px; }
.inv-chip { padding:5px 9px; border-radius:6px; background:#f1f5f7; color:#34415c; font-size:11px; }

/* ---------- Table ---------- */
.inv-table-wrap { width:100%; overflow-x:auto; border:1px solid #cbd5e1; border-radius:8px; }
.inv-table { width:100%; min-width:1100px; border-collapse:collapse; }
.inv-table thead th { padding:12px 13px; background:#f3f5f9; border:1px solid #cbd5e1; color:#596579; font-size:11px; font-weight:700; text-align:left; white-space:nowrap; }
.inv-table tbody td { padding:13px; border:1px solid #d5dce6; color:#34415c; font-size:12px; vertical-align:middle; }
.inv-table tbody tr:hover { background:#f8fafc; }
.inv-col-no { width:55px; text-align:center !important; }
.inv-right { text-align:right !important; }
.inv-link { color:#223a70; text-decoration:none; font-weight:700; font-size:13px; }
.inv-link:hover { color:#2ba7a0; }
.inv-sub { margin-top:4px; color:#7d8797; font-size:11px; line-height:1.5; }
.inv-footer { display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; margin-top:18px; color:#6b7280; font-size:13px; }
.inv-overdue-tag { display:inline-block; margin-top:5px; padding:3px 7px; border-radius:5px; background:#fdeaea; color:#c03939; font-size:10px; font-weight:700; }

/* ---------- Badges ---------- */
.inv-badge { display:inline-flex; align-items:center; padding:5px 9px; border-radius:6px; font-size:11px; font-weight:600; white-space:nowrap; background:#f0f1f4; color:#737d8d; }
.inv-status-draft { background:#f0f1f4; color:#737d8d; }
.inv-status-sent { background:#edf1fa; color:#385b9a; }
.inv-status-paid { background:#e8f5ef; color:#16805f; }
.inv-status-cancelled { background:#f6eded; color:#a15555; }
.inv-status-overdue { background:#fdeaea; color:#c03939; }

/* ---------- Row actions ---------- */
.inv-actions { display:flex; align-items:center; gap:6px; }
.inv-actions form { margin:0; }
.inv-icon-btn { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; padding:7px; box-sizing:border-box; border:1px solid #e0e5ed; border-radius:8px; background:#fff; cursor:pointer; text-decoration:none; }
.inv-icon-btn svg { width:17px; height:17px; fill:none; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
.inv-icon-btn.view { color:#2563eb; }
.inv-icon-btn.edit { color:#d97706; }
.inv-icon-btn.delete { color:#dc2626; }
.inv-icon-btn:hover { background:#f8fafc; }

/* ---------- Form ---------- */
.inv-card, .inv-card .card-body, .inv-form-layout, .inv-form-main { overflow:visible !important; }
.inv-form-layout { display:grid; grid-template-columns:minmax(0,1.6fr) minmax(280px,.65fr); gap:28px; align-items:start; width:100%; }
.inv-form-main { min-width:0; }
.inv-section { margin:25px 0 18px; padding-top:20px; border-top:1px solid #edf0f5; }
.inv-section.first { margin-top:0; padding-top:0; border-top:0; }
.inv-section h4 { margin:0 0 4px; color:#17284f; font-size:13px; font-weight:700; }
.inv-section p { margin:0; color:#8a94a6; font-size:11px; line-height:1.5; }
.inv-two { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:0 18px; }
.inv-field { margin-bottom:19px; }
.inv-field label { display:block; margin-bottom:7px; color:#17284f; font-size:13px; font-weight:600; }
.inv-field .required { color:#c94a4a; }
.inv-field input, .inv-field select, .inv-field textarea { display:block; width:100%; box-sizing:border-box; padding:11px 12px; border:1px solid #d9dee8; border-radius:8px; background:#fff; color:#17284f; font-family:inherit; font-size:13px; }
.inv-field input, .inv-field select { height:42px; }
.inv-field textarea { min-height:90px; resize:vertical; line-height:1.6; }
.inv-field input:focus, .inv-field select:focus, .inv-field textarea:focus { outline:none; border-color:#2ba7a0; box-shadow:0 0 0 3px rgba(43,167,160,.08); }
.inv-field input[readonly] { background:#f6f8fb; color:#68758b; }
.inv-hint, .inv-error { display:block; margin-top:6px; font-size:11px; line-height:1.5; }
.inv-hint { color:#8a94a6; }
.inv-error { color:#c94a4a; }
.inv-form-actions { display:flex; justify-content:flex-end; align-items:center; gap:8px; padding-top:16px; margin-top:8px; border-top:1px solid #edf0f5; }

/* ---------- Item rows ---------- */
.inv-items-wrap { overflow-x:auto; border:1px solid #e6eaf0; border-radius:10px; }
.inv-items { width:100%; min-width:680px; border-collapse:collapse; }
.inv-items th { padding:10px 10px; background:#f8f9fc; border-bottom:1px solid #edf0f5; color:#8a94a6; font-size:10px; font-weight:700; text-align:left; text-transform:uppercase; letter-spacing:.3px; white-space:nowrap; }
.inv-items td { padding:8px 10px; border-bottom:1px solid #edf0f5; vertical-align:middle; }
.inv-items tbody tr:last-child td { border-bottom:0; }
.inv-items select, .inv-items input { width:100%; height:38px; box-sizing:border-box; padding:0 9px; border:1px solid #d9dee8; border-radius:7px; background:#fff; color:#17284f; font-family:inherit; font-size:12px; }
.inv-items select:focus, .inv-items input:focus { outline:none; border-color:#2ba7a0; box-shadow:0 0 0 3px rgba(43,167,160,.08); }
.inv-items .c-product { min-width:230px; }
.inv-items .c-qty { width:80px; }
.inv-items .c-price { width:130px; }
.inv-items .c-disc { width:110px; }
.inv-items .c-total { width:130px; text-align:right; color:#17284f; font-size:12px; font-weight:700; white-space:nowrap; }
.inv-items .c-remove { width:46px; text-align:center; }
.inv-remove { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; padding:0; border:1px solid #e0e5ed; border-radius:7px; background:#fff; color:#dc2626; font-size:18px; line-height:1; cursor:pointer; }
.inv-remove:hover { background:#fdf2f2; }
.inv-add-row { display:inline-flex; align-items:center; justify-content:center; margin-top:12px; min-height:34px; padding:0 14px; border:1px dashed #b7d9cc; border-radius:8px; background:#f5fbf8; color:#15966f; font-family:inherit; font-size:12px; font-weight:600; cursor:pointer; }
.inv-add-row:hover { background:#eaf7f3; }
.inv-items-empty { padding:24px; text-align:center; color:#9aa3b2; font-size:12px; }

/* ---------- Totals panel ---------- */
.inv-panel { position:sticky; top:92px; padding:24px; box-sizing:border-box; border:1px solid #e6eaf0; border-radius:12px; background:#fafbfd; }
.inv-panel-label { margin-bottom:18px; color:#8a94a6; font-size:10px; font-weight:700; letter-spacing:1.2px; }
.inv-panel-number { color:#17284f; font-size:17px; font-weight:700; overflow-wrap:anywhere; }
.inv-panel-caption { margin-top:5px; color:#7d8797; font-size:11px; }
.inv-panel-divider { height:1px; margin:19px 0; background:#e5e9ef; }
.inv-sum-row { display:flex; justify-content:space-between; gap:12px; margin-bottom:12px; color:#596579; font-size:12px; }
.inv-sum-row strong { color:#34415c; font-weight:600; text-align:right; }
.inv-sum-total { display:flex; justify-content:space-between; align-items:baseline; gap:12px; padding-top:14px; border-top:1px solid #e5e9ef; }
.inv-sum-total span { color:#17284f; font-size:12px; font-weight:700; }
.inv-sum-total strong { color:#15966f; font-size:19px; font-weight:700; text-align:right; }
.inv-panel-note { margin-top:18px; padding:13px 14px; border-radius:8px; background:#f1f5f7; }
.inv-panel-note strong { display:block; margin-bottom:5px; color:#34415c; font-size:11px; }
.inv-panel-note p { margin:0; color:#8a94a6; font-size:10px; line-height:1.6; }

/* ---------- Show / document ---------- */
.inv-doc { padding:34px; }
.inv-doc-top { display:flex; justify-content:space-between; align-items:flex-start; gap:24px; flex-wrap:wrap; padding-bottom:24px; margin-bottom:26px; border-bottom:1px solid #edf0f5; }
.inv-doc-title { margin:0 0 6px; color:#17284f; font-size:24px; font-weight:700; letter-spacing:.5px; }
.inv-doc-number { color:#7d8797; font-size:13px; }
.inv-doc-meta { display:grid; grid-template-columns:repeat(2,auto); gap:8px 26px; text-align:left; }
.inv-doc-meta span { display:block; color:#8a94a6; font-size:10px; text-transform:uppercase; letter-spacing:.35px; }
.inv-doc-meta strong { display:block; margin-top:3px; color:#34415c; font-size:12px; font-weight:600; }
.inv-parties { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:24px; margin-bottom:28px; }
.inv-party-label { display:block; margin-bottom:8px; color:#8a94a6; font-size:10px; letter-spacing:.35px; text-transform:uppercase; }
.inv-party strong { display:block; margin-bottom:4px; color:#17284f; font-size:14px; }
.inv-party p { margin:0; color:#596579; font-size:12px; line-height:1.7; overflow-wrap:anywhere; }
.inv-doc-table-wrap { overflow-x:auto; border:1px solid #e6eaf0; border-radius:10px; }
.inv-doc-table { width:100%; min-width:640px; border-collapse:collapse; }
.inv-doc-table th { padding:11px 14px; background:#f8f9fc; border-bottom:1px solid #edf0f5; color:#8a94a6; font-size:10px; font-weight:700; text-align:left; text-transform:uppercase; letter-spacing:.3px; }
.inv-doc-table td { padding:13px 14px; border-bottom:1px solid #edf0f5; color:#46536a; font-size:12px; vertical-align:top; }
.inv-doc-table tbody tr:last-child td { border-bottom:0; }
.inv-totals { width:min(340px,100%); margin:22px 0 0 auto; }
.inv-totals .inv-sum-row { font-size:12px; }
.inv-notes { margin-top:26px; padding-top:20px; border-top:1px solid #edf0f5; }
.inv-notes p { margin:0; color:#596579; font-size:12px; line-height:1.8; white-space:pre-line; overflow-wrap:anywhere; }
.inv-status-actions { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.inv-status-actions form { margin:0; }
.inv-btn-sm { display:inline-flex; align-items:center; justify-content:center; min-height:34px; padding:0 14px; border:1px solid #d9dee8; border-radius:8px; background:#fff; color:#34415c; font-family:inherit; font-size:12px; font-weight:600; cursor:pointer; text-decoration:none; }
.inv-btn-sm:hover { background:#f8fafc; }
.inv-btn-sm.success { border-color:#15966f; background:#15966f; color:#fff; }
.inv-btn-sm.success:hover { background:#127f5d; }
.inv-btn-sm.danger { color:#c03939; border-color:#f0cccc; }
.inv-btn-sm.danger:hover { background:#fdf2f2; }
.inv-empty { padding:34px 20px; text-align:center; }
.inv-empty strong { display:block; margin-bottom:6px; color:#34415c; font-size:13px; }
.inv-empty span { color:#9aa3b2; font-size:11px; line-height:1.7; }

/* ---------- Responsive ---------- */
@media (max-width:1100px) {
    .inv-summary { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .inv-search { flex:1 1 100%; }
    .inv-form-layout { grid-template-columns:1fr; }
    .inv-panel { position:static; order:-1; }
}
@media (max-width:700px) {
    .inv-filter-bar { flex-direction:column; align-items:stretch; }
    .inv-ffield select, .inv-ffield input[type=date] { width:100%; }
    .inv-two, .inv-parties { grid-template-columns:1fr; }
    .inv-doc { padding:20px; }
    .inv-form-actions { flex-direction:column-reverse; align-items:stretch; }
    .inv-form-actions .btn { width:100%; justify-content:center; }
}

/* ---------- Print ---------- */
@media print {
    .sidebar, .sidebar-overlay, .topbar, .page-head .actions, .no-print, .alert { display:none !important; }
    .main { margin:0 !important; padding:0 !important; }
    .content { padding:0 !important; }
    .card { border:0 !important; box-shadow:none !important; }
    .inv-doc { padding:0; }
}
</style>