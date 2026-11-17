<style>
/* A7.4 Calendar: module-only stylesheet. Matches Service Tickets / Activities CRM palette. */
.calendar-main-card,.calendar-form-card{margin-bottom:18px}.calendar-header{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}.calendar-nav{display:flex;gap:7px}.calendar-nav .btn{min-width:36px;justify-content:center;text-align:center}.calendar-filter-bar{display:flex;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:18px}.calendar-filter-bar input,.calendar-filter-bar select{height:40px;padding:0 12px;border:1px solid #d9dee8;border-radius:8px;color:#34415c;background:#fff;font-size:12px;box-sizing:border-box}.calendar-filter-bar select{min-width:210px}.calendar-legend{display:flex;align-items:center;gap:13px;margin-left:auto;flex-wrap:wrap;color:#64748b;font-size:11px}.calendar-legend span{display:inline-flex;align-items:center;gap:6px}.calendar-dot{width:8px;height:8px;display:inline-block;border-radius:50%}.calendar-dot.personal{background:#7c6bcd}.calendar-dot.activity{background:#168b85}.calendar-dot.todo{background:#ca8b2b}.calendar-scroll{overflow-x:auto;border:1px solid #cbd5e1;border-radius:9px}.calendar-grid{display:grid;grid-template-columns:repeat(7,minmax(140px,1fr));min-width:980px}.calendar-weekday{padding:12px 8px;text-align:center;background:#f3f5f9;color:#53627b;font-size:11px;font-weight:700;border-right:1px solid #d5dce6;border-bottom:1px solid #cbd5e1}.calendar-weekday:nth-child(7){border-right:0}.calendar-day{min-height:118px;padding:8px;border-right:1px solid #d5dce6;border-bottom:1px solid #d5dce6;min-width:0;box-sizing:border-box;background:#fff}.calendar-day:nth-child(7n){border-right:0}.calendar-day.outside{background:#f7f9fc}.calendar-day.today{background:#f2fbf8}.calendar-day-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}.calendar-day-number{font-size:12px;font-weight:700;color:#34415c}.calendar-day.today .calendar-day-number{color:#16856a;background:#dcefe9;border-radius:50%;padding:5px 7px}.calendar-day-head a{display:grid;place-items:center;width:22px;height:22px;color:#8793a7;text-decoration:none;border-radius:6px;font-size:18px}.calendar-day-head a:hover{background:#e4f3ec;color:#16856a}.calendar-day-events{display:grid;gap:4px}.calendar-event{display:flex;align-items:center;gap:5px;padding:5px 7px;border-radius:5px;text-decoration:none;min-width:0;border-left:3px solid;font-size:10px}.calendar-event.personal{background:#f2efff;color:#6d50a4;border-color:#8c70ca}.calendar-event.activity{background:#eaf7f3;color:#167d70;border-color:#168b85}.calendar-event.todo{background:#fff5df;color:#946200;border-color:#ca8b2b}.calendar-event-time{white-space:nowrap;font-weight:700;font-size:9px}.calendar-event-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.calendar-more{font-size:10px;color:#8793a7;padding-left:8px}
.calendar-form-layout{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(280px,.75fr);gap:30px;align-items:start}.calendar-fields,.calendar-preview{min-width:0}.calendar-section{margin:23px 0 16px;padding-top:20px;border-top:1px solid #edf0f5}.calendar-section:first-child{margin-top:0;padding-top:0;border:0}.calendar-section h4{margin:0 0 5px;font-size:13px;color:#17284f}.calendar-section p{margin:0;color:#8a94a6;font-size:11px}.calendar-field{margin-bottom:18px}.calendar-field label{display:block;font-size:12px;font-weight:600;color:#17284f;margin-bottom:7px}.calendar-field label span{color:#c94a4a}.calendar-field input,.calendar-field textarea{width:100%;box-sizing:border-box;border:1px solid #d9dee8;border-radius:8px;padding:10px 12px;color:#17284f;background:#fff;font-size:13px;font-family:inherit}.calendar-field input{height:42px}.calendar-field textarea{resize:vertical;min-height:115px;line-height:1.6}.calendar-field :is(input,textarea):focus{outline:none;border-color:#2ba7a0;box-shadow:0 0 0 3px #2ba7a014}.calendar-field small{display:block;margin-top:5px;color:#c94a4a;font-size:11px}.calendar-date-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}.calendar-checkbox{display:flex;align-items:center;gap:9px;margin:0 0 18px;font-size:12px;color:#34415c}.calendar-form-actions{display:flex;align-items:center;justify-content:flex-end;gap:9px;padding-top:16px;border-top:1px solid #edf0f5}.calendar-preview{padding:24px;border:1px solid #e6eaf0;border-radius:12px;background:#fafbfd;position:sticky;top:90px}.calendar-preview-label{display:block;font-size:10px;color:#8a94a6;letter-spacing:1px;font-weight:700;margin-bottom:20px}.calendar-preview-icon{width:52px;height:52px;display:grid;place-items:center;border-radius:11px;background:#eaf7f3;color:#167d70;font-size:14px;font-weight:700;flex-shrink:0}.calendar-preview h3{margin:14px 0 5px;color:#17284f;font-size:17px;overflow-wrap:anywhere}.calendar-preview>small{font-size:11px;color:#7d8797}.calendar-preview-rule{height:1px;background:#e5e9ef;margin:20px 0}.calendar-preview-item{display:flex;flex-direction:column;gap:5px;margin-bottom:17px}.calendar-preview-item span{color:#8a94a6;font-size:11px}.calendar-preview-item strong{color:#34415c;font-size:12px;font-weight:600;overflow-wrap:anywhere;white-space:pre-wrap}.calendar-preview-note{padding:13px 14px;border-radius:8px;background:#f1f5f7;color:#7d8797;font-size:11px;line-height:1.6}.calendar-show-grid{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(270px,.75fr);gap:18px;align-items:start}.calendar-show-grid>.card{min-width:0}.calendar-event-identity{display:flex;align-items:center;gap:14px;border-bottom:1px solid #edf0f5;padding-bottom:18px;margin-bottom:20px}.calendar-event-identity h2{font-size:17px;color:#17284f;margin:0 0 5px}.calendar-event-identity span{font-size:11px;color:#7d8797}.calendar-info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 24px}.calendar-info-grid div{min-width:0}.calendar-info-grid span,.calendar-description span{display:block;color:#8a94a6;font-size:10px;letter-spacing:.3px;text-transform:uppercase;margin-bottom:7px}.calendar-info-grid strong{font-size:12px;color:#34415c;overflow-wrap:anywhere}.calendar-description{margin-top:20px;padding-top:18px;border-top:1px solid #edf0f5}.calendar-description p{color:#596579;font-size:12px;line-height:1.75;white-space:pre-wrap;overflow-wrap:anywhere}.calendar-summary-row{display:flex;justify-content:space-between;gap:12px;border-bottom:1px solid #edf0f5;padding:13px 0;color:#8a94a6;font-size:12px}.calendar-summary-row strong{color:#34415c;font-size:12px;text-align:right}.calendar-delete{margin-top:22px;color:#b91c1c;border-color:#fecaca}.calendar-confirm-backdrop[hidden]{display:none!important}.calendar-confirm-backdrop{position:fixed;inset:0;z-index:22000;background:#0e1c3699;display:flex;align-items:center;justify-content:center;padding:16px}.calendar-confirm-dialog{max-width:450px;width:100%;background:#fff;padding:29px;border-radius:15px;text-align:center;box-shadow:0 20px 70px #14203738}.calendar-confirm-icon{background:#fff0ee;color:#c03939;font-weight:800;border-radius:13px;width:54px;height:54px;display:grid;place-items:center;font-size:25px;margin:0 auto 16px}.calendar-confirm-dialog h3{color:#17284f;font-size:18px;margin:0 0 10px}.calendar-confirm-dialog p{color:#64748b;font-size:12px;line-height:1.65;margin-bottom:20px}.calendar-confirm-dialog>div:last-child{display:flex;gap:9px;justify-content:center}.calendar-danger-btn{background:#c03939!important;color:white!important;border-color:#c03939!important}
@media(max-width:1000px){.calendar-form-layout,.calendar-show-grid{grid-template-columns:1fr}.calendar-preview{position:static}}@media(max-width:650px){.calendar-date-grid,.calendar-info-grid{grid-template-columns:1fr}.calendar-filter-bar input,.calendar-filter-bar select{width:100%}.calendar-filter-bar .btn{width:100%}.calendar-form-actions{flex-wrap:wrap}.calendar-form-actions .btn{flex:1}.calendar-legend{margin-left:0}}


/* A7.4.2 — calendar navigation, fixed-height month cells, day timeline */
.calendar-toolbar{display:grid;grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);align-items:center;gap:16px;margin-bottom:18px}
.calendar-month-picker{display:flex;align-items:center;gap:8px;flex-wrap:wrap;min-width:0}
.calendar-month-picker label{font-size:11px;color:#64748b;font-weight:600}
.calendar-month-picker select{height:38px;border:1px solid #d9dee8;border-radius:8px;padding:0 10px;max-width:135px;background:#fff;color:#34415c;font-size:12px}
.calendar-month-picker .btn{min-height:38px}
.calendar-view-toggle{display:inline-flex;align-items:center;justify-content:center;gap:4px;padding:4px;border:1px solid #dde4ed;border-radius:10px;background:#f3f5f9;justify-self:center}
.calendar-toggle-btn{min-width:98px;height:34px;padding:0 14px;border:1px solid transparent;border-radius:7px;background:transparent;color:#7a879a;font-size:12px;font-weight:700;cursor:pointer;transition:.18s}
.calendar-toggle-btn.active{background:#fff;box-shadow:0 1px 5px #14203715;border-color:#e4e8ee}
.calendar-toggle-btn.active.work{color:#138477}.calendar-toggle-btn.active.personal{color:#7556b0}
.calendar-toolbar .calendar-legend{margin-left:0;justify-self:end;justify-content:flex-end}
.calendar-nav-arrow{font-size:18px;font-weight:600}
.calendar-scroll .calendar-day{height:158px;min-height:158px;max-height:158px;overflow:hidden;cursor:pointer;transition:background .16s}
.calendar-scroll .calendar-day:hover:not(.outside){background:#f7fafc}
.calendar-scroll .calendar-day:focus-visible{outline:2px solid #168b85;outline-offset:-3px}
.calendar-day-head .calendar-open-day{border:0;background:transparent;padding:0;cursor:pointer;display:flex;align-items:center;justify-content:center;border-radius:7px}
.calendar-day-head .calendar-open-day:hover{background:#eaf7f3}
.calendar-day-events{grid-template-rows:repeat(3,auto);gap:4px}
.calendar-day-events .calendar-event{width:100%;box-sizing:border-box;text-align:left;border-top:0;border-right:0;border-bottom:0;cursor:pointer;line-height:1.4;min-height:24px;max-height:24px;overflow:hidden}
.calendar-day-events .calendar-event:hover{filter:brightness(.965)}
.calendar-more{display:inline-block;border:0;background:none;text-align:left;cursor:pointer;font-weight:700;color:#406c99;font-size:10px;padding:2px 7px;line-height:17px}
.calendar-more:hover{text-decoration:underline}
.calendar-help{font-size:11px;color:#8793a7;margin:12px 0 0;line-height:1.6}
.calendar-day-backdrop[hidden]{display:none!important}
.calendar-day-backdrop{position:fixed;inset:0;z-index:25000;background:rgba(16,29,54,.62);display:flex;align-items:center;justify-content:center;padding:20px}
.calendar-day-dialog{width:min(100%,590px);max-height:min(740px,calc(100dvh - 40px));overflow:hidden;display:flex;flex-direction:column;border-radius:14px;background:#fff;box-shadow:0 22px 70px #14203740;min-width:0}
.calendar-day-dialog-head{display:flex;justify-content:space-between;gap:16px;align-items:start;padding:22px 24px 17px;border-bottom:1px solid #e9eef5}
.calendar-day-kicker{font-size:10px;letter-spacing:1px;color:#168b85;font-weight:800}
.calendar-day-dialog-head h3{font-size:18px;margin:5px 0;color:#17284f}
.calendar-day-dialog-head p{font-size:11px;color:#8a94a6;margin:4px 0 0}
.calendar-day-close{border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#667085;font-size:22px;width:34px;height:34px;cursor:pointer;flex-shrink:0}
.calendar-day-dialog-body{overflow:auto;overscroll-behavior:contain;padding:10px 24px 20px;flex:1}
.calendar-day-empty{padding:35px 14px;color:#8a94a6;font-size:12px;text-align:center}
.calendar-timeline-entry{display:grid;grid-template-columns:62px 3px minmax(0,1fr);gap:12px;min-height:74px;padding-top:14px}
.calendar-timeline-clock{font-size:11px;font-weight:700;color:#596579;text-align:right;padding-top:8px;white-space:nowrap}
.calendar-timeline-bar{width:3px;border-radius:4px;background:#8c70ca;position:relative}
.calendar-timeline-bar::before{content:'';position:absolute;top:9px;left:-4px;border-radius:50%;height:11px;width:11px;background:inherit;border:2px solid #fff;box-sizing:border-box;box-shadow:0 0 0 1px #d9dee8}
.calendar-timeline-bar.activity{background:#168b85}.calendar-timeline-bar.todo{background:#ca8b2b}
.calendar-timeline-content{padding:5px 0 18px;border-bottom:1px solid #edf0f5;min-width:0}
.calendar-timeline-topline{display:flex;align-items:center;flex-wrap:wrap;gap:7px;margin-bottom:6px}
.calendar-timeline-kind{font-size:10px;font-weight:700;color:#7556b0;background:#f2efff;padding:3px 7px;border-radius:5px}
.calendar-timeline-kind.activity{background:#eaf7f3;color:#167d70}.calendar-timeline-kind.todo{background:#fff5df;color:#946200}
.calendar-timeline-ongoing{font-size:10px;color:#8a94a6}
.calendar-timeline-name{display:block;font-size:13px;color:#17284f;font-weight:700;text-decoration:none;overflow-wrap:anywhere}
.calendar-timeline-name:hover{color:#168b85;text-decoration:underline}
.calendar-timeline-subtitle{font-size:11px;color:#8793a7;margin:5px 0 0;overflow-wrap:anywhere}
.calendar-timeline-manage{display:inline-block;margin-top:8px;color:#168b85;font-size:11px;text-decoration:none;font-weight:600}
.calendar-timeline-manage:hover{text-decoration:underline}
.calendar-day-dialog-foot{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 24px;border-top:1px solid #edf0f5}
body.calendar-modal-open{overflow:hidden}
.calendar-delete-section{padding-top:16px;margin-top:16px;border-top:1px solid #edf0f5}
.calendar-delete-section h4{font-size:13px;color:#17284f;margin:0 0 6px}
.calendar-delete-section p{font-size:11px;line-height:1.6;color:#8a94a6;margin:0 0 10px}
.calendar-delete-section label{display:block;font-size:11px;font-weight:700;color:#34415c;margin-bottom:7px}
.calendar-delete-section input[type=date]{width:100%;box-sizing:border-box;height:40px;border:1px solid #d9dee8;border-radius:8px;padding:0 10px;color:#17284f}
.calendar-delete-section .calendar-delete{margin-top:10px}
.calendar-excluded-dates{font-size:11px;line-height:1.6;color:#596579;margin-top:12px;overflow-wrap:anywhere}
@media(max-width:1200px){.calendar-toolbar{grid-template-columns:1fr auto}.calendar-toolbar .calendar-legend{grid-column:1/-1;justify-self:start}}
@media(max-width:760px){.calendar-toolbar{grid-template-columns:1fr}.calendar-view-toggle{justify-self:start}.calendar-toolbar .calendar-legend{grid-column:auto}.calendar-month-picker label{font-size:11px}.calendar-day-dialog-head,.calendar-day-dialog-body,.calendar-day-dialog-foot{padding-left:16px;padding-right:16px}}
@media(max-width:480px){.calendar-day-dialog-foot{flex-direction:column}.calendar-day-dialog-foot .btn{width:100%;justify-content:center}.calendar-timeline-entry{grid-template-columns:52px 3px minmax(0,1fr);gap:9px}}


/* A7.4.3 — 3-way calendar mode + multi-select all-day date manager */
.calendar-view-toggle{white-space:nowrap}
.calendar-toggle-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;min-width:88px;box-sizing:border-box}
.calendar-toggle-btn:hover{background:#e7edf4;color:#34415c;text-decoration:none}
.calendar-toggle-btn:focus-visible{outline:2px solid #168b85;outline-offset:2px}
.calendar-toggle-btn.active.work{color:#138477}
.calendar-toggle-btn.active.all{color:#284b82}
.calendar-toggle-btn.active.personal{color:#7556b0}
.calendar-mini-calendar{border:1px solid #dde4ed;border-radius:11px;padding:12px;background:#fafbfd}
.calendar-mini-header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:13px}
.calendar-mini-header strong{font-size:13px;color:#17284f;font-weight:700;text-align:center}
.calendar-mini-arrow{width:32px;height:32px;border:1px solid #e2e8f0;border-radius:8px;background:white;color:#34415c;cursor:pointer;font-size:17px;line-height:1}
.calendar-mini-arrow:hover:not(:disabled){background:#eaf7f3;border-color:#bde1db;color:#167d70}
.calendar-mini-arrow:disabled{cursor:default;opacity:.35}
.calendar-mini-weekdays,.calendar-mini-days{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:4px}
.calendar-mini-weekdays{margin-bottom:5px}
.calendar-mini-weekdays span{text-align:center;color:#8491a4;font-weight:700;font-size:10px}
.calendar-mini-day,.calendar-mini-blank{display:flex;align-items:center;justify-content:center;aspect-ratio:1;border:1px solid transparent;min-width:0;border-radius:7px;font-size:11px;font-weight:600;background:white;color:#34415c}
.calendar-mini-day{cursor:pointer;border-color:#e2e8f0;transition:.15s}
.calendar-mini-day:hover:not(:disabled){border-color:#168b85;background:#eaf7f3}
.calendar-mini-day.selected,.calendar-mini-day.selected:hover{background:#168b85;color:white;border-color:#168b85}
.calendar-mini-day.removed{background:#f1f3f6;color:#a5afbb;text-decoration:line-through;border-color:#edf0f5}
.calendar-mini-day.outside{background:transparent;color:#c7cdd6;border-color:transparent}
.calendar-mini-day:disabled{cursor:not-allowed}
.calendar-mini-day:focus-visible,.calendar-mini-arrow:focus-visible{outline:2px solid #168b85;outline-offset:2px}
.calendar-mini-legend{display:flex;flex-wrap:wrap;align-items:center;gap:7px 12px;margin-top:12px;font-size:10px;color:#8190a2}
.calendar-mini-legend span{display:inline-flex;align-items:center;gap:5px}
.calendar-mini-legend-dot{display:inline-block;width:10px;height:10px;border-radius:3px;border:1px solid #dde4ed;background:#fff}
.calendar-mini-legend-dot.selected{background:#168b85;border-color:#168b85}
.calendar-mini-legend-dot.removed{background:#dfe4eb;border-color:#dfe4eb}
.calendar-mini-actions{display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-top:10px}
.calendar-mini-textbtn{border:0;background:transparent;color:#168b85;font-size:10px;font-weight:700;cursor:pointer;padding:4px 0;text-align:left}
.calendar-mini-textbtn:hover:not(:disabled){text-decoration:underline}
.calendar-mini-textbtn:disabled{opacity:.4;cursor:default}
.calendar-mini-selection-status{margin:12px 0 6px!important;line-height:1.7!important;color:#596579!important;font-size:11px!important;overflow-wrap:anywhere}
.calendar-remove-selected{margin-top:5px!important}
.calendar-remove-selected:disabled{opacity:.4;cursor:not-allowed}
@media(max-width:760px){.calendar-view-toggle{justify-self:center}.calendar-toolbar .calendar-legend{justify-self:start}}
@media(max-width:480px){.calendar-toggle-btn{min-width:73px;padding:0 9px}.calendar-mini-calendar{padding:9px}.calendar-mini-weekdays,.calendar-mini-days{gap:3px}}

/* A7.4.10 Private note reminders; keep Calendar's existing palette and single stylesheet. */
.calendar-dot.note{background:#8b6ac6}.calendar-event.note{background:#f4efff;color:#6c4b9c;border-color:#8b6ac6}.calendar-timeline-bar.note{background:#8b6ac6}.calendar-timeline-kind.note{color:#7152aa;background:#f3efff}
</style>
