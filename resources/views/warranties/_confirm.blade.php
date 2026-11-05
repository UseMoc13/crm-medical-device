<div class="warranty-confirm" id="warrantyDeleteModal" hidden role="dialog" aria-modal="true" aria-labelledby="warrantyDeleteTitle">
  <div class="warranty-confirm-dialog"><div class="warranty-confirm-symbol">!</div><h3 id="warrantyDeleteTitle">Delete Warranty?</h3><p id="warrantyDeleteMessage">This action cannot be undone.</p>
    <div class="warranty-confirm-actions"><button type="button" class="btn" id="warrantyDeleteCancel">Cancel</button><button type="button" class="btn warranty-danger-btn" id="warrantyDeleteConfirm">Delete Warranty</button></div>
  </div>
</div>
<script>
(()=>{const modal=document.getElementById('warrantyDeleteModal');const message=document.getElementById('warrantyDeleteMessage');let selected=null;
const close=()=>{modal.hidden=true;selected=null};document.querySelectorAll('.warranty-delete-form').forEach(form=>form.addEventListener('submit',e=>{e.preventDefault();selected=form;message.textContent='Delete '+form.dataset.number+'? This action cannot be undone.';modal.hidden=false}));document.getElementById('warrantyDeleteConfirm').addEventListener('click',()=>{if(selected)selected.submit()});document.getElementById('warrantyDeleteCancel').addEventListener('click',close);modal.addEventListener('click',e=>{if(e.target===modal)close()});document.addEventListener('keydown',e=>{if(e.key==='Escape')close()});
})();
</script>
