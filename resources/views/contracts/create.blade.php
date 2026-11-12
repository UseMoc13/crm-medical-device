@extends('layouts.app')
@section('title', 'Create Contract')
@section('content')
<div class="page-head"><div><h1>Create Contract</h1><p>Register a contract and associate it with a sales order.</p></div><div class="actions"><a href="{{ route('contracts.index') }}" class="btn">← Back to Contracts</a></div></div>
@if($errors->any())<div class="alert danger"><strong>Please correct the following errors:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@include('contracts._form')
@endsection
