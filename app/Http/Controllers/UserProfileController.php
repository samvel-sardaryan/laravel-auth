<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            'posts' => $user->posts()->published()->withCardData(request()->user())->latest()->paginate(10),
        ]);
    }
}
