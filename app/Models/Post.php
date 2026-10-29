<?php

namespace App\Models;

use App\Enums\PostStatus;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['title', 'content', 'status', 'category_id'])]
final class Post extends Model
{
    public const int TITLE_MAX_LENGTH = 150;

    public const int CONTENT_MAX_LENGTH = 5000;

    public const int TAGS_INPUT_MAX_LENGTH = 255;

    public const int MAX_IMAGES = 5;

    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use SoftDeletes;

    protected function casts(): array
    {
        return ['status' => PostStatus::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_likes');
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
