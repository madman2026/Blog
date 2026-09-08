<?php

namespace Modules\Blog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Blog\Database\Factories\CategoryTranslationFactory;

#[Fillable('category_id', 'locale', 'name', 'slug', 'description')]
#[RouteKey('slug')]
class CategoryTranslation extends Model
{
    use HasFactory;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected static function newFactory(): CategoryTranslationFactory
    {
        return CategoryTranslationFactory::new();
    }
}
