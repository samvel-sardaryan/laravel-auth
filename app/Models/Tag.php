<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

#[Fillable(['name'])]
class Tag extends Model
{
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    public static function idsFromInput(?string $raw): array
    {
        return Str::of($raw ?? '')
            ->explode(',')
            ->map(fn ($name) => mb_strtolower(trim($name)))
            ->filter(fn ($name) => $name !== '' && mb_strlen($name) <= 30)
            ->unique()
            ->take(5)
            ->map(fn ($name) => static::firstOrCreate(['name' => $name])->id)
            ->values()
            ->all();
    }
}
