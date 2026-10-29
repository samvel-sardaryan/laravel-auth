<?php

namespace App\Listeners;

use App\Repositories\Contracts\ActivityRepositoryInterface;
use Illuminate\Auth\Events\PasswordReset;

final readonly class LogPasswordReset
{
    public function __construct(private ActivityRepositoryInterface $activities) {}

    /**
     * Handle the event.
     */
    public function handle(PasswordReset $event): void
    {
        $this->activities->record('password.reset', by: $event->user);
    }
}
