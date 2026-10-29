<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

final readonly class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $target): bool
    {
        return $user->hasPermission('view_users');
    }

    public function updateRole(User $user, User $target): Response
    {
        if ($user->is($target)) {
            return Response::deny('You can not update your own role');
        }

        if (! $user->hasPermission('manage_users')) {
            return Response::deny('You can not update user role');
        }

        return Response::allow();
    }
}
