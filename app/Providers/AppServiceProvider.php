<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(fn() => Password::min(8)->letters()->mixedCase()->numbers());
        Gate::before(fn(User $user, $ability) => $user->hasPermission($ability) ?: null);
        Relation::enforceMorphMap([
            'user' => User::class,
            'post' => Post::class,
            'comment' => Comment::class,
        ]);
        RateLimiter::for('posts', function (Request $request) {
            return Limit::perDay(10)->by($request->user()->id)->response(fn() => back()->withInput()->withErrors(['title' => 'You can create at most 10 posts a day.']));
        });
        RateLimiter::for('comments', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()->id)->response(fn() => back()->withInput()->withErrors(['content' => 'You can post at most 5 comments a minute.']));
        });
    }
}
