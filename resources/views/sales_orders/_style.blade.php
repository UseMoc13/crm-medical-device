<style>
.so-card { overflow: hidden; margin-bottom: 20px; }
.so-card .card-head { padding: 21px 24px; display: flex; justify-content: space-between; align-items: center; gap: 12px; border-bottom: 1px solid #e8edf4; }
.so-card .card-head h3 { margin: 0 0 5px; font-size: 16px; color: #17284f; }
.so-card .card-head p { margin: 0; color: #8390a8; font-size: 12px; }
.so-filter-wrap { padding: 18px 24px; border-bottom: 1px solid #edf0f5; }
.so-filter { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.so-filter input, .so-filter select { min-height: 41px; padding: 9px 12px; border: 1px solid #dce2ec; border-radius: 8px; background: #fff; font-size: 13px; color: #17284f; }
.so-filter input { flex: 1 1 250px; }
.so-filter select { flex: 0 1 190px; }
.so-table-scroll { width: 100%; overflow-x: auto; }
.so-table { width: 100%; border-collapse: collapse; white-space: nowrap; font-size: 12.5px; }
.so-table th { padding: 14px 18px; background: #f8fafd; border-bottom: 1px solid #e7ecf3; color: #64738e; font-weight: 700; text-align: left; }
.so-table td { padding: 15px 18px; border-bottom: 1px solid #eef1f5; color: #34415c; vertical-align: middle; }
.so-table tr:last-child td { border-bottom: 0; }
.so-table tbody tr:hover { background: #fbfcff; }
.so-table .so-right { text-align: right; }
.so-number { font-weight: 700; color: #17284f !important; }
.so-strong-link, .so-text-link { color: #22588f; font-weight: 650; text-decoration: none; }
.so-strong-link:hover, .so-text-link:hover { text-decoration: underline; }
.so-muted { color: #98a3b4; font-size: 11px; }
.so-block { display: block; padding-top: 4px; }
.so-actions { display: flex; gap: 6px; align-items: center; }
.so-action { color: #233b65; text-decoration: none; border: 1px solid #dce3ee; background: #fff; border-radius: 7px; font-size: 12px; padding: 7px 10px; white-space: nowrap; }
.so-action:hover { background: #f0f5fc; }
.so-status { display: inline-flex; border-radius: 8px; padding: 5px 10px; background: #f1f3f6; color: #64748b; font-weight: 650; font-size: 11px; white-space: nowrap; }
.so-status-confirmed, .so-status-completed { background: #e8f7f0; color: #168360; }
.so-status-processing { background: #e9f2fd; color: #2567a5; }
.so-status-cancelled { background: #fcebec; color: #b93649; }
.so-status-draft { background: #edf0f5; color: #64748b; }
.so-table-footer { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; color: #8490a5; font-size: 12px; padding: 15px 24px; border-top: 1px solid #eef1f5; }
.so-empty { padding: 42px 18px !important; text-align: center; color: #7f8ba0 !important; }
.so-detail-layout { display: grid; grid-template-columns: minmax(0,1.7fr) minmax(310px,1fr); gap: 22px; align-items: start; }
.so-detail-main, .so-detail-side { min-width: 0; }
.so-info-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 22px 28px; padding: 24px; }
.so-field { display: flex; flex-direction: column; gap: 8px; min-width: 0; }
.so-field>span { color: #8390a8; font-size: 11px; }
.so-field strong { color: #17284f; font-size: 13px; font-weight: 600; overflow-wrap: anywhere; }
.so-field-full { grid-column: 1 / -1; }
.so-items-table { min-width: 690px; }
.so-preview-card { position: sticky; top: 88px; padding: 23px; }
.so-preview-heading { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
.so-preview-heading h3 { margin: 0; color: #17284f; font-size: 16px; }
.so-preview-heading p { margin: 4px 0 0; color: #92a0b1; font-size: 11px; }
.so-preview-title { margin-top: 22px; font-size: 16px; font-weight: 750; color: #17284f; overflow-wrap: anywhere; }
.so-preview-meta { display: grid; gap: 16px; padding: 20px 0; margin-top: 12px; border-bottom: 1px solid #e9edf3; }
.so-preview-meta>div, .so-totals>div { display: flex; justify-content: space-between; gap: 15px; }
.so-preview-meta span, .so-totals span { color: #8794a9; font-size: 11px; }
.so-preview-meta strong, .so-totals strong { color: #17284f; font-size: 12px; font-weight: 650; text-align: right; overflow-wrap: anywhere; }
.so-preview-items { padding: 20px 0; border-bottom: 1px solid #e9edf3; }
.so-preview-items h4 { margin: 0 0 14px; font-size: 12px; color: #17284f; }
.so-preview-item { display: flex; justify-content: space-between; gap: 15px; margin-bottom: 13px; font-size: 11px; }
.so-preview-item:last-child { margin-bottom: 0; }
.so-preview-item strong { color: #17284f; font-size: 11px; }
.so-preview-item>div span { display: block; color: #97a5b9; margin-top: 4px; }
.so-preview-item>strong { text-align: right; white-space: nowrap; }
.so-totals { padding-top: 18px; display: grid; gap: 15px; }
.so-totals .so-grand-total { padding-top: 17px; border-top: 1px solid #dfe6ef; align-items: center; }
.so-grand-total span { font-weight: 700; color: #17284f; }
.so-grand-total strong { font-size: 20px; color: #15966f; }
.so-summary-note { color: #8390a8; font-size: 10px; line-height: 1.5; }
.so-download-wide { width: 100%; justify-content: center; margin-top: 15px; text-decoration: none; box-sizing: border-box; display: flex; }
.so-secondary-link { margin-top: 8px; }
.so-header-actions { display: flex; flex-wrap: wrap; gap: 8px; }
@media(max-width: 1100px) { .so-detail-layout { grid-template-columns: minmax(0,1fr); } .so-preview-card { position: static; } }
@media(max-width: 650px) { .so-info-grid { grid-template-columns: 1fr; padding: 18px; } .so-card .card-head, .so-filter-wrap { padding: 18px; } .so-filter>* { flex: 1 1 100% !important; } .so-header-actions { width: 100%; } }
</style>
