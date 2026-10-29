<?php

namespace App\Repositories\Eloquent;

use App\Models\Activity;
use App\Models\User;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class EloquentActivityRepository implements ActivityRepositoryInterface
{
    public function record(string $action, ?Model $subject = null, ?User $by = null): void
    {
        Activity::create([
            'user_id' => ($by ?? auth()->user())?->id,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip_address' => request()->ip(),
        ]);
    }

    public function paginateLog(string $userSearch, int $perPage): LengthAwarePaginator
    {
        return Activity::with(['user', 'subject'])
            ->when($userSearch, fn (Builder $query) => $query->whereHas('user', fn (Builder $q) => $q->where('name', 'like', "%{$userSearch}%")
                ->orWhere('email', 'like', "%{$userSearch}%")))
            ->latest()
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
