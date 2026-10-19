<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Pagination;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(User $user): View
    {
        return view('users.show', [
            'user' => $user->load('profile'),
            'posts' => $user->posts()->published()->withCardData(request()->user())->latest()->paginate(Pagination::USER_POSTS),
        ]);
    }
}
