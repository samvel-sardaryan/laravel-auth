<?php

namespace App\Repositories\Contracts;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface CommentRepositoryInterface
{
    public function threadFor(Post $post): Collection;

    public function topLevelIdOf(int $commentId): int;

    public function create(Post $post, User $author, string $content, ?int $parentId): Comment;

    public function update(Comment $comment, array $attributes): void;

    public function delete(Comment $comment): void;
}
