<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ModerationController extends Controller
{
    public function index(): View
    {
        return view('moderator', ['reports' => Report::with(['reporter', 'reportable'])->latest()->paginate(20)->onEachSide(1)]);
    }

    public function hidePost(Post $post): RedirectResponse
    {
        $post->delete();
        Activity::record('post.deleted_by_moderator', $post);
        return back()->with('status', 'Post hidden.');
    }

    public function hideComment(Comment $comment): RedirectResponse
    {
        $comment->delete();
        Activity::record('comment.deleted_by_moderator', $comment);
        return back()->with('status', 'Comment hidden.');
    }
}
