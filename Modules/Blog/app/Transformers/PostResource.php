<?php

namespace Modules\Blog\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\User\Transformers\UserResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'type' => $this->type->value,
            'status' => $this->status->value,
            'author' => new UserResource($this->whenLoaded('author')),
            'translations' => $this->whenLoaded(
                'translations',
                fn () => $this->translations->map(fn ($translation): array => [
                    'locale' => $translation->locale,
                    'title' => $translation->title,
                    'slug' => $translation->slug,
                    'summary' => $translation->summary,
                    'body' => $translation->body,
                    'seo_title' => $translation->seo_title,
                    'seo_description' => $translation->seo_description,
                ])->values(),
            ),
            'categories' => $this->whenLoaded(
                'categories',
                fn () => $this->categories->map(fn ($category): array => [
                    'id' => $category->getKey(),
                    'translations' => $category->translations->map->only(['locale', 'name', 'slug']),
                ]),
            ),
            'tags' => $this->whenLoaded(
                'tags',
                fn () => $this->tags->map(fn ($tag): array => [
                    'id' => $tag->getKey(),
                    'translations' => $tag->translations->map->only(['locale', 'name', 'slug']),
                ]),
            ),
            'comments_count' => $this->whenCounted('comments'),
            'likes_count' => $this->whenCounted('likes'),
            'views_count' => $this->whenCounted('views'),
            'featured_image' => $this->whenLoaded('media', fn (): ?array => $this->hasMedia('featured_image') ? [
                'original' => $this->getFirstMediaUrl('featured_image'),
                'thumbnail' => $this->getFirstMediaUrl('featured_image', 'thumbnail'),
                'card' => $this->getFirstMediaUrl('featured_image', 'card'),
                'hero' => $this->getFirstMediaUrl('featured_image', 'hero'),
                'srcset' => $this->getFirstMedia('featured_image')?->getSrcset('hero'),
            ] : null),
            'review_notes' => $this->when(
                $request->user()?->can('update', $this->resource) === true,
                $this->review_notes,
            ),
            'submitted_at' => $this->submitted_at,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
