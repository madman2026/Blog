<?php

namespace Modules\Blog\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Blog\Events\PostSubmittedForReview;
use Modules\Blog\Interfaces\Repositories\PostRepository;
use Modules\Blog\Models\Post;
use Modules\Blog\Services\PostWorkflow;

final readonly class SubmitPostForReview
{
    public function __construct(
        private PostWorkflow $workflow,
        private PostRepository $posts,
    ) {}

    public function handle(Post $post): Post
    {
        if (! $this->workflow->canSubmit($post->status)) {
            throw ValidationException::withMessages([
                'post' => __('This post cannot be submitted in its current state.'),
            ]);
        }

        if (! $this->posts->hasTranslations($post)) {
            throw ValidationException::withMessages([
                'translations' => __('At least one translation is required.'),
            ]);
        }

        $post = $this->posts->submit($post);

        PostSubmittedForReview::dispatch($post);

        return $post;
    }
}
