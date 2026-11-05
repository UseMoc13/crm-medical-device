<aside class="installation-preview" aria-label="Installation preview">
  <div class="preview-eyebrow">INSTALLATION LIVE PREVIEW</div>
  <div class="preview-head"><div class="preview-symbol">⚙</div><div><h3 id="previewHeading">{{ isset($installation) ? 'Installation Details' : 'New Installation' }}</h3><small>Installation scheduling summary</small></div></div>
  <div class="preview-line"><span>Delivery</span><strong id="previewDelivery">{{ isset($installation) ? ($installation->delivery?->delivery_number ?? '—') : 'Not selected' }}</strong></div>
  <div class="preview-line"><span>Assigned Technician</span><strong id="previewUser">{{ isset($installation) ? ($installation->user?->name ?? '—') : 'Not selected' }}</strong></div>
  <div class="preview-line"><span>Installation Date</span><strong id="previewDate">{{ isset($installation) ? ($installation->installation_date?->format('d M Y') ?? 'Not scheduled') : 'Not scheduled' }}</strong></div>
  <div class="preview-line"><span>Status</span><strong id="previewStatus">{{ $installation->status ?? 'Scheduled' }}</strong></div>
  <div class="preview-line"><span>Location</span><strong id="previewLocation">{{ isset($installation) ? ($installation->location ?: 'Not provided') : 'Not provided' }}</strong></div>
  <div class="preview-line"><span>Notes</span><strong id="previewNotes">{{ isset($installation) ? ($installation->notes ?: '—') : '—' }}</strong></div>
  <div class="preview-notice">Every new Installation starts as Scheduled. Once started, technicians can record checklist completion and notes from Installation Details.</div>
</aside>
<script>
(()=>{
 const fields={delivery_id:'previewDelivery',user_id:'previewUser',installation_date:'previewDate',location:'previewLocation',notes:'previewNotes'};
 const defaults={delivery_id:'Not selected',user_id:'Not selected',installation_date:'Not scheduled',location:'Not provided',notes:'—'};
 function refresh(){Object.entries(fields).forEach(([name,id])=>{
 const control=document.querySelector(`[name="${name}"]`);const out=document.getElementById(id);if(!control||!out)return;
 let value=control.value.trim();if(control.tagName==='SELECT')value=control.options[control.selectedIndex]?.text.trim()||'';
 if(name==='installation_date'&&value){const dt=new Date(value+'T00:00:00');value=dt.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});}
 out.textContent=value||defaults[name];
 });}
 document.querySelectorAll('[name="delivery_id"],[name="user_id"],[name="installation_date"],[name="location"],[name="notes"]').forEach(x=>{x.addEventListener('input',refresh);x.addEventListener('change',refresh)});refresh();
})();
</script>
