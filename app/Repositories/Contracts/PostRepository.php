<?php

namespace App\Repositories\Contracts;

use App\Data\PostFilters;
use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;


interface PostRepository
{
    public function paginateFeed(PostFilters $filters, ?User $viewer, int $perPage): LengthAwarePaginator;
    public function paginatePublishedBy(User $author, ?User $viewer, int $perPage): LengthAwarePaginator;
    public function paginateBy(User $author, int $perPage): LengthAwarePaginator;
    public function paginateTrashed(int $perPage): LengthAwarePaginator;
    public function loadForShow(Post $post, ?User $viewer): Post;
    public function loadForEdit(Post $post): Post;
    public function create(User $author, array $attributes, ?string $tags, array $imagePaths): Post;
    public function update(Post $post, array $attributes, ?string $tags, array $newImagePaths, array $removeImageIds): array;
    public function delete(Post $post): void;
    public function restore(Post $post): void;
    public function toggleLike(Post $post, User $user): bool;

}
