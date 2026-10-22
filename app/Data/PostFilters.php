<?php

namespace App\Data;

use Illuminate\Http\Request;

final readonly class PostFilters
{
    public function __construct(public ?string $search, public ?string $category, public ?string $tag, public ?int $author, public ?string $sort) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->query('q'),
            category: $request->query('category'),
            tag: $request->query('tag'),
            author: $request->integer('author') ?: null,
            sort: $request->query('sort')
        );
    }
}
