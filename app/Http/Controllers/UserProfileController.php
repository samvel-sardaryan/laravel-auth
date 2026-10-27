<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PostService;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final readonly class UserProfileController extends Controller
{
    public function __construct(private ProfileService $profiles, private PostService $posts) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, User $user): View
    {
        return view('users.show', [
            'user' => $this->profiles->publicProfile($user),
            'posts' => $this->posts->publishedBy($user, $request->user()),
        ]);
    }
}
