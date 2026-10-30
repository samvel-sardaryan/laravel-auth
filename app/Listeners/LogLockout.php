<?php

namespace App\Listeners;

use App\Repositories\Contracts\ActivityRepositoryInterface;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class LogLockout
{
    public function __construct(private ActivityRepositoryInterface $activities) {}

    /**
     * Handle the event.
     */
    public function handle(Lockout $event): void
    {
        try {
            $this->activities->record('login.blocked');
        } catch (Throwable $exception) {
            Log::error('Recording lockout activity failed', ['ip' => $event->request->ip(), 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
