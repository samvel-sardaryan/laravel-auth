<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name'])]
final class Tag extends Model
{
    public const int NAME_MAX_LENGTH = 30;

    public const int MAX_PER_POST = 5;

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
