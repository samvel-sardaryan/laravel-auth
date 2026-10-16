@extends('layouts.app')
@section('title', 'Posts')
@section('content')
<h1>Posts</h1>

@can('create_post')
    <p><a href="{{ route('posts.create') }}">New post</a></p>
@endcan

<form method="get" action="{{ route('posts.index') }}" class="filters">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search posts">
    <select name="category">
        <option value="">All categories</option>
        @foreach ($categories as $category)
            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
        @endforeach
    </select>
    <input type="text" name="tag" value="{{ request('tag') }}" placeholder="Tag">
    <select name="sort">
        @foreach (['new' => 'Newest', 'liked' => 'Most liked', 'commented' => 'Most discussed'] as $key => $label)
            <option value="{{ $key }}" @selected(request('sort', 'new') === $key)>{{ $label }}</option>
        @endforeach
    </select>
    @if (request('author'))
        <input type="hidden" name="author" value="{{ request('author') }}">
    @endif
    <button type="submit">Search</button>
    <a href="{{ route('posts.index') }}">Clear</a>
</form>

<x-field-error field="reason" />

@forelse ($posts as $post)
    <x-post-card :post="$post" />
@empty
    <p class="hint">No posts match those filters.</p>
@endforelse

{{ $posts->links('partials.pagination') }}

@if ($posts->total() > 0)
    <p class="count">{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}, page {{ $posts->currentPage() }} of {{ $posts->lastPage() }}</p>
@endif
@endsection
