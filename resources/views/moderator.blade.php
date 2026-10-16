@extends('layouts.app')
@section('title', 'Moderation')
@section('content')
<h1>Moderation queue</h1>

@can('view_users')
    <p><a href="{{ route('admin.users.index') }}">Users</a></p>
@endcan

@if ($reports->isEmpty())
    <p class="hint">Nothing has been reported.</p>
@else
    <table>
        <thead>
            <tr>
                <th>Reported</th>
                <th>Type</th>
                <th>Target</th>
                <th>Reason</th>
                <th>By</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reports as $report)
                @php
                    $target = $report->reportable;
                    $isPost = $target instanceof \App\Models\Post;
                @endphp
                <tr>
                    <td>{{ $report->created_at->diffForHumans() }}</td>
                    <td>{{ $report->reportable_type }}</td>
                    <td>
                        @if ($target === null)
                            <span class="hint">No longer exists</span>
                        @elseif ($isPost)
                            <a href="{{ route('posts.show', $target) }}">{{ $target->title }}</a>
                        @else
                            <a href="{{ route('posts.show', $target->post_id) }}#comment-{{ $target->id }}">{{ Str::limit($target->content, 60) }}</a>
                        @endif
                    </td>
                    <td>{{ $report->reason }}</td>
                    <td>{{ $report->reporter->name }}</td>
                    <td>
                        @if ($target !== null && $isPost)
                            @can('moderate_posts')
                                <form method="post" action="{{ route('moderation.posts.hide', $target) }}" onsubmit="return confirm('Hide this post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Hide</button>
                                </form>
                            @endcan
                        @elseif ($target !== null)
                            @can('moderate_comments')
                                <form method="post" action="{{ route('moderation.comments.hide', $target) }}" onsubmit="return confirm('Hide this comment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Hide</button>
                                </form>
                            @endcan
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $reports->links('partials.pagination') }}
@endif

<a href="{{ route('dashboard') }}">Back to dashboard</a>
@endsection
