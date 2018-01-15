@extends('layouts.master')

@section('content')

    <h1>Welcome</h1>
    <p class="lead">
      test
    </p>
    <hr>

    <a href="/{{ route('tasks.index') }}" class="btn btn-info">View Tasks</a>
    <a href="/{{ route('tasks.create') }}" class="btn btn-primary">Add New Task</a>

@stop
