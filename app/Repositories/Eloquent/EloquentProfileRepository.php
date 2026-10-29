<?php

namespace App\Repositories\Eloquent;

use App\Models\Profile;
use App\Models\User;
use App\Repositories\Contracts\ProfileRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class EloquentProfileRepository implements ProfileRepositoryInterface
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
