<?php

namespace App\Repositories\Contracts;

use App\Models\Profile;
use App\Models\User;

interface ProfileRepository
{
    public function forUser(User $user): Profile;
    public function saveDetails(User $user, array $details): Profile;
    public function clearAvatar(Profile $profile): void;
}
