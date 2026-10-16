<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('view', $post);
        $inserted = DB::table('post_likes')->insertOrIgnore(['user_id' => $request->user()->id, 'post_id' => $post->id]);
        if ($inserted === 0) {
            $post->likers()->detach($request->user()->id);
        }
        return back();
    }
}
