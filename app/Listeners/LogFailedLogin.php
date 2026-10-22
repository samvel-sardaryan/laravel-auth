<?php

namespace App\Listeners;

use App\Repositories\Contracts\ActivityRepository;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function __construct(private readonly ActivityRepository $activities) {}

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        $this->activities->record('login.failed', $event->user);
    }
}
