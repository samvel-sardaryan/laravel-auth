<?php

namespace App\Listeners;

use App\Repositories\Contracts\ActivityRepositoryInterface;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class LogFailedLogin
{
    public function __construct(private ActivityRepositoryInterface $activities) {}

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        try {
            $this->activities->record('login.failed', $event->user);
        } catch (Throwable $exception) {
            Log::error('Recording failed login activity failed', ['user_id' => $event->user?->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
