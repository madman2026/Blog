<?php

namespace Modules\Interaction\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Blog\Models\Post;
use Modules\Interaction\Events\CommentSubmitted;
use Modules\Interaction\Interfaces\Repositories\CommentRepository;
use Modules\Interaction\Models\Comment;
use Modules\Interaction\Services\CommentWorkflow;
use Modules\User\Models\User;

final readonly class SubmitComment
{
    public function __construct(
        private CommentWorkflow $workflow,
        private CommentRepository $comments,
    ) {}

    public function handle(User $user, Post $post, string $body, ?Comment $parent = null): Comment
    {
        if (! $this->workflow->acceptsComments($post)) {
            throw ValidationException::withMessages([
                'post' => __('Comments are only available on published posts.'),
            ]);
        }

        if ($parent && ! $this->workflow->isValidParent($post, $parent)) {
            throw ValidationException::withMessages([
                'parent_id' => __('The selected parent comment is invalid.'),
            ]);
        }

        $comment = $this->comments->create($user, $post, $body, $parent);

        CommentSubmitted::dispatch($comment);

        return $comment;
    }
}
