<?php

namespace App\Http\Controllers\Admin;

use App\Data\UserFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use App\Services\UserAdminService;
use App\Support\Pagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final readonly class UserController extends Controller
{
    public function __construct(private UserAdminService $userAdmin) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);
        $users = $this->userAdmin->listUsers(UserFilters::fromRequest($request))->onEachSide(Pagination::LINKS_ON_EACH_SIDE)->withQueryString();
        $roles = $this->userAdmin->roles();

        return view('admin.users.index', ['users' => $users, 'roles' => $roles]);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $role = $this->userAdmin->changeRole($user, (int) $request->validated('role_id'), $request->user());
        return back()->with('status', "{$user->name} role updated successfully to {$role->name}");
    }
}
