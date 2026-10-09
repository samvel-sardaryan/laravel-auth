<?php

namespace App\Observers;

use App\Models\Role;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $role = Role::where('name', config('roles.default'))->firstOrFail();
        $user->roles()->attach($role->id);
    }
}
