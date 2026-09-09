<?php

namespace Modules\Interaction\Interfaces\Repositories;

use Modules\Blog\Models\Post;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Models\Comment;
use Modules\User\Models\User;

interface CommentRepository
{
    public function create(User $user, Post $post, string $body, ?Comment $parent = null): Comment;

    public function moderate(Comment $comment, User $moderator, CommentStatus $status, ?string $notes): Comment;
}
