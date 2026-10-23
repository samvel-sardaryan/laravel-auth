<?php

namespace App\Repositories\Eloquent;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Repositories\Contracts\ProfileRepository;

final readonly class EloquentProfileRepository implements ProfileRepository
{
    public function forUser(User $user): Profile
    {
        return $user->profile ?? new Profile;
    }

    public function saveDetails(User $user, array $details): Profile
    {
        return DB::transaction(fn () => $user->profile()->updateOrCreate([], $details));
    }

    public function clearAvatar(Profile $profile): void
    {
        $profile->update(['avatar' => null]);
    }
}
