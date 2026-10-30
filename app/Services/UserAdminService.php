<?php

namespace App\Services;

use App\Data\UserFilters;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Support\Pagination;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class UserAdminService
{
    public function __construct(private UserRepositoryInterface $users, private RoleRepositoryInterface $roles, private ActivityRepositoryInterface $activities) {}

    public function listUsers(UserFilters $filters): LengthAwarePaginator
    {
        return $this->users->paginateForAdmin($filters, Pagination::ADMIN_USERS);
    }

    public function roles(): Collection
    {
        return $this->roles->allOrdered();
    }

    public function changeRole(User $user, int $roleId, User $admin): Role
    {
        try {
            $role = $this->roles->findOrFail($roleId);
            $oldRoles = $this->users->changeRole($user, $role);
            Log::info('Updated user role', ['changed_by' => $admin->id, 'target_user' => $user->id, 'old_role' => $oldRoles, 'new_role' => $role->name]);
            $this->activities->record('role.changed', $user);
        } catch (Throwable $exception) {
            Log::error('Changing user role failed', ['changed_by' => $admin->id, 'target_user' => $user->id, 'role_id' => $roleId, 'error' => $exception->getMessage()]);
            throw $exception;
        }

        return $role;
    }
}
