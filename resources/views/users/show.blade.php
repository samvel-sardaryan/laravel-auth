@extends('layouts.app')
@section('title', $user->name)
@section('content')
@php
    $profile = $user->profile;
    $details = collect([
        'First name' => $profile?->first_name,
        'Last name' => $profile?->last_name,
        'Phone' => $profile?->phone,
        'Location' => $profile?->location,
        'Date of birth' => $profile?->date_of_birth?->format('Y-m-d'),
        'Bio' => $profile?->bio,
    ])->filter(fn ($value) => filled($value));
@endphp

<h1>{{ $user->name }}</h1>
<p><span>Role:</span> {{ $user->roles->pluck('label')->join(', ') }}</p>
<p><span>Joined:</span> {{ $user->created_at->format('Y-m-d') }}</p>

<div class="field">
    @if ($profile?->avatar)
        <img src="{{ Storage::url($profile->avatar) }}" alt="Profile picture" width="120" height="120">
    @else
        <span class="avatar-placeholder">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
    @endif
</div>

<h2>Profile details</h2>
@forelse ($details as $label => $value)
    <p><span>{{ $label }}:</span> {!! nl2br(e($value)) !!}</p>
@empty
    <p class="hint">This user has not filled in their profile yet.</p>
@endforelse

<a href="{{ url('/') }}">Home</a>
@endsection
