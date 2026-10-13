<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        return (new MailMessage)
            ->subject('Reset Your Password')
            ->markdown('mail.reset-password', [
                'url' => $url,
                'name' => $notifiable->name,
                'expiresIn' => config('auth.passwords.users.expire'),
            ]);
    }
}
