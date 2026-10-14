<?php

namespace App\Http\Controllers;

use App\Actions\SaveProfileDetails;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileDetailsRequest;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'profile' => $user->profile ?? new Profile,
        ]);
    }

    public function updateAccount(UpdateAccountRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());
        $emailChanged = $user->isDirty('email');
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        $user->save();
        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with('status', 'Profile updated');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->password = $request->validated('password');
        $user->save();

        return back()->with('status', 'Password updated');
    }

    public function updateDetails(UpdateProfileDetailsRequest $request, SaveProfileDetails $save): RedirectResponse
    {
        $user = $request->user();
        $save->handle($user, $request->validated(), $request->file('avatar'));

        return back()->with('status', 'Profile updated');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->profile;
        if ($profile && $profile->avatar) {
            Storage::disk('public')->delete($profile->avatar);
            $profile->update(['avatar' => null]);

            return back()->with('status', 'Avatar deleted');
        }

        return back()->with('error', 'No avatar to delete');
    }
}
