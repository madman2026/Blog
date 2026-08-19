<?php

namespace Modules\Blog\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Blog\Database\Factories\CategoryFactory;

#[Fillable('name', 'slug')]
class Category extends Model
{
    use HasFactory , Sluggable;

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
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
