<?php

namespace Modules\Blog\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Events\PostSubmittedForReview;
use Modules\Blog\Models\Post;

class SubmitPostForReview
{
    public function handle(Post $post): Post
    {
        if (! in_array($post->status, [PostStatus::Draft, PostStatus::ChangesRequested], true)) {
            throw ValidationException::withMessages([
                'post' => __('This post cannot be submitted in its current state.'),
            ]);
        }

        if (! $post->translations()->exists()) {
            throw ValidationException::withMessages([
                'translations' => __('At least one translation is required.'),
            ]);
        }

        $post->forceFill([
            'status' => PostStatus::PendingReview,
            'submitted_at' => now(),
            'reviewed_by' => null,
            'reviewed_at' => null,
        ])->save();

        PostSubmittedForReview::dispatch($post);

        return $post->refresh();
    }
}
