<?php

namespace App\Repositories\Eloquent;

use App\Data\PostFilters;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\PostRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class EloquentPostRepository implements PostRepository
{
    public function paginateFeed(PostFilters $filters, ?User $viewer, int $perPage): LengthAwarePaginator
    {
        return Post::published()->filtered($filters)->withCardData($viewer)->sorted($filters->sort)->paginate($perPage);
    }

    public function paginatePublishedBy(User $author, ?User $viewer, int $perPage): LengthAwarePaginator
    {
        return $author->posts()->published()->withCardData($viewer)->latest()->paginate($perPage);
    }

    public function paginateBy(User $author, int $perPage): LengthAwarePaginator
    {
        return $author->posts()->latest()->paginate($perPage);
    }

    public function paginateTrashed(int $perPage): LengthAwarePaginator
    {
        return Post::onlyTrashed()->with(['user', 'category'])->latest('deleted_at')->paginate($perPage);
    }

    public function loadForShow(Post $post, ?User $viewer): Post
    {
        $post->load(['user', 'category', 'tags', 'images'])->loadCount(['likers', 'comments']);
        $post->loadExists(['likers as liked' => fn ($q) => $q->where('post_likes.user_id', $viewer?->id)]);
        return $post;
    }

    public function loadForEdit(Post $post): Post
    {
        return $post->load('tags', 'images');
    }

    public function create(User $author, array $attributes, ?string $tags, array $imagePaths): Post
    {
        return DB::transaction(function () use ($author, $attributes, $tags, $imagePaths) {
            $post = new Post($attributes);
            $post->user()->associate($author); 
            $post->save();
            $post->tags()->sync($this->tagIdsFrom($tags));
            $post->images()->createMany(array_map(fn ($path) => ['path' => $path], $imagePaths));
            return $post;
        });
    }

    public function update(Post $post, array $attributes, ?string $tags, array $newImagePaths, array $removeImageIds): array
    {
        return DB::transaction(function () use ($post, $attributes, $tags, $newImagePaths, $removeImageIds) {
            $post->fill($attributes)->save();
            $post->tags()->sync($this->tagIdsFrom($tags));
            $removedPaths = $post->images()->whereIn('id', $removeImageIds)->pluck('path')->all();
            $post->images()->whereIn('id', $removeImageIds)->delete();
            $post->images()->createMany(array_map(fn ($path) => ['path' => $path], $newImagePaths));
            return $removedPaths;
        });
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }

    public function restore(Post $post): void
    {
        $post->restore();
    }

    public function toggleLike(Post $post, User $user): bool
    {
        $inserted = DB::table('post_likes')->insertOrIgnore(['user_id' => $user->id, 'post_id' => $post->id]); 
        if ($inserted === 0) { 
            $post->likers()->detach($user->id); 
            return false; 
        }
        return true;
    }

    private function tagIdsFrom(?string $input): array 
    {
        return Str::of($input ?? '')
            ->explode(',')
            ->map(fn ($name) => mb_strtolower(trim($name)))
            ->filter(fn ($name) => $name !== '' && mb_strlen($name) <= Tag::NAME_MAX_LENGTH)
            ->unique()
            ->take(Tag::MAX_PER_POST)
            ->map(fn ($name) => Tag::firstOrCreate(['name' => $name])->id)
            ->values()->all();
    }
}
