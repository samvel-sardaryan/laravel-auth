<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'is_default'])]
final class Category extends Model
{
    public const int NAME_MAX_LENGTH = 50;

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
