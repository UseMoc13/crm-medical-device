@extends('layouts.app')
@section('title', 'Edit Contract')
@section('content')
<div class="page-head"><div><h1>Edit Contract</h1><p>Update contract details and manage its linked document.</p></div><div class="actions">{{-- A7103:contracts.show --}}@if(\App\Support\CrmViewAction::allows('contracts.show'))
<a href="{{ route('contracts.show',$contract->contract_id) }}" class="btn">← Back to Contract Details</a>
@endif{{-- /A7103 --}}</div></div>
@if($errors->any())<div class="alert danger"><strong>Please correct the following errors:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@include('contracts._form')
@endsection
