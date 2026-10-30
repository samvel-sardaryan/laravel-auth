<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\Contracts\CommentRepositoryInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\ReportRepositoryInterface;
use App\Support\Pagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class ModerationService
{
    public function __construct(private ReportRepositoryInterface $reports, private PostRepositoryInterface $posts, private CommentRepositoryInterface $comments, private ActivityRepositoryInterface $activities) {}

    public function report(Post|Comment $target, User $reporter, string $reason): bool
    {
        try {
            return $this->reports->file($target, $reporter, $reason);
        } catch (Throwable $exception) {
            Log::error('Filing report failed', ['target_type' => $target->getMorphClass(), 'target_id' => $target->id, 'reporter_id' => $reporter->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    public function queue(): LengthAwarePaginator
    {
        return $this->reports->paginateQueue(Pagination::MODERATION_QUEUE);
    }

    public function hidePost(Post $post): void
    {
        try {
            $this->posts->delete($post);
            $this->activities->record('post.deleted_by_moderator', $post);
        } catch (Throwable $exception) {
            Log::error('Hiding post failed', ['post_id' => $post->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    public function hideComment(Comment $comment): void
    {
        try {
            $this->comments->delete($comment);
            $this->activities->record('comment.deleted_by_moderator', $comment);
        } catch (Throwable $exception) {
            Log::error('Hiding comment failed', ['comment_id' => $comment->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    public function deletedPosts(): LengthAwarePaginator
    {
        return $this->posts->paginateTrashed(Pagination::DELETED_POSTS);
    }

    public function restorePost(Post $post): void
    {
        try {
            $this->posts->restore($post);
            $this->activities->record('post.restored', $post);
        } catch (Throwable $exception) {
            Log::error('Restoring post failed', ['post_id' => $post->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
