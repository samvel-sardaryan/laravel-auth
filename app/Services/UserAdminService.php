<?php

namespace App\Services;

use App\Data\UserFilters;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\ActivityRepository;
use App\Repositories\Contracts\RoleRepository;
use App\Repositories\Contracts\UserRepository;
use App\Support\Pagination;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

final readonly class UserAdminService
{
    public function __construct(private UserRepository $users, private RoleRepository $roles, private ActivityRepository $activities) {}

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
        $role = $this->roles->findOrFail($roleId);
        $oldRoles = $this->users->changeRole($user, $role);
        Log::info('Updated user role', ['changed_by' => $admin->id, 'target_user' => $user->id, 'old_role' => $oldRoles, 'new_role' => $role->name]);
        $this->activities->record('role.changed', $user);
        return $role;
    }
}
