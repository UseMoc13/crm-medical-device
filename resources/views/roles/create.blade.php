@extends('layouts.app')
@section('title', 'Create Role')
@section('content')
<div class="page-head"><div><h1>Create Role</h1><p>Set up role information and access by module.</p></div><div class="actions">
{{-- A7105:roles.index --}}
@if(\App\Support\CrmViewAction::allows('roles.index'))
<a class="btn" href="{{ route('roles.index') }}">← Back to Roles</a>
@endif
{{-- /A7105 --}}
</div></div>
@if($errors->any())<div class="alert error"><strong>Please review the following fields:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

{{-- A7105:roles.store --}}
@if(\App\Support\CrmViewAction::allows('roles.store'))
<form method="POST" action="{{ route('roles.store') }}">@csrf
@include('roles._form')
</form>
@endif
{{-- /A7105 --}}

@endsection
