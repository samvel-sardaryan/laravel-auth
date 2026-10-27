<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Auth\Events\PasswordReset;

final readonly class AccountService
{
    public function __construct(private UserRepository $users) {}

    public function recordLogin(User $user): void
    {
        $this->users->recordLogin($user);
    }

    public function updateAccount(User $user, array $data): void
    {
        if ($this->users->updateAccount($user, $data)) { 
            $user->sendEmailVerificationNotification(); 
        }
    }

    public function updatePassword(User $user, string $password): void
    {
        $this->users->updatePassword($user, $password);
    }

    public function resetPassword(User $user, string $password): void
    {
        $this->users->resetPassword($user, $password);
        event(new PasswordReset($user));
    }
}
