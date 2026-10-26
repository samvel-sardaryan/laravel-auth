<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Pagination;
use App\Repositories\Contracts\UserRepository;
use Illuminate\View\View;
use App\Repositories\Contracts\PostRepository;
use Illuminate\Http\Request;

final readonly class UserProfileController extends Controller
{
    public function __construct(private UserRepository $users, private PostRepository $posts) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, User $user): View
    {
        return view('users.show', [
            'user' => $this->users->loadProfile($user),
            'posts' => $this->posts->paginatePublishedBy($user, $request->user(), Pagination::USER_POSTS),
        ]);
    }
}
