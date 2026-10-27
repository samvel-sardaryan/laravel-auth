<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\ActivityRepository;
use App\Repositories\Contracts\CommentRepository;
use Illuminate\Database\Eloquent\Collection;

final readonly class CommentService
{
    public function __construct(private CommentRepository $comments, private ActivityRepository $activities) {}

    public function threadFor(Post $post): Collection
    {
        return $this->comments->threadFor($post);
    }

    public function add(Post $post, User $author, string $content, ?int $parentId): Comment
    {
        if ($parentId) {
            $parentId = $this->comments->topLevelIdOf($parentId);
        }
        return $this->comments->create($post, $author, $content, $parentId);
    }

    public function update(Comment $comment, array $data): void
    {
        $this->comments->update($comment, $data);
    }

    public function delete(Comment $comment, User $by): void
    {
        $byAuthor = $by->id === $comment->user_id;
        $this->comments->delete($comment);
        $this->activities->record($byAuthor ? 'comment.deleted' : 'comment.deleted_by_moderator', $comment);
    }
}
