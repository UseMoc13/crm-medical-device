@extends('layouts.app')
@section('title', 'Create Role')
@section('content')
<div class="page-head"><div><h1>Create Role</h1><p>Set up role information and access by module.</p></div><div class="actions"><a class="btn" href="{{ route('roles.index') }}">← Back to Roles</a></div></div>
@if($errors->any())<div class="alert error"><strong>Please review the following fields:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('roles.store') }}">@csrf
@include('roles._form')
</form>
@endsection
