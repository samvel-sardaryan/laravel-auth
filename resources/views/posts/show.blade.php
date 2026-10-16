@extends('layouts.app')
@section('title', $post->title)
@section('content')
<x-field-error field="reason" />
<x-post-card :post="$post" />

<section class="comments" id="comments">
    <h2>Comments</h2>

    @forelse ($comments as $comment)
        <x-comment :comment="$comment" :post="$post" />
    @empty
        <p>No comments yet.</p>
    @endforelse

    @if ($post->status !== \App\Enums\PostStatus::Published)
        <p class="hint">Comments are closed on {{ $post->status->value }} posts.</p>
    @elseif (auth()->guest())
        <p><a href="{{ route('login') }}">Log in</a> to comment.</p>
    @elseif (! auth()->user()->hasVerifiedEmail())
        <p><a href="{{ route('verification.notice') }}">Verify your email</a> to comment.</p>
    @else
        <form method="post" action="{{ route('comments.store', $post) }}" id="comment-form" novalidate>
            @csrf
            <div class="field">
                <label for="content">Add a comment</label>
                <textarea id="content" name="content" rows="4">{{ old('content') }}</textarea>
                <x-field-error field="content" />
                <x-field-error field="parent_id" />
            </div>
            <button type="submit">Comment</button>
        </form>
    @endif
</section>

<a href="{{ route('posts.index') }}">Back to posts</a>
@endsection
