<?php

namespace Modules\Blog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Blog\Database\Factories\TagTranslationFactory;

#[Fillable('tag_id', 'locale', 'name', 'slug')]
#[RouteKey('slug')]
class TagTranslation extends Model
{
    use HasFactory;

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    protected static function newFactory(): TagTranslationFactory
    {
        return TagTranslationFactory::new();
    }
}
