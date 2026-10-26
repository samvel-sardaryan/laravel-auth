<?php

namespace App\Actions;

use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\PostRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class SavePost
{
    public function __construct(private PostRepository $posts) {}

    public function create(User $author, array $data, array $images): Post
    {
        $paths = array_map(fn ($file) => $file->store('posts', 'public'), $images);
        try {
            $post = $this->posts->create($author, Arr::only($data, ['title', 'content', 'status', 'category_id']), $data['tags'] ?? null, $paths);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($paths);
            throw $exception;
        }
        return $post;
    }

    public function update(Post $post, array $data, array $images, array $removeImageIds): Post
    {
        $paths = array_map(fn ($file) => $file->store('posts', 'public'), $images);
        try { 
            $oldPaths = $this->posts->update($post, Arr::only($data, ['title', 'content', 'status', 'category_id']), $data['tags'] ?? null, $paths, $removeImageIds); 
        }
        catch (Throwable $exception) {
            Storage::disk('public')->delete($paths);
            throw $exception;
        }
        Storage::disk('public')->delete($oldPaths);
        return $post;
    }
}
