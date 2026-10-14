<?php

namespace App\Listeners;

use App\Models\Activity;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        Activity::record('login.failed', $event->user);
    }
}
