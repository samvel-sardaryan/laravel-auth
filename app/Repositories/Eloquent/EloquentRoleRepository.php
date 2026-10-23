<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\RoleRepository;

final readonly class EloquentRoleRepository implements RoleRepository
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
