@extends('layouts.app')
@section('title', 'Create Branch')
@section('content')
<div class="page-head">
    <div><h1>Create Branch</h1><p>Register a new branch within the CRM system.</p></div>
    <div class="actions">
{{-- A7105:branches.index --}}
@if(\App\Support\CrmViewAction::allows('branches.index'))
<a href="{{ route('branches.index') }}" class="btn">← Back to Branches</a>
@endif
{{-- /A7105 --}}
</div>
</div>
@include('branches._form')
@include('branches._style')
@endsection
