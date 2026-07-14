<?php

namespace Modules\Article\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Article\Database\Factories\ArticleFactory;
use Modules\User\Models\User;

#[Fillable(['title' , 'slug' , 'summery' , 'body' , 'image'])]
class Article extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    protected static function newFactory(): ArticleFactory
    {
        return ArticleFactory::new();
    }
}
