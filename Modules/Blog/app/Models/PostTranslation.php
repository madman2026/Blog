<?php

namespace Modules\Blog\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;
use Modules\Blog\Database\Factories\PostTranslationFactory;
use Modules\Blog\Enums\PostStatus;

#[Fillable(
    'post_id',
    'locale',
    'title',
    'slug',
    'summary',
    'body',
    'seo_title',
    'seo_description',
)]
#[RouteKey('slug')]
class PostTranslation extends Model
{
    use HasFactory;
    use Searchable;
    use Sluggable;

    /** @return array<string, array<string, mixed>> */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'unique' => true,
                'onUpdate' => false,
            ],
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function shouldBeSearchable(): bool
    {
        return $this->post->status === PostStatus::Published
            && $this->post->published_at?->isPast() === true;
    }

    /** @return array<string, int|string|null> */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->getKey(),
            'post_id' => $this->post_id,
            'locale' => $this->locale,
            'title' => $this->title,
            'summary' => $this->summary,
            'body' => strip_tags($this->body),
        ];
    }

    protected static function newFactory(): PostTranslationFactory
    {
        return PostTranslationFactory::new();
    }
}
