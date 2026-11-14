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
</style>
