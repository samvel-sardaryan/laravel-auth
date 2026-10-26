<?php

namespace App\Http\Controllers;

use App\Actions\SaveProfileDetails;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileDetailsRequest;
use App\Support\Pagination;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Contracts\ProfileRepository;
use App\Repositories\Contracts\PostRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Actions\RemoveAvatar;
use Illuminate\View\View;

final readonly class ProfileController extends Controller
{
    public function __construct(private UserRepository $users, private ProfileRepository $profiles, private PostRepository $posts) {}

    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'profile' => $this->profiles->forUser($user),
            'posts' => $this->posts->paginateBy($user, Pagination::USER_POSTS)
        ]);
    }

    public function updateAccount(UpdateAccountRequest $request): RedirectResponse
    {
        $user = $request->user();
        $emailChanged = $this->users->updateAccount($user, $request->validated());
        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with('status', 'Profile updated');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $this->users->updatePassword($user, $request->validated('password'));

        return back()->with('status', 'Password updated');
    }

    public function updateDetails(UpdateProfileDetailsRequest $request, SaveProfileDetails $save): RedirectResponse
    {
        $user = $request->user();
        $save->handle($user, $request->validated(), $request->file('avatar'));

        return back()->with('status', 'Profile updated');
    }

    public function destroyAvatar(Request $request, RemoveAvatar $remove): RedirectResponse
    {
        if ($remove->handle($request->user())) {
            return back()->with('status', 'Avatar deleted');
        }

        return back()->with('error', 'No avatar to delete');
    }
}
