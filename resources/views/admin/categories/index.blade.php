@extends('layouts.app')
@section('title', 'Categories')
@section('content')
<h1>Categories</h1>

<x-field-error field="category" />
<x-field-error field="name" />
<x-field-error field="slug" />

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Posts</th>
            <th>Rename</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($categories as $category)
            <tr>
                <td>{{ $category->id }}</td>
                <td>
                    {{ $category->name }}
                    @if ($category->is_default)
                        <span class="hint">(default)</span>
                    @endif
                </td>
                <td>{{ $category->slug }}</td>
                <td>{{ $category->posts_count }}</td>
                <td>
                    <form method="post" action="{{ route('admin.categories.update', $category) }}" novalidate>
                        @csrf
                        @method('PUT')
                        <input type="text" name="name" value="{{ $category->name }}" aria-label="Name">
                        <input type="text" name="slug" value="{{ $category->slug }}" aria-label="Slug">
                        <button type="submit">Rename</button>
                    </form>
                </td>
                <td>
                    @if ($category->is_default)
                        <span class="hint">Cannot be deleted</span>
                    @else
                        <form method="post" action="{{ route('admin.categories.destroy', $category) }}"
                            onsubmit="return confirm('Delete this category? Its posts move to the default category.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<h2>New category</h2>
<form method="post" action="{{ route('admin.categories.store') }}" novalidate>
    @csrf
    <input type="hidden" name="form" value="create">
    <div class="field">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="{{ old('form') === 'create' ? old('name') : '' }}">
    </div>
    <div class="field">
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" value="{{ old('form') === 'create' ? old('slug') : '' }}">
        <p class="hint">Lowercase letters, digits and dashes, e.g. <code>how-to</code>.</p>
    </div>
    <button type="submit">Create</button>
</form>

<a href="{{ route('admin.index') }}">Back to admin</a>
@endsection
