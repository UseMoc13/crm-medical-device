@extends('layouts.app')
@section('title', 'Create Team')
@section('content')
<div class="page-head"><div><h1>Create Team</h1><p>Configure a permanent operational team or a cross-functional project team.</p></div><div class="actions"><a href="{{ route('teams.index') }}" class="btn">← Back to Teams</a></div></div>
@if($errors->any())<div class="alert error"><strong>Please review the following fields:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form id="crmTeamForm" action="{{ route('teams.store') }}" method="POST">@csrf
    @include('teams._form')
</form>
@include('teams._style')
@endsection
