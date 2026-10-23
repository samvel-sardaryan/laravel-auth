<?php

namespace App\Actions;

use App\Models\User;
use App\Repositories\Contracts\ProfileRepository;
use Illuminate\Support\Facades\Storage;

final readonly class RemoveAvatar
{
    public function __construct(private ProfileRepository $profiles) {}

    public function handle(User $user): bool 
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
