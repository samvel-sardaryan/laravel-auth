<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use App\Repositories\Contracts\ProfileRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class ProfileService
{
    public function __construct(private ProfileRepositoryInterface $profiles, private UserRepositoryInterface $users) {}

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
        $oldPath = $this->profiles->forUser($user)->avatar;
        $newPath = null;

        try {
            $newPath = $avatar?->store('avatars', 'public');
            $details = Arr::except($data, 'avatar');

            if ($newPath) {
                $details['avatar'] = $newPath;
            }

            $this->profiles->saveDetails($user, $details);
        } catch (Throwable $exception) {
            Log::error('Saving profile details failed', ['user_id' => $user->id, 'error' => $exception->getMessage()]);

            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if ($newPath && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }
    }

    public function removeAvatar(User $user): bool
    {
        $profile = $this->profiles->forUser($user);

        if (! $profile->avatar) {
            return false;
        }

        try {
            Storage::disk('public')->delete($profile->avatar);
            $this->profiles->clearAvatar($profile);
        } catch (Throwable $exception) {
            Log::error('Removing avatar failed', ['user_id' => $user->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }

        return true;
    }
}
