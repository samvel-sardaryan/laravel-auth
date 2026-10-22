<?php

namespace App\Listeners;

use App\Repositories\Contracts\ActivityRepository;
use Illuminate\Auth\Events\Lockout;

class LogLockout
{
    public function __construct(private readonly ActivityRepository $activities) {}

    /**
     * Handle the event.
     */
    public function handle(Lockout $event): void
    {
        $this->activities->record('login.blocked');
    }
}
