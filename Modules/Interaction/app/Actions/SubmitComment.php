<?php

namespace Modules\Interaction\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Models\Post;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Events\CommentSubmitted;
use Modules\Interaction\Models\Comment;
use Modules\User\Models\User;

class SubmitComment
{
    public function handle(User $user, Post $post, string $body, ?Comment $parent = null): Comment
    {
        if ($post->status !== PostStatus::Published || $post->published_at?->isFuture()) {
            throw ValidationException::withMessages([
                'post' => __('Comments are only available on published posts.'),
            ]);
        }

        if ($parent && ($parent->commentable_type !== $post->getMorphClass()
            || $parent->commentable_id !== $post->getKey()
            || $parent->parent_id !== null)) {
            throw ValidationException::withMessages([
                'parent_id' => __('The selected parent comment is invalid.'),
            ]);
        }

        $comment = $post->comments()->create([
            'user_id' => $user->getKey(),
            'parent_id' => $parent?->getKey(),
            'body' => $body,
            'status' => CommentStatus::Pending,
        ]);

        CommentSubmitted::dispatch($comment);

        return $comment;
    }
}
