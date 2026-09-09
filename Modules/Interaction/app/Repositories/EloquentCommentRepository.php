<?php

namespace Modules\Interaction\Repositories;

use Modules\Blog\Models\Post;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Interfaces\Repositories\CommentRepository;
use Modules\Interaction\Models\Comment;
use Modules\User\Models\User;

final class EloquentCommentRepository implements CommentRepository
{
    public function create(User $user, Post $post, string $body, ?Comment $parent = null): Comment
    {
        return $post->comments()->create([
            'user_id' => $user->getKey(),
            'parent_id' => $parent?->getKey(),
            'body' => $body,
            'status' => CommentStatus::Pending,
        ]);
    }

    public function moderate(Comment $comment, User $moderator, CommentStatus $status, ?string $notes): Comment
    {
        $comment->update([
            'status' => $status,
            'moderated_by' => $moderator->getKey(),
            'moderated_at' => now(),
            'moderation_notes' => $notes,
        ]);

        return $comment->refresh();
    }
}
