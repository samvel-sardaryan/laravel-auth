@extends('layouts.app')
@section('title', 'Deleted posts')
@section('content')
<h1>Deleted posts</h1>

<p class="hint">
    Deleted posts are hidden everywhere, including from their author. Their images are kept on disk,
    so restoring a post brings them back.
</p>

@if ($posts->isEmpty())
    <p class="hint">Nothing has been deleted.</p>
@else
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Status</th>
                <th>Deleted</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($posts as $post)
                <tr>
                    <td>{{ $post->id }}</td>
                    <td>{{ $post->title }}</td>
                    <td><a href="{{ route('users.show', $post->user) }}">{{ $post->user->name }}</a></td>
                    <td>{{ $post->category->name }}</td>
                    <td>
                        {{ $post->status->value }}
                        @if ($post->status !== \App\Enums\PostStatus::Published)
                            <span class="hint">(stays off the feed when restored)</span>
                        @endif
                    </td>
                    <td>{{ $post->deleted_at->diffForHumans() }}</td>
                    <td>
                        <form method="post" action="{{ route('admin.posts.restore', $post) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit">Restore</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $posts->links('partials.pagination') }}
@endif

<a href="{{ route('admin.index') }}">Back to admin</a>
@endsection
