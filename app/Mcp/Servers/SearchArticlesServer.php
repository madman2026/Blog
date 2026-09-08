<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\SearchArticlesTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Blog Article Search')]
#[Version('1.0.0')]
#[Instructions('Use search_articles to find blog articles whose title, summary, body, or slug contains a query.')]
class SearchArticlesServer extends Server
{
    protected array $tools = [
        SearchArticlesTool::class,
    ];
}
