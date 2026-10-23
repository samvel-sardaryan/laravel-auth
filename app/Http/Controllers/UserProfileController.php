<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Pagination;
use App\Repositories\Contracts\UserRepository;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function __construct(private readonly UserRepository $users) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(User $user): View
    {
        return view('users.show', [
            'user' => $this->users->loadProfile($user),
            'posts' => $user->posts()->published()->withCardData(request()->user())->latest()->paginate(Pagination::USER_POSTS),
        ]);
    }
}
