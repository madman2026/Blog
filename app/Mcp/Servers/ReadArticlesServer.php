<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\ReadArticleTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Blog Article Reader')]
#[Version('1.0.0')]
#[Instructions('Use read_article when the user asks to retrieve one blog article by its numeric ID or slug.')]
class ReadArticlesServer extends Server
{
    protected array $tools = [
        ReadArticleTool::class,
    ];
}
