<?php

namespace App\Listeners;

use App\Models\Activity;
use Illuminate\Auth\Events\PasswordReset;

class LogPasswordReset
{
    /**
     * Handle the event.
     */
    public function handle(PasswordReset $event): void
    {
        Activity::record('password.reset', by: $event->user);
    }
}
