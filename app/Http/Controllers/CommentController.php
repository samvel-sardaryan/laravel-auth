<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final readonly class CommentController extends Controller
{
    public function __construct(private CommentService $comments) {}

    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        Gate::authorize('view', $post);
        $comment = $this->comments->add($post, $request->user(), $request->validated('content'), $request->validated('parent_id'));

        return redirect(route('posts.show', $post).'#comment-'.$comment->id);
    }

    public function edit(Comment $comment): View
    {
        return view('comments.edit', ['comment' => $comment]);
    }

    public function update(UpdateCommentRequest $request, Comment $comment): RedirectResponse
    {
        $this->comments->update($comment, $request->validated());

        return redirect(route('posts.show', $comment->post_id).'#comment-'.$comment->id);
    }

    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        $this->comments->delete($comment, $request->user());

        return back()->with('status', 'Comment deleted.');
    }
}
