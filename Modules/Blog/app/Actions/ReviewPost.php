<?php

namespace Modules\Blog\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Enums\ReviewDecision;
use Modules\Blog\Events\PostReviewed;
use Modules\Blog\Models\Post;
use Modules\User\Models\User;

class ReviewPost
{
    public function handle(Post $post, User $reviewer, ReviewDecision $decision, ?string $notes = null): Post
    {
        if ($post->status !== PostStatus::PendingReview) {
            throw ValidationException::withMessages([
                'post' => __('Only pending posts may be reviewed.'),
            ]);
        }

        $status = match ($decision) {
            ReviewDecision::Publish => PostStatus::Published,
            ReviewDecision::RequestChanges => PostStatus::ChangesRequested,
            ReviewDecision::Reject => PostStatus::Archived,
        };

        $post = DB::transaction(function () use ($post, $reviewer, $status, $notes): Post {
            $post->forceFill([
                'status' => $status,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'review_notes' => $notes,
                'published_at' => $status === PostStatus::Published ? now() : null,
            ])->save();

            return $post->refresh();
        });

        PostReviewed::dispatch($post);

        return $post;
    }
}
