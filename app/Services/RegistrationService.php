<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class RegistrationService
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function register(array $data): User
    {
        try {
            $user = $this->users->create($data);
            event(new Registered($user));
        } catch (Throwable $exception) {
            Log::error('User registration failed', ['email' => $data['email'] ?? null, 'error' => $exception->getMessage()]);
            throw $exception;
        }

        return $user;
    }

    public function findForVerification(int $id): User
    {
        return $this->users->findOrFail($id);
    }

    public function verifyEmail(User $user): void
    {
        try {
            $this->users->markEmailAsVerified($user);
            event(new Verified($user));
        } catch (Throwable $exception) {
            Log::error('Email verification failed', ['user_id' => $user->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    public function resendVerification(User $user): void
    {
        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            Log::error('Resending email verification failed', ['user_id' => $user->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
