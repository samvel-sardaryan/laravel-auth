<?php

namespace App\Repositories\Contracts;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReportRepository
{
    public function file(Post|Comment $target, User $reporter, string $reason): bool;
    public function paginateQueue(int $perPage): LengthAwarePaginator;
}
