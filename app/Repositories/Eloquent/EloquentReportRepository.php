<?php

namespace App\Repositories\Eloquent;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\Report;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\ReportRepository;

final readonly class EloquentReportRepository implements ReportRepository
{
    public function file(Post|Comment $target, User $reporter, string $reason): bool
    {
        $report = $target->reports()->firstOrCreate(['reporter_id' => $reporter->id], ['reason' => $reason]);
        return $report->wasRecentlyCreated;
    }

    public function paginateQueue(int $perPage): LengthAwarePaginator
    {
        return Report::with(['reporter', 'reportable'])->latest()->paginate($perPage);
    }
}
