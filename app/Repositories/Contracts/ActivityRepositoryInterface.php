<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface ActivityRepositoryInterface
{
    public function record(string $action, ?Model $subject = null, ?User $by = null): void;

    public function paginateLog(string $userSearch, int $perPage): LengthAwarePaginator;
}
