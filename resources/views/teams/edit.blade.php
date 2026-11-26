@extends('layouts.app')
@section('title', 'Edit Team')
@section('content')
<div class="page-head"><div><h1>Edit Team</h1><p>Update team identity, leader and individual member selections.</p></div><div class="actions">
{{-- A7105:teams.show --}}
@if(\App\Support\CrmViewAction::allows('teams.show'))
<a href="{{ route('teams.show', $team) }}" class="btn">← Back to Team</a>
@endif
{{-- /A7105 --}}
</div></div>
@if($errors->any())<div class="alert error"><strong>Please review the following fields:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

{{-- A7105:teams.update --}}
@if(\App\Support\CrmViewAction::allows('teams.update'))
<form id="crmTeamForm" action="{{ route('teams.update', $team) }}" method="POST">@csrf @method('PUT')
    @include('teams._form')
</form>
@endif
{{-- /A7105 --}}

@include('teams._style')
@endsection
