<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['path', 'post_id'])]
class PostImage extends Model
{
    public const int MAX_KILOBYTES = 2048;

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
