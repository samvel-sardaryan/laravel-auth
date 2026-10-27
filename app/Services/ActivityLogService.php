<?php

namespace App\Services;

use App\Repositories\Contracts\ActivityRepository;
use App\Support\Pagination;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class ActivityLogService
{
    public function __construct(private ActivityRepository $activities) {}

    public function page(string $userSearch): LengthAwarePaginator
    {
        return $this->activities->paginateLog($userSearch, Pagination::ACTIVITY_LOG);
    }
}
