<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);
        $search = $request->string('search')->trim()->toString();
        $users = User::query()
            ->with('roles')
            ->when($search, fn($query) => $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->when($request->query('role'), fn($q, $role) => $q->whereHas('roles', fn($q) => $q->where('name', $role)))
            ->when(in_array($request->query('verified'), ['yes', 'no'], true), fn($q) => $request->query('verified') === 'yes' ? $q->whereNotNull('email_verified_at') : $q->whereNull('email_verified_at'))
            ->orderBy('id')
            ->paginate(15)
            ->onEachSide(1)
            ->withQueryString();
        $roles = Role::orderBy('id')->get();
        return view('admin.users.index', ['users' => $users, 'roles' => $roles]);
    }
}
