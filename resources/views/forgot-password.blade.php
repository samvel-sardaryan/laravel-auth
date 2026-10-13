@extends('layouts.app')
@section('title', 'Forgot password')
@section('content')
<h1>Forgot your password?</h1>
<p>Enter your email and we'll send you a link to choose a new password.</p>
<form method="post" action="{{ route('password.email') }}" novalidate>
    @csrf
    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        <x-field-error field="email" />
    </div>
    <button type="submit">Send reset link</button>
</form>
<p>
    <a href="{{ route('login') }}">Back to login</a>
</p>
@endsection
