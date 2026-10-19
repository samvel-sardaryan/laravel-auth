<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Post;
use App\Support\Pagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeletedPostController extends Controller
{
    public function index(): View
    {
        return view('admin.posts.deleted', ['posts' => Post::onlyTrashed()->with(['user', 'category'])->latest('deleted_at')->paginate(Pagination::DELETED_POSTS)->onEachSide(Pagination::LINKS_ON_EACH_SIDE)]);
    }

    public function restore(Post $post): RedirectResponse
    {
        $post->restore();
        Activity::record('post.restored', $post);

        return back()->with('status', 'Post restored.');
    }
}
