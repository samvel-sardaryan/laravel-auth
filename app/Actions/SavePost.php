<?php

namespace App\Actions;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SavePost
{
    public function handle(Post $post, array $data, array $newImages, array $removeImageIds): Post
    {
        $paths = array_map(fn ($file) => $file->store('posts', 'public'), $newImages);
        $oldPaths = [];
        try {
            DB::transaction(function () use ($post, $data, $paths, $removeImageIds, &$oldPaths) {
                $post->fill(Arr::only($data, ['title', 'content', 'status', 'category_id']))->save();
                $post->tags()->sync(Tag::idsFromInput($data['tags'] ?? null));
                $removed = $post->images()->whereIn('id', $removeImageIds)->get();
                $oldPaths = $removed->pluck('path')->all();
                $post->images()->whereIn('id', $removeImageIds)->delete();
                $post->images()->createMany(array_map(fn ($path) => ['path' => $path], $paths));
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($paths);
            throw $exception;
        }
        Storage::disk('public')->delete($oldPaths);

        return $post;
    }
}
