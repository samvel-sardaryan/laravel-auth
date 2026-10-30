<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class CategoryService
{
    public function __construct(private CategoryRepositoryInterface $categories) {}

    public function allOrdered(): Collection
    {
        return $this->categories->allOrdered();
    }

    public function allWithPostCounts(): Collection
    {
        return $this->categories->allWithPostCounts();
    }

    public function create(array $data): Category
    {
        try {
            return $this->categories->create($data);
        } catch (Throwable $exception) {
            Log::error('Creating category failed', ['name' => $data['name'] ?? null, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    public function update(Category $category, array $data): void
    {
        try {
            $this->categories->update($category, $data);
        } catch (Throwable $exception) {
            Log::error('Updating category failed', ['category_id' => $category->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    public function delete(Category $category): bool
    {
        if ($category->is_default) {
            return false;
        }

        try {
            $this->categories->deleteMovingPostsToDefault($category);
        } catch (Throwable $exception) {
            Log::error('Deleting category failed', ['category_id' => $category->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }

        return true;
    }
}
