<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Throwable;

final readonly class AccountService
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function recordLogin(User $user): void
    {
        try {
            $this->users->recordLogin($user);
        } catch (Throwable $exception) {
            Log::error('Recording user login failed', ['user_id' => $user->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    public function updateAccount(User $user, array $data): void
    {
        try {
            if ($this->users->updateAccount($user, $data)) {
                $user->sendEmailVerificationNotification();
            }
        } catch (Throwable $exception) {
            Log::error('Updating user account failed', ['user_id' => $user->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    public function updatePassword(User $user, string $password): void
    {
        try {
            $this->users->updatePassword($user, $password);
        } catch (Throwable $exception) {
            Log::error('Updating user password failed', ['user_id' => $user->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    public function resetPassword(User $user, string $password): void
    {
        try {
            $this->users->resetPassword($user, $password);
            event(new PasswordReset($user));
        } catch (Throwable $exception) {
            Log::error('Resetting user password failed', ['user_id' => $user->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    public function sendResetLink(string $email): void
    {
        try {
            Password::sendResetLink(['email' => $email]);
        } catch (Throwable $exception) {
            Log::error('Sending password reset link failed', ['email' => $email, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
