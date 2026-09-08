<?php

use App\Mcp\Servers\CreateArticleServer;
use App\Mcp\Servers\ReadArticlesServer;
use App\Mcp\Servers\SearchArticlesServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('blog-article-reader', ReadArticlesServer::class);
Mcp::local('blog-article-search', SearchArticlesServer::class);
Mcp::local('blog-article-writer', CreateArticleServer::class);
