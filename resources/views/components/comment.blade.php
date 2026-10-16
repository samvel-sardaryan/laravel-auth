@props(['comment', 'post'])

@php
    $canReply = auth()->user()?->hasVerifiedEmail() && $post->status === \App\Enums\PostStatus::Published;
@endphp

<div class="comment" id="comment-{{ $comment->id }}">
    @if ($comment->trashed())
        <p class="meta">[deleted]</p>
    @else
        <p class="meta">
            <a href="{{ route('users.show', $comment->user) }}">{{ $comment->user->name }}</a>
            {{ $comment->created_at->diffForHumans() }}
            @if ($comment->updated_at->gt($comment->created_at))
                <span class="hint">(edited)</span>
            @endif
        </p>
        <div>{!! nl2br(e($comment->content)) !!}</div>

        @if (auth()->user()?->hasVerifiedEmail() && auth()->id() !== $comment->user_id)
            <form method="post" action="{{ route('reports.comment', $comment) }}" class="report">
                @csrf
                <input type="text" name="reason" maxlength="255" required placeholder="Report this comment: why?" aria-label="Report reason">
                <button type="submit">Report</button>
            </form>
        @endif

        <p class="actions">
            @can('update', $comment)
                <a href="{{ route('comments.edit', $comment) }}">Edit</a>
            @endcan
            @can('delete', $comment)
                <form method="post" action="{{ route('comments.destroy', $comment) }}" onsubmit="return confirm('Delete this comment?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            @endcan
        </p>

        @if ($canReply)
            <details>
                <summary>Reply</summary>
                <form method="post" action="{{ route('comments.store', $post) }}" novalidate>
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    <textarea name="content" rows="3" aria-label="Your reply">{{ $comment->parent_id ? '@' . $comment->user->name . ' ' : '' }}</textarea>
                    <button type="submit">Reply</button>
                </form>
            </details>
        @endif
    @endif

    @if ($comment->relationLoaded('replies') && $comment->replies->isNotEmpty())
        <div class="replies">
            @foreach ($comment->replies as $reply)
                <x-comment :comment="$reply" :post="$post" />
            @endforeach
        </div>
    @endif
</div>
