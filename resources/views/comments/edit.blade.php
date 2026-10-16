@extends('layouts.app')
@section('title', 'Edit comment')
@section('content')
<h1>Edit comment</h1>

<form method="post" action="{{ route('comments.update', $comment) }}" novalidate>
    @csrf
    @method('PUT')
    <div class="field">
        <label for="content">Comment</label>
        <textarea id="content" name="content" rows="4">{{ old('content', $comment->content) }}</textarea>
        <x-field-error field="content" />
    </div>
    <button type="submit">Save</button>
</form>

<a href="{{ route('posts.show', $comment->post_id) }}#comment-{{ $comment->id }}">Cancel</a>
@endsection
