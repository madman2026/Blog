<?php

namespace Modules\Blog\Actions;

use Modules\Blog\Interfaces\Repositories\PostRepository;
use Modules\Blog\Models\Post;

final readonly class DeletePost
{
    public function __construct(private PostRepository $posts) {}

    public function handle(Post $post): void
    {
        $this->posts->delete($post);
    }
}
