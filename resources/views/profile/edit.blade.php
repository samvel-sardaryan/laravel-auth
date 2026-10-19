@extends('layouts.app')
@section('title', 'Profile')
@section('content')
<h1>Profile</h1>

<h2>Account details</h2>
<form method="post" action="{{ route('profile.update') }}" novalidate>
    @csrf
    @method('PATCH')
    <div class="field">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}">
        <x-field-error field="name" />
    </div>
    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}">
        <x-field-error field="email" />
        <p class="hint">Changing your email requires verifying the new address.</p>
    </div>
    <button type="submit">Save</button>
</form>

<h2>Change password</h2>
<form method="post" action="{{ route('profile.password') }}" novalidate>
    @csrf
    @method('PATCH')
    <div class="field">
        <label for="current_password">Current password</label>
        <input type="password" id="current_password" name="current_password">
        <x-field-error field="current_password" />
    </div>
    <div class="field">
        <label for="password">New password</label>
        <input type="password" id="password" name="password">
        <x-field-error field="password" />
    </div>
    <div class="field">
        <label for="password_confirmation">Confirm new password</label>
        <input type="password" id="password_confirmation" name="password_confirmation">
    </div>
    <button type="submit">Save</button>
</form>

<h2>Profile details</h2>
<div class="field">
    @if ($profile->avatar)
    <img src="{{ Storage::url($profile->avatar) }}" alt="Profile picture" width="120" height="120">
    <form method="post" action="{{ route('profile.avatar.destroy') }}">
        @csrf
        @method('DELETE')
        <button type="submit">Remove picture</button>
    </form>
    @else
    <span class="avatar-placeholder">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
    <p class="hint">No profile picture yet.</p>
    @endif
</div>

<form method="post" action="{{ route('profile.details') }}" enctype="multipart/form-data" novalidate>
    @csrf
    @method('PATCH')
    <div class="field">
        <label for="first_name">First name</label>
        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $profile->first_name) }}">
        <x-field-error field="first_name" />
    </div>
    <div class="field">
        <label for="last_name">Last name</label>
        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $profile->last_name) }}">
        <x-field-error field="last_name" />
    </div>
    <div class="field">
        <label for="phone">Phone</label>
        <input type="tel" id="phone" name="phone" value="{{ old('phone', $profile->phone) }}">
        <p class="hint">Digits, spaces and + ( ) - only. {{ \App\Models\Profile::PHONE_MIN_DIGITS }} to {{ \App\Models\Profile::PHONE_MAX_DIGITS }} digits.</p>
        <x-field-error field="phone" />
    </div>
    <div class="field">
        <label for="location">Location</label>
        <input type="text" id="location" name="location" value="{{ old('location', $profile->location) }}">
        <x-field-error field="location" />
    </div>
    <div class="field">
        <label for="bio">Bio</label>
        <textarea id="bio" name="bio">{{ old('bio', $profile->bio) }}</textarea>
        <x-field-error field="bio" />
    </div>
    <div class="field">
        <label for="date_of_birth">Date of birth</label>
        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $profile->date_of_birth?->format('Y-m-d')) }}">
        <x-field-error field="date_of_birth" />
    </div>
    <div class="field">
        <label for="avatar">Profile picture</label>
        <input type="file" id="avatar" name="avatar" accept="image/*">
        <p class="hint">JPG, PNG or WEBP, up to {{ \App\Models\Profile::AVATAR_MAX_KILOBYTES / 1024 }} MB. Leave empty to keep the current one.</p>
        <x-field-error field="avatar" />
    </div>
    <button type="submit">Save</button>
</form>

@isset($posts)
<h2>My posts</h2>
@can('create_post')
<p><a href="{{ route('posts.create') }}">New post</a></p>
@endcan
@forelse ($posts as $post)
<p>
    <a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
    <span class="status-badge">{{ $post->status->value }}</span>
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
</p>
@empty
<p class="hint">You have not posted yet.</p>
@endforelse
{{ $posts->links('partials.pagination') }}
@endisset

<p><a href="{{ route('users.show', $user) }}">See your public profile</a></p>
<a href="{{ route('dashboard') }}">Back to dashboard</a>
@endsection