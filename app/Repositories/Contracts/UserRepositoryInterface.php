<?php

namespace App\Repositories\Contracts;

use App\Data\UserFilters;
use App\Models\Role;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function create(array $attributes): User;

    public function findOrFail(int $id): User;

    public function markEmailAsVerified(User $user): void;

    public function recordLogin(User $user): void;

    public function updateAccount(User $user, array $attributes): bool;

    public function updatePassword(User $user, string $password): void;

    public function resetPassword(User $user, string $password): void;

    public function paginateForAdmin(UserFilters $filters, int $perPage): LengthAwarePaginator;

    public function changeRole(User $user, Role $role): string;

    public function attachRole(User $user, Role $role): void;

    public function loadProfile(User $user): User;
}
