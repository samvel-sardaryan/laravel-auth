<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use App\Repositories\Contracts\CategoryRepository;

final readonly class EloquentCategoryRepository implements CategoryRepository
{
    public function allWithPostCounts(): Collection
    {
        return Category::withCount('posts')->orderBy('name')->get();
    }

    public function allOrdered(): Collection
    {
        return Category::orderBy('name')->get();
    }

    public function create(array $attributes): Category
    {
        return Category::create($attributes);
    }

    public function update(Category $category, array $attributes): void
    {
        $category->update($attributes);
    }

    public function deleteMovingPostsToDefault(Category $category): void
    {
        $default = Category::where('is_default', true)->firstOrFail();
        DB::transaction(function () use ($category, $default) {
            Post::withTrashed()->where('category_id', $category->id)->update(['category_id' => $default->id]);
            $category->delete();
        });
    }
}
