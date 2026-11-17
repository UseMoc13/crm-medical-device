<style>
/* A7.3 TO-DO — one scoped stylesheet, consistent with Service Tickets / Activities. */
.todo-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:18px}.todo-stat{padding:20px 23px;min-width:0}.todo-stat>span{display:block;color:#7d8797;font-size:11px;font-weight:600}.todo-stat>strong{display:block;margin:7px 0;color:#17284f;font-size:25px;font-weight:700}.todo-stat>small{color:#8a94a6;font-size:11px}.todo-danger{color:#bd4148!important}
.todo-filter-bar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:18px}.todo-search{position:relative;flex:1 1 240px;min-width:200px}.todo-search>span{position:absolute;top:50%;left:13px;transform:translateY(-50%);color:#7d8797;font-size:20px}.todo-filter-bar input,.todo-filter-bar select{height:40px;box-sizing:border-box;border:1px solid #d9dee8;border-radius:8px;background:#fff;color:#34415c;font-size:12px;padding:0 12px;font-family:inherit}.todo-filter-bar input{width:100%;padding-left:37px}.todo-filter-bar select{min-width:127px;max-width:190px}.todo-filter-bar .btn{min-height:40px}.todo-filter-bar :is(input,select):focus,.todo-field :is(input,select,textarea):focus{outline:none;border-color:#2ba7a0;box-shadow:0 0 0 3px #2ba7a015}.todo-active-filters{display:flex;gap:7px;align-items:center;flex-wrap:wrap;margin-bottom:15px;font-size:12px;color:#7d8797}.todo-active-filters span{padding:5px 9px;background:#f1f5f7;border-radius:6px;color:#34415c;font-size:11px}
.todo-table-wrap{width:100%;overflow-x:auto;border:1px solid #cbd5e1;border-radius:8px}.todo-table{width:100%;min-width:1030px;border-collapse:collapse;color:#34415c;font-size:12px}.todo-table th,.todo-table td{padding:12px 13px;border:1px solid #d5dce6;text-align:left;vertical-align:middle}.todo-table th{background:#f3f5f9;color:#52617a;font-weight:700;font-size:11px}.todo-table tbody tr:hover{background:#f8fafc}.todo-table .todo-no{text-align:center;width:48px}.todo-actions-col{width:170px;text-align:center!important}.todo-title-link{color:#223a70;font-size:13px;font-weight:700;text-decoration:none}.todo-title-link:hover{color:#16856a}.todo-subtitle{display:block;color:#8a94a6;font-size:11px;margin-top:5px;line-height:1.5}.todo-tag{display:inline-flex;align-items:center;padding:5px 9px;border-radius:6px;font-size:11px;font-weight:700;white-space:nowrap;background:#eef1f6;color:#596579}.todo-tag-personal{background:#eef2ff;color:#5264a6}.todo-tag-work{background:#eaf7f3;color:#167d70}.todo-priority-low{background:#f0f1f4;color:#737d8d}.todo-priority-medium{background:#fff5d9;color:#946200}.todo-priority-high{background:#fff0df;color:#b97828}.todo-priority-urgent{background:#fdeaea;color:#c03939}.todo-status-todo{background:#edf1fa;color:#385b9a}.todo-status-in_progress{background:#fff5d9;color:#946200}.todo-status-completed{background:#e8f5ef;color:#16805f}.todo-status-cancelled{background:#f0f1f4;color:#737d8d}
.todo-table-actions{display:flex;gap:6px;align-items:center;justify-content:center}.todo-table-actions form{display:inline-flex;margin:0}.todo-action{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #e0e5ed;border-radius:8px;background:#fff;color:#68758b;cursor:pointer;text-decoration:none;padding:7px;font-size:16px}.todo-action svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}.todo-action.view{color:#2563eb}.todo-action.edit{color:#d97706}.todo-action.delete{color:#dc2626}.todo-action.complete{color:#16805f}.todo-action:hover{background:#f1f5f9}.todo-pagination{margin-top:18px}.todo-table-footer{margin-top:17px;color:#6b7280;font-size:12px}.todo-empty{text-align:center;padding:30px 18px}.todo-empty-icon{display:grid;place-items:center;width:46px;height:46px;margin:0 auto 11px;border-radius:10px;background:#f1f3f6;color:#8a94a6;font-size:22px}.todo-empty h4{color:#34415c;font-size:14px;margin:0 0 6px}.todo-empty p{color:#8a94a6;font-size:12px;margin:0 0 14px}.todo-empty.compact{padding:18px}
.todo-form-layout{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(280px,.75fr);gap:32px;align-items:start}.todo-form-main{min-width:0}.todo-section{margin:23px 0 17px;padding-top:18px;border-top:1px solid #edf0f5}.todo-section:first-child{margin-top:0;padding-top:0;border-top:0}.todo-section h4{font-size:13px;color:#17284f;margin:0 0 4px}.todo-section p{font-size:11px;color:#8a94a6;margin:0;line-height:1.6}.todo-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.todo-field{min-width:0}.todo-field.full{grid-column:1/-1}.todo-field label{display:block;font-size:12px;color:#17284f;font-weight:600;margin-bottom:7px}.todo-field input,.todo-field select,.todo-field textarea{width:100%;box-sizing:border-box;border:1px solid #d9dee8;border-radius:8px;background:#fff;color:#17284f;font:inherit;font-size:13px;padding:10px 12px}.todo-field input,.todo-field select{height:42px}.todo-field textarea{min-height:118px;resize:vertical;line-height:1.6}.todo-field small{display:block;color:#8a94a6;font-size:11px;line-height:1.5;margin-top:6px}.todo-field .todo-error{color:#bd4148}.todo-required{color:#bd4148}.todo-optional{color:#8a94a6;font-size:10px}.todo-form-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:24px;padding-top:18px;border-top:1px solid #edf0f5}.todo-preview-panel{position:sticky;top:92px;min-width:0;box-sizing:border-box;border:1px solid #e6eaf0;border-radius:12px;background:#fafbfd;padding:24px}.todo-preview-heading{color:#8a94a6;font-size:10px;font-weight:700;letter-spacing:1.2px}.todo-preview-icon{display:grid;place-items:center;background:#eaf7f3;color:#167d70;width:52px;height:52px;border-radius:12px;font-size:21px;margin:18px 0}.todo-preview-panel h3{margin:0;color:#17284f;font-size:17px;overflow-wrap:anywhere}.todo-preview-panel>p{color:#8a94a6;font-size:11px;margin:5px 0 20px}.todo-preview-line{display:flex;flex-direction:column;gap:4px;padding:11px 0;border-top:1px solid #e5e9ef}.todo-preview-line span{font-size:11px;color:#8a94a6}.todo-preview-line strong{font-size:12px;color:#34415c;overflow-wrap:anywhere}.todo-preview-note{background:#f1f5f7;border-radius:8px;padding:14px;margin-top:14px}.todo-preview-note strong{font-size:11px;color:#34415c}.todo-preview-note p{font-size:11px;color:#596579;white-space:pre-wrap;overflow-wrap:anywhere;line-height:1.6}
.todo-details-grid{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(280px,.85fr);gap:18px;margin-bottom:18px}.todo-details-grid .card{min-width:0}.todo-identity{display:flex;align-items:center;gap:13px;border-bottom:1px solid #edf0f5;padding-bottom:20px;margin-bottom:20px}.todo-identity-icon{display:grid;place-items:center;flex:0 0 50px;width:50px;height:50px;border-radius:11px;background:#eaf7f3;color:#167d70;font-weight:700;font-size:19px}.todo-identity>div:nth-child(2){flex:1;min-width:0}.todo-identity h2{margin:0 0 5px;color:#17284f;font-size:17px;overflow-wrap:anywhere}.todo-identity span:not(.todo-tag){font-size:11px;color:#8a94a6}.todo-info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 24px}.todo-info-grid>div{min-width:0}.todo-info-grid span,.todo-description>span{display:block;color:#8a94a6;font-size:10px;text-transform:uppercase;letter-spacing:.35px;margin-bottom:6px}.todo-info-grid strong{display:block;color:#34415c;font-size:12px;line-height:1.6;overflow-wrap:anywhere}.todo-description{border-top:1px solid #edf0f5;padding-top:18px;margin-top:20px}.todo-description p{color:#596579;font-size:12px;line-height:1.8;white-space:pre-wrap;overflow-wrap:anywhere}.todo-summary-item{display:flex;gap:12px;align-items:center;padding:11px 0}.todo-summary-item+.todo-summary-item{border-top:1px solid #edf0f5}.todo-summary-icon{width:38px;height:38px;flex:0 0 38px;display:grid;place-items:center;border-radius:9px;background:#eaf7f3;color:#167d70;font-weight:700}.todo-summary-item>div:last-child{min-width:0}.todo-summary-item span{display:block;color:#8a94a6;font-size:10px;margin-bottom:4px}.todo-summary-item strong{display:block;color:#17284f;font-size:12px;overflow-wrap:anywhere}.todo-summary-item small{display:block;color:#9aa3b2;font-size:10px;margin-top:4px}.todo-summary-card form{margin-top:15px}.todo-related-card{margin-bottom:18px}.todo-related{display:flex;align-items:center;gap:14px;min-width:0}.todo-related>div:nth-child(2){flex:1;min-width:0}.todo-related span{font-size:10px;color:#8a94a6}.todo-related h4{font-size:14px;color:#17284f;margin:4px 0}.todo-related p{font-size:11px;color:#8a94a6;margin:0}.todo-mono{font-family:Consolas,Monaco,monospace;font-size:11px!important}
@media(max-width:1100px){.todo-filter-bar .todo-search{flex:1 1 100%}.todo-filter-bar select{flex:1;max-width:none}.todo-details-grid{grid-template-columns:1fr}}@media(max-width:900px){.todo-form-layout{grid-template-columns:1fr}.todo-preview-panel{position:static}}@media(max-width:650px){.todo-stats{grid-template-columns:1fr}.todo-form-grid,.todo-info-grid{grid-template-columns:1fr}.todo-form-actions{flex-direction:column-reverse}.todo-form-actions .btn{width:100%;justify-content:center}.todo-identity{flex-wrap:wrap}.todo-filter-bar select,.todo-filter-bar .btn{width:100%;max-width:none}.todo-related{flex-wrap:wrap}}

/* A7.4.4: CRM-consistent task workspace, assignment cards, file submissions. */
.todo-page-head{margin-bottom:18px}
.todo-dashboard-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px;margin-bottom:19px}
.todo-dashboard-stat{display:flex;align-items:center;gap:15px;padding:19px 20px;min-width:0}
.todo-stat-icon{display:grid;place-items:center;flex:0 0 46px;width:46px;height:46px;border-radius:12px;font-size:19px;font-weight:800;background:#eaf7f3;color:#167d70}
.todo-stat-icon.upcoming{background:#eef2ff;color:#5264a6}
.todo-stat-icon.overdue{background:#fff0df;color:#b97828}
.todo-stat-icon.done{background:#e6f6ed;color:#16805f}
.todo-dashboard-stat>div:last-child{min-width:0}
.todo-dashboard-stat span{display:block;color:#7d8797;font-size:11px;font-weight:600}
.todo-dashboard-stat strong{display:block;color:#17284f;font-size:25px;line-height:1.3;margin:4px 0;font-weight:750}
.todo-dashboard-stat small{color:#95a0af;font-size:10px;line-height:1.5}
.todo-list-card-heading{display:flex;align-items:center;justify-content:space-between;gap:15px}
.todo-list-card-heading .todo-calendar-link{font-size:12px;white-space:nowrap}
.todo-view-tabs{display:flex;gap:5px;align-items:center;flex-wrap:wrap;overflow:auto;border-bottom:1px solid #e5eaf1;margin:-1px 0 19px;padding-bottom:10px}
.todo-view-tab{padding:9px 14px;color:#63738a;text-decoration:none;border-radius:8px;font-size:12px;font-weight:600;white-space:nowrap;transition:background .16s,color .16s}
.todo-view-tab.active{background:#e8f5ef;color:#167d70}
.todo-view-tab:hover{background:#f2f5f9;color:#17284f}
.todo-filter-bar .todo-search{flex:1 1 230px}
.todo-filter-bar select{min-width:110px;max-width:160px}
.todo-task-cell{min-width:185px;max-width:320px}
.todo-owner-line,.todo-date-caption{display:block;color:#8a94a6;font-size:10px;line-height:1.5;margin-top:4px}
.todo-date-value{display:block;color:#34415c;font-size:12px;font-weight:600;white-space:nowrap}
.todo-date-value.overdue{color:#c03939}
.todo-assignee-muted{color:#8a94a6;font-size:11px}
.todo-assignee-stack{display:flex;flex-direction:column;align-items:flex-start;gap:5px}
.todo-assignee-stack small{color:#8a94a6;font-size:10px}
.todo-avatar-group{display:flex;padding-left:4px}
.todo-avatar-group span{display:grid;place-items:center;background:#eaf7f3;color:#167d70;border:2px solid #fff;border-radius:50%;font-weight:750;font-size:10px;width:27px;height:27px;margin-left:-4px}
.todo-progress-top{display:flex;align-items:center;justify-content:space-between;gap:9px;font-size:10px;color:#6b7890;white-space:nowrap}
.todo-progress-top strong{color:#17284f;font-size:12px}
.todo-progress-track{height:5px;border-radius:99px;background:#eaf0f4;margin-top:7px;overflow:hidden;min-width:80px}
.todo-progress-track span{height:100%;background:#2ba7a0;display:block;border-radius:99px}
.todo-table{min-width:1270px}
.todo-table th,.todo-table td{padding:13px 12px}
.todo-table td{line-height:1.5}
.todo-actions-col{min-width:160px}
.todo-assignee-choices{display:grid;gap:9px;padding:12px;border:1px solid #e5eaf1;border-radius:10px;background:#f8fafc;max-height:330px;overflow:auto}
.todo-assignee-choice{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:10px 12px;border-radius:8px;border:1px solid #e5eaf1;background:#fff}
.todo-assignee-left{display:flex;align-items:center;gap:10px;min-width:0}
.todo-assignee-left>input,.todo-file-required input{accent-color:#168a82;width:16px;height:16px;flex:0 0 16px}
.todo-assignee-left label{display:flex;align-items:center;gap:9px;cursor:pointer;min-width:0}
.todo-assignee-left label strong{display:block;color:#253958;font-size:12px;white-space:normal;word-break:break-word}
.todo-assignee-left label small{display:block;color:#8a94a6;font-size:10px;margin-top:3px}
.todo-assignee-avatar{display:grid;place-items:center;flex:0 0 32px;width:32px;height:32px;border-radius:9px;background:#eaf7f3;color:#167d70;font-size:12px;font-weight:750}
.todo-assignee-rules{display:flex;align-items:center;gap:13px;flex-wrap:wrap}
.todo-file-required{display:flex;align-items:center;gap:6px;cursor:pointer;font-size:11px;color:#34415c;white-space:nowrap}
.todo-file-required:has(input:disabled){opacity:.45}
.todo-form-footnote,.todo-form-message{font-size:11px;color:#738197;line-height:1.7;margin:10px 0}
.todo-assignment-list{display:grid;gap:13px}
.todo-assignment-card{background:#fff;border:1px solid #e5eaf1;border-radius:11px;padding:18px;min-width:0}
.todo-assignment-head{display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap;padding-bottom:13px;border-bottom:1px solid #edf1f5}
.todo-assignment-person{display:flex;align-items:center;gap:10px;min-width:0}
.todo-person-icon{display:grid;place-items:center;width:41px;height:41px;flex:0 0 41px;border-radius:10px;background:#eaf7f3;color:#167d70;font-weight:800}
.todo-assignment-person strong{display:block;color:#17284f;font-size:13px}
.todo-assignment-person small{display:block;color:#8a94a6;font-size:11px;margin-top:3px}
.todo-assignment-badges{display:flex;flex-wrap:wrap;gap:7px}
.todo-assignment-file-list{display:grid;gap:8px;margin:13px 0}
.todo-assignment-file{display:flex;align-items:center;gap:12px;padding:10px 12px;background:#f8fafc;border:1px solid #edf1f5;border-radius:8px;min-width:0}
.todo-file-mark{display:grid;place-items:center;border-radius:8px;width:34px;height:34px;background:#eef2ff;color:#5264a6;font-size:17px;flex:0 0 34px}
.todo-file-label{min-width:0;flex:1}
.todo-file-label strong{font-size:12px;color:#34415c;display:block;overflow-wrap:anywhere}
.todo-file-label small{font-size:10px;color:#8a94a6;margin-top:4px;display:block}
.todo-assignment-file .btn{font-size:11px;white-space:nowrap}
.todo-assignment-no-files,.todo-submission-locked{margin:5px 0;color:#8a94a6;font-size:11px}
.todo-download-per-person{margin-top:5px;font-size:11px}
.todo-submission-controls{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;flex-wrap:wrap;padding:18px 0 2px;border-top:1px solid #edf1f5;margin-top:14px}
.todo-upload-form{display:flex;flex-direction:column;align-items:flex-start;gap:9px;flex:1 1 300px;min-width:0}
.todo-upload-form label{color:#17284f;font-size:12px;font-weight:650}
.todo-upload-form input{width:100%;box-sizing:border-box;min-height:42px;padding:10px;border:1px solid #d9dee8;border-radius:8px;font:inherit;font-size:12px;background:#fff}
.todo-upload-form small{color:#7d8797;font-size:10px;line-height:1.5}
.todo-upload-form+.todo-submission-controls form{margin:0}
#todo-work-only[hidden]{display:none!important}
@media(max-width:1300px){.todo-dashboard-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:800px){.todo-assignment-file{flex-wrap:wrap}}
@media(max-width:650px){.todo-dashboard-stats{grid-template-columns:1fr}.todo-assignee-choice{align-items:flex-start;flex-direction:column}.todo-submission-controls{align-items:stretch;flex-direction:column}.todo-submission-controls>.btn,.todo-submission-controls form>.btn{width:100%}.todo-assignment-head{align-items:flex-start}}

/* A7.4.7 — filtered/paginated recipient selector (single _style.blade.php). */
#todo-work-only{margin-top:30px;padding-top:4px}
#todo-work-only>.todo-section:first-child{margin-top:22px;padding-top:20px;border-top:1px solid #e5eaf1}
.todo-recipient-picker{border:1px solid #e5eaf1;border-radius:12px;background:#fff;padding:18px;margin-top:14px}
.todo-recipient-picker-intro,.todo-recipient-toolbar,.todo-recipient-pagination,.todo-selected-head{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap}
.todo-recipient-picker-intro strong,.todo-selected-head h5{color:#17284f;font-size:13px;font-weight:700;margin:0}
.todo-recipient-picker-intro p,.todo-selected-head p{font-size:11px;color:#8793a6;line-height:1.6;margin:5px 0 0}
.todo-recipient-limit{font-size:10px;font-weight:700;border-radius:6px;padding:6px 9px;background:#f0f4f9;color:#51617b}
.todo-recipient-filter-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px;margin:18px 0}
.todo-recipient-toolbar{border-top:1px solid #edf1f6;padding-top:13px;margin-bottom:10px;font-size:11px;color:#65748b}
.todo-recipient-toolbar>div{display:flex;gap:7px;flex-wrap:wrap}
.todo-recipient-toolbar .btn,.todo-recipient-pagination .btn,.todo-selected-head .btn{padding:7px 10px;font-size:11px;min-height:33px}
.todo-recipient-results{border:1px solid #e5eaf1;border-radius:9px;background:#f8fafc;max-height:288px;overflow:auto;padding:7px;display:grid;align-content:start;gap:7px}
.todo-recipient-row{background:#fff;border:1px solid #e5eaf1;padding:10px 12px;display:flex;align-items:center;gap:11px;border-radius:8px}
.todo-recipient-row input,.todo-recipient-flag input{accent-color:#168a82;width:16px;height:16px;flex:0 0 16px}
.todo-recipient-avatar{display:grid;place-items:center;width:32px;height:32px;flex:0 0 32px;background:#eaf7f3;border-radius:9px;color:#167d70;font-size:12px;font-weight:750}
.todo-recipient-person{min-width:0;flex:1}
.todo-recipient-person strong,.todo-selected-person-info strong{font-size:12px;color:#223a5c;display:block;overflow-wrap:anywhere}
.todo-recipient-person small,.todo-selected-person-info small{font-size:10px;color:#8590a2;margin-top:3px;display:block;overflow-wrap:anywhere}
.todo-recipient-pagination{margin:12px 0 18px;color:#67768e;font-size:11px}
.todo-selected-head{border-top:1px solid #edf1f6;padding-top:16px;margin-bottom:11px}
.todo-selected-person{border:1px solid #e5eaf1;border-radius:9px;background:#f8fafc;padding:11px 12px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin:7px 0}
.todo-selected-person-info{min-width:160px;flex:1}
.todo-selected-person-flags{display:flex;flex-wrap:wrap;align-items:center;gap:15px}
.todo-recipient-flag{display:inline-flex!important;gap:6px;align-items:center;white-space:nowrap;font-size:11px!important;color:#34415c!important;font-weight:500!important;margin:0!important;cursor:pointer}
.todo-recipient-remove{background:#fff;border:1px solid #f1d8d8;border-radius:6px;color:#b7434b;cursor:pointer;padding:7px 10px;font-size:11px}
.todo-recipient-remove:hover{background:#fff0f0}
.todo-recipient-stale{color:#b97828!important}
.todo-recipient-message{padding:14px;color:#8290a3;font-size:11px;line-height:1.6;margin:0}
.todo-recipient-error{border-radius:7px;background:#fff0f0;color:#b7434b;padding:10px 12px;font-size:11px;margin-top:10px}
.todo-preview-panel{max-height:calc(100vh - 112px);overflow-y:auto}
@media(max-width:900px){.todo-preview-panel{max-height:none;overflow:visible}}
@media(max-width:620px){.todo-recipient-filter-grid{grid-template-columns:1fr}.todo-selected-person-flags{width:100%}.todo-recipient-picker{padding:13px}}


/* A7.4.9 submission review. Keep all To-Do styles in this module's single _style.blade.php. */
.todo-review-card{margin:20px 0}.todo-review-card .card-body{padding:20px}.todo-review-list{display:grid;gap:13px}.todo-review-item{border:1px solid #e5eaf1;border-radius:11px;padding:17px 18px;background:#fff}.todo-review-item-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}.todo-review-person{display:flex;gap:11px;align-items:center;min-width:0}.todo-review-person strong{font-size:12px;color:#17284f;display:block}.todo-review-person small{font-size:10px;color:#8290a0;display:block;margin-top:4px}.todo-review-avatar{background:#eaf7f3;color:#167d70;border-radius:10px;width:40px;height:40px;display:grid;place-items:center;font-size:14px;font-weight:750;flex:0 0 40px}.todo-review-state{border-radius:99px;border:1px solid #dde6ee;padding:6px 11px;color:#677589;background:#f5f7fa;font-weight:700;font-size:10px;white-space:nowrap}.todo-review-state-pending_review{background:#fff5e7;color:#b17823;border-color:#f0deb9}.todo-review-state-approved,.todo-review-state-legacy_accepted{background:#eaf7f2;color:#167d70;border-color:#cae9dc}.todo-review-state-revision_requested{background:#fff0f1;color:#b7434b;border-color:#f1d7d9}.todo-review-hint{font-size:11px;color:#728199;line-height:1.7;margin:10px 0 0}.todo-review-feedback{margin-top:12px;border:1px solid #f0dcbd;border-radius:9px;padding:12px 14px;background:#fff9ef}.todo-review-feedback strong{font-size:11px;color:#a66d21}.todo-review-feedback p{font-size:12px;color:#4f5970;white-space:pre-wrap;overflow-wrap:anywhere;line-height:1.65;margin:7px 0}.todo-review-feedback small{font-size:10px;color:#8a94a6}.todo-review-form{display:grid;gap:10px;margin-top:14px;padding-top:14px;border-top:1px solid #e8edf3}.todo-review-form label{font-size:11px;font-weight:700;color:#4e5f79}.todo-review-form label span{font-weight:500;color:#8b97a8}.todo-review-form textarea{width:100%;box-sizing:border-box;resize:vertical;border:1px solid #d9e1eb;border-radius:8px;padding:10px 12px;min-height:85px;font-size:12px;font-family:inherit;line-height:1.6;color:#25334d;background:#fff}.todo-review-form textarea:focus{outline:0;border-color:#15966f;box-shadow:0 0 0 3px #15966f1c}.todo-review-actions{display:flex;gap:9px;justify-content:flex-end;flex-wrap:wrap}.todo-review-actions .btn{font-size:11px}.todo-review-history{margin-top:20px;border-top:1px solid #e5eaf1;padding-top:18px}.todo-review-history h4{color:#17284f;font-size:13px;margin:0 0 4px}.todo-review-history>p{font-size:11px;color:#8b97a8;margin:0 0 13px}.todo-review-history-list{padding:0;margin:0;list-style:none;display:grid;gap:11px}.todo-review-history-list li{display:flex;gap:11px;padding:12px 14px;background:#f8fafc;border:1px solid #e7ecf2;border-radius:9px}.todo-review-history-mark{display:grid;place-items:center;background:#eaf7f3;color:#167d70;flex:0 0 26px;width:26px;height:26px;border-radius:8px;font-size:14px}.todo-review-history-list li strong{font-size:11px;color:#263b5a}.todo-review-history-list li small{display:block;font-size:10px;color:#8593a5;margin-top:4px}.todo-review-history-list li p{font-size:11px;color:#56647b;margin:8px 0 0;white-space:pre-wrap;overflow-wrap:anywhere;line-height:1.6}@media(max-width:620px){.todo-review-card .card-body{padding:14px}.todo-review-item{padding:13px}.todo-review-actions{justify-content:stretch}.todo-review-actions .btn{flex:1 1 140px;text-align:center}}
</style>
