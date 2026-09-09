<?php

namespace Modules\Blog\Services;

use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Enums\ReviewDecision;

final class PostWorkflow
{
    public function canSubmit(PostStatus $status): bool
    {
        return in_array($status, [PostStatus::Draft, PostStatus::ChangesRequested], true);
    }

    public function canReview(PostStatus $status): bool
    {
        return $status === PostStatus::PendingReview;
    }

    public function statusAfterEdit(PostStatus $status): PostStatus
    {
        return $status === PostStatus::ChangesRequested ? PostStatus::Draft : $status;
    }

    public function reviewedStatus(ReviewDecision $decision): PostStatus
    {
        return match ($decision) {
            ReviewDecision::Publish => PostStatus::Published,
            ReviewDecision::RequestChanges => PostStatus::ChangesRequested,
            ReviewDecision::Reject => PostStatus::Archived,
        };
    }
}
