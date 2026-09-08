<?php

namespace Modules\Interaction\Policies;

use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Models\Comment;
use Modules\User\Enums\UserPermission;
use Modules\User\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(UserPermission::CommentsViewAny->value);
    }

    public function view(?User $user, Comment $comment): bool
    {
        return $comment->status === CommentStatus::Approved
            || $user?->getKey() === $comment->user_id
            || $user?->can(UserPermission::CommentsViewAny->value) === true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->getKey()
            && $comment->status === CommentStatus::Pending;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->getKey()
            || $user->can(UserPermission::CommentsDeleteAny->value);
    }

    public function moderate(User $user, Comment $comment): bool
    {
        return $comment->status === CommentStatus::Pending
            && $user->can(UserPermission::CommentsModerate->value);
    }
}
