<?php

namespace App\Observers;

use App\Models\User;
use App\Repositories\Contracts\RoleRepository;
use App\Repositories\Contracts\UserRepository;

class UserObserver
{
    public function __construct(private readonly RoleRepository $roles, private readonly UserRepository $users) {}

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $role = $this->roles->findByNameOrFail(config('roles.default'));        
        $this->users->attachRole($user, $role);
    }
}
