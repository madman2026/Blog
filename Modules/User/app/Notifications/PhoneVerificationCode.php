<?php

namespace Modules\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PhoneVerificationCode extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private readonly string $code)
    {
        $this->afterCommit();
        $this->onQueue('notifications');
    }

    public function code(): string
    {
        return $this->code;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Verify your phone number'))
            ->greeting(__('Phone verification'))
            ->line(__('Use this one-time code to verify :phone:', ['phone' => $notifiable->phone]))
            ->line($this->code)
            ->line(__('This code expires in 10 minutes.'));
    }
}
