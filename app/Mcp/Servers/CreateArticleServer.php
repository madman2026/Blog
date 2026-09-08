<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreateArticleTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Blog Article Writer')]
#[Version('1.0.0')]
#[Instructions('Use create_article only when the user explicitly asks to create a blog article. New articles are drafts unless published is true.')]
class CreateArticleServer extends Server
{
    protected array $tools = [
        CreateArticleTool::class,
    ];
}
