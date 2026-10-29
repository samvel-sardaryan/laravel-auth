<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

final readonly class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function allOrdered(): Collection
    {
        return Role::orderBy('id')->get();
    }

    public function findOrFail(int $id): Role
    {
        return Role::findOrFail($id);
    }

    public function findByNameOrFail(string $name): Role
    {
        return Role::where('name', $name)->firstOrFail();
    }
}
