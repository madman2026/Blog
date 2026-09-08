<?php

namespace Modules\Blog\Policies;

use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Models\Post;
use Modules\User\Enums\UserPermission;
use Modules\User\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(UserPermission::PostsViewAny->value);
    }

    public function view(?User $user, Post $post): bool
    {
        if ($post->status === PostStatus::Published && $post->published_at?->isPast()) {
            return true;
        }

        return $user !== null && (
            $user->can(UserPermission::PostsUpdateAny->value)
            || $post->author_id === $user->getKey()
        );
    }

    public function create(User $user): bool
    {
        return $user->can(UserPermission::PostsCreate->value);
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->can(UserPermission::PostsUpdateAny->value)) {
            return true;
        }

        return $user->can(UserPermission::PostsUpdateOwn->value)
            && $post->author_id === $user->getKey()
            && in_array($post->status, [PostStatus::Draft, PostStatus::ChangesRequested], true);
    }

    public function delete(User $user, Post $post): bool
    {
        if ($user->can(UserPermission::PostsDeleteAny->value)) {
            return true;
        }

        return $user->can(UserPermission::PostsDeleteOwn->value)
            && $post->author_id === $user->getKey()
            && $post->status !== PostStatus::Published;
    }

    public function submit(User $user, Post $post): bool
    {
        return $user->can(UserPermission::PostsSubmit->value)
            && $post->author_id === $user->getKey()
            && in_array($post->status, [PostStatus::Draft, PostStatus::ChangesRequested], true);
    }

    public function review(User $user, Post $post): bool
    {
        return $user->can(UserPermission::PostsReview->value)
            && $post->status === PostStatus::PendingReview;
    }

    public function restore(User $user): bool
    {
        return $user->can(UserPermission::PostsDeleteAny->value);
    }

    public function forceDelete(User $user): bool
    {
        return $user->can(UserPermission::RolesManage->value);
    }
}
