<?php

namespace App\Observers;

use App\Models\User;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class UserObserver
{
    public function __construct(private RoleRepositoryInterface $roles, private UserRepositoryInterface $users) {}

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        try {
            $role = $this->roles->findByNameOrFail(config('roles.default'));
            $this->users->attachRole($user, $role);
        } catch (Throwable $exception) {
            Log::error('Attaching default role to user failed', ['user_id' => $user->id, 'role' => config('roles.default'), 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
