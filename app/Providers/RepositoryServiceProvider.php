<?php

namespace App\Providers;

use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\CommentRepositoryInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\ProfileRepositoryInterface;
use App\Repositories\Contracts\ReportRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentActivityRepository;
use App\Repositories\Eloquent\EloquentCategoryRepository;
use App\Repositories\Eloquent\EloquentCommentRepository;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentProfileRepository;
use App\Repositories\Eloquent\EloquentReportRepository;
use App\Repositories\Eloquent\EloquentRoleRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        ActivityRepositoryInterface::class => EloquentActivityRepository::class,
        RoleRepositoryInterface::class => EloquentRoleRepository::class,
        UserRepositoryInterface::class => EloquentUserRepository::class,
        ProfileRepositoryInterface::class => EloquentProfileRepository::class,
        CategoryRepositoryInterface::class => EloquentCategoryRepository::class,
        PostRepositoryInterface::class => EloquentPostRepository::class,
        CommentRepositoryInterface::class => EloquentCommentRepository::class,
        ReportRepositoryInterface::class => EloquentReportRepository::class,
    ];
}
