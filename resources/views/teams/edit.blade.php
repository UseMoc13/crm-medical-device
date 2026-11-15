@extends('layouts.app')
@section('title', 'Edit Team')
@section('content')
<div class="page-head"><div><h1>Edit Team</h1><p>Update team identity, leader and individual member selections.</p></div><div class="actions"><a href="{{ route('teams.show', $team) }}" class="btn">← Back to Team</a></div></div>
@if($errors->any())<div class="alert error"><strong>Please review the following fields:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form id="crmTeamForm" action="{{ route('teams.update', $team) }}" method="POST">@csrf @method('PUT')
    @include('teams._form')
</form>
@include('teams._style')
@endsection
