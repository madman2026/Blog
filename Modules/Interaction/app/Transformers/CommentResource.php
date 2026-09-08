<?php

namespace Modules\Interaction\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Interaction\Models\Comment;
use Modules\User\Transformers\UserResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'body' => $this->body,
            'status' => $this->when(
                $request->user()?->getKey() === $this->user_id
                    || $request->user()?->can('viewAny', Comment::class) === true,
                $this->status->value,
            ),
            'user' => new UserResource($this->whenLoaded('user')),
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
