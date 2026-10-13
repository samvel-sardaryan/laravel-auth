@extends('layouts.app')
@section('title', 'Moderation')
@section('content')
<h1>Moderation queue</h1>
<p class="hint">Nothing has been reported.</p>
@can('view_users')
    <p><a href="{{ route('admin.users.index') }}">Users</a></p>
@endcan
<a href="{{ route('dashboard') }}">Back to dashboard</a>
@endsection
