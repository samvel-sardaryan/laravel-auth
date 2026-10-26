<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Repositories\Contracts\ActivityRepository;
use App\Repositories\Contracts\CommentRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final readonly class CommentController extends Controller
{
    public function __construct(private ActivityRepository $activities, private CommentRepository $comments) {}

    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        Gate::authorize('view', $post);
        $parentId = $request->validated('parent_id');
        if ($parentId) { 
            $parentId = $this->comments->topLevelIdOf((int) $parentId); 
        }
        $comment = $this->comments->create($post, $request->user(), $request->validated('content'), $parentId);

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
        $byAuthor = $request->user()->id === $comment->user_id;
        $this->comments->delete($comment);
        $this->activities->record($byAuthor ? 'comment.deleted' : 'comment.deleted_by_moderator', $comment);

        return back()->with('status', 'Comment deleted.');
    }
}
