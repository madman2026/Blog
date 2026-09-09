<?php

namespace Modules\Interaction\Services;

use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Models\Post;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Models\Comment;

final class CommentWorkflow
{
    public function acceptsComments(Post $post): bool
    {
        return $post->status === PostStatus::Published
            && $post->published_at !== null
            && ! $post->published_at->isFuture();
    }

    public function isValidParent(Post $post, Comment $parent): bool
    {
        return $parent->commentable_type === $post->getMorphClass()
            && $parent->commentable_id === $post->getKey()
            && $parent->parent_id === null;
    }

    public function isModerationDecision(CommentStatus $status): bool
    {
        return $status !== CommentStatus::Pending;
    }
}
