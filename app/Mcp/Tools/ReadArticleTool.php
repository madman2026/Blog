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
use Modules\Blog\Application\Articles\Actions\ReadArticle;
use Modules\Blog\Domain\Articles\ArticleIdentifier;

#[Name('read_article')]
#[Title('Read one blog article')]
#[Description('Retrieve one complete blog article by its numeric ID or slug.')]
#[IsReadOnly]
class ReadArticleTool extends Tool
{
    public function handle(Request $request, ReadArticle $readArticle): Response|ResponseFactory
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $article = $readArticle->handle(ArticleIdentifier::fromString($validated['identifier']));

        if ($article === null) {
            return Response::error("No article was found for identifier [{$validated['identifier']}].");
        }

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
            'identifier' => $schema->string()
                ->max(255)
                ->description('The article numeric ID or slug.')
                ->required(),
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
