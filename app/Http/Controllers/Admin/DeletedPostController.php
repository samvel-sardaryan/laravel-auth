<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Repositories\Contracts\ActivityRepository;
use App\Repositories\Contracts\PostRepository;
use App\Support\Pagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class DeletedPostController extends Controller
{
    public function __construct(private ActivityRepository $activities, private PostRepository $posts) {}

    public function index(): View
    {
        return view('admin.posts.deleted', ['posts' => $this->posts->paginateTrashed(Pagination::DELETED_POSTS)->onEachSide(Pagination::LINKS_ON_EACH_SIDE)]);
    }

    public function restore(Post $post): RedirectResponse
    {
        $this->posts->restore($post);
        $this->activities->record('post.restored', $post);

        return back()->with('status', 'Post restored.');
    }
}
