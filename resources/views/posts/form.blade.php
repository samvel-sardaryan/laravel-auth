@extends('layouts.app')
@section('title', $post->exists ? 'Edit post' : 'New post')
@section('content')
<h1>{{ $post->exists ? 'Edit post' : 'New post' }}</h1>

<form method="post" action="{{ $post->exists ? route('posts.update', $post) : route('posts.store') }}" enctype="multipart/form-data" novalidate>
    @csrf
    @if ($post->exists)
        @method('PUT')
    @endif
    <div class="field">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="{{ old('title', $post->title) }}">
        <x-field-error field="title" />
    </div>
    <div class="field">
        <label for="content">Content</label>
        <textarea id="content" name="content" rows="10">{{ old('content', $post->content) }}</textarea>
        <x-field-error field="content" />
    </div>
    <div class="field">
        <label for="tags">Tags</label>
        <input type="text" id="tags" name="tags" value="{{ old('tags', $post->tags->pluck('name')->join(', ')) }}">
        <p class="hint">Comma separated, up to 5. Lowercased automatically.</p>
        <x-field-error field="tags" />
    </div>
    <div class="field">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id">
            <option value="">Choose a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $post->category_id) === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <x-field-error field="category_id" />
    </div>
    <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $post->status?->value ?? 'draft') === $status->value)>{{ ucfirst($status->value) }}</option>
            @endforeach
        </select>
        <p class="hint">A draft is visible only to you. Archived posts stay off the feed.</p>
        <x-field-error field="status" />
    </div>
    @if ($post->images->isNotEmpty())
        <div class="field">
            <p>Current images</p>
            <div class="images">
                @foreach ($post->images as $image)
                    <label class="image-choice">
                        <img src="{{ Storage::url($image->path) }}" alt="" width="120">
                        <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" @checked(in_array($image->id, old('remove_images', [])))> Remove
                    </label>
                @endforeach
            </div>
        </div>
    @endif
    <div class="field">
        <label for="images">{{ $post->exists ? 'Add images' : 'Images' }}</label>
        <input type="file" id="images" name="images[]" accept="image/*" multiple>
        <p class="hint">JPG, PNG, GIF or WEBP, up to 2 MB each, 5 in total. If the form comes back with an error, choose the files again.</p>
        <x-field-error field="images" />
        <x-field-error field="images.*" />
    </div>
    <button type="submit">{{ $post->exists ? 'Save changes' : 'Publish' }}</button>
</form>

<a href="{{ $post->exists ? route('posts.show', $post) : route('posts.index') }}">Back</a>
@endsection
