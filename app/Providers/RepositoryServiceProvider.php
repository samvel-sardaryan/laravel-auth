<?php

namespace App\Providers;

use App\Repositories\Contracts\ActivityRepository;
use App\Repositories\Eloquent\EloquentActivityRepository;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        ActivityRepository::class => EloquentActivityRepository::class,
    ];
}
