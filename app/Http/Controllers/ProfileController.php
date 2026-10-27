<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileDetailsRequest;
use App\Services\AccountService;
use App\Services\PostService;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final readonly class ProfileController extends Controller
{
    public function __construct(private AccountService $account, private ProfileService $profiles, private PostService $posts) {}

    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'profile' => $this->profiles->forUser($user),
            'posts' => $this->posts->ownPosts($user),
        ]);
    }

    public function updateAccount(UpdateAccountRequest $request): RedirectResponse
    {
        $this->account->updateAccount($request->user(), $request->validated());

        return back()->with('status', 'Profile updated');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $this->account->updatePassword($user, $request->validated('password'));

        return back()->with('status', 'Password updated');
    }

    public function updateDetails(UpdateProfileDetailsRequest $request): RedirectResponse
    {
        $this->profiles->saveDetails($request->user(), $request->validated(), $request->file('avatar'));

        return back()->with('status', 'Profile updated');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        if ($this->profiles->removeAvatar($request->user())) {
            return back()->with('status', 'Avatar deleted');
        }

        return back()->with('error', 'No avatar to delete');
    }
}
