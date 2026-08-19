<?php

namespace Modules\Blog\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Blog\Database\Factories\TagFactory;

#[Fillable('name')]
class Tag extends Model
{
    use HasFactory , Sluggable;

    public function Tagable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }
}
