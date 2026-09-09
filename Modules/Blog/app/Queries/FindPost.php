<?php

namespace Modules\Blog\Queries;

use Modules\Blog\Models\Post;

final class FindPost
{
    public function byId(int $postId): Post
    {
        return Post::query()->findOrFail($postId);
    }
}
