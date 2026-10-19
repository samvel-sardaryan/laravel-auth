<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        Gate::authorize('view', $post);
        $parentId = $request->validated('parent_id');
        if ($parentId) {
            $parent = Comment::findOrFail($parentId);
            $parentId = $parent->parent_id ?? $parent->id;
        }
        $comment = new Comment(['content' => $request->validated('content'), 'parent_id' => $parentId]);
        $comment->user()->associate($request->user());
        $post->comments()->save($comment);

        return redirect(route('posts.show', $post).'#comment-'.$comment->id);
    }

    public function edit(Comment $comment): View
    {
        return view('comments.edit', ['comment' => $comment]);
    }

    public function update(UpdateCommentRequest $request, Comment $comment): RedirectResponse
    {
        $comment->update($request->validated());

        return redirect(route('posts.show', $comment->post).'#comment-'.$comment->id);
    }

    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        $byAuthor = $request->user()->id === $comment->user_id;
        $comment->delete();
        Activity::record($byAuthor ? 'comment.deleted' : 'comment.deleted_by_moderator', $comment);

        return back()->with('status', 'Comment deleted.');
    }
}
