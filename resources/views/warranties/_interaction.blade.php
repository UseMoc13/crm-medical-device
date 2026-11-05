<script>
(()=>{
const byId=id=>document.getElementById(id);
const ids=['warranty_type','serial_number_id','customer_id','start_date','end_date','status'];
const preview={warranty_type:'wPreviewType',serial_number_id:'wPreviewSerial',customer_id:'wPreviewCustomer',start_date:'wPreviewStart',end_date:'wPreviewEnd',status:'wPreviewStatus'};
const display=id=>{const el=byId(id);if(!el)return '—';if(el.tagName==='SELECT')return el.selectedIndex>0?(el.selectedOptions[0].textContent.trim()||'—'):'—';if(el.type==='date' && el.value){const d=new Date(el.value+'T12:00:00');return d.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});}return el.value||'—'};
const refresh=()=>ids.forEach(id=>{const target=byId(preview[id]);if(target)target.textContent=display(id)});
ids.forEach(id=>byId(id)?.addEventListener('change',()=>{if(id==='serial_number_id'){const customer=byId('customer_id'),serial=byId('serial_number_id');const selected=serial.selectedOptions[0]?.dataset.customerId;if(selected && Array.from(customer.options).some(x=>x.value===selected))customer.value=selected;}
if(id==='start_date'){const end=byId('end_date'),start=byId('start_date');end.min=start.value;}refresh()}));
byId('start_date') && (byId('end_date').min=byId('start_date').value);
refresh();
})();
</script>
