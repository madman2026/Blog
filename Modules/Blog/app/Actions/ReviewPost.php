<?php

namespace Modules\Blog\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Blog\Enums\ReviewDecision;
use Modules\Blog\Events\PostReviewed;
use Modules\Blog\Interfaces\Repositories\PostRepository;
use Modules\Blog\Models\Post;
use Modules\Blog\Services\PostWorkflow;
use Modules\User\Models\User;

final readonly class ReviewPost
{
    public function __construct(
        private PostWorkflow $workflow,
        private PostRepository $posts,
    ) {}

    public function handle(Post $post, User $reviewer, ReviewDecision $decision, ?string $notes = null): Post
    {
        if (! $this->workflow->canReview($post->status)) {
            throw ValidationException::withMessages([
                'post' => __('Only pending posts may be reviewed.'),
            ]);
        }

        $post = $this->posts->review(
            $post,
            $reviewer,
            $this->workflow->reviewedStatus($decision),
            $notes,
        );

        PostReviewed::dispatch($post);

        return $post;
    }
}
