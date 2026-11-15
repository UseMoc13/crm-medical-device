@extends('layouts.app')
@section('title', 'Edit To-Do')
@section('content')
<div class="page-head"><div><h1>Edit To-Do</h1><p>Update task progress, deadline, or related Activity.</p></div><div class="actions"><a href="{{ route('todos.show', ['todo'=>$todo->getKey()]) }}" class="btn">&larr; Back to Details</a></div></div>
<section class="card todo-create-card"><div class="card-head"><div><h3>Edit Task</h3><p>Review all changes before saving.</p></div></div><div class="card-body"><form id="todo-form" method="POST" action="{{ route('todos.update', ['todo'=>$todo->getKey()]) }}">@csrf @method('PUT') @include('todos._form')</form></div></section>
@include('todos._style')
@endsection
