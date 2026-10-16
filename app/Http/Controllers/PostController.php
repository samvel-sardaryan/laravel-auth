<?php

namespace App\Http\Controllers;

use App\Actions\SavePost;
use App\Enums\PostStatus;
use App\Http\Requests\PostRequest;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $posts = Post::published()
            ->filtered($request->only('q', 'category', 'tag', 'author'))
            ->withCardData($request->user())
            ->sorted($request->query('sort'))
            ->paginate(10)
            ->onEachSide(1)
            ->withQueryString();

        return view('posts.index', ['posts' => $posts, 'categories' => Category::orderBy('name')->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('posts.form', ['post' => new Post, 'categories' => Category::orderBy('name')->get(), 'statuses' => PostStatus::cases()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $request, SavePost $save): RedirectResponse
    {
        $post = new Post;
        $post->user()->associate($request->user());
        $save->handle($post, $request->validated(), $request->file('images', []), []);
        Activity::record('post.created', $post);

        return redirect()->route('posts.show', $post)->with('status', 'Post created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post): View
    {
        Gate::authorize('view', $post);
        $post->load(['user', 'category', 'tags', 'images'])->loadCount(['likers', 'comments']);
        $post->loadExists(['likers as liked' => fn($q) => $q->where('post_likes.user_id', $request->user()?->id)]);
        $comments = $post->comments()->whereNull('parent_id')->withTrashed()->where(fn(Builder $q) => $q->whereNull('deleted_at')->orWhereHas('replies'))
            ->with(['user', 'replies' => fn($q) => $q->with('user')->oldest()])->oldest()->get();

        return view('posts.show', ['post' => $post, 'comments' => $comments]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post): View
    {
        return view('posts.form', ['post' => $post->load('tags', 'images'), 'categories' => Category::orderBy('name')->get(), 'statuses' => PostStatus::cases()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostRequest $request, Post $post, SavePost $save): RedirectResponse
    {
        $save->handle($post, $request->validated(), $request->file('images', []), $request->validated('remove_images', []));
        Activity::record('post.updated', $post);

        return redirect()->route('posts.show', $post)->with('status', 'Post updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();
        Activity::record('post.deleted', $post);

        return redirect()->route('posts.index')->with('status', 'Post deleted.');
    }
}
