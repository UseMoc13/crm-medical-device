@extends('layouts.app')
@section('title', 'Create Team')
@section('content')
<div class="page-head"><div><h1>Create Team</h1><p>Configure a permanent operational team or a cross-functional project team.</p></div><div class="actions">
{{-- A7105:teams.index --}}
@if(\App\Support\CrmViewAction::allows('teams.index'))
<a href="{{ route('teams.index') }}" class="btn">← Back to Teams</a>
@endif
{{-- /A7105 --}}
</div></div>
@if($errors->any())<div class="alert error"><strong>Please review the following fields:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

{{-- A7105:teams.store --}}
@if(\App\Support\CrmViewAction::allows('teams.store'))
<form id="crmTeamForm" action="{{ route('teams.store') }}" method="POST">@csrf
    @include('teams._form')
</form>
@endif
{{-- /A7105 --}}

@include('teams._style')
@endsection
