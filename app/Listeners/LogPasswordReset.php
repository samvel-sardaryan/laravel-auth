<?php

namespace App\Listeners;

use App\Repositories\Contracts\ActivityRepositoryInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class LogPasswordReset
{
    public function __construct(private ActivityRepositoryInterface $activities) {}

    /**
     * Handle the event.
     */
    public function handle(PasswordReset $event): void
    {
        try {
            $this->activities->record('password.reset', by: $event->user);
        } catch (Throwable $exception) {
            Log::error('Recording password reset activity failed', ['user_id' => $event->user->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
