<style>
/* CRM A7.4.5: one module stylesheet. Service Tickets palette / component conventions. */
.org-overview{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin:0 0 18px}
.org-stat{display:flex;align-items:center;gap:17px;min-width:0;padding:21px 22px}
.org-stat-symbol{width:46px;height:46px;flex:0 0 46px;border-radius:11px;background:#eaf7f3;color:#167d70;display:grid;place-items:center;font-size:17px;font-weight:800}
.org-stat-symbol.indigo{background:#eef2ff;color:#5264a6}.org-stat-symbol.amber{background:#fff4e5;color:#b97828}
.org-stat span{display:block;color:#7d8797;font-size:11px;font-weight:600}.org-stat strong{display:block;color:#17284f;font-size:23px;line-height:1.5}.org-stat small{display:block;color:#99a3b4;font-size:10px}
.org-security-note{display:flex;gap:12px;padding:15px 18px;margin-bottom:19px;background:#f5f9fc;border:1px solid #dce8f1;border-radius:9px}
.org-security-icon{flex:0 0 25px;width:25px;height:25px;border-radius:50%;background:#e8eff9;display:grid;place-items:center;font-weight:800;color:#376087;font-size:13px}
.org-security-note strong{color:#17284f;font-size:12px}.org-security-note p{color:#677790;font-size:11px;line-height:1.7;margin:4px 0 0}
.org-section-tabs{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap}
.org-section-tabs a{display:inline-flex;align-items:center;height:37px;padding:0 15px;border:1px solid #d9dee8;border-radius:8px;background:#fff;font-size:12px;text-decoration:none;font-weight:600;color:#34415c}
.org-section-tabs a:hover{border-color:#2ba7a0;color:#167d70;background:#f3faf9}
.org-panel{margin-bottom:18px;scroll-margin-top:22px}.org-panel .card-head h3{color:#17284f;font-size:15px;margin:0 0 5px}.org-panel .card-head p{color:#8a94a6;font-size:11px;margin:0}.org-panel-meta{color:#64748b;font-size:11px;font-weight:700}
.org-help{border:1px solid #e6edf4;background:#f9fbfd;color:#63728a;border-radius:8px;padding:11px 14px;margin:0 0 16px;font-size:11px;line-height:1.7}.org-help strong{color:#34415c}
.org-table-wrap{width:100%;overflow:auto;border:1px solid #cbd5e1;border-radius:8px}
.org-table{width:100%;min-width:920px;border-collapse:collapse;table-layout:auto}
.org-table th{padding:13px 15px;background:#f3f5f9;text-align:left;color:#34415c;font-size:11px;font-weight:700;white-space:nowrap;border-right:1px solid #d5dce6;border-bottom:1px solid #cbd5e1}
.org-table td{padding:13px 15px;border-right:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;vertical-align:middle;color:#34415c;font-size:12px}
.org-table th:last-child,.org-table td:last-child{border-right:0;width:85px;text-align:center}.org-table tr:last-child td{border-bottom:0}.org-table tbody tr:hover{background:#fafcfe}
.org-identity{display:flex;align-items:center;gap:10px;min-width:190px}.org-identity>div{min-width:0}.org-identity strong{display:block;color:#17284f;font-size:12px;word-break:break-word}.org-identity small{display:block;max-width:260px;color:#8a94a6;font-size:10px;line-height:1.5;margin-top:4px;overflow-wrap:anywhere}
.org-avatar{width:35px;height:35px;flex:0 0 35px;border-radius:8px;display:grid;place-items:center;background:#eaf7f3;color:#167d70;font-size:12px;font-weight:800}.org-avatar.indigo{background:#eef2ff;color:#5264a6}
.org-select{height:40px;width:100%;min-width:210px;max-width:390px;border:1px solid #d9dee8;background:white;color:#34415c;border-radius:8px;padding:0 10px;font-size:12px}
.org-select:focus{outline:none;border-color:#2ba7a0;box-shadow:0 0 0 3px rgba(43,167,160,.08)}
.org-link-pill,.org-root-pill{display:inline-flex;max-width:290px;white-space:normal;word-break:break-word;gap:7px;align-items:center;background:#eaf7f3;color:#167d70;padding:6px 9px;border-radius:6px;font-size:10px;font-weight:700;line-height:1.6}
.org-link-pill span{color:#69958b}.org-root-pill{background:#f1f3f6;color:#68758b}
.org-save{min-height:36px;min-width:66px;justify-content:center;font-size:11px}.org-save:hover{border-color:#2ba7a0;color:#167d70}
.org-muted{display:block;font-size:10px;color:#8791a1;line-height:1.6;margin-top:4px}.org-role-name{display:block;font-size:11px;color:#34415c;font-weight:600}
.org-footer-note{font-size:11px;color:#8a94a6;line-height:1.7;margin:6px 0 20px}
.org-table .sr-only{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap}
@media(max-width:990px){.org-overview{grid-template-columns:1fr 1fr}.org-stat{padding:15px 16px}}
@media(max-width:650px){.org-overview{grid-template-columns:1fr}.org-security-note{padding:12px}.org-panel .card-head{flex-wrap:wrap}.org-stat strong{font-size:20px}}
</style>
