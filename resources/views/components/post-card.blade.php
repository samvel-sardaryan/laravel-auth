@props(['post'])

<article class="post">
    <h3><a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></h3>
    <p class="meta">
        by <a href="{{ route('users.show', $post->user) }}">{{ $post->user->name }}</a>
        in <a href="{{ route('posts.index', ['category' => $post->category->slug]) }}">{{ $post->category->name }}</a>
        {{ $post->created_at->diffForHumans() }}
        @if ($post->status !== \App\Enums\PostStatus::Published)
            <span class="status-badge">{{ $post->status->value }}</span>
        @endif
    </p>
    <div>{!! nl2br(e($post->content)) !!}</div>
    @if ($post->images->isNotEmpty())
        <p class="images">
            @foreach ($post->images as $image)
                <img src="{{ Storage::url($image->path) }}" alt="" width="160">
            @endforeach
        </p>
    @endif
    @if ($post->tags->isNotEmpty())
        <p class="tags">
            @foreach ($post->tags as $tag)
                <a href="{{ route('posts.index', ['tag' => $tag->name]) }}">#{{ $tag->name }}</a>
            @endforeach
        </p>
    @endif
    <p class="social">
        {{ $post->likers_count }} {{ Str::plural('like', $post->likers_count) }}
        &middot;
        <a href="{{ route('posts.show', $post) }}#comments">{{ $post->comments_count }} {{ Str::plural('comment', $post->comments_count) }}</a>
    </p>
    @canany(['update', 'delete'], $post)
        <p class="actions">
            @can('update', $post)
                <a href="{{ route('posts.edit', $post) }}">Edit</a>
            @endcan
            @can('delete', $post)
                <form method="post" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            @endcan
        </p>
    @endcanany
</article>
