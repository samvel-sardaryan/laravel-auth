<?php

namespace App\Listeners;

use App\Repositories\Contracts\ActivityRepositoryInterface;
use Illuminate\Auth\Events\Lockout;

final readonly class LogLockout
{
    public function __construct(private ActivityRepositoryInterface $activities) {}

    /**
     * Handle the event.
     */
    public function handle(Lockout $event): void
    {
        $this->activities->record('login.blocked');
    }
}
