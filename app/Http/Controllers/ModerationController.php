<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Repositories\Contracts\ActivityRepository;
use App\Repositories\Contracts\PostRepository;
use App\Repositories\Contracts\CommentRepository;
use App\Repositories\Contracts\ReportRepository;
use App\Support\Pagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class ModerationController extends Controller
{
    public function __construct(private ActivityRepository $activities, private PostRepository $posts, private CommentRepository $comments, private ReportRepository $reports) {}

    public function index(): View
    {
        return view('moderator', ['reports' => $this->reports->paginateQueue(Pagination::MODERATION_QUEUE)->onEachSide(Pagination::LINKS_ON_EACH_SIDE)]);
    }

    public function hidePost(Post $post): RedirectResponse
    {
        $this->posts->delete($post);
        $this->activities->record('post.deleted_by_moderator', $post);

        return back()->with('status', 'Post hidden.');
    }

    public function hideComment(Comment $comment): RedirectResponse
    {
        $this->comments->delete($comment);
        $this->activities->record('comment.deleted_by_moderator', $comment);

        return back()->with('status', 'Comment hidden.');
    }
}
