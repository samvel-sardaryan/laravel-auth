<?php

namespace App\Policies;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final readonly class PostPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Post $post): Response
    {
        if ($post->status === PostStatus::Published) {
            return Response::allow();
        }

        if ($user && ($user->id === $post->user_id || $user->hasPermission('manage_posts'))) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_post');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->hasPermission('manage_posts');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->hasPermission('manage_posts');
    }
}
