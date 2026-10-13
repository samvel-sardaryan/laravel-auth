@extends('layouts.app')
@section('title', 'Admin')
@section('content')
<h1>Admin</h1>
<ul>
    @can('view_users')
        <li><a href="{{ route('admin.users.index') }}">Users</a></li>
    @endcan
</ul>
<a href="{{ route('dashboard') }}">Back to dashboard</a>
@endsection
