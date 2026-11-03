<style>
/* =====================================================
   SERVICE HISTORY (prefix: sh-)
===================================================== */

/* ---------- Summary ---------- */
.sh-summary { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:14px; margin-bottom:18px; }
.sh-summary-card { padding:16px 18px; border:1px solid #e6eaf0; border-radius:12px; background:#fff; }
.sh-summary-card span { display:block; color:#8a94a6; font-size:11px; }
.sh-summary-card strong { display:block; margin-top:6px; color:#17284f; font-size:22px; font-weight:700; }

/* ---------- Filter ---------- */
.sh-filter-bar { display:flex; align-items:flex-end; gap:10px; flex-wrap:wrap; margin-bottom:18px; }
.sh-search { position:relative; flex:1 1 260px; min-width:220px; }
.sh-search input { width:100%; height:40px; box-sizing:border-box; padding:0 38px; border:1px solid #d9dee8; border-radius:8px; background:#fff; color:#17284f; font-size:13px; }
.sh-search-icon { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#7d8797; font-size:19px; pointer-events:none; }
.sh-search-clear { position:absolute; right:10px; top:50%; transform:translateY(-50%); width:22px; height:22px; display:flex; align-items:center; justify-content:center; border-radius:50%; color:#7d8797; text-decoration:none; font-size:18px; }
.sh-search-clear:hover { background:#edf1f5; color:#17284f; }
.sh-field label { display:block; margin-bottom:5px; color:#8a94a6; font-size:10px; letter-spacing:.3px; text-transform:uppercase; }
.sh-field select, .sh-field input[type=date] { height:40px; min-width:140px; box-sizing:border-box; padding:0 12px; border:1px solid #d9dee8; border-radius:8px; background:#fff; color:#34415c; font-family:inherit; font-size:12px; }
.sh-search input:focus, .sh-field select:focus, .sh-field input:focus { outline:none; border-color:#2ba7a0; box-shadow:0 0 0 3px rgba(43,167,160,.08); }
.sh-btn { height:40px; display:inline-flex; align-items:center; justify-content:center; white-space:nowrap; text-decoration:none; }
.sh-filter-summary { display:flex; align-items:center; gap:7px; flex-wrap:wrap; margin-bottom:15px; color:#7d8797; font-size:12px; }
.sh-chip { padding:5px 9px; border-radius:6px; background:#f1f5f7; color:#34415c; font-size:11px; }

/* ---------- Table ---------- */
.sh-table-wrap { width:100%; overflow-x:auto; border:1px solid #cbd5e1; border-radius:8px; }
.sh-table { width:100%; min-width:1150px; border-collapse:collapse; }
.sh-table thead th { padding:12px 13px; background:#f3f5f9; border:1px solid #cbd5e1; color:#596579; font-size:11px; font-weight:700; text-align:left; white-space:nowrap; }
.sh-table tbody td { padding:13px; border:1px solid #d5dce6; color:#34415c; font-size:12px; vertical-align:middle; }
.sh-table tbody tr:hover { background:#f8fafc; }
.sh-col-no { width:55px; text-align:center !important; }
.sh-link { color:#223a70; text-decoration:none; font-weight:700; font-size:13px; }
.sh-link:hover { color:#2ba7a0; }
.sh-sub { margin-top:4px; color:#7d8797; font-size:11px; line-height:1.5; }
.sh-clip { display:block; max-width:260px; overflow-wrap:anywhere; }
.sh-footer { display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; margin-top:18px; color:#6b7280; font-size:13px; }

/* ---------- Badges ---------- */
.sh-badge { display:inline-flex; align-items:center; padding:5px 9px; border-radius:6px; font-size:11px; font-weight:600; white-space:nowrap; background:#f0f1f4; color:#737d8d; }
.sh-type-ticket { background:#edf1fa; color:#385b9a; }
.sh-type-maintenance { background:#eaf7f3; color:#167d70; }
.sh-outcome-open { background:#edf1fa; color:#385b9a; }
.sh-outcome-in-progress { background:#fff5d9; color:#946200; }
.sh-outcome-on-hold { background:#fff0df; color:#b97828; }
.sh-outcome-resolved, .sh-outcome-closed, .sh-outcome-passed { background:#e8f5ef; color:#16805f; }
.sh-outcome-passed-with-notes { background:#edf1fa; color:#385b9a; }
.sh-outcome-requires-follow-up { background:#fff0df; color:#b97828; }
.sh-outcome-failed { background:#fdeaea; color:#c03939; }

/* ---------- Row actions ---------- */
.sh-actions { display:flex; align-items:center; gap:6px; }
.sh-icon-btn { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; padding:7px; box-sizing:border-box; border:1px solid #e0e5ed; border-radius:8px; background:#fff; text-decoration:none; }
.sh-icon-btn svg { width:17px; height:17px; fill:none; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
.sh-icon-btn.view { color:#2563eb; }
.sh-icon-btn.history { color:#15966f; }
.sh-icon-btn:hover { background:#f8fafc; }

/* ---------- Device history page ---------- */
.sh-grid { display:grid; grid-template-columns:minmax(0,1.45fr) minmax(280px,.85fr); gap:18px; margin-bottom:18px; }
.sh-block { min-width:0; margin-bottom:18px; }
.sh-grid .sh-block { margin-bottom:0; }
.sh-block .card-head h3 { margin:0 0 5px; color:#17284f; font-size:15px; font-weight:700; }
.sh-block .card-head p { margin:0; color:#8a94a6; font-size:11px; line-height:1.6; }
.sh-identity { display:flex; align-items:center; gap:13px; padding-bottom:20px; margin-bottom:21px; border-bottom:1px solid #edf0f5; }
.sh-identity-icon { display:flex; align-items:center; justify-content:center; flex:0 0 50px; width:50px; height:50px; border-radius:11px; background:#eaf7f3; color:#167d70; font-size:14px; font-weight:700; }
.sh-identity-body { flex:1; min-width:0; }
.sh-identity-body h2 { margin:0 0 5px; color:#17284f; font-size:17px; font-weight:700; overflow-wrap:anywhere; }
.sh-identity-body span { color:#7d8797; font-size:11px; }
.sh-info-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:20px 24px; }
.sh-info-label { display:block; margin-bottom:6px; color:#8a94a6; font-size:10px; letter-spacing:.35px; text-transform:uppercase; }
.sh-info-item strong { display:block; color:#34415c; font-size:12px; font-weight:600; line-height:1.6; overflow-wrap:anywhere; }
.sh-stat { padding:13px 0; }
.sh-stat + .sh-stat { border-top:1px solid #edf0f5; }
.sh-stat span { display:block; margin-bottom:4px; color:#8a94a6; font-size:10px; }
.sh-stat strong { display:block; color:#17284f; font-size:14px; font-weight:700; }
.sh-stat small { display:block; margin-top:3px; color:#9aa3b2; font-size:10px; }

.sh-upcoming { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 0; }
.sh-upcoming + .sh-upcoming { border-top:1px solid #edf0f5; }
.sh-upcoming strong { display:block; color:#34415c; font-size:12px; }
.sh-upcoming small { display:block; margin-top:3px; color:#8a94a6; font-size:11px; }

/* ---------- Timeline ---------- */
.sh-month { margin:6px 0 14px; color:#8a94a6; font-size:11px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; }
.sh-timeline { position:relative; margin:0 0 22px; padding-left:28px; }
.sh-timeline::before { content:''; position:absolute; left:8px; top:6px; bottom:6px; width:2px; background:#e8ebf1; }
.sh-event { position:relative; padding:0 0 22px; }
.sh-event:last-child { padding-bottom:0; }
.sh-dot { position:absolute; left:-28px; top:4px; width:18px; height:18px; box-sizing:border-box; border:3px solid #fff; border-radius:50%; background:#385b9a; box-shadow:0 0 0 2px #e8ebf1; }
.sh-event.maintenance .sh-dot { background:#15966f; }
.sh-event-card { padding:15px 17px; border:1px solid #e6eaf0; border-radius:10px; background:#fff; }
.sh-event-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.sh-event-ref { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.sh-event-title { margin-top:8px; color:#17284f; font-size:13px; font-weight:600; line-height:1.5; overflow-wrap:anywhere; }
.sh-event-meta { margin-top:5px; color:#8a94a6; font-size:11px; }
.sh-event-desc { margin:10px 0 0; color:#596579; font-size:12px; line-height:1.7; white-space:pre-line; overflow-wrap:anywhere; }
.sh-event-details { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px 20px; margin-top:12px; padding-top:12px; border-top:1px solid #edf0f5; }
.sh-event-details span { display:block; margin-bottom:3px; color:#8a94a6; font-size:10px; text-transform:uppercase; letter-spacing:.3px; }
.sh-event-details p { margin:0; color:#46536a; font-size:12px; line-height:1.6; white-space:pre-line; overflow-wrap:anywhere; }
.sh-event-foot { margin-top:12px; }
.sh-event-foot a { color:#15966f; font-size:11px; font-weight:600; text-decoration:none; }
.sh-event-foot a:hover { text-decoration:underline; }
.sh-empty { padding:34px 20px; text-align:center; }
.sh-empty strong { display:block; margin-bottom:6px; color:#34415c; font-size:13px; }
.sh-empty span { color:#9aa3b2; font-size:11px; line-height:1.7; }

/* ---------- Responsive ---------- */
@media (max-width:1200px) {
    .sh-summary { grid-template-columns:repeat(3,minmax(0,1fr)); }
    .sh-search { flex:1 1 100%; }
}
@media (max-width:950px) {
    .sh-grid { grid-template-columns:minmax(0,1fr); }
}
@media (max-width:700px) {
    .sh-summary { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .sh-filter-bar { flex-direction:column; align-items:stretch; }
    .sh-field select, .sh-field input[type=date] { width:100%; }
    .sh-info-grid, .sh-event-details { grid-template-columns:1fr; }
}
</style>
