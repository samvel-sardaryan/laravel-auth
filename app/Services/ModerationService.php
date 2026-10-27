<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\ActivityRepository;
use App\Repositories\Contracts\CommentRepository;
use App\Repositories\Contracts\PostRepository;
use App\Repositories\Contracts\ReportRepository;
use App\Support\Pagination;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class ModerationService
{
    public function __construct(private ReportRepository $reports, private PostRepository $posts, private CommentRepository $comments, private ActivityRepository $activities) {}

    public function report(Post|Comment $target, User $reporter, string $reason): bool
    {
        return $this->reports->file($target, $reporter, $reason);
    }

    public function queue(): LengthAwarePaginator
    {
        return $this->reports->paginateQueue(Pagination::MODERATION_QUEUE);
    }

    public function hidePost(Post $post): void
    {
        $this->posts->delete($post);
        $this->activities->record('post.deleted_by_moderator', $post);
    }

    public function hideComment(Comment $comment): void
    {
        $this->comments->delete($comment);
        $this->activities->record('comment.deleted_by_moderator', $comment);
    }

    public function deletedPosts(): LengthAwarePaginator
    {
        return $this->posts->paginateTrashed(Pagination::DELETED_POSTS);
    }

    public function restorePost(Post $post): void
    {
        $this->posts->restore($post);
        $this->activities->record('post.restored', $post);
    }
}
