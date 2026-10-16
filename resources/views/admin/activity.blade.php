@extends('layouts.app')
@section('title', 'Activity log')
@section('content')
<h1>Activity log</h1>

<form method="get" action="{{ route('admin.activity') }}" class="filters">
    <label for="user">User</label>
    <input type="search" id="user" name="user" value="{{ request('user') }}" placeholder="Name or email">
    <button type="submit">Search</button>
    <a href="{{ route('admin.activity') }}">Clear</a>
</form>

@if ($activities->isEmpty())
    <p class="hint">{{ request('user') ? 'No activity for that user.' : 'Nothing has been logged yet.' }}</p>
@else
    <table>
        <thead>
            <tr>
                <th>When</th>
                <th>Who</th>
                <th>Action</th>
                <th>Target</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($activities as $activity)
                @php($subject = $activity->subject)
                <tr>
                    <td>{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        @if ($activity->user)
                            <a href="{{ route('users.show', $activity->user) }}">{{ $activity->user->name }}</a>
                        @elseif (str_starts_with($activity->action, 'login.'))
                            <span class="hint">guest</span>
                        @else
                            <span class="hint">deleted user</span>
                        @endif
                    </td>
                    <td>{{ $activity->action }}</td>
                    <td>
                        @if ($activity->subject_type === null)
                            <span class="hint">&mdash;</span>
                        @elseif ($subject instanceof \App\Models\Post)
                            <a href="{{ route('posts.show', $subject) }}">post #{{ $subject->id }}</a>
                        @elseif ($subject instanceof \App\Models\Comment)
                            <a href="{{ route('posts.show', $subject->post_id) }}#comment-{{ $subject->id }}">comment #{{ $subject->id }}</a>
                        @elseif ($subject instanceof \App\Models\User)
                            <a href="{{ route('users.show', $subject) }}">user #{{ $subject->id }}</a>
                        @else
                            {{ $activity->subject_type }} #{{ $activity->subject_id }}
                        @endif
                    </td>
                    <td>{{ $activity->ip_address }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $activities->links('partials.pagination') }}

    <p class="count">{{ $activities->total() }} {{ Str::plural('entry', $activities->total()) }}, page {{ $activities->currentPage() }} of {{ $activities->lastPage() }}</p>
@endif

<a href="{{ route('admin.index') }}">Back to admin</a>
@endsection
