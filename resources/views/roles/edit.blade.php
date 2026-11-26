@extends('layouts.app')
@section('title', 'Edit Role')
@section('content')
<div class="page-head"><div><h1>Edit Role</h1><p>Update role information and access by module.</p></div><div class="actions">
{{-- A7105:roles.show --}}
@if(\App\Support\CrmViewAction::allows('roles.show'))
<a class="btn" href="{{ route('roles.show', $role) }}">← Back to Role</a>
@endif
{{-- /A7105 --}}
</div></div>
@if($errors->any())<div class="alert error"><strong>Please review the following fields:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

{{-- A7105:roles.update --}}
@if(\App\Support\CrmViewAction::allows('roles.update'))
<form method="POST" action="{{ route('roles.update', $role) }}">@csrf @method('PUT')
@include('roles._form')
</form>
@endif
{{-- /A7105 --}}

@endsection
