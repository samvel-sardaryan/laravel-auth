@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<h1>Dashboard</h1>
<div>
    <p><span>Hello</span> {{ $user->name }}</p>
    <p><span>Email:</span> {{ $user->email }}</p>
    <p><span>Created at:</span> {{ $user->created_at->diffForHumans() }}</p>
</div>
@endsection