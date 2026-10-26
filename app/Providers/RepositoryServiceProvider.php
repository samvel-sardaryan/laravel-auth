<?php

namespace App\Providers;

use App\Repositories\Contracts\ActivityRepository;
use App\Repositories\Eloquent\EloquentActivityRepository;
use App\Repositories\Contracts\RoleRepository;
use App\Repositories\Eloquent\EloquentRoleRepository;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\ProfileRepository;
use App\Repositories\Eloquent\EloquentProfileRepository;
use App\Repositories\Contracts\CategoryRepository;
use App\Repositories\Eloquent\EloquentCategoryRepository;
use App\Repositories\Contracts\PostRepository;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Contracts\CommentRepository;
use App\Repositories\Eloquent\EloquentCommentRepository;
use App\Repositories\Contracts\ReportRepository;
use App\Repositories\Eloquent\EloquentReportRepository;

final class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        ActivityRepository::class => EloquentActivityRepository::class,
        RoleRepository::class => EloquentRoleRepository::class,
        UserRepository::class => EloquentUserRepository::class,
        ProfileRepository::class => EloquentProfileRepository::class,
        CategoryRepository::class => EloquentCategoryRepository::class,
        PostRepository::class => EloquentPostRepository::class,
        CommentRepository::class => EloquentCommentRepository::class,
        ReportRepository::class => EloquentReportRepository::class,
    ];
}
