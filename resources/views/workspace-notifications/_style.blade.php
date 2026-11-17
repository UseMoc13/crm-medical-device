<style>
/* A7.4.11: one global stylesheet for notification inbox and topbar. */
.wsn-topbar{position:relative;flex-shrink:0}.wsn-topbar summary{list-style:none;cursor:pointer;position:relative;display:inline-grid;place-items:center}.wsn-topbar summary::-webkit-details-marker{display:none}.wsn-count{position:absolute;top:-4px;right:-8px;min-width:17px;height:17px;padding:0 3px;box-sizing:border-box;background:#dc4c56;border:2px solid #fff;color:#fff;border-radius:99px;display:grid;place-items:center;font-size:9px;font-weight:700;line-height:1}.wsn-dropdown{position:absolute;right:-32px;top:calc(100% + 16px);z-index:41000;width:min(356px,calc(100vw - 24px));max-height:min(475px,75vh);overflow:auto;background:#fff;border:1px solid #dfe5ef;border-radius:12px;box-shadow:0 20px 50px #17284f26;color:#17284f}.wsn-dropdown-head,.wsn-dropdown-foot{padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid #edf0f5}.wsn-dropdown-head strong{font-size:13px}.wsn-dropdown-head a,.wsn-dropdown-foot a{color:#158b7a;font-size:11px;font-weight:700;text-decoration:none}.wsn-dropdown-foot{border-top:1px solid #edf0f5;border-bottom:0;justify-content:flex-start}.wsn-recent-item{margin:0;border-bottom:1px solid #eff2f6}.wsn-recent-item button{display:block;width:100%;border:0;background:#fff;padding:12px 16px;text-align:left;font:inherit;cursor:pointer}.wsn-recent-item.is-unread button{background:#f1fbf8}.wsn-recent-item button:hover,.wsn-recent-item button:focus-visible{background:#eaf7f3;outline:0}.wsn-recent-title,.wsn-recent-sub{display:block;overflow-wrap:anywhere}.wsn-recent-title{font-size:12px;font-weight:700;color:#17284f}.wsn-recent-sub{font-size:11px;color:#53627b;margin-top:3px;line-height:1.5}.wsn-recent-item small{display:block;color:#8793a7;font-size:10px;margin-top:5px}.wsn-empty{padding:18px;color:#8793a7;font-size:12px;margin:0}.wsn-inbox{margin:0 0 24px}.wsn-card-head{display:flex;justify-content:space-between;gap:15px;align-items:center;flex-wrap:wrap}.wsn-inbox-body{padding:0!important}.wsn-inbox-item{padding:17px 20px;display:flex;gap:12px;align-items:center;border-bottom:1px solid #edf0f5}.wsn-inbox-item.is-unread{background:#f7fcfb}.wsn-inbox-dot{width:7px;height:7px;flex:0 0 7px;border-radius:50%;background:#c4cfdb}.wsn-inbox-item.is-unread .wsn-inbox-dot{background:#13a080}.wsn-inbox-copy{flex:1;min-width:0}.wsn-inbox-copy strong{font-size:13px;color:#17284f}.wsn-inbox-copy p{font-size:12px;color:#53627b;line-height:1.5;margin:5px 0}.wsn-inbox-copy small{font-size:10px;color:#8a94a6}.wsn-actions{display:flex;gap:6px;flex-wrap:wrap}.wsn-actions form{margin:0}.wsn-actions .btn{font-size:11px}.wsn-inbox-empty{text-align:center;padding:45px 20px;color:#778397}.wsn-inbox-empty span{font-size:30px}.wsn-inbox-empty h4{font-size:15px;color:#17284f;margin:10px 0 4px}.wsn-inbox-empty p{font-size:12px;margin:0}.wsn-pagination{padding:15px}.wsn-card-head button:disabled{opacity:.5;cursor:not-allowed}@media(max-width:640px){.wsn-dropdown{right:-80px}.wsn-inbox-item{align-items:flex-start;flex-wrap:wrap}.wsn-actions{padding-left:20px}.wsn-inbox-copy{min-width:calc(100% - 35px)}}
/* A7.4.11.1.1: shared SVG bell, sized for each location. */
.wsn-icon{display:block;width:20px;height:20px;flex-shrink:0;stroke:currentColor}
.wsn-bell .wsn-icon{width:20px;height:20px}
.sidebar .nav a[data-label="Notifications"] .ico .wsn-icon{width:17px;height:17px}
.wsn-inbox-empty .wsn-icon{width:34px;height:34px;margin:0 auto;color:#8093a9}

/* A7.4.11.2: notification preferences + dropdown improvements. */
.wsn-count[hidden]{display:none!important}
.wsn-dropdown-links{display:flex;align-items:center;gap:11px}
.wsn-settings-layout{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin-bottom:30px}
.wsn-settings-card .card-body{padding:0!important}
.wsn-settings-choice{display:flex;justify-content:space-between;align-items:center;gap:20px;padding:18px 20px;border-bottom:1px solid #edf0f5;cursor:pointer}
.wsn-settings-choice:last-child{border-bottom:0}
.wsn-settings-choice strong{display:block;font-size:13px;color:#17284f}
.wsn-settings-choice small{display:block;margin-top:6px;font-size:11px;line-height:1.55;color:#6b7b91}
.wsn-settings-choice input{width:18px;height:18px;accent-color:#15966f;flex:none;cursor:pointer}
.wsn-settings-lead{padding:20px;border-top:1px solid #edf0f5}
.wsn-settings-lead>label{display:block;color:#17284f;font-size:12px;font-weight:700;margin-bottom:8px}
.wsn-settings-lead>select{display:block;width:100%;max-width:340px;min-height:40px;padding:8px 12px;border:1px solid #d9e1ea;border-radius:8px;background:#fff;color:#263a55;font-family:inherit;font-size:12px}
.wsn-settings-lead>small{display:block;color:#708096;margin-top:9px;font-size:11px;line-height:1.6;max-width:560px}
.wsn-settings-lead .wsn-settings-error{color:#ca4049}
.wsn-settings-actions{grid-column:1/-1;display:flex;justify-content:flex-end}
@media(max-width:920px){.wsn-settings-layout{grid-template-columns:1fr}}
@media(max-width:480px){.wsn-settings-choice{padding:14px}.wsn-dropdown-links{gap:8px}.wsn-dropdown-head{padding:13px}}
</style>
