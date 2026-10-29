<?php

namespace App\Repositories\Eloquent;

use App\Data\UserFilters;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class EloquentUserRepository implements UserRepositoryInterface
{
    public function create(array $attributes): User
    {
        return User::create($attributes);
    }

    public function findOrFail(int $id): User
    {
        return User::findOrFail($id);
    }

    public function markEmailAsVerified(User $user): void
    {
        $user->markEmailAsVerified();
    }

    public function recordLogin(User $user): void
    {
        $user->last_login_at = now();
        $user->save();
    }

    public function updateAccount(User $user, array $attributes): bool
    {
        $user->fill($attributes);
        $emailChanged = $user->isDirty('email');

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $emailChanged;
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->password = $password;
        $user->save();
    }

    public function resetPassword(User $user, string $password): void
    {
        $user->password = $password;
        $user->setRememberToken(Str::random(User::REMEMBER_TOKEN_LENGTH));
        $user->save();
    }

    public function paginateForAdmin(UserFilters $filters, int $perPage): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->when($filters->search, fn ($query) => $query->where(fn ($q) => $q->where('name', 'like', "%{$filters->search}%")->orWhere('email', 'like', "%{$filters->search}%")))
            ->when($filters->role, fn ($q, $role) => $q->whereHas('roles', fn ($q) => $q->where('name', $role)))
            ->when($filters->verified !== null, fn ($q) => $filters->verified ? $q->whereNotNull('email_verified_at') : $q->whereNull('email_verified_at'))
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function changeRole(User $user, Role $role): string
    {
        $oldRoles = $user->roles->pluck('name')->join(', ');
        DB::transaction(fn () => $user->roles()->sync($role->id));

        return $oldRoles;
    }

    public function attachRole(User $user, Role $role): void
    {
        $user->roles()->attach($role->id);
    }

    public function loadProfile(User $user): User
    {
        return $user->load('profile');
    }
}
