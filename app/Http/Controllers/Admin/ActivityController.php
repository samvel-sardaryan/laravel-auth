<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Support\Pagination;
use Illuminate\Http\Request;
use Illuminate\View\View;

final readonly class ActivityController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $search = $request->string('user')->trim()->toString();
        $activities = $this->activityLog->page($search)->onEachSide(Pagination::LINKS_ON_EACH_SIDE)->withQueryString();

        return view('admin.activity', ['activities' => $activities]);
    }
}
