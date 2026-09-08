<?php

namespace Modules\Blog\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Blog\Models\Post;

class PostSubmittedForReview extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Post $post)
    {
        $this->afterCommit();
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /** @return array<string, string> */
    public function viaQueues(): array
    {
        return ['database' => 'notifications', 'broadcast' => 'notifications'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post-submitted-for-review',
            'post_id' => $this->post->getKey(),
            'author_id' => $this->post->author_id,
            'message' => __('A post is ready for review.'),
        ];
    }
}
