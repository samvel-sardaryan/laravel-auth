<?php

namespace App\Repositories\Contracts;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;


interface RoleRepository
{
    public function allOrdered(): Collection;
    public function findOrFail(int $id): Role;
    public function findByNameOrFail(string $name): Role;
}
