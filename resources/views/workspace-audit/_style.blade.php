<style>
/* A7.4.12.1: one stylesheet for the read-only audit module. */
.wsa-card{margin-bottom:26px}.wsa-readonly{color:#19836e;background:#e8f7f3;border-radius:99px;padding:6px 10px;font-size:11px;font-weight:700}
.wsa-filters{display:grid;grid-template-columns:repeat(5,minmax(140px,1fr));gap:12px;align-items:end}
.wsa-filters label{font-size:11px;font-weight:700;color:#354762;display:grid;gap:7px}
.wsa-filters select,.wsa-filters input{width:100%;min-width:0;box-sizing:border-box;min-height:39px;border:1px solid #dce3ed;background:#fff;color:#17284f;border-radius:8px;padding:8px;font:inherit;font-size:12px}
.wsa-filter-actions{display:flex;align-items:center;gap:9px}.wsa-button{background:#178e7b;color:#fff;border:0;border-radius:8px;min-height:39px;padding:0 17px;font:600 12px inherit;cursor:pointer}.wsa-button:hover{background:#107363}.wsa-reset{font-size:12px;color:#64758b;text-decoration:none}
.wsa-table-wrap{overflow:auto;border-top:1px solid #e7edf4}.wsa-table{border-collapse:collapse;width:100%;min-width:920px;font-size:12px;text-align:left}
.wsa-table th{background:#f6f9fc;color:#526481;white-space:nowrap;text-transform:uppercase;letter-spacing:.04em;font-size:10px;font-weight:750;padding:13px 17px;border-bottom:1px solid #e8edf4}
.wsa-table td{padding:14px 17px;vertical-align:top;border-bottom:1px solid #ecf0f5;color:#344760}.wsa-table tr:hover td{background:#fbfdfd}.wsa-table strong{display:block;color:#17284f;font-size:12px}.wsa-table small{display:block;margin-top:5px;color:#8692a4;font-size:10px;overflow-wrap:anywhere;max-width:215px}
.wsa-event{display:inline-block;background:#eaf7f4;color:#126e61;padding:6px 9px;border-radius:6px;font-size:11px;font-weight:700;white-space:nowrap}
.wsa-table code{font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:10px;line-height:1.6;overflow-wrap:anywhere;white-space:normal;color:#405873}.wsa-nowrap{white-space:nowrap}
.wsa-empty{text-align:center;color:#72829c!important;padding:48px 20px!important}.wsa-pager{padding:16px}
@media(max-width:1150px){.wsa-filters{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:670px){.wsa-filters{grid-template-columns:1fr 1fr}.wsa-filter-actions{grid-column:1/-1}}@media(max-width:420px){.wsa-filters{grid-template-columns:1fr}}
</style>
