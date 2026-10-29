<?php

namespace App\Repositories\Eloquent;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final readonly class EloquentCommentRepository implements CommentRepositoryInterface
{
    public function threadFor(Post $post): Collection
    {
        return $post->comments()
            ->whereNull('parent_id')
            ->withTrashed()->where(fn (Builder $q) => $q->whereNull('deleted_at')->orWhereHas('replies'))
            ->with(['user', 'replies' => fn ($q) => $q->with('user')->oldest()])
            ->oldest()->get();
    }

    public function topLevelIdOf(int $commentId): int
    {
        $comment = Comment::findOrFail($commentId);

        return $comment->parent_id ?? $comment->id;
    }

    public function create(Post $post, User $author, string $content, ?int $parentId): Comment
    {
        $comment = new Comment(['content' => $content, 'parent_id' => $parentId]);
        $comment->user()->associate($author);
        $post->comments()->save($comment);

        return $comment;
    }

    public function update(Comment $comment, array $attributes): void
    {
        $comment->update($attributes);
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}
