<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class CommentService
{
    public function __construct(private CommentRepositoryInterface $comments, private ActivityRepositoryInterface $activities) {}

    public function threadFor(Post $post): Collection
    {
        return $this->comments->threadFor($post);
    }

    public function add(Post $post, User $author, string $content, ?int $parentId): Comment
    {
        try {
            if ($parentId) {
                $parentId = $this->comments->topLevelIdOf($parentId);
            }

            return $this->comments->create($post, $author, $content, $parentId);
        } catch (Throwable $exception) {
            Log::error('Adding comment failed', ['post_id' => $post->id, 'user_id' => $author->id, 'parent_id' => $parentId, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    public function update(Comment $comment, array $data): void
    {
        try {
            $this->comments->update($comment, $data);
        } catch (Throwable $exception) {
            Log::error('Updating comment failed', ['comment_id' => $comment->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    public function delete(Comment $comment, User $by): void
    {
        $byAuthor = $by->id === $comment->user_id;

        try {
            $this->comments->delete($comment);
            $this->activities->record($byAuthor ? 'comment.deleted' : 'comment.deleted_by_moderator', $comment);
        } catch (Throwable $exception) {
            Log::error('Deleting comment failed', ['comment_id' => $comment->id, 'by' => $by->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
