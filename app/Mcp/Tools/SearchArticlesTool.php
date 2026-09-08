<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\ArticleSchema;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Modules\Blog\Application\Articles\Actions\SearchArticles;
use Modules\Blog\Application\Articles\Data\SearchArticlesData;
use Modules\Blog\Domain\Articles\Article;

#[Name('search_articles')]
#[Title('Search blog articles')]
#[Description('Search article titles, summaries, bodies, and slugs. Optionally filter by publication status.')]
#[IsReadOnly]
class SearchArticlesTool extends Tool
{
    public function handle(Request $request, SearchArticles $searchArticles): ResponseFactory
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'between:1,50'],
            'published' => ['nullable', 'boolean'],
        ]);

        $criteria = new SearchArticlesData(
            query: $validated['query'],
            limit: (int) ($validated['limit'] ?? 10),
            published: array_key_exists('published', $validated) ? (bool) $validated['published'] : null,
        );

        $articles = array_map(
            static fn (Article $article): array => $article->toArray(),
            $searchArticles->handle($criteria),
        );

        return Response::structured([
            'query' => $criteria->query,
            'count' => count($articles),
            'articles' => $articles,
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->max(255)
                ->description('Text to find in article title, summary, body, or slug.')
                ->required(),
            'limit' => $schema->integer()
                ->min(1)
                ->max(50)
                ->default(10)
                ->description('Maximum number of matching articles to return.'),
            'published' => $schema->boolean()
                ->description('Optional publication-status filter. Omit it to search all articles.'),
        ];
    }

    /** @return array<string, Type> */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required(),
            'count' => $schema->integer()->required(),
            'articles' => $schema->array()
                ->items(ArticleSchema::make($schema))
                ->required(),
        ];
    }
}
