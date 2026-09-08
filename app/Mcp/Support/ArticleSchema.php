<?php

namespace App\Mcp\Support;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\ObjectType;

final class ArticleSchema
{
    public static function make(JsonSchema $schema): ObjectType
    {
        return $schema->object([
            'id' => $schema->integer()->required(),
            'title' => $schema->string()->required(),
            'slug' => $schema->string()->required(),
            'summary' => $schema->string()->nullable(),
            'body' => $schema->string()->required(),
            'published' => $schema->boolean()->required(),
            'created_at' => $schema->string()->required(),
            'updated_at' => $schema->string()->required(),
        ])->withoutAdditionalProperties();
    }
}
