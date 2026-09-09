<?php

namespace Modules\Blog\Interfaces\Repositories;

use Modules\Blog\Application\Posts\Data\SavePostData;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Models\Post;
use Modules\User\Models\User;

interface PostRepository
{
    public function save(User $author, SavePostData $data, ?Post $post = null): Post;

    public function hasTranslations(Post $post): bool;

    public function submit(Post $post): Post;

    public function review(Post $post, User $reviewer, PostStatus $status, ?string $notes): Post;

    public function delete(Post $post): void;
}
