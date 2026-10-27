<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;


final readonly class RegistrationService
{
    public function __construct(private UserRepository $users) {}

    public function register(array $data): User
    {
        $user = $this->users->create($data);
        event(new Registered($user));
        return $user;
    }

    public function findForVerification(int $id): User
    {
        return $this->users->findOrFail($id);
    }

    public function verifyEmail(User $user): void
    {
        $this->users->markEmailAsVerified($user);
        event(new Verified($user));
    }
}
