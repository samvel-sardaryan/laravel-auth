<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use App\Repositories\Contracts\ProfileRepository;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class ProfileService
{
    public function __construct(private ProfileRepository $profiles, private UserRepository $users) {}

    public function forUser(User $user): Profile
    {
        return $this->profiles->forUser($user);
    }

    public function publicProfile(User $user): User
    {
        return $this->users->loadProfile($user);
    }

    public function saveDetails(User $user, array $data, ?UploadedFile $avatar): void
    {
        $newPath = $avatar?->store('avatars', 'public');
        $oldPath = $this->profiles->forUser($user)->avatar;
        $details = Arr::except($data, 'avatar');
        if ($newPath) {
            $details['avatar'] = $newPath;
        }
        try {
            $this->profiles->saveDetails($user, $details);
        } catch (Throwable $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $e;
        }
        if ($newPath && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }
    }

    public function removeAvatar(User $user): bool
    {
        $profile = $this->profiles->forUser($user);
        if (!$profile->avatar) {
            return false;
        }
        Storage::disk('public')->delete($profile->avatar);
        $this->profiles->clearAvatar($profile);
        return true;
    }
}
