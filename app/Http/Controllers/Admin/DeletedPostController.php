<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\ModerationService;
use App\Support\Pagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class DeletedPostController extends Controller
{
    public function __construct(private ModerationService $moderation) {}

    public function index(): View
    {
        return view('admin.posts.deleted', ['posts' => $this->moderation->deletedPosts()->onEachSide(Pagination::LINKS_ON_EACH_SIDE)]);
    }

    public function restore(Post $post): RedirectResponse
    {
        $this->moderation->restorePost($post);

        return back()->with('status', 'Post restored.');
    }
}
