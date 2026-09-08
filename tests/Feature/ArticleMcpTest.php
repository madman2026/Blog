<?php

use App\Mcp\Servers\CreateArticleServer;
use App\Mcp\Servers\ReadArticlesServer;
use App\Mcp\Servers\SearchArticlesServer;
use App\Mcp\Tools\CreateArticleTool;
use App\Mcp\Tools\ReadArticleTool;
use App\Mcp\Tools\SearchArticlesTool;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Blog\Models\Post;

uses(LazilyRefreshDatabase::class);

test('it creates a draft article through the writer server', function () {
    CreateArticleServer::tool(CreateArticleTool::class, [
        'title' => 'A practical MCP article',
        'body' => 'This article explains how local MCP tools work.',
        'summary' => 'An MCP introduction.',
    ])
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json
            ->where('article.title', 'A practical MCP article')
            ->where('article.slug', 'a-practical-mcp-article')
            ->where('article.published', false)
            ->etc());

    $this->assertDatabaseHas('posts', [
        'title' => 'A practical MCP article',
        'published' => false,
    ]);
});

test('it reads an article by slug through the reader server', function () {
    $post = Post::factory()->create([
        'title' => 'Readable article',
        'slug' => 'readable-article',
        'published' => true,
    ]);

    ReadArticlesServer::tool(ReadArticleTool::class, [
        'identifier' => $post->slug,
    ])
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json
            ->where('article.id', $post->id)
            ->where('article.slug', 'readable-article')
            ->where('article.published', true)
            ->etc());
});

test('it reports a missing article through the reader server', function () {
    ReadArticlesServer::tool(ReadArticleTool::class, [
        'identifier' => 'missing-article',
    ])
        ->assertHasErrors()
        ->assertSee('No article was found');
});

test('it searches articles and filters publication status', function () {
    Post::factory()->create([
        'title' => 'Laravel MCP published guide',
        'published' => true,
    ]);

    Post::factory()->create([
        'title' => 'Laravel MCP private notes',
        'published' => false,
    ]);

    Post::factory()->create([
        'title' => 'Unrelated post',
        'body' => 'Nothing about the requested subject.',
        'published' => true,
    ]);

    SearchArticlesServer::tool(SearchArticlesTool::class, [
        'query' => 'Laravel MCP',
        'published' => true,
        'limit' => 10,
    ])
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json
            ->where('query', 'Laravel MCP')
            ->where('count', 1)
            ->has('articles', 1)
            ->where('articles.0.title', 'Laravel MCP published guide')
            ->etc());
});
