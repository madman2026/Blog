<?php

namespace Modules\Blog\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'parent_id' => $this->parent_id,
            'is_active' => $this->is_active,
            'translations' => $this->whenLoaded(
                'translations',
                fn () => $this->translations->map->only([
                    'locale',
                    'name',
                    'slug',
                    'description',
                ])->values(),
            ),
            'children_count' => $this->whenCounted('children'),
            'posts_count' => $this->whenCounted('posts'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
