@extends('layouts.app')
@section('title', 'Login')
@section('content')
<form method="post" action="{{ route('login') }}" novalidate>
    @csrf
    <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        @include('partials.field-error', ['field' => 'email'])
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        @include('partials.field-error', ['field' => 'password'])
    </div>
    <button type="submit">Login</button>
</form>
<p>
    <a href="{{ route('register') }}">Register</a>
</p>
@endsection