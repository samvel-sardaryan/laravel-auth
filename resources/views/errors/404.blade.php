@extends('layouts.app')
@section('title', 'Page not found')
@section('content')
<h1>Page not found.</h1>
<a href="{{ route('dashboard') }}">Go to dashboard</a>
@endsection