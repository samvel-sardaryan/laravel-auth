@extends('layouts.app')
@section('title', 'Moderation')
@section('content')
<h1>Moderation queue</h1>
<p class="hint">Nothing has been reported.</p>
<a href="{{ route('dashboard') }}">Back to dashboard</a>
@endsection
