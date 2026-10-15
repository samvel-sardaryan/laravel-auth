@extends('layouts.app')
@section('title', $post->title)
@section('content')
<x-post-card :post="$post" />

<section class="comments" id="comments">
    <h2>Comments</h2>
    <p class="hint">Comments arrive in Step 22.</p>
</section>

<a href="{{ route('posts.index') }}">Back to posts</a>
@endsection
