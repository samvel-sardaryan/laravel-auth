<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function allWithPostCounts(): Collection;

    public function allOrdered(): Collection;

    public function create(array $attributes): Category;

    public function update(Category $category, array $attributes): void;

    public function deleteMovingPostsToDefault(Category $category): void;
}
