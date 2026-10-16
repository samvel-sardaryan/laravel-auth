<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DeletedPostController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/posts');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])->middleware(['throttle:6,1', 'signed'])->name('verification.verify');

Route::get('/users/{user}', UserProfileController::class)->name('users.show');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/email/verify', [VerifyEmailController::class, 'notice'])->name('verification.notice');
    Route::post('/email/resend', [VerifyEmailController::class, 'resend'])->middleware(['throttle:1,1'])->name('verification.send');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'updateAccount'])->name('profile.update');
    Route::patch('/profile/details', [ProfileController::class, 'updateDetails'])->name('profile.details');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->middleware(['permission:view_dashboard'])->name('dashboard');
    Route::get('/moderator', [ModerationController::class, 'index'])->middleware(['permission:access_moderator_page'])->name('moderator');
    Route::delete('/moderation/posts/{post}', [ModerationController::class, 'hidePost'])->middleware(['permission:moderate_posts'])->name('moderation.posts.hide');
    Route::delete('/moderation/comments/{comment}', [ModerationController::class, 'hideComment'])->middleware(['permission:moderate_comments'])->name('moderation.comments.hide');
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::view('/', 'admin.index')->middleware(['permission:access_admin_page'])->name('index');
        Route::get('/users', [UserController::class, 'index'])->middleware(['permission:view_users'])->name('users.index');
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->middleware(['permission:manage_users'])->name('users.role.update');
        Route::resource('categories', CategoryController::class)->middleware(['permission:access_admin_page'])->only(['index', 'store', 'update', 'destroy']);
        Route::get('/deleted-posts', [DeletedPostController::class, 'index'])->middleware(['permission:view_deleted_posts'])->name('posts.deleted');
        Route::patch('/deleted-posts/{post}/restore', [DeletedPostController::class, 'restore'])->middleware(['permission:view_deleted_posts'])->withTrashed()->name('posts.restore');
        Route::get('activity', ActivityController::class)->middleware(['permission:access_admin_page'])->name('activity');
    });
    Route::get('/posts/create', [PostController::class, 'create'])->middleware(['permission:create_post'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->middleware(['permission:create_post', 'throttle:posts'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->middleware(['can:update,post'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->middleware(['can:update,post'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->middleware(['can:delete,post'])->name('posts.destroy');
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->middleware(['throttle:comments'])->name('comments.store');
    Route::get('/comments/{comment}/edit', [CommentController::class, 'edit'])->middleware(['can:update,comment'])->name('comments.edit');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->middleware(['can:update,comment'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->middleware(['can:delete,comment'])->name('comments.destroy');
    Route::post('/posts/{post}/like', LikeController::class)->name('posts.like');
    Route::post('/posts/{post}/report', [ReportController::class, 'post'])->name('reports.post');
    Route::post('/comments/{comment}/report', [ReportController::class, 'comment'])->name('reports.comment');
});

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->whereNumber('post')->name('posts.show');
