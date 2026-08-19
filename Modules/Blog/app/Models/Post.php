<?php

namespace Modules\Blog\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Blog\Database\Factories\PostFactory;

#[Fillable(
    'title',
    'body',
    'summary',
    'image',
    'slug',
    'published',
)]
class Post extends Model
{
    use HasFactory;
    use Sluggable;

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
        ];
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'unique' => true,
            ],
        ];
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(
            Category::class,
            'categoriable'
        );
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(
            Tag::class,
            'tagable'
        );
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
