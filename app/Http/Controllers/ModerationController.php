<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Services\ModerationService;
use App\Support\Pagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class ModerationController extends Controller
{
    public function __construct(private ModerationService $moderation) {}

    public function index(): View
    {
        return view('moderator', ['reports' => $this->moderation->queue()->onEachSide(Pagination::LINKS_ON_EACH_SIDE)]);
    }

    public function hidePost(Post $post): RedirectResponse
    {
        $this->moderation->hidePost($post);

        return back()->with('status', 'Post hidden.');
    }

    public function hideComment(Comment $comment): RedirectResponse
    {
        $this->moderation->hideComment($comment);

        return back()->with('status', 'Comment hidden.');
    }
}
