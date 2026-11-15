@extends('layouts.app')
@section('title', 'Create To-Do')
@section('content')
<div class="page-head"><div><h1>Create To-Do</h1><p>Plan a new personal or work task.</p></div><div class="actions"><a href="{{ route('todos.index') }}" class="btn">&larr; Back to To-Do</a></div></div>
<section class="card todo-create-card"><div class="card-head"><div><h3>New Task</h3><p>Fill in the details and review the live preview.</p></div></div><div class="card-body"><form id="todo-form" method="POST" action="{{ route('todos.store') }}">@csrf @include('todos._form')</form></div></section>
@include('todos._style')
@endsection
