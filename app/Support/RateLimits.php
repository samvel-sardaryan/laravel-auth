<?php

namespace App\Support;

final class RateLimits
{
    public const int LOGIN_MAX_ATTEMPTS = 5;

    public const int LOGIN_LOCKOUT_MINUTES = 15;

    public const int POSTS_PER_DAY = 10;

    public const int COMMENTS_PER_MINUTE = 5;

    public const int VERIFICATION_EMAILS_PER_MINUTE = 1;

    public const int VERIFICATION_ATTEMPTS_PER_MINUTE = 6;
}
