<?php

namespace Modules\Blog\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Blog\Models\Post;

class PostReviewed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Post $post)
    {
        $this->afterCommit();
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    /** @return array<string, string> */
    public function viaQueues(): array
    {
        return [
            'database' => 'notifications',
            'broadcast' => 'notifications',
            'mail' => 'notifications',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your post was reviewed'))
            ->line(__('Your post status is now :status.', [
                'status' => $this->post->status->value,
            ]))
            ->action(__('Open post management'), url('/blog/manage/posts/'.$this->post->getKey().'/edit'));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post-reviewed',
            'post_id' => $this->post->getKey(),
            'status' => $this->post->status->value,
            'message' => __('Your post was reviewed.'),
        ];
    }
}
