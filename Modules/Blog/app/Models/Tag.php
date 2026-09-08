<?php

namespace Modules\Blog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Blog\Database\Factories\TagFactory;

#[Fillable([])]
class Tag extends Model
{
    use HasFactory;

    public function translations(): HasMany
    {
        return $this->hasMany(TagTranslation::class);
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }
}
