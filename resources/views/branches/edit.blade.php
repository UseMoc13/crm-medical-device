@extends('layouts.app')
@section('title', 'Edit Branch')
@section('content')
<div class="page-head">
    <div><h1>Edit Branch</h1><p>Update branch information and operational status.</p></div>
    <div class="actions">
{{-- A7105:branches.show --}}
@if(\App\Support\CrmViewAction::allows('branches.show'))
<a href="{{ route('branches.show', $branch) }}" class="btn">← Back to Branch</a>
@endif
{{-- /A7105 --}}
</div>
</div>
@include('branches._form')
@include('branches._style')
@endsection
