@extends('layouts.app')
@section('title', 'Edit Role')
@section('content')
<div class="page-head"><div><h1>Edit Role</h1><p>Update role information and access by module.</p></div><div class="actions"><a class="btn" href="{{ route('roles.show', $role) }}">← Back to Role</a></div></div>
@if($errors->any())<div class="alert error"><strong>Please review the following fields:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('roles.update', $role) }}">@csrf @method('PUT')
@include('roles._form')
</form>
@endsection
