<?php

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Events\UserRegistered;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Enums\ReviewDecision;
use Modules\Blog\Events\PostReviewed;
use Modules\Blog\Events\PostSubmittedForReview;
use Modules\Blog\Interfaces\Repositories\PostRepository;
use Modules\Blog\Repositories\EloquentPostRepository;
use Modules\Blog\Services\PostWorkflow;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Events\CommentModerated;
use Modules\Interaction\Events\CommentSubmitted;
use Modules\Interaction\Interfaces\Repositories\CommentRepository;
use Modules\Interaction\Repositories\EloquentCommentRepository;
use Modules\Interaction\Services\CommentWorkflow;
use Modules\User\Events\AuthorApplicationReviewed;
use Modules\User\Events\AuthorApplicationSubmitted;
use Modules\User\Events\ManagedUserRoleChanged;
use Modules\User\Events\ManagedUserStatusChanged;
use Modules\User\Interfaces\Repositories\UserRepository;
use Modules\User\Repositories\EloquentUserRepository;

arch('domain code does not depend on application or infrastructure')
    ->expect('Modules\Blog\Domain')
    ->not->toUse([
        'Modules\Blog\Application',
        'Modules\Blog\Infrastructure',
        'Modules\Blog\Http',
        'Modules\Blog\Repositories',
    ]);

arch('delivery and application layers do not own transactions')
    ->expect([
        'Modules\Auth\Actions',
        'Modules\Auth\Http',
        'Modules\Blog\Actions',
        'Modules\Blog\Http',
        'Modules\Blog\Services',
        'Modules\Interaction\Actions',
        'Modules\Interaction\Http',
        'Modules\Interaction\Services',
        'Modules\User\Actions',
        'Modules\User\Http',
        'Modules\User\Services',
    ])
    ->not->toUse(DB::class);

arch('notifications are queued')
    ->expect([
        'Modules\Blog\Notifications',
        'Modules\Interaction\Notifications',
        'Modules\User\Notifications',
    ])
    ->toImplement(ShouldQueue::class);

it('binds repository contracts to infrastructure implementations', function (): void {
    expect(app(PostRepository::class))->toBeInstanceOf(EloquentPostRepository::class)
        ->and(app(CommentRepository::class))->toBeInstanceOf(EloquentCommentRepository::class)
        ->and(app(UserRepository::class))->toBeInstanceOf(EloquentUserRepository::class);
});

it('dispatches integration events only after their transaction commits', function (): void {
    expect([
        PostReviewed::class,
        PostSubmittedForReview::class,
        CommentModerated::class,
        CommentSubmitted::class,
        AuthorApplicationReviewed::class,
        AuthorApplicationSubmitted::class,
        UserRegistered::class,
        ManagedUserRoleChanged::class,
        ManagedUserStatusChanged::class,
    ])->each->toImplement(ShouldDispatchAfterCommit::class);
});

it('keeps post workflow decisions independent from persistence', function (): void {
    $workflow = new PostWorkflow;

    expect($workflow->canSubmit(PostStatus::Draft))->toBeTrue()
        ->and($workflow->canSubmit(PostStatus::Published))->toBeFalse()
        ->and($workflow->canReview(PostStatus::PendingReview))->toBeTrue()
        ->and($workflow->statusAfterEdit(PostStatus::ChangesRequested))->toBe(PostStatus::Draft)
        ->and($workflow->reviewedStatus(ReviewDecision::Publish))->toBe(PostStatus::Published);
});

it('keeps comment moderation decisions independent from persistence', function (): void {
    $workflow = new CommentWorkflow;

    expect($workflow->isModerationDecision(CommentStatus::Approved))->toBeTrue()
        ->and($workflow->isModerationDecision(CommentStatus::Rejected))->toBeTrue()
        ->and($workflow->isModerationDecision(CommentStatus::Pending))->toBeFalse();
});
