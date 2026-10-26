<?php

namespace App\Observers;

use App\Models\User;
use App\Repositories\Contracts\RoleRepository;
use App\Repositories\Contracts\UserRepository;

final readonly class UserObserver
{
    public function __construct(private RoleRepository $roles, private UserRepository $users) {}

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $role = $this->roles->findByNameOrFail(config('roles.default'));        
        $this->users->attachRole($user, $role);
    }
}
