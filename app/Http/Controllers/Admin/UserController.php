<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\Activity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);
        $search = $request->string('search')->trim()->toString();
        $users = User::query()
            ->with('roles')
            ->when($search, fn ($query) => $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->when($request->query('role'), fn ($q, $role) => $q->whereHas('roles', fn ($q) => $q->where('name', $role)))
            ->when(in_array($request->query('verified'), ['yes', 'no'], true), fn ($q) => $request->query('verified') === 'yes' ? $q->whereNotNull('email_verified_at') : $q->whereNull('email_verified_at'))
            ->orderBy('id')
            ->paginate(15)
            ->onEachSide(1)
            ->withQueryString();
        $roles = Role::orderBy('id')->get();

        return view('admin.users.index', ['users' => $users, 'roles' => $roles]);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $oldRoles = $user->roles->pluck('name')->join(', ');
        $role = Role::findOrFail($request->validated('role_id'));
        DB::transaction(fn () => $user->roles()->sync($role->id));
        Log::info('Updated user role', ['changed_by' => $request->user()->id, 'target_user' => $user->id, 'old_role' => $oldRoles, 'new_role' => $role->name]);
        Activity::record('role.changed', $user);

        return back()->with('status', "{$user->name} role updated successfully to {$role->name}");
    }
}
