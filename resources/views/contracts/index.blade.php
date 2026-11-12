@extends('layouts.app')
@section('title','Contracts')
@section('content')
<div class="page-head"><div><h1>Contracts</h1><p>Manage contracts and agreements associated with sales orders.</p></div><div class="actions"><a href="{{ route('contracts.create') }}" class="btn primary">+ New Contract</a></div></div>
<div class="card contract-list-card"><div class="card-head"><div><h3>Contract List</h3><p>{{ $contracts->total() }} contracts found</p></div></div>
<div class="card-body">
<form method="GET" action="{{ route('contracts.index') }}" class="contract-filter-bar">
  <div class="contract-search"><label for="contractSearch">Search</label><input class="form-control" id="contractSearch" name="search" value="{{ request('search') }}" placeholder="Contract no. or sales order..."></div>
  <div><label for="contractStatus">Status</label><select class="form-control" id="contractStatus" name="status"><option value="">All Statuses</option>@foreach(['draft'=>'Draft','active'=>'Active','expired'=>'Expired','terminated'=>'Terminated'] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select></div>
  <div><label for="contractType">Type</label><select class="form-control" id="contractType" name="contract_type"><option value="">All Types</option>@foreach(['Sales Contract','Service Agreement','Maintenance Contract','Rental Agreement','Distribution Agreement','Other'] as $type)<option value="{{ $type }}" @selected(request('contract_type')===$type)>{{ $type }}</option>@endforeach</select></div>
  <div class="contract-filter-actions"><button class="btn primary" type="submit">Apply Filters</button><a class="btn" href="{{ route('contracts.index') }}">Reset</a></div>
</form>
<div class="contract-table-scroll"><table class="contract-list-table"><thead><tr><th>No.</th><th>Contract Number</th><th>Sales Order</th><th>Contract Type</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Document</th><th>Actions</th></tr></thead><tbody>
@forelse($contracts as $contract)
<tr><td>{{ $contracts->firstItem()+$loop->index }}</td><td><a class="contract-strong-link" href="{{ route('contracts.show',$contract->contract_id) }}">{{ $contract->contract_number }}</a></td><td>{{ $contract->salesOrder->sales_order_number ?? '—' }}</td><td>{{ $contract->contract_type ?: '—' }}</td><td>{{ $contract->start_date?->format('d M Y') ?? '—' }}</td><td>{{ $contract->end_date?->format('d M Y') ?? '—' }}</td><td><span class="contract-status {{ strtolower($contract->status) }}">{{ ucfirst($contract->status) }}</span></td><td>@if($contract->document_path)<a href="{{ route('contracts.download',$contract->contract_id) }}">Download PDF</a>@else<span class="muted">No document</span>@endif</td>
<td><div class="contract-table-actions"><a class="contract-icon-action view" title="View" aria-label="View contract" href="{{ route('contracts.show',$contract->contract_id) }}"><svg viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></a><a class="contract-icon-action edit" title="Edit" aria-label="Edit contract" href="{{ route('contracts.edit',$contract->contract_id) }}"><svg viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg></a><button type="button" class="contract-icon-action delete" aria-label="Delete contract" title="Delete" data-delete-contract data-contract-name="{{ $contract->contract_number }}" data-contract-form="delete-contract-{{ $contract->contract_id }}"><svg viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6M10 11v5m4-5v5"/></svg></button><form method="POST" id="delete-contract-{{ $contract->contract_id }}" action="{{ route('contracts.destroy',$contract->contract_id) }}" hidden>@csrf @method('DELETE')</form></div></td></tr>
@empty<tr><td colspan="9" class="contract-empty">No contracts match your filters.</td></tr>@endforelse
</tbody></table></div>
<div class="contract-table-footer"><span>Showing {{ $contracts->firstItem() ?? 0 }}–{{ $contracts->lastItem() ?? 0 }} of {{ $contracts->total() }} Contracts</span>@if($contracts->hasPages())<div>{{ $contracts->links() }}</div>@endif</div>
</div></div>
<div id="contractDeleteModal" class="contract-delete-modal" hidden aria-hidden="true"><div class="contract-delete-backdrop" data-contract-close></div><div class="contract-delete-dialog" role="dialog" aria-modal="true" aria-labelledby="contractDeleteHeading"><div class="contract-delete-symbol">!</div><h3 id="contractDeleteHeading">Delete Contract?</h3><p>Are you sure you want to delete <strong id="contractDeleteName"></strong>?</p><small>This action cannot be undone.</small><div class="contract-delete-actions"><button type="button" class="btn" data-contract-close>Cancel</button><button type="button" class="btn contract-danger-btn" id="contractConfirmDelete">Delete Contract</button></div></div></div>
@include('contracts._style')
<script>
document.addEventListener('DOMContentLoaded',()=>{
 const modal=document.getElementById('contractDeleteModal'),name=document.getElementById('contractDeleteName'),confirm=document.getElementById('contractConfirmDelete');let pending=null,trigger=null;
 function close(){modal.hidden=true;modal.setAttribute('aria-hidden','true');pending=null;trigger?.focus();}
 document.querySelectorAll('[data-delete-contract]').forEach(btn=>btn.addEventListener('click',()=>{pending=document.getElementById(btn.dataset.contractForm);if(!pending)return;trigger=btn;name.textContent=btn.dataset.contractName;modal.hidden=false;modal.setAttribute('aria-hidden','false');modal.querySelector('[data-contract-close]:last-of-type')?.focus();}));
 modal.querySelectorAll('[data-contract-close]').forEach(el=>el.addEventListener('click',close));
 document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!modal.hidden)close();});
 confirm.addEventListener('click',()=>{if(!pending)return;confirm.disabled=true;pending.submit();});
});
</script>
@endsection
