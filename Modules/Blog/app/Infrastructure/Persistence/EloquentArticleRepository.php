<?php

namespace Modules\Blog\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Blog\Application\Articles\Data\CreateArticleData;
use Modules\Blog\Application\Articles\Data\SearchArticlesData;
use Modules\Blog\Domain\Articles\Article;
use Modules\Blog\Domain\Articles\ArticleIdentifier;
use Modules\Blog\Domain\Articles\Contracts\ArticleRepository;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Enums\PostType;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\PostTranslation;
use Modules\User\Models\User;

final class EloquentArticleRepository implements ArticleRepository
{
    public function create(CreateArticleData $data): Article
    {
        $author = User::query()
            ->where('email', config('platform.super_user.email'))
            ->firstOrFail();

        $post = DB::transaction(function () use ($data, $author): Post {
            $post = Post::query()->create([
                'author_id' => $author->getKey(),
                'type' => PostType::Article,
                'status' => $data->published ? PostStatus::Published : PostStatus::Draft,
                'published_at' => $data->published ? now() : null,
            ]);

            $post->translations()->create([
                'locale' => $data->locale,
                'title' => $data->title,
                'body' => $data->body,
                'summary' => $data->summary,
            ]);

            return $post->load('translations');
        });

        return $this->toDomain($post);
    }

    public function find(ArticleIdentifier $identifier): ?Article
    {
        $post = Post::query()
            ->with('translations')
            ->when(
                $identifier->id !== null,
                fn (Builder $query): Builder => $query->whereKey($identifier->id),
                fn (Builder $query): Builder => $query->whereHas(
                    'translations',
                    fn (Builder $translationQuery): Builder => $translationQuery->where('slug', $identifier->slug),
                ),
            )
            ->first();

        if (! $post) {
            return null;
        }

        $translation = $identifier->slug
            ? $post->translations->firstWhere('slug', $identifier->slug)
            : $post->translation();

        return $translation ? $this->toDomain($post, $translation) : null;
    }

    public function search(SearchArticlesData $data): array
    {
        return Post::query()
            ->with('translations')
            ->whereHas('translations', function (Builder $query) use ($data): void {
                $query->where(function (Builder $query) use ($data): void {
                    $query
                        ->where('title', 'like', "%{$data->query}%")
                        ->orWhere('summary', 'like', "%{$data->query}%")
                        ->orWhere('body', 'like', "%{$data->query}%")
                        ->orWhere('slug', 'like', "%{$data->query}%");
                });
            })
            ->when(
                $data->published === true,
                fn (Builder $query): Builder => $query->published(),
            )
            ->when(
                $data->published === false,
                fn (Builder $query): Builder => $query->whereNot('status', PostStatus::Published),
            )
            ->latest()
            ->limit($data->limit)
            ->get()
            ->map(fn (Post $post): Article => $this->toDomain($post))
            ->values()
            ->all();
    }

    private function toDomain(Post $post, ?PostTranslation $translation = null): Article
    {
        $translation ??= $post->translation();

        return new Article(
            id: $post->getKey(),
            locale: $translation->locale,
            title: $translation->title,
            slug: $translation->slug,
            summary: $translation->summary,
            body: $translation->body,
            published: $post->status === PostStatus::Published,
            createdAt: $post->created_at->toAtomString(),
            updatedAt: $post->updated_at->toAtomString(),
        );
    }
}
