<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class CategoryController extends Controller
{
    public function __construct(private CategoryRepository $categories) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => $this->categories->allWithPostCounts(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->categories->create($request->validated());

        return back()->with('status', 'Category created successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categories->update($category, $request->validated());

        return back()->with('status', 'Category updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->is_default) {
            return back()->withErrors(['category' => 'Default category cannot be deleted']);
        }
        $this->categories->deleteMovingPostsToDefault($category);

        return back()->with('status', 'Category deleted successfully');
    }
}
