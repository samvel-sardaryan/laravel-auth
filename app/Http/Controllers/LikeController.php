<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final readonly class LikeController extends Controller
{
    public function __construct(private PostService $posts) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('view', $post);
        $this->posts->toggleLike($post, $request->user());

        return back();
    }
}
