<style>
.activity-filter{display:flex;gap:12px;flex-wrap:wrap;align-items:end}.activity-filter label,.activity-form label{display:block;font-size:12px;color:#6b7588;margin-bottom:6px;font-weight:600}.activity-filter input,.activity-filter select,.activity-form input,.activity-form select,.activity-form textarea{width:100%;padding:10px 12px;border:1px solid #dde4eb;border-radius:8px;color:#23334f;background:#fff;font:inherit;min-height:39px}.activity-filter .field{flex:1 1 145px}.activity-filter .search{flex:2 1 240px}.activity-table{width:100%;border-collapse:collapse}.activity-table th{text-align:left;color:#8490a1;font-size:11px;font-weight:700;padding:14px 16px;background:#f9fafc;border-bottom:1px solid #e8ebf0}.activity-table td{padding:14px 16px;border-bottom:1px solid #edf0f4;color:#4b5972;font-size:12px;vertical-align:middle}.activity-table .subject{font-weight:700;color:#17284f;text-decoration:none}.activity-table .subject:hover{text-decoration:underline}.activity-type{display:inline-block;padding:5px 9px;border-radius:7px;background:#e8f6f2;color:#16826f;font-weight:700;font-size:11px}.activity-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.activity-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}.activity-form .span-all{grid-column:1/-1}.activity-details{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.activity-details .detail{padding:14px;border:1px solid #edf0f4;border-radius:9px}.activity-details small{display:block;color:#8590a0;margin-bottom:6px}.activity-details strong{color:#25334d;overflow-wrap:anywhere}.activity-confirm{border:0;border-radius:10px;padding:20px;width:min(420px,calc(100vw - 28px));box-shadow:0 24px 65px #17284f40}.activity-confirm::backdrop{background:#101b3199}.activity-confirm .actions{display:flex;justify-content:flex-end;gap:8px;margin-top:22px}.activity-empty{padding:38px;text-align:center;color:#8a94a6}.activity-table-wrap{overflow:auto}.activity-table{min-width:720px}@media(max-width:680px){.activity-form,.activity-details{grid-template-columns:1fr}}
/* A7.2.2 — Form and preview layout based on CRM card conventions */
.activity-editor-layout{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(300px,1fr);gap:20px;align-items:start}
.activity-editor-card{min-width:0;overflow:hidden}
.activity-editor-card .card-body{padding:22px}
.activity-editor-actions{border-top:1px solid #edf0f4;padding:17px 22px;display:flex;gap:10px;justify-content:flex-end}
.activity-form{gap:20px 17px}
.activity-form label{font-size:11px;font-weight:700;color:#617089;letter-spacing:.01em}
.activity-form input,.activity-form select,.activity-form textarea{min-height:42px;font-size:12px;border-radius:8px;transition:border-color .15s,box-shadow .15s}
.activity-form textarea{resize:vertical;min-height:125px}
.activity-form input:focus,.activity-form select:focus,.activity-form textarea:focus{border-color:#15966f;outline:none;box-shadow:0 0 0 3px #15966f1c}
.activity-form input[type=date],.activity-form input[type=time]{min-width:0;max-width:100%;color-scheme:light}
.activity-time-group{display:grid;grid-template-columns:1.1fr .9fr;gap:12px;min-width:0}
.activity-time-group>div{min-width:0}
.activity-required{color:#c44b53}
.activity-optional{color:#a3adbb;font-weight:500;margin-left:4px}
.activity-field-error{display:block;color:#ba3947;font-size:11px;margin-top:5px}
.activity-preview-panel{background:#fff;border:1px solid #e3e9f0;border-radius:12px;overflow:hidden;position:sticky;top:18px;box-shadow:0 2px 12px #17284f07}
.activity-preview-top{display:flex;align-items:center;gap:12px;padding:19px 20px;background:#f9fbfc;border-bottom:1px solid #edf0f4}
.activity-preview-top h3{font-size:14px;color:#17284f;margin:0 0 4px}
.activity-preview-top p{font-size:11px;color:#8a94a6;margin:0;line-height:1.5}
.activity-preview-icon{width:36px;height:36px;border-radius:9px;display:grid;place-items:center;background:#e8f6f2;color:#16826f;font-size:20px;flex-shrink:0}
.activity-preview-body{padding:22px 20px}
.activity-preview-overline{font-size:10px;color:#98a4b6;font-weight:800;letter-spacing:.075em;margin-bottom:11px}
.activity-preview-body h4{font-size:17px;line-height:1.35;color:#17284f;margin:0 0 12px;overflow-wrap:anywhere}
.activity-preview-separator{height:1px;background:#edf0f4;margin:18px 0}
.activity-preview-row{display:flex;justify-content:space-between;gap:14px;margin:11px 0;font-size:11px;line-height:1.5}
.activity-preview-row span{color:#8a94a6;flex-shrink:0}
.activity-preview-row strong{color:#35445e;font-weight:700;text-align:right;overflow-wrap:anywhere}
.activity-preview-description{font-size:12px;line-height:1.65;color:#54637b;white-space:pre-wrap;overflow-wrap:anywhere;margin:0}
.activity-preview-footer{background:#fbfcfe;border-top:1px solid #edf0f4;padding:12px 20px;color:#94a0ae;font-size:10px}
@media(max-width:1050px){.activity-editor-layout{grid-template-columns:1fr}.activity-preview-panel{position:static}}
@media(max-width:600px){.activity-form,.activity-time-group{grid-template-columns:1fr}.activity-editor-actions{flex-wrap:wrap}.activity-editor-actions .btn{flex:1;text-align:center}}

/* ============================================================
   A7.2.3 — Activities Index & Show, Service Ticket design parity
   Scoped selectors prevent collisions with other CRM modules.
   ============================================================ */
.activity-list-card .card-body{min-width:0}
.activity-list-filter{display:flex;align-items:end;gap:10px;flex-wrap:wrap;margin-bottom:18px}
.activity-list-search{position:relative;flex:1 1 255px;min-width:215px}
.activity-list-search input{width:100%;height:40px;box-sizing:border-box;border:1px solid #d9dee8;border-radius:8px;padding:0 38px;background:#fff;color:#17284f;font-size:13px}
.activity-list-search input:focus,.activity-list-filter select:focus,.activity-list-filter input[type=date]:focus{outline:none;border-color:#2ba7a0;box-shadow:0 0 0 3px rgba(43,167,160,.08)}
.activity-search-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#7d8797;font-size:19px;pointer-events:none}
.activity-search-clear{position:absolute;right:12px;top:50%;transform:translateY(-50%);text-decoration:none;font-size:20px;color:#7d8797}
.activity-filter-select{flex:0 1 155px}
.activity-filter-select select{height:40px;box-sizing:border-box;min-width:140px;width:100%;padding:0 12px;border:1px solid #d9dee8;border-radius:8px;background:#fff;color:#34415c;font-size:12px}
.activity-date-filter{display:flex;flex-direction:column;gap:4px;flex:0 1 160px;min-width:140px}
.activity-date-filter label{font-size:10px;font-weight:700;color:#8a94a6}
.activity-date-filter input{width:100%;box-sizing:border-box;height:40px;border:1px solid #d9dee8;border-radius:8px;padding:0 10px;background:#fff;color:#34415c;font-size:12px}
.activity-filter-button,.activity-reset-button{height:40px;min-width:68px;display:inline-flex;justify-content:center;align-items:center}
.activity-list-filter-summary{display:flex;flex-wrap:wrap;align-items:center;gap:7px;margin-bottom:15px;color:#7d8797;font-size:12px}
.activity-filter-chip{background:#f1f5f7;border-radius:6px;padding:5px 9px;color:#34415c;font-size:11px}
.activity-list-table-wrap{width:100%;overflow-x:auto;border:1px solid #cbd5e1;border-radius:8px}
.activity-list-table{width:100%;min-width:1100px;border-collapse:collapse;border-spacing:0}
.activity-list-table th{padding:12px 13px;border:1px solid #cbd5e1;background:#f3f5f9;text-align:left;color:#596579;font-size:10px;font-weight:700;white-space:nowrap}
.activity-list-table td{padding:13px;border:1px solid #d5dce6;color:#596579;font-size:12px;line-height:1.55;vertical-align:middle}
.activity-list-table tbody tr:hover{background:#f8fafc}
.activity-list-table .activity-col-no{width:55px;min-width:55px;text-align:center}
.activity-list-table .activity-col-title{min-width:185px}
.activity-list-table .activity-col-actions{min-width:132px;text-align:center}
.activity-list-subject{display:block;color:#223a70;font-weight:700;font-size:13px;line-height:1.4;text-decoration:none;overflow-wrap:anywhere}
.activity-list-subject:hover{color:#15966f;text-decoration:underline}
.activity-list-subtitle{margin-top:4px;font-size:11px;color:#8a94a6;line-height:1.5}
.activity-list-type{display:inline-flex;align-items:center;padding:5px 9px;border-radius:6px;background:#e8f5ef;color:#16805f;font-size:11px;font-weight:700;white-space:nowrap}
.activity-list-type-call{background:#edf1fa;color:#385b9a}
.activity-list-type-meeting{background:#e8f5ef;color:#16805f}
.activity-list-type-follow-up{background:#fff5d9;color:#946200}
.activity-list-type-demo{background:#eef2ff;color:#5264a6}
.activity-list-type-site-visit{background:#fff0df;color:#b97828}
.activity-list-type-task{background:#f1f3f6;color:#596579}
.activity-list-schedule strong{display:block;color:#34415c;font-weight:600;white-space:nowrap}
.activity-list-schedule small{display:block;margin-top:3px;color:#8a94a6;font-size:11px}
.activity-related-line{max-width:280px;overflow-wrap:anywhere;color:#34415c;font-size:11px;line-height:1.5}
.activity-related-line+.activity-related-line{margin-top:4px}
.activity-list-owner{font-size:12px;color:#34415c}
.activity-list-muted{color:#9aa3b2;font-size:11px}
.activity-list-actions{display:flex;gap:6px;justify-content:center;align-items:center;white-space:nowrap}
.activity-action-btn{display:inline-flex;justify-content:center;align-items:center;width:34px;height:34px;border:1px solid #e0e5ed;border-radius:8px;background:#fff;color:#68758b;cursor:pointer;padding:7px;box-sizing:border-box;text-decoration:none}
.activity-action-btn svg{display:block;width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.activity-action-btn.view{color:#2563eb}.activity-action-btn.view:hover{background:#edf4ff;border-color:#cbdcf9}
.activity-action-btn.edit{color:#d97706}.activity-action-btn.edit:hover{background:#fff6e8;border-color:#f5dfbb}
.activity-action-btn.delete{color:#dc2626}.activity-action-btn.delete:hover{background:#fff1f2;border-color:#fecdd3}
.activity-list-pagination{margin-top:18px}
.activity-list-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:18px;color:#6b7280;font-size:12px}
.activity-empty-state{text-align:center;padding:34px 16px}
.activity-empty-state h4,.activity-show-empty h4{margin:0 0 6px;font-size:13px;color:#34415c}
.activity-empty-state p,.activity-show-empty p{margin:0 0 14px;color:#9aa3b2;font-size:11px;line-height:1.6}
.activity-empty-icon{width:44px;height:44px;border-radius:10px;background:#f1f3f6;color:#8a94a6;display:grid;place-items:center;margin:0 auto 12px;font-size:17px;font-weight:700}
.activity-delete-dialog{box-sizing:border-box;width:min(100%,440px);max-width:calc(100vw - 28px);border:0;border-radius:14px;padding:28px;text-align:center;box-shadow:0 24px 65px #17284f40;color:#34415c}
.activity-delete-dialog::backdrop{background:rgba(16,27,49,.58)}
.activity-delete-symbol{width:52px;height:52px;display:grid;place-items:center;margin:0 auto 14px;border-radius:12px;background:#feecec;color:#bc3939;font-size:24px;font-weight:800}
.activity-delete-dialog h3{font-size:17px;color:#17284f;margin:0 0 8px}
.activity-delete-dialog p{font-size:12px;color:#667085;line-height:1.7;margin:0 0 22px;overflow-wrap:anywhere}
.activity-delete-buttons{display:flex;justify-content:center;gap:10px}
.activity-delete-confirm{background:#dc2626;color:#fff;border-color:#dc2626}
.activity-delete-confirm:hover{background:#b91c1c;border-color:#b91c1c}
.activity-show-overview{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(280px,.85fr);gap:18px;margin-bottom:18px;align-items:stretch}
.activity-show-overview>.card{min-width:0;display:flex;flex-direction:column;margin-bottom:0}
.activity-show-overview .card-body{flex:1;min-width:0}
.activity-show-identity{display:flex;gap:13px;align-items:center;padding-bottom:20px;margin-bottom:21px;border-bottom:1px solid #edf0f5}
.activity-show-identity-icon{display:grid;place-items:center;flex:0 0 50px;width:50px;height:50px;border-radius:11px;background:#eaf7f3;color:#167d70;font-size:15px;font-weight:700}
.activity-show-identity-content{flex:1;min-width:0}
.activity-show-identity-content h2{margin:0 0 5px;color:#17284f;font-size:17px;line-height:1.4;overflow-wrap:anywhere}
.activity-show-identity-content>span{color:#7d8797;font-size:11px}
.activity-show-info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 24px}
.activity-show-info{min-width:0}
.activity-show-info>span,.activity-show-description>span,.activity-show-related-top>div>span,.activity-show-related-meta>span{display:block;margin-bottom:6px;color:#8a94a6;font-size:10px;text-transform:uppercase;letter-spacing:.35px}
.activity-show-info>strong{display:block;color:#34415c;font-size:12px;font-weight:600;line-height:1.6;overflow-wrap:anywhere}
.activity-show-description{padding-top:18px;margin-top:20px;border-top:1px solid #edf0f5}
.activity-show-description p{margin:0;color:#596579;font-size:12px;line-height:1.8;white-space:pre-wrap;overflow-wrap:anywhere}
.activity-show-summary-list{display:flex;flex-direction:column}
.activity-show-summary-item{display:flex;align-items:center;gap:12px;padding:11px 0}
.activity-show-summary-item+.activity-show-summary-item{border-top:1px solid #edf0f5}
.activity-summary-icon{display:grid;place-items:center;flex:0 0 38px;width:38px;height:38px;border-radius:9px;font-size:12px;font-weight:700}
.activity-summary-icon.type,.activity-summary-icon.related{background:#eaf7f3;color:#167d70}
.activity-summary-icon.date{background:#eef2ff;color:#5264a6}
.activity-summary-icon.owner{background:#fff4e5;color:#c27a28}
.activity-show-summary-item>div:last-child{flex:1;min-width:0}
.activity-show-summary-item span{display:block;margin-bottom:4px;color:#8a94a6;font-size:10px}
.activity-show-summary-item strong{display:block;margin-bottom:3px;color:#17284f;font-size:12px;overflow-wrap:anywhere}
.activity-show-summary-item small{display:block;color:#9aa3b2;font-size:10px;line-height:1.5}
.activity-show-relations{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;align-items:stretch;margin-bottom:18px}
.activity-show-relations>.card{min-width:0;display:flex;flex-direction:column;margin-bottom:0}
.activity-show-relations>.card .card-body{flex:1;min-width:0}
.activity-show-related-top{display:flex;align-items:center;gap:13px;min-width:0;margin-bottom:20px}
.activity-show-related-top>div:last-child{min-width:0;flex:1}
.activity-show-related-top h4{margin:0;color:#34415c;font-size:13px;line-height:1.5;overflow-wrap:anywhere}
.activity-related-icon{width:46px;height:46px;flex:0 0 46px;display:grid;place-items:center;border-radius:10px;font-weight:700;font-size:14px}
.activity-related-icon.customer{color:#5264a6;background:#eef2ff}
.activity-related-icon.lead{color:#c27a28;background:#fff4e5}
.activity-related-icon.opportunity{color:#167d70;background:#eaf7f3}
.activity-show-related-meta{padding-top:14px;border-top:1px solid #edf0f5}
.activity-show-related-meta strong{display:block;color:#34415c;font-size:11px;font-weight:600;line-height:1.5;overflow-wrap:anywhere;word-break:break-word}
.activity-show-empty{text-align:center;padding:28px 8px}
.activity-show-registration-card{margin-bottom:18px;min-width:0}
.activity-show-uuid{font-family:Consolas,Monaco,monospace;font-size:11px!important;word-break:break-word}
@media(max-width:1100px){.activity-show-relations{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:950px){.activity-show-overview{grid-template-columns:1fr}}
@media(max-width:800px){.activity-list-search{flex-basis:100%}.activity-filter-select,.activity-date-filter{flex:1 1 155px}}
@media(max-width:650px){.activity-list-filter{align-items:stretch;flex-direction:column}.activity-list-search,.activity-filter-select,.activity-date-filter{width:100%;min-width:0;flex:auto}.activity-show-relations,.activity-show-info-grid{grid-template-columns:1fr}.activity-show-identity{align-items:flex-start;flex-wrap:wrap}.activity-show-identity-content{flex-basis:calc(100% - 70px)}.activity-delete-buttons{flex-wrap:wrap}}

/* A7.2.4 — Keep global delete confirmation as the only confirmation UI */
.activity-list-delete-form { margin: 0; display: inline-flex; }
.activity-list-delete-form .activity-action-btn.delete { appearance: none; vertical-align: middle; }
.activity-list-delete-form .activity-action-btn.delete svg { width: 17px; height: 17px; fill: none !important; stroke: currentColor !important; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
.activity-link-hint { display: block; margin-top: 6px; color: #8993a4; font-size: 11px; line-height: 1.55; }
.activity-form select option[hidden] { display: none; }
</style>
