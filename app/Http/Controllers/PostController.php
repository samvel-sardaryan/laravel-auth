<?php

namespace App\Http\Controllers;

use App\Data\PostFilters;
use App\Enums\PostStatus;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Services\CategoryService;
use App\Services\CommentService;
use App\Services\PostService;
use App\Support\Pagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final readonly class PostController extends Controller
{
    public function __construct(private CategoryService $categories, private PostService $posts, private CommentService $comments) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = PostFilters::fromRequest($request);
        $posts = $this->posts->feed($filters, $request->user())
            ->onEachSide(Pagination::LINKS_ON_EACH_SIDE)
            ->withQueryString();

        return view('posts.index', ['posts' => $posts, 'categories' => $this->categories->allOrdered()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('posts.form', ['post' => new Post, 'categories' => $this->categories->allOrdered(), 'statuses' => PostStatus::cases()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $request): RedirectResponse
    {
        $post = $this->posts->create($request->user(), $request->validated(), $request->file('images', []));

        return redirect()->route('posts.show', $post)->with('status', 'Post created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post): View
    {
        Gate::authorize('view', $post);
        $post = $this->posts->forShow($post, $request->user());
        $comments = $this->comments->threadFor($post);

        return view('posts.show', ['post' => $post, 'comments' => $comments]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post): View
    {
        return view('posts.form', ['post' => $this->posts->forEdit($post), 'categories' => $this->categories->allOrdered(), 'statuses' => PostStatus::cases()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $this->posts->update($post, $request->validated(), $request->file('images', []), $request->validated('remove_images', []));

        return redirect()->route('posts.show', $post)->with('status', 'Post updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $this->posts->delete($post);

        return redirect()->route('posts.index')->with('status', 'Post deleted.');
    }
}
