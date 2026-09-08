<?php

use App\Mcp\Servers\CreateArticleServer;
use App\Mcp\Servers\ReadArticlesServer;
use App\Mcp\Servers\SearchArticlesServer;
use App\Mcp\Tools\CreateArticleTool;
use App\Mcp\Tools\ReadArticleTool;
use App\Mcp\Tools\SearchArticlesTool;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\PostTranslation;
use Modules\User\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\User\Enums\UserRole;
use Modules\User\Models\User;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);

    User::factory()->create([
        'email' => config('platform.super_user.email'),
    ])->assignRole(UserRole::SuperUser->value);
});

test('it creates a draft article through the writer server', function () {
    CreateArticleServer::tool(CreateArticleTool::class, [
        'title' => 'A practical MCP article',
        'body' => 'This article explains how local MCP tools work.',
        'summary' => 'An MCP introduction.',
        'locale' => 'en',
    ])
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json
            ->where('article.title', 'A practical MCP article')
            ->where('article.slug', 'a-practical-mcp-article')
            ->where('article.locale', 'en')
            ->where('article.published', false)
            ->etc());

    expect(Post::query()->whereHas(
        'translations',
        fn ($query) => $query->where('title', 'A practical MCP article'),
    )->exists())->toBeTrue();
});

test('it reads an article by slug through the reader server', function () {
    $post = Post::factory()->published()->create();
    $translation = PostTranslation::factory()->for($post)->create([
        'title' => 'Readable article',
        'slug' => 'readable-article',
        'locale' => 'en',
    ]);

    ReadArticlesServer::tool(ReadArticleTool::class, [
        'identifier' => $translation->slug,
    ])
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json
            ->where('article.id', $post->id)
            ->where('article.slug', 'readable-article')
            ->where('article.locale', 'en')
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
    $published = Post::factory()->published()->create();
    PostTranslation::factory()->for($published)->create([
        'title' => 'Laravel MCP published guide',
    ]);

    $private = Post::factory()->create();
    PostTranslation::factory()->for($private)->create([
        'title' => 'Laravel MCP private notes',
    ]);

    $unrelated = Post::factory()->published()->create();
    PostTranslation::factory()->for($unrelated)->create([
        'title' => 'Unrelated post',
        'body' => 'Nothing about the requested subject.',
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
