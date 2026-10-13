@extends('layouts.app')
@section('title', 'Reset password')
@section('content')
<h1>Choose a new password</h1>
<form method="post" action="{{ route('password.update') }}" novalidate>
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <x-field-error field="token" />
    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required>
        <x-field-error field="email" />
    </div>
    <div class="field">
        <label for="password">New password</label>
        <input type="password" id="password" name="password" required>
        <x-field-error field="password" />
    </div>
    <div class="field">
        <label for="password_confirmation">Confirm new password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>
    </div>
    <button type="submit">Reset password</button>
</form>
@endsection
