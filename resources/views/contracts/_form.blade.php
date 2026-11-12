@php
    $editing = isset($contract);
    $selectedOrder = old('sales_order_id', $contract->sales_order_id ?? '');
    $types = ['Sales Contract','Service Agreement','Maintenance Contract','Rental Agreement','Distribution Agreement','Other'];
    $statuses = ['draft'=>'Draft','active'=>'Active','expired'=>'Expired','terminated'=>'Terminated'];
@endphp
<div class="card contract-form-card">
  <div class="card-head"><div><h3>Contract Information</h3><p>{{ $editing ? 'Update contract information and review your changes.' : 'Enter contract details and review the live preview.' }}</p></div><span class="contract-required"><b>*</b> Required fields</span></div>
  <div class="card-body">
  <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('contracts.update', $contract->contract_id) : route('contracts.store') }}" id="contractEditor">
    @csrf @if($editing) @method('PUT') @endif
    <div class="contract-editor-layout">
      <div class="contract-editor-main">
        <section class="contract-form-section"><div class="contract-section-title"><h4>General Information</h4><p>Contract identifier and linked sales order.</p></div>
          <div class="contract-form-grid">
            <div class="form-group"><label>Contract Number</label><div class="contract-auto-field"><input class="form-control" readonly value="{{ $editing ? $contract->contract_number : 'Generated automatically' }}"><span>Auto</span></div><small class="contract-hint">{{ $editing ? 'Contract number cannot be changed.' : 'Assigned after the contract is saved.' }}</small></div>
            <div class="form-group"><label for="sales_order_id">Sales Order <b class="contract-asterisk">*</b></label><select class="form-control" name="sales_order_id" id="sales_order_id" required><option value="">Select Sales Order</option>@foreach($salesOrders as $order)<option value="{{ $order->sales_order_id }}" @selected((string)$selectedOrder === (string)$order->sales_order_id)>{{ $order->sales_order_number ?? $order->sales_order_id }}</option>@endforeach</select>@error('sales_order_id')<small class="contract-error">{{ $message }}</small>@enderror</div>
          </div>
        </section>
        <section class="contract-form-section"><div class="contract-section-title"><h4>Contract Period</h4><p>Set the start and end dates for this agreement.</p></div>
          <div class="contract-form-grid">
            <div class="form-group"><label for="start_date">Start Date <b class="contract-asterisk">*</b></label><input type="date" class="form-control" name="start_date" id="start_date" value="{{ old('start_date', isset($contract) ? optional($contract->start_date)->format('Y-m-d') : '') }}" required>@error('start_date')<small class="contract-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="end_date">End Date</label><input type="date" class="form-control" name="end_date" id="end_date" value="{{ old('end_date', isset($contract) ? optional($contract->end_date)->format('Y-m-d') : '') }}">@error('end_date')<small class="contract-error">{{ $message }}</small>@enderror</div>
          </div>
        </section>
        <section class="contract-form-section"><div class="contract-section-title"><h4>Classification & Status</h4><p>Choose agreement type and current lifecycle status.</p></div>
          <div class="contract-form-grid">
            <div class="form-group"><label for="contract_type">Contract Type</label><select class="form-control" id="contract_type" name="contract_type"><option value="">Select Contract Type</option>@foreach($types as $type)<option value="{{ $type }}" @selected(old('contract_type', $contract->contract_type ?? '') === $type)>{{ $type }}</option>@endforeach</select></div>
            <div class="form-group"><label for="status">Status <b class="contract-asterisk">*</b></label><select class="form-control" name="status" id="status" required>@foreach($statuses as $key=>$text)<option value="{{ $key }}" @selected(old('status', $contract->status ?? 'draft') === $key)>{{ $text }}</option>@endforeach</select></div>
          </div>
        </section>
        <section class="contract-form-section"><div class="contract-section-title"><h4>Contract Document</h4><p>Attach a private PDF document (maximum 5 MB).</p></div>
          <div class="form-group"><label for="document">Contract Document (PDF)</label><input type="file" accept="application/pdf,.pdf" class="form-control" name="document" id="document">@error('document')<small class="contract-error">{{ $message }}</small>@enderror
            @if($editing && $contract->document_path)<small class="contract-hint">Current document: <a href="{{ route('contracts.download', $contract->contract_id) }}">Download PDF</a>. Leave empty to keep it.</small>@else<small class="contract-hint">Optional. PDF only, up to 5 MB.</small>@endif
          </div>
        </section>
        <div class="contract-form-actions"><a class="btn" href="{{ $editing ? route('contracts.show',$contract->contract_id) : route('contracts.index') }}">Cancel</a><button type="submit" class="btn primary">{{ $editing ? 'Save Changes' : 'Create Contract' }}</button></div>
      </div>
      <aside class="contract-preview-panel" aria-label="Contract live preview">
        <div class="contract-preview-label">CONTRACT PREVIEW</div>
        <div class="contract-preview-head"><div class="contract-preview-icon">C</div><div><h3 id="previewContractNumber">{{ $editing ? $contract->contract_number : 'New Contract' }}</h3><span>Linked sales agreement</span></div></div>
        <div class="contract-preview-divider"></div>
        <div class="contract-preview-row"><span>Sales Order</span><strong id="previewSalesOrder">Not selected</strong></div>
        <div class="contract-preview-row"><span>Contract Type</span><strong id="previewContractType">Not selected</strong></div>
        <div class="contract-preview-row"><span>Start Date</span><strong id="previewStart">—</strong></div>
        <div class="contract-preview-row"><span>End Date</span><strong id="previewEnd">—</strong></div>
        <div class="contract-preview-divider"></div>
        <div class="contract-preview-row"><span>Status</span><strong class="contract-preview-status" id="previewContractStatus">Draft</strong></div>
        <div class="contract-preview-row"><span>Document</span><strong id="previewDocument">{{ $editing && $contract->document_path ? 'Existing PDF' : 'No document' }}</strong></div>
        <div class="contract-preview-note"><strong>Contract Information</strong><p>This preview updates as you edit the form. The final values are saved when you submit.</p></div>
      </aside>
    </div>
  </form></div>
</div>
@include('contracts._style')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const ids=['sales_order_id','contract_type','start_date','end_date','status','document'];
  const nodes=Object.fromEntries(ids.map(id=>[id,document.getElementById(id)]));
  function dateValue(v){if(!v)return '—';const parts=v.split('-');return parts.length===3?`${parts[2]}/${parts[1]}/${parts[0]}`:v;}
  function update(){
    const opt=nodes.sales_order_id.selectedOptions[0];
    document.getElementById('previewSalesOrder').textContent=nodes.sales_order_id.value?opt.textContent.trim():'Not selected';
    document.getElementById('previewContractType').textContent=nodes.contract_type.value||'Not selected';
    document.getElementById('previewStart').textContent=dateValue(nodes.start_date.value);
    document.getElementById('previewEnd').textContent=dateValue(nodes.end_date.value);
    const status=nodes.status.value || 'draft';const el=document.getElementById('previewContractStatus');el.textContent=status.charAt(0).toUpperCase()+status.slice(1);el.className='contract-preview-status '+status;
    document.getElementById('previewDocument').textContent=nodes.document.files.length?nodes.document.files[0].name:@json($editing && !empty($contract->document_path)) ? 'Existing PDF' : 'No document';
  }
  ids.forEach(id=>{nodes[id].addEventListener('input',update);nodes[id].addEventListener('change',update);});update();
});
</script>
