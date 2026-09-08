<?php

namespace Modules\Blog\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Builder;
use Modules\Blog\Application\Articles\Data\CreateArticleData;
use Modules\Blog\Application\Articles\Data\SearchArticlesData;
use Modules\Blog\Domain\Articles\Article;
use Modules\Blog\Domain\Articles\ArticleIdentifier;
use Modules\Blog\Domain\Articles\Contracts\ArticleRepository;
use Modules\Blog\Models\Post;

final class EloquentArticleRepository implements ArticleRepository
{
    public function create(CreateArticleData $data): Article
    {
        $post = Post::query()->create([
            'title' => $data->title,
            'body' => $data->body,
            'summary' => $data->summary,
            'published' => $data->published,
        ]);

        return $this->toDomain($post->refresh());
    }

    public function find(ArticleIdentifier $identifier): ?Article
    {
        $post = Post::query()
            ->when(
                $identifier->id !== null,
                fn (Builder $query): Builder => $query->whereKey($identifier->id),
                fn (Builder $query): Builder => $query->where('slug', $identifier->slug),
            )
            ->first();

        return $post instanceof Post ? $this->toDomain($post) : null;
    }

    public function search(SearchArticlesData $data): array
    {
        return Post::query()
            ->where(function (Builder $query) use ($data): void {
                $query
                    ->where('title', 'like', "%{$data->query}%")
                    ->orWhere('summary', 'like', "%{$data->query}%")
                    ->orWhere('body', 'like', "%{$data->query}%")
                    ->orWhere('slug', 'like', "%{$data->query}%");
            })
            ->when(
                $data->published !== null,
                fn (Builder $query): Builder => $query->where('published', $data->published),
            )
            ->latest()
            ->limit($data->limit)
            ->get()
            ->map(fn (Post $post): Article => $this->toDomain($post))
            ->values()
            ->all();
    }

    private function toDomain(Post $post): Article
    {
        return new Article(
            id: $post->getKey(),
            title: $post->title,
            slug: $post->slug,
            summary: $post->summary,
            body: $post->body,
            published: $post->published,
            createdAt: $post->created_at->toAtomString(),
            updatedAt: $post->updated_at->toAtomString(),
        );
    }
}
