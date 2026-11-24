<style>
/* A7.9.5.1 — Branches: single module stylesheet; compatible with CRM card/button layout. */
.branch-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px}
.branch-stat{padding:19px 20px;background:#fff;border:1px solid #e3eaf2;border-radius:12px;box-shadow:0 4px 15px rgba(22,40,79,.035);display:flex;flex-direction:column;gap:5px}
.branch-stat span{font-size:12px;color:#62738b;font-weight:600}.branch-stat strong{font-size:28px;color:#17284f;line-height:1.2}.branch-stat small{font-size:11px;color:#8b9aaa}
.branch-directory .card-body,.branch-related-card .card-body{padding:20px}
.branch-filter{display:flex;flex-wrap:wrap;align-items:stretch;gap:10px;margin-bottom:19px}
.branch-search{flex:1 1 220px}.branch-filter select{min-width:137px}
.branch-filter input,.branch-filter select,.branch-field input,.branch-field select,.branch-field textarea{box-sizing:border-box;display:block;width:100%;min-height:42px;padding:10px 12px;border:1px solid #d8e2ed;border-radius:8px;background:#fff;color:#253550;font-family:inherit;font-size:13px;outline:none;transition:border-color .16s,box-shadow .16s}
.branch-filter input:focus,.branch-filter select:focus,.branch-field input:focus,.branch-field select:focus,.branch-field textarea:focus{border-color:#229a91;box-shadow:0 0 0 3px rgba(34,154,145,.13)}
.branch-sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.branch-table-wrap{width:100%;overflow-x:auto;border:1px solid #e5ebf2;border-radius:10px}
.branch-table{width:100%;min-width:760px;border-collapse:collapse;margin:0;background:#fff;font-size:13px}
.branch-table thead th{text-align:left;padding:13px 15px;border-bottom:1px solid #e5ebf2;background:#f8fafc;color:#68778d;font-size:11px;letter-spacing:.045em;text-transform:uppercase;white-space:nowrap;font-weight:700}
.branch-table tbody td{padding:14px 15px;border-bottom:1px solid #edf0f6;vertical-align:middle;color:#35445e}
.branch-table tbody tr:last-child td{border-bottom:0}.branch-table tbody tr:hover{background:#fcfefd}
.branch-identity{display:flex;align-items:center;gap:11px;min-width:155px}.branch-identity>div{min-width:0;display:flex;flex-direction:column;gap:4px}
.branch-mini-avatar{display:inline-grid;place-items:center;flex:none;width:37px;height:37px;border-radius:9px;background:#e7f5f2;color:#178778;font-weight:750;font-size:14px}
.branch-name{color:#1c3254;font-weight:700;text-decoration:none}.branch-name:hover,.branch-text-link:hover{text-decoration:underline;color:#168e84}
.branch-code{font-size:11px;font-weight:650;letter-spacing:.03em;color:#718098}
.branch-ellipsis{max-width:235px;display:inline-block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;vertical-align:middle}
.branch-count{display:inline-flex;align-items:center;justify-content:center;min-width:29px;padding:4px 9px;background:#f1f5fa;color:#344766;border-radius:6px;font-size:11px;font-weight:700}
.branch-status{display:inline-flex;align-items:center;gap:6px;border-radius:7px;font-size:11px;font-weight:700;padding:6px 10px;white-space:nowrap;background:#f1f3f7;color:#6d7889}
.branch-status:before{content:"";height:6px;width:6px;border-radius:50%;background:currentColor}
.branch-status.active{background:#e8f8ef;color:#15804c}.branch-status.inactive{background:#f1f3f7;color:#737f8f}
.branch-actions{display:flex;align-items:center;gap:8px}.branch-actions a,.branch-delete-link{display:inline-block;padding:5px 7px;border:0;background:none;color:#24618d;font-family:inherit;font-size:12px;font-weight:650;text-decoration:none;cursor:pointer;border-radius:6px}
.branch-actions a:hover,.branch-delete-link:hover{background:#eff7fb}.branch-delete-link{color:#b83e4d}.branch-delete-link:hover{background:#fff0f2}
.branch-actions form{display:inline;margin:0}.branch-empty{padding:28px 16px;text-align:center;color:#718096}.branch-empty a{color:#178b82;font-weight:650}
.branch-pagination{display:flex;justify-content:center;margin-top:18px}
.branch-editor-layout{display:grid;grid-template-columns:minmax(0,1.8fr) minmax(260px,1fr);gap:28px;align-items:start}
.branch-section-title{font-weight:750;color:#17284f;margin:6px 0 19px;padding-bottom:12px;border-bottom:1px solid #e9edf4}
.branch-field-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 18px}
.branch-field{display:flex;flex-direction:column;gap:7px;min-width:0}.branch-field-full{grid-column:1/-1}.branch-field label{font-size:12px;color:#354862;font-weight:700}.branch-field small{color:#74859b;font-size:11px;line-height:1.5}.branch-required{color:#cb4652}
.branch-field textarea{min-height:110px;resize:vertical;line-height:1.5}.branch-field .is-invalid{border-color:#db5964}.branch-error{font-size:11px;color:#ba3440;font-weight:600}
.branch-validation{background:#fff1f2;border:1px solid #fecdd3;color:#9f2332;border-radius:9px;padding:12px 16px;margin-bottom:20px;font-size:12px}.branch-validation ul{margin:8px 0 0 18px;padding:0}
.branch-form-footer{display:flex;justify-content:flex-end;gap:9px;padding-top:23px;margin-top:22px;border-top:1px solid #edf0f4}
.branch-editor-preview{border:1px solid #dfe8ef;border-radius:11px;background:linear-gradient(145deg,#f7fbfc,#fff);padding:24px 20px;text-align:center;position:sticky;top:24px}
.branch-eyebrow{font-size:10px;color:#6a8597;font-weight:800;letter-spacing:.15em;text-align:left}
.branch-avatar{display:grid;place-items:center;width:68px;height:68px;border-radius:17px;background:#e2f3f1;color:#198c80;font-size:28px;font-weight:800;flex:none}
.branch-editor-preview .branch-avatar{margin:20px auto 14px}
.branch-editor-preview h3{font-size:18px;color:#17284f;overflow-wrap:anywhere;margin:0 0 6px}.branch-preview-code{font-size:12px;color:#62738b;font-weight:600;overflow-wrap:anywhere}
.branch-preview-separator{height:1px;background:#e3eaf2;margin:24px 0 8px}.branch-preview-row{display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:10px 0;font-size:12px}.branch-preview-row span{color:#75859a}.branch-preview-row strong{color:#253a59;text-align:right;overflow-wrap:anywhere}
.branch-preview-note{text-align:left;background:#edf6f6;border-radius:9px;padding:13px;font-size:11px;line-height:1.6;color:#526a76;margin:17px 0 0}
.branch-detail-grid{display:grid;grid-template-columns:minmax(0,1.8fr) minmax(270px,1fr);gap:18px;align-items:stretch}
.branch-profile{display:flex;gap:16px;align-items:center;border-bottom:1px solid #e9eef4;padding:4px 0 23px}.branch-profile>div{min-width:0;flex:1;display:flex;flex-direction:column;gap:5px}.branch-profile h2{margin:0;font-size:18px;color:#17284f;overflow-wrap:anywhere}
.branch-details{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:23px;margin-top:23px}.branch-details>div{display:flex;flex-direction:column;gap:7px;min-width:0}.branch-details small{color:#75849a;font-size:11px;font-weight:650}.branch-details strong{font-size:13px;color:#283b54;font-weight:650;overflow-wrap:anywhere}.branch-detail-wide{grid-column:1/-1}.branch-address{white-space:pre-line}
.branch-detail-metric{border-bottom:1px solid #edf0f5;padding:14px 0;display:flex;align-items:center;justify-content:space-between;gap:12px}.branch-detail-metric span{font-size:12px;color:#63738a}.branch-detail-metric strong{color:#1a3456;font-size:21px}
.branch-note{margin-top:18px;background:#f5f8fc;border-radius:8px;padding:13px;font-size:12px;color:#667993;line-height:1.6}
.branch-related-card{margin-top:20px}.branch-related-card .card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}.branch-text-link{color:#1c7691;font-size:12px;font-weight:650;text-decoration:none}
.branch-team-list{display:flex;flex-direction:column}.branch-team-row{display:flex;gap:12px;align-items:center;padding:12px 2px;border-bottom:1px solid #edf0f5}.branch-team-row:last-child{border-bottom:0}.branch-team-row>div{display:flex;flex:1;flex-direction:column;gap:4px}.branch-team-row small{font-size:11px;color:#74829a}
.branch-modal{display:none;position:fixed;inset:0;z-index:3000;align-items:center;justify-content:center;padding:18px}.branch-modal.open{display:flex}.branch-modal-overlay{position:absolute;inset:0;background:rgba(12,27,49,.5)}
.branch-modal-dialog{position:relative;width:min(100%,435px);background:#fff;border:1px solid #ebedf3;border-radius:13px;box-shadow:0 20px 70px rgba(0,0,0,.2);padding:27px;text-align:center}
.branch-modal-icon{display:grid;place-items:center;border-radius:50%;background:#fff2f1;color:#c64351;font-size:24px;font-weight:700;width:48px;height:48px;margin:0 auto 14px}
.branch-modal-dialog h3{margin:0 0 9px;color:#182f50}.branch-modal-dialog p{font-size:13px;color:#5f6f83;line-height:1.65}.branch-modal-warning{background:#fff8f3;padding:11px;border-radius:7px}.branch-modal-buttons{display:flex;justify-content:center;gap:10px;margin-top:22px}.branch-danger-button{background:#c5434e;color:#fff;border:1px solid #c5434e}.branch-danger-button:hover{background:#a92f3c}
@media(max-width:1020px){.branch-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.branch-editor-layout,.branch-detail-grid{grid-template-columns:1fr}.branch-editor-preview{position:static}}
@media(max-width:610px){.branch-stats,.branch-field-grid,.branch-details{grid-template-columns:1fr}.branch-profile{flex-wrap:wrap}.branch-profile .branch-status{margin-left:0}.branch-filter>.btn{flex:1 1 100px}.branch-form-footer{flex-wrap:wrap}.branch-form-footer .btn{flex:1 1 120px;text-align:center}.branch-directory .card-body,.branch-related-card .card-body{padding:13px}}
</style>
