<?php

namespace App\Models;

use App\Data\PostFilters;
use App\Enums\PostStatus;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
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

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', PostStatus::Published);
    }

    #[Scope]
    protected function filtered(Builder $query, PostFilters $filters): void
    {
        $query
            ->when($filters->search, fn (Builder $q, string $term) => $q->where(fn (Builder $w) => $w->where('title', 'like', "%{$term}%")->orWhere('content', 'like', "%{$term}%")))
            ->when($filters->category, fn (Builder $q, string $slug) => $q->whereHas('category', fn (Builder $c) => $c->where('slug', $slug)))
            ->when($filters->tag, fn (Builder $q, string $tag) => $q->whereHas('tags', fn (Builder $t) => $t->where('name', mb_strtolower($tag))))
            ->when($filters->author, fn (Builder $q, int $id) => $q->where('user_id', $id));
    }

    #[Scope]
    protected function sorted(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'liked' => $query->orderByDesc('likers_count'),
            'commented' => $query->orderByDesc('comments_count'),
            default => $query->latest()
        };
        $query->orderByDesc('id');
    }

    #[Scope]
    protected function withCardData(Builder $query, ?User $viewer): void
    {
        $query->with(['user', 'category', 'tags', 'images'])->withCount(['likers', 'comments'])->withExists(['likers as liked' => fn (Builder $q) => $q->where('post_likes.user_id', $viewer?->id)]);
    }
}
