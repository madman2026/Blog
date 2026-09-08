<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'status' => $this->status->value,
            'user' => new UserResource($this->whenLoaded('user')),
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'review_notes' => $this->review_notes,
            'reviewed_at' => $this->reviewed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
