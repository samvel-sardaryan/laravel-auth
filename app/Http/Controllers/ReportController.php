<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Repositories\Contracts\ReportRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final readonly class ReportController extends Controller
{
    public function __construct(private ReportRepository $reports){}

    public function post(StoreReportRequest $request, Post $post): RedirectResponse
    {
        Gate::authorize('view', $post);

        return $this->file($request, $post);
    }

    public function comment(StoreReportRequest $request, Comment $comment): RedirectResponse
    {
        return $this->file($request, $comment);
    }

    private function file(StoreReportRequest $request, Post|Comment $target): RedirectResponse
    {
        abort_if($target->user_id === $request->user()->id, Response::HTTP_FORBIDDEN, 'You cannot report your own content.');
        $isNew = $this->reports->file($target, $request->user(), $request->validated('reason'));

        return back()->with('status', $isNew ? 'Thanks, this has been reported.' : 'You already reported this.');
    }
}
