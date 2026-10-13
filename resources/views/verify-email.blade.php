@extends('layouts.app')
@section('title', 'Verify your email')
@section('content')
<h1>Verify your email</h1>
<p>We sent a verification link to <strong>{{ auth()->user()->email }}</strong>. Open it to finish setting up your account.</p>
<form method="post" action="{{ route('verification.send') }}" novalidate>
    @csrf
    <p>Didn't get the email?</p>
    <button type="submit">Resend verification email</button>
</form>
@endsection