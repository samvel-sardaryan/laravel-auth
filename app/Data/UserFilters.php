<?php

namespace App\Data;

use Illuminate\Http\Request;

final readonly class UserFilters
{
    public function __construct(public string $search, public ?string $role, public ?bool $verified) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string('search')->trim()->toString(),
            role: $request->query('role'),
            verified: match ($request->query('verified')) {
                'yes' => true,
                'no' => false,
                default => null,
            },
        );
    }
}
