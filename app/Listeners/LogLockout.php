<?php

namespace App\Listeners;

use App\Models\Activity;
use Illuminate\Auth\Events\Lockout;

class LogLockout
{
    /**
     * Handle the event.
     */
    public function handle(Lockout $event): void
    {
        Activity::record('login.blocked');
    }
}
