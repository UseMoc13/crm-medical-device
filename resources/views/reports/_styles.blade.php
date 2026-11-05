<style>
/* =====================================================
   REPORTS (prefix: rp-)
===================================================== */

/* ---------- Period filter ---------- */
.rp-filter { display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap; }
.rp-field label { display:block; margin-bottom:5px; color:#8a94a6; font-size:10px; letter-spacing:.3px; text-transform:uppercase; }
.rp-field input[type=date] { height:40px; min-width:150px; box-sizing:border-box; padding:0 12px; border:1px solid #d9dee8; border-radius:8px; background:#fff; color:#34415c; font-family:inherit; font-size:12px; }
.rp-field input:focus { outline:none; border-color:#2ba7a0; box-shadow:0 0 0 3px rgba(43,167,160,.08); }
.rp-btn { height:40px; display:inline-flex; align-items:center; justify-content:center; white-space:nowrap; text-decoration:none; }
.rp-presets { display:flex; align-items:center; gap:7px; flex-wrap:wrap; margin-top:14px; }
.rp-presets span { color:#8a94a6; font-size:11px; }
.rp-preset { padding:6px 11px; border:1px solid #e0e5ed; border-radius:999px; background:#fff; color:#46536a; font-size:11px; font-weight:600; text-decoration:none; }
.rp-preset:hover { background:#f5fbf8; border-color:#c6ded5; color:#15966f; }
.rp-preset.active { background:#eaf7f3; border-color:#c6ded5; color:#15966f; }

/* ---------- KPI ---------- */
.rp-section-title { margin:26px 0 12px; color:#17284f; font-size:14px; font-weight:700; }
.rp-section-title small { margin-left:8px; color:#9aa3b2; font-size:11px; font-weight:400; }
.rp-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin-bottom:6px; }
.rp-kpi { padding:16px 18px; border:1px solid #e6eaf0; border-radius:12px; background:#fff; }
.rp-kpi span { display:block; color:#8a94a6; font-size:11px; }
.rp-kpi strong { display:block; margin-top:6px; color:#17284f; font-size:20px; font-weight:700; overflow-wrap:anywhere; }
.rp-kpi small { display:block; margin-top:4px; color:#9aa3b2; font-size:10px; line-height:1.5; }
.rp-kpi.success strong { color:#16805f; }
.rp-kpi.danger strong { color:#c03939; }
.rp-kpi.info strong { color:#385b9a; }

/* ---------- Layout ---------- */
.rp-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; margin-bottom:18px; }
.rp-block { min-width:0; margin-bottom:18px; }
.rp-grid .rp-block { margin-bottom:0; }
.rp-block .card-head h3 { margin:0 0 5px; color:#17284f; font-size:15px; font-weight:700; }
.rp-block .card-head p { margin:0; color:#8a94a6; font-size:11px; line-height:1.6; }

/* ---------- Monthly chart ---------- */
.rp-legend { display:flex; align-items:center; gap:16px; margin-bottom:16px; color:#68758b; font-size:11px; }
.rp-legend i { display:inline-block; width:10px; height:10px; margin-right:6px; border-radius:3px; vertical-align:middle; }
.rp-legend .inv { background:#385b9a; }
.rp-legend .paid { background:#15966f; }
.rp-chart-wrap { overflow-x:auto; }
.rp-chart { display:flex; align-items:flex-end; gap:12px; height:230px; min-width:max-content; padding:6px 4px 0; border-bottom:1px solid #e5e9ef; }
.rp-month { display:flex; flex-direction:column; align-items:center; justify-content:flex-end; width:54px; height:100%; }
.rp-bars { display:flex; align-items:flex-end; justify-content:center; gap:4px; width:100%; height:100%; }
.rp-bar { width:20px; min-height:2px; border-radius:4px 4px 0 0; transition:opacity .15s ease; }
.rp-bar:hover { opacity:.75; }
.rp-bar.inv { background:#385b9a; }
.rp-bar.paid { background:#15966f; }
.rp-labels { display:flex; gap:12px; min-width:max-content; padding:8px 4px 0; }
.rp-labels span { width:54px; color:#8a94a6; font-size:10px; text-align:center; white-space:nowrap; }
.rp-peak { margin-top:10px; color:#9aa3b2; font-size:10px; }

/* ---------- Horizontal bars ---------- */
.rp-list { display:flex; flex-direction:column; gap:15px; }
.rp-row-head { display:flex; justify-content:space-between; align-items:baseline; gap:12px; margin-bottom:6px; }
.rp-row-label { color:#34415c; font-size:12px; font-weight:600; overflow-wrap:anywhere; }
.rp-row-sub { display:block; margin-top:2px; color:#9aa3b2; font-size:10px; font-weight:400; }
.rp-row-value { color:#17284f; font-size:12px; font-weight:700; white-space:nowrap; text-align:right; }
.rp-track { height:8px; border-radius:999px; background:#f0f3f7; overflow:hidden; }
.rp-fill { height:100%; min-width:2px; border-radius:999px; background:#2ba7a0; }
.rp-fill.blue { background:#385b9a; }
.rp-fill.amber { background:#d9a02b; }
.rp-fill.red { background:#c85b5b; }
.rp-fill.gray { background:#98a2b3; }
.rp-empty { padding:26px 10px; text-align:center; color:#9aa3b2; font-size:11px; line-height:1.7; }

.rp-minis { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-bottom:20px; }
.rp-mini { padding:12px 14px; border-radius:10px; background:#f6f8fb; }
.rp-mini span { display:block; color:#8a94a6; font-size:10px; }
.rp-mini strong { display:block; margin-top:4px; color:#17284f; font-size:16px; font-weight:700; }
.rp-sub-title { margin:18px 0 12px; color:#8a94a6; font-size:10px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; }
.rp-sub-title:first-child { margin-top:0; }

/* ---------- Responsive ---------- */
@media (max-width:1100px) {
    .rp-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .rp-grid { grid-template-columns:minmax(0,1fr); }
    .rp-grid .rp-block { margin-bottom:0; }
}
@media (max-width:700px) {
    .rp-filter { flex-direction:column; align-items:stretch; }
    .rp-field input[type=date] { width:100%; }
    .rp-minis { grid-template-columns:1fr; }
}

/* ---------- Print ---------- */
@media print {
    .sidebar, .sidebar-overlay, .topbar, .page-head .actions, .no-print { display:none !important; }
    .main { margin:0 !important; }
    .card { break-inside:avoid; }
}
</style>