<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'type' => class_basename($this->resource->type),
            'payload' => $this->resource->data,
            'read_at' => $this->resource->read_at,
            'created_at' => $this->resource->created_at,
        ];
    }
}
