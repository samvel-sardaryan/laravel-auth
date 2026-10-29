<?php

namespace App\Observers;

use App\Models\User;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

final readonly class UserObserver
{
    public function __construct(private RoleRepositoryInterface $roles, private UserRepositoryInterface $users) {}

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $role = $this->roles->findByNameOrFail(config('roles.default'));
        $this->users->attachRole($user, $role);
    }
}
