<?php

namespace App\Services;

use App\Data\PostFilters;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Support\Pagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class PostService
{
    public function __construct(private PostRepositoryInterface $posts, private ActivityRepositoryInterface $activities) {}

    public function feed(PostFilters $filters, ?User $viewer): LengthAwarePaginator
    {
        return $this->posts->paginateFeed($filters, $viewer, Pagination::FEED);
    }

    public function publishedBy(User $author, ?User $viewer): LengthAwarePaginator
    {
        return $this->posts->paginatePublishedBy($author, $viewer, Pagination::USER_POSTS);
    }

    public function ownPosts(User $author): LengthAwarePaginator
    {
        return $this->posts->paginateBy($author, Pagination::USER_POSTS);
    }

    public function forShow(Post $post, ?User $viewer): Post
    {
        return $this->posts->loadForShow($post, $viewer);
    }

    public function forEdit(Post $post): Post
    {
        return $this->posts->loadForEdit($post);
    }

    public function create(User $author, array $data, array $images): Post
    {
        $paths = array_map(fn ($file) => $file->store('posts', 'public'), $images);
        try {
            $post = $this->posts->create($author, Arr::only($data, ['title', 'content', 'status', 'category_id']), $data['tags'] ?? null, $paths);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($paths);
            throw $exception;
        }
        $this->activities->record('post.created', $post);

        return $post;
    }

    public function update(Post $post, array $data, array $images, array $removeImageIds): Post
    {
        $paths = array_map(fn ($file) => $file->store('posts', 'public'), $images);
        try {
            $oldPaths = $this->posts->update($post, Arr::only($data, ['title', 'content', 'status', 'category_id']), $data['tags'] ?? null, $paths, $removeImageIds);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($paths);
            throw $exception;
        }
        Storage::disk('public')->delete($oldPaths);
        $this->activities->record('post.updated', $post);

        return $post;
    }

    public function delete(Post $post): void
    {
        $this->posts->delete($post);
        $this->activities->record('post.deleted', $post);
    }

    public function toggleLike(Post $post, User $user): bool
    {
        return $this->posts->toggleLike($post, $user);
    }
}
