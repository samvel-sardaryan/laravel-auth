<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Support\Pagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $search = $request->string('user')->trim()->toString();
        $activities = Activity::with(['user', 'subject'])->when($search, fn (Builder $q) => $q->whereHas('user', fn (Builder $u) => $u->where(fn (Builder $w) => $w->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))))->latest()->orderByDesc('id')->paginate(Pagination::ACTIVITY_LOG)->onEachSide(Pagination::LINKS_ON_EACH_SIDE)->withQueryString();

        return view('admin.activity', ['activities' => $activities]);
    }
}
