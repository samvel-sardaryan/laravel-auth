<?php

namespace App\Listeners;

use App\Repositories\Contracts\ActivityRepository;
use Illuminate\Auth\Events\Lockout;

final readonly class LogLockout
{
    public function __construct(private ActivityRepository $activities) {}

    /**
     * Handle the event.
     */
    public function handle(Lockout $event): void
    {
        $this->activities->record('login.blocked');
    }
}
