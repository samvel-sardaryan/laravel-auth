@extends('layouts.app')
@section('title', 'Register')
@section('content')
<form action="{{ route('register') }}" method="post" novalidate>
    @csrf
    <div class="field">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" required>
        <x-field-error field="name" />
    </div>
    <div class="field">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required>
        <x-field-error field="email" />
    </div>
    <div class="field">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
        <x-field-error field="password" />
    </div>
    <div class="field">
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" name="password_confirmation" id="password_confirmation" required>
    </div>
    <button type="submit">Register</button>
    <p>
        <a href="{{ route('login') }}">Already have an account? Login</a>
    </p>
</form>
@endsection