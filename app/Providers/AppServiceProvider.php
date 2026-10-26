<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Support\RateLimits;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
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
        Password::defaults(fn () => Password::min(User::PASSWORD_MIN_LENGTH)->letters()->mixedCase()->numbers());
        Gate::before(fn (User $user, $ability) => $user->hasPermission($ability) ?: null);
        Relation::enforceMorphMap([
            'user' => User::class,
            'post' => Post::class,
            'comment' => Comment::class,
        ]);
        RateLimiter::for('posts', function (Request $request) {
            return Limit::perDay(RateLimits::POSTS_PER_DAY)->by($request->user()->id)->response(fn () => back()->withInput()->withErrors(['title' => 'You can create at most '.RateLimits::POSTS_PER_DAY.' posts a day.']));
        });
        RateLimiter::for('comments', function (Request $request) {
            return Limit::perMinute(RateLimits::COMMENTS_PER_MINUTE)->by($request->user()->id)->response(fn () => back()->withInput()->withErrors(['content' => 'You can post at most '.RateLimits::COMMENTS_PER_MINUTE.' comments a minute.']));
        });
        RateLimiter::for('verification-emails', function (Request $request) {
            return Limit::perMinute(RateLimits::VERIFICATION_EMAILS_PER_MINUTE)->by($request->user()->id);
        });
        RateLimiter::for('verification-attempts', function (Request $request) {
            return Limit::perMinute(RateLimits::VERIFICATION_ATTEMPTS_PER_MINUTE)->by($request->ip());
        });
    }
}
