@extends('layouts.app')
@section('title', 'Access denied')
@section('content')
<h1>{{ $exception->getMessage() ?: "You don't have permission to access this page." }}</h1>
<a href="{{ route('dashboard') }}">Go to dashboard</a>
@endsection