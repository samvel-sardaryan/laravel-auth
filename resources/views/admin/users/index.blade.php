@extends('layouts.app')
@section('title', 'Users')
@section('content')
<h1>Users</h1>

<form method="get" action="{{ route('admin.users.index') }}" class="filters">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Name or email">
    <select name="role">
        <option value="">All roles</option>
        @foreach ($roles as $role)
            <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->label }}</option>
        @endforeach
    </select>
    <select name="verified">
        <option value="">Verified or not</option>
        <option value="yes" @selected(request('verified') === 'yes')>Verified</option>
        <option value="no" @selected(request('verified') === 'no')>Not verified</option>
    </select>
    <button type="submit">Filter</button>
    <a href="{{ route('admin.users.index') }}">Reset</a>
</form>

<x-field-error field="role_id" />

@if ($users->isNotEmpty())
    <p class="count">Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}</p>
@endif

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Roles</th>
            <th>Verified</th>
            <th>Created</th>
            <th>Change role</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->roles->pluck('label')->join(', ') }}</td>
                <td>{{ $user->hasVerifiedEmail() ? 'Yes' : 'No' }}</td>
                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                <td>
                    @can('updateRole', $user)
                        <form method="post" action="{{ route('admin.users.role.update', $user) }}">
                            @csrf
                            @method('PATCH')
                            <select name="role_id">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected($user->roles->contains($role))>{{ $role->label }}</option>
                                @endforeach
                            </select>
                            <button type="submit">Change</button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">No users match.</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{ $users->links('partials.pagination') }}

<a href="{{ route('admin.index') }}">Back to admin</a>
@endsection
