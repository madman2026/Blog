<?php

namespace Modules\Blog\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Blog\Application\Posts\Data\SavePostData;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Interfaces\Repositories\PostRepository;
use Modules\Blog\Models\Post;
use Modules\Blog\Services\PostWorkflow;
use Modules\User\Models\User;

final readonly class EloquentPostRepository implements PostRepository
{
    public function __construct(private PostWorkflow $workflow) {}

    public function save(User $author, SavePostData $data, ?Post $post = null): Post
    {
        return DB::transaction(function () use ($author, $data, $post): Post {
            $post ??= new Post([
                'author_id' => $author->getKey(),
                'status' => PostStatus::Draft,
            ]);

            $post->type = $data->type;
            $status = $this->workflow->statusAfterEdit($post->status);

            if ($status !== $post->status) {
                $post->status = $status;
                $post->reviewed_by = null;
                $post->reviewed_at = null;
            }

            $post->save();

            $locales = collect($data->translations)->pluck('locale');
            $post->translations()->whereNotIn('locale', $locales)->delete();

            foreach ($data->translations as $translation) {
                $post->translations()->updateOrCreate(
                    ['locale' => $translation['locale']],
                    collect($translation)->except('locale')->all(),
                );
            }

            $post->categories()->sync($data->categoryIds);
            $post->tags()->sync($data->tagIds);

            return $post->load(['translations', 'categories.translations', 'tags.translations']);
        });
    }

    public function hasTranslations(Post $post): bool
    {
        return $post->translations()->exists();
    }

    public function submit(Post $post): Post
    {
        $post->forceFill([
            'status' => PostStatus::PendingReview,
            'submitted_at' => now(),
            'reviewed_by' => null,
            'reviewed_at' => null,
        ])->save();

        return $post->refresh();
    }

    public function review(Post $post, User $reviewer, PostStatus $status, ?string $notes): Post
    {
        return DB::transaction(function () use ($post, $reviewer, $status, $notes): Post {
            $post->forceFill([
                'status' => $status,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'review_notes' => $notes,
                'published_at' => $status === PostStatus::Published ? now() : null,
            ])->save();

            return $post->refresh();
        });
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }
}
