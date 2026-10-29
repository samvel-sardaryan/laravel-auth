<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

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
        return $this->categories->create($data);
    }

    public function update(Category $category, array $data): void
    {
        $this->categories->update($category, $data);
    }

    public function delete(Category $category): bool
    {
        if ($category->is_default) {
            return false;
        }

        $this->categories->deleteMovingPostsToDefault($category);

        return true;
    }
}
