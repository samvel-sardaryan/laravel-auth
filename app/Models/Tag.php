<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

#[Fillable(['name'])]
class Tag extends Model
{
    public const int NAME_MAX_LENGTH = 30;

    public const int MAX_PER_POST = 5;

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    public static function idsFromInput(?string $raw): array
    {
        return Str::of($raw ?? '')
            ->explode(',')
            ->map(fn ($name) => mb_strtolower(trim($name)))
            ->filter(fn ($name) => $name !== '' && mb_strlen($name) <= self::NAME_MAX_LENGTH)
            ->unique()
            ->take(self::MAX_PER_POST)
            ->map(fn ($name) => static::firstOrCreate(['name' => $name])->id)
            ->values()
            ->all();
    }
}
