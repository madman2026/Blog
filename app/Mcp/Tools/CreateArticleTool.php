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
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Modules\Blog\Application\Articles\Actions\CreateArticle;
use Modules\Blog\Application\Articles\Data\CreateArticleData;

#[Name('create_article')]
#[Title('Create a blog article')]
#[Description('Create a blog article with an automatically generated unique slug. Articles are drafts by default.')]
#[IsDestructive(false)]
class CreateArticleTool extends Tool
{
    public function handle(Request $request, CreateArticle $createArticle): ResponseFactory
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'summary' => ['nullable', 'string'],
            'published' => ['nullable', 'boolean'],
        ]);

        $article = $createArticle->handle(new CreateArticleData(
            title: $validated['title'],
            body: $validated['body'],
            summary: $validated['summary'] ?? null,
            published: (bool) ($validated['published'] ?? false),
        ));

        return Response::structured(['article' => $article->toArray()]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()
                ->max(255)
                ->description('The article title. A unique slug is generated from it.')
                ->required(),
            'body' => $schema->string()
                ->description('The complete article body.')
                ->required(),
            'summary' => $schema->string()
                ->description('An optional short article summary.'),
            'published' => $schema->boolean()
                ->default(false)
                ->description('Whether to publish immediately. Defaults to false.'),
        ];
    }

    /** @return array<string, Type> */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'article' => ArticleSchema::make($schema)->required(),
        ];
    }
}
