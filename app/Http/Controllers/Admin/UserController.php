<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Data\UserFilters;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use App\Repositories\Contracts\ActivityRepository;
use App\Repositories\Contracts\RoleRepository;
use App\Repositories\Contracts\UserRepository;
use App\Support\Pagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final readonly class UserController extends Controller
{
    public function __construct(private ActivityRepository $activities, private RoleRepository $roles, private UserRepository $users) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);
        $search = $request->string('search')->trim()->toString();
        $users = $this->users->paginateForAdmin(UserFilters::fromRequest($request), Pagination::ADMIN_USERS)->onEachSide(Pagination::LINKS_ON_EACH_SIDE)->withQueryString();
        $roles = $this->roles->allOrdered();

        return view('admin.users.index', ['users' => $users, 'roles' => $roles]);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $role = $this->roles->findOrFail((int) $request->validated('role_id'));
        $oldRoles = $this->users->changeRole($user, $role);
        Log::info('Updated user role', ['changed_by' => $request->user()->id, 'target_user' => $user->id, 'old_role' => $oldRoles, 'new_role' => $role->name]);
        $this->activities->record('role.changed', $user);

        return back()->with('status', "{$user->name} role updated successfully to {$role->name}");
    }
}
