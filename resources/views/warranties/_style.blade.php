<style>
/* Warranty styles are scoped to this module to preserve layouts.app defaults. */
.warranty-filter-bar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:18px}.warranty-filter-field:first-child{flex:1 1 230px}.warranty-filter-field{min-width:150px}.warranty-filter-bar input,.warranty-filter-bar select{box-sizing:border-box;width:100%;height:40px;border:1px solid #d9dee8;border-radius:8px;padding:0 12px;background:#fff;color:#34415c;font-size:12px}.warranty-filter-bar .btn{min-height:40px}
.warranty-table-wrap{width:100%;overflow-x:auto;border:1px solid #e5e7eb;border-radius:8px}.warranty-table{width:100%;min-width:1080px;border-collapse:collapse;table-layout:auto}.warranty-table th{background:#f9fafb;border:1px solid #e5e7eb;padding:13px 14px;color:#374151;font-size:10px;font-weight:600;letter-spacing:.5px;text-align:left;text-transform:uppercase;white-space:nowrap}.warranty-table td{border:1px solid #e5e7eb;padding:13px 14px;color:#17284f;font-size:12px;vertical-align:middle}.warranty-table tr:hover td{background:#f8fafc}.warranty-table th:first-child,.warranty-table td:first-child{text-align:center;width:55px}.warranty-table th:last-child,.warranty-table td:last-child{text-align:center}.warranty-table .table-actions{display:flex;gap:7px;align-items:center;justify-content:center}.warranty-table .table-actions form{margin:0;display:inline-flex}.warranty-table .action-btn{box-sizing:border-box;display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;padding:7px;border:1px solid #e5e7eb;background:#fff;border-radius:7px;cursor:pointer;text-decoration:none}.warranty-table .action-btn svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}.warranty-table .action-btn.view{color:#2563eb}.warranty-table .action-btn.edit{color:#d97706}.warranty-table .action-btn.delete{color:#dc2626}.warranty-table-wrap+.table-footer{display:flex;flex-wrap:wrap;justify-content:space-between;gap:14px;align-items:center;margin-top:18px;font-size:12px;color:#64748b}
.warranty-editor-layout{display:grid;grid-template-columns:minmax(0,1.3fr) minmax(310px,.7fr);gap:22px;align-items:start}.warranty-editor-layout>.card{min-width:0}.warranty-required-note{font-size:12px;color:#64748b;white-space:nowrap}.warranty-editor-layout .required-mark{color:#dc2626}.warranty-section-heading{margin-bottom:22px}.warranty-section-heading h4{font-size:15px;color:#17284f;margin:0 0 6px}.warranty-section-heading p{font-size:12px;color:#64748b;margin:0}.warranty-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}.warranty-form-grid .form-group{display:flex;flex-direction:column;gap:8px;min-width:0}.warranty-form-grid .full-width{grid-column:1/-1}.warranty-form-grid label{font-size:13px;font-weight:600;color:#334155}.warranty-form-grid .form-control{box-sizing:border-box;width:100%;min-height:42px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:7px;background:#fff;color:#1e293b;font-family:inherit;font-size:13px}.warranty-form-grid textarea.form-control{line-height:1.6;resize:vertical}.warranty-form-grid .form-control:focus{outline:0;border-color:#2563eb;box-shadow:0 0 0 3px #2563eb1a}.warranty-form-grid .form-control[readonly]{background:#f8fafc;color:#64748b}.warranty-form-grid .is-invalid{border-color:#dc2626}.warranty-form-grid .field-error{font-size:12px;color:#dc2626}.warranty-form-grid .form-hint{font-size:11px;color:#64748b}.warranty-form-grid .auto-number-field{position:relative}.warranty-form-grid .auto-number-badge{position:absolute;right:10px;top:50%;transform:translateY(-50%);padding:4px 7px;border-radius:5px;background:#dbeafe;color:#1d4ed8;font-size:10px;font-weight:700}.warranty-form-actions{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-top:28px;padding-top:22px;border-top:1px solid #e2e8f0}
.warranty-preview-panel{position:sticky;top:88px;box-sizing:border-box;min-width:0;background:#fafbfd;border:1px solid #e3e8ef;border-radius:12px;padding:24px}.warranty-preview-eyebrow{font-size:10px;font-weight:700;letter-spacing:1px;color:#8190a3;margin-bottom:20px}.warranty-preview-title{display:flex;gap:12px;align-items:center;border-bottom:1px solid #e3e8ef;padding-bottom:20px}.warranty-preview-icon{width:42px;height:42px;border-radius:10px;background:#e5f5ef;color:#128165;display:grid;place-items:center;font-size:24px}.warranty-preview-title h3{font-size:16px;color:#17284f;margin:0 0 5px}.warranty-preview-title small{font-size:11px;color:#8792a5}.warranty-preview-line{display:flex;justify-content:space-between;gap:15px;padding:12px 0;border-bottom:1px solid #e8edf3;font-size:11px}.warranty-preview-line>span{color:#8390a3}.warranty-preview-line strong{font-size:12px;color:#17284f;text-align:right;overflow-wrap:anywhere}.warranty-preview-badge{display:inline-block;background:#eaf5f0;border-radius:6px;padding:5px 9px;color:#15815f;font-size:10px}.warranty-preview-notice{margin:18px 0 0;background:#edf4f3;color:#57746c;padding:12px;border-radius:8px;font-size:10px;line-height:1.6}
.warranty-detail-layout{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;align-items:stretch}.warranty-detail-layout>.card{min-width:0}.warranty-information-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:24px}.warranty-information-field{display:flex;flex-direction:column;align-items:flex-start;gap:8px;min-width:0}.warranty-information-field>span{color:#64748b;font-size:13px}.warranty-information-field strong{font-size:14px;color:#1e293b;overflow-wrap:anywhere}.warranty-terms{white-space:pre-line;color:#34415c;font-size:13px;line-height:1.7;margin:0}.warranty-detail-actions{display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;margin-top:20px}.warranty-detail-actions form{margin:0}
.warranty-confirm[hidden],.warranty-toast[hidden]{display:none!important}.warranty-confirm{position:fixed;inset:0;z-index:20000;display:flex;align-items:center;justify-content:center;padding:18px;background:#0e1c3694}.warranty-confirm-dialog{width:min(100%,440px);box-sizing:border-box;background:#fff;border-radius:16px;padding:29px;box-shadow:0 22px 70px #14203738;text-align:center}.warranty-confirm-symbol{display:grid;place-items:center;width:54px;height:54px;border-radius:15px;background:#fff1ef;color:#c33e46;font-size:24px;font-weight:800;margin:0 auto 15px}.warranty-confirm-dialog h3{font-size:19px;color:#17284f;margin:0 0 9px}.warranty-confirm-dialog p{font-size:13px;color:#667085;line-height:1.6;margin:0 0 22px}.warranty-confirm-actions{display:flex;justify-content:center;gap:9px;flex-wrap:wrap}.warranty-danger-btn{color:#fff!important;background:#c94a4a!important;border-color:#c94a4a!important}.warranty-toast{position:fixed;left:50%;top:18px;transform:translateX(-50%);z-index:25000;min-width:260px;max-width:min(90vw,560px);padding:13px 18px;border-radius:9px;box-shadow:0 12px 30px #0e1c3629;font-size:13px;font-weight:600;line-height:1.6;background:#ecfdf5;border:1px solid #86efac;color:#166534;text-align:center}.warranty-toast.danger{background:#fef2f2;border-color:#fca5a5;color:#991b1b}
@media(max-width:1050px){.warranty-editor-layout{grid-template-columns:1fr}.warranty-preview-panel{position:static}.warranty-detail-layout{grid-template-columns:1fr}}@media(max-width:700px){.warranty-form-grid,.warranty-information-grid{grid-template-columns:1fr}.warranty-form-grid .full-width{grid-column:auto}.warranty-filter-bar{flex-direction:column;align-items:stretch}.warranty-filter-field{width:100%}.warranty-filter-bar .btn{width:100%}}

/* Warranty index status pills - matches Delivery badges, scoped to Warranties */
.warranty-table .warranty-status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    max-width: 100%;
    padding: 5px 10px;
    border-radius: 20px;
    border: 1px solid transparent;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.4;
    white-space: nowrap;
}
.warranty-table .warranty-status-success { color: #166534; background: #dcfce7; border-color: #bbf7d0; }
.warranty-table .warranty-status-warning { color: #92400e; background: #fef3c7; border-color: #fde68a; }
.warranty-table .warranty-status-danger { color: #991b1b; background: #fee2e2; border-color: #fecaca; }
.warranty-table .warranty-status-info { color: #1d4ed8; background: #dbeafe; border-color: #bfdbfe; }
.warranty-table .warranty-status-secondary { color: #475569; background: #f1f5f9; border-color: #e2e8f0; }


/* Warranty Show v5 — harmonized with Delivery Details without changing other CRUD pages */
.warranty-show-card{margin-bottom:18px;min-width:0}
.warranty-show-card .card-head{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.warranty-show-card .card-head h3{margin:0 0 5px}
.warranty-show-card .card-head p{margin:0;color:#64748b;font-size:13px;line-height:1.6}
.warranty-show-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:24px}
.warranty-show-field{display:flex;flex-direction:column;align-items:flex-start;gap:8px;min-width:0}
.warranty-show-label{font-size:13px;color:var(--text-muted,#64748b)}
.warranty-show-value{font-size:14px;font-weight:600;color:var(--text-primary,#1e293b);overflow-wrap:anywhere;line-height:1.5}
.warranty-show-related{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;align-items:stretch}
.warranty-show-related>.card{min-width:0}
.warranty-show-related-fields{grid-template-columns:1fr;gap:22px}
.warranty-show-id{font-size:12px;word-break:break-all}
.warranty-show-terms{margin:0;white-space:pre-line;font-size:13px;color:#34415c;line-height:1.8;overflow-wrap:anywhere}
.warranty-show-bottom-actions{display:flex;justify-content:flex-end;align-items:center;gap:10px;flex-wrap:wrap;margin:20px 0}
.warranty-show-bottom-actions form{margin:0}
.warranty-coverage-panel{margin-top:24px;padding:18px 20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px}
.warranty-coverage-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px;font-size:12px;color:#17284f}
.warranty-coverage-heading span{color:#64748b;font-weight:500}
.warranty-coverage-track{height:9px;background:#e2e8f0;border-radius:99px;overflow:hidden}
.warranty-coverage-fill{height:100%;border-radius:99px;background:#158765;transition:width .2s ease}
.warranty-coverage-dates{display:flex;justify-content:space-between;gap:16px;margin-top:10px;color:#64748b;font-size:11px}
/* Dedicated badges: status no longer renders as bare bold text on Show */
.warranty-show-primary .warranty-status-badge{display:inline-flex;align-items:center;justify-content:center;max-width:100%;padding:5px 10px;border:1px solid transparent;border-radius:20px;font-size:11px;font-weight:600;line-height:1.4;white-space:nowrap}
.warranty-show-primary .warranty-status-success{color:#166534;background:#dcfce7;border-color:#bbf7d0}
.warranty-show-primary .warranty-status-warning{color:#92400e;background:#fef3c7;border-color:#fde68a}
.warranty-show-primary .warranty-status-danger{color:#991b1b;background:#fee2e2;border-color:#fecaca}
.warranty-show-primary .warranty-status-secondary{color:#475569;background:#f1f5f9;border-color:#e2e8f0}
@media(max-width:900px){.warranty-show-related{grid-template-columns:1fr}}
@media(max-width:700px){.warranty-show-grid{grid-template-columns:1fr;gap:20px}.warranty-show-bottom-actions{align-items:stretch}.warranty-show-card .card-head{align-items:flex-start}}

</style>
