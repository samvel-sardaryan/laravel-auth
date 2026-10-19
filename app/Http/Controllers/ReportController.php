<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function post(StoreReportRequest $request, Post $post): RedirectResponse
    {
        Gate::authorize('view', $post);

        return $this->file($request, $post);
    }

    public function comment(StoreReportRequest $request, Comment $comment): RedirectResponse
    {
        return $this->file($request, $comment);
    }

    private function file(StoreReportRequest $request, Model $target): RedirectResponse
    {
        abort_if($target->user_id === $request->user()->id, Response::HTTP_FORBIDDEN, 'You cannot report your own content.');
        $report = $target->reports()->firstOrCreate(['reporter_id' => $request->user()->id], ['reason' => $request->validated('reason')]);

        return back()->with('status', $report->wasRecentlyCreated ? 'Thanks, this has been reported.' : 'You already reported this.');
    }
}
